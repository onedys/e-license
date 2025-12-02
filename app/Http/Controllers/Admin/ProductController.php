<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::withCount(['orders', 'licensePools'])->latest()->paginate(20);
        
        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'total_sales' => Product::withSum('orders', 'total_amount')->get()->sum('orders_sum_total_amount'),
        ];
        
        return view('admin.products.index', compact('products', 'stats'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
            'stock_type' => 'required|in:1,2',
            'available_stock' => 'nullable|integer|min:0',
            'features' => 'nullable|string',
        ]);
        
        $product = Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'category' => $request->category,
            'stock_type' => $request->stock_type,
            'available_stock' => $request->stock_type == 2 ? $request->available_stock : null,
            'features' => $request->features ? explode(',', $request->features) : null,
            'is_active' => true,
        ]);
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
            'stock_type' => 'required|in:1,2',
            'available_stock' => 'nullable|integer|min:0',
            'features' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        $product->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'category' => $request->category,
            'stock_type' => $request->stock_type,
            'available_stock' => $request->stock_type == 2 ? $request->available_stock : null,
            'features' => $request->features ? explode(',', $request->features) : null,
            'is_active' => $request->has('is_active'),
        ]);
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->orders()->exists()) {
            return back()->with('error', 'Cannot delete product with existing orders.');
        }
        
        $product->delete();
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}