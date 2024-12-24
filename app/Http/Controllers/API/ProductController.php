<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::when(
            $request->category_id,
            fn($q) => $q->where('category_id', $request->category_id)
        )->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
}
