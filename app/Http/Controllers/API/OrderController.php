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

        $order = DB::transaction(function () use ($validated) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'status' => 'pending',
                'order_date' => now()
            ]);

            $total = 0;
            foreach ($validated['products'] as $item) {
                $product = Product::find($item['product_id']);
                $price = $product->price * $item['quantity'];
                $total += $price;

                $order->orderDetails()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->price
                ]);
            }

            $order->update(['total_amount' => $total]);
            return $order;
        });

        return response()->json([
            'success' => true,
            'data' => $order->load('orderDetails'),
            'message' => 'Order created successfully'
        ]);
    }
}
