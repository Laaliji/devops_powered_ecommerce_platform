<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('tenant_id', auth()->user()->current_tenant_id)
            ->with('category')
            ->get();

        return inertia('Products/Index', ['products' => $products]);
    }

    public function show(Product $product)
    {
        return inertia('Products/Show', ['product' => $product]);
    }
}
