<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::active()
            ->withCount('licensePools')
            ->get();
        
        return view('products.index', compact('products'));
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->active()
            ->firstOrFail();
        
        return view('products.show', compact('product'));
    }
}