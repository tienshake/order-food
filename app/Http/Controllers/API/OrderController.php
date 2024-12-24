<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1'
        ]);

        $total = 0;
        foreach ($validated['products'] as $item) {
            $product = Product::find($item['product_id']);
            $total += $product->price * $item['quantity'];
        }

        $order = DB::transaction(function () use ($validated, $total) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'status' => 'pending',
                'order_date' => now(),
                'total_amount' => $total
            ]);

            // Tạo order details
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

        return response()->json([
            'success' => true,
            'data' => $order->load('orderDetails'),
            'message' => 'Order created successfully'
        ]);
    }
}
