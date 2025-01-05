<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function createCheckoutSession(Request $request)
    {
        $validated = $request->validate([
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1'
        ]);

        try {
            // Tính tổng tiền và lấy thông tin sản phẩm
            $items = [];
            $total = 0;
            foreach ($validated['products'] as $item) {
                $product = Product::find($item['product_id']);
                $total += $product->price * $item['quantity'];

                // Tạo line item cho Stripe
                $items[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $product->name,
                            'images' => [$product->image], // nếu có
                        ],
                        'unit_amount' => $product->price * 100, // Stripe uses cents
                    ],
                    'quantity' => $item['quantity'],
                ];
            }

            // Tạo order trong trạng thái pending
            $order = DB::transaction(function () use ($validated, $total) {
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'status' => 'pending',
                    'order_date' => now(),
                    'total_amount' => $total
                ]);

                foreach ($validated['products'] as $item) {
                    $product = Product::find($item['product_id']);
                    $order->orderDetails()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $product->price
                    ]);
                }

                return $order;
            });

            // Tạo Stripe Checkout Session
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $items,
                'mode' => 'payment',
                'success_url' => 'http://localhost:3000/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => 'http://localhost:3000/cancel',
                'metadata' => [
                    'order_id' => $order->id
                ]
            ]);

            // Cập nhật order với payment_intent_id
            $order->update([
                'session_id' => $session->id,                // Lưu session_id 
                'payment_intent_id' => $session->payment_intent  // Vẫn giữ cái này
            ]);

            return response()->json([
                'url' => $session->url
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Webhook để xử lý kết quả thanh toán
    public function webhook(Request $request)
    {
        Log::info('Webhook Called Start');  // Log 1

        $endpoint_secret = config('services.stripe.webhook_secret');
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );

            Log::info('Event Type:', ['type' => $event->type]);  // Log 2

            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;

                    Log::info('Session Data:', [
                        'session_id' => $session->id
                    ]);

                    // Tìm và cập nhật trạng thái order
                    $order = Order::where('session_id', $session->id)->first();
                    if ($order) {
                        $order->update([
                            'status' => 'paid'
                        ]);
                    }
                    break;
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Webhook Error:', [  // Log error
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // API lấy thông tin đơn hàng sau khi thanh toán thành công
    public function getPaymentSuccess(Request $request)
    {
        $sessionId = $request->query('session_id');

        Stripe::setApiKey(config('services.stripe.secret'));
        $session = Session::retrieve($sessionId);

        // Tìm theo session_id trước
        $order = Order::where('session_id', $sessionId)
            ->with('orderDetails.product')
            ->first();

        // Nếu không tìm thấy, thử tìm theo payment_intent_id
        if (!$order) {
            $order = Order::where('payment_intent_id', $session->payment_intent)
                ->with('orderDetails.product')
                ->first();
        }

        return response()->json([
            'order' => $order,
            'payment_status' => $session->payment_status,
            'customer_email' => $session->customer_details->email
        ]);
    }
}
