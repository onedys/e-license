<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        return view('cart.index', compact('cart', 'total'));
    }

    public function add(Request $request, $productId)
    {
        $product = Product::active()->findOrFail($productId);
        
        $cart = session()->get('cart', []);
        
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $request->quantity ?? 1;
        } else {
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $request->quantity ?? 1,
                'image' => $product->image,
            ];
        }
        
        session()->put('cart', $cart);
        
        return redirect()->back()
            ->with('success', 'Produk berhasil ditambahkan ke keranjang');
    }

    public function update(Request $request, $productId)
    {
        $cart = session()->get('cart', []);
        
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $request->quantity;
            session()->put('cart', $cart);
        }
        
        return redirect()->back()
            ->with('success', 'Keranjang berhasil diperbarui');
    }

    public function remove($productId)
    {
        $cart = session()->get('cart', []);
        
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
        }
        
        return redirect()->back()
            ->with('success', 'Produk berhasil dihapus dari keranjang');
    }

    public function clear()
    {
        session()->forget('cart');
        
        return redirect()->route('products.index')
            ->with('success', 'Keranjang berhasil dikosongkan');
    }
}