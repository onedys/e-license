@extends('layouts.app')

@section('title', 'Keranjang Belanja - e-License')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h4>
            </div>
            <div class="card-body">
                @if(empty($cart))
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                        <h5>Keranjang Belanja Kosong</h5>
                        <p class="text-muted">Tambahkan produk ke keranjang untuk memulai belanja.</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary">
                            <i class="fas fa-store"></i> Belanja Sekarang
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $total = 0; @endphp
                                @foreach($cart as $item)
                                @php
                                    $subtotal = $item['price'] * $item['quantity'];
                                    $total += $subtotal;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item['image'])
                                            <img src="{{ asset('storage/' . $item['image']) }}" 
                                                 alt="{{ $item['name'] }}" 
                                                 style="width: 60px; height: 60px; object-fit: cover; margin-right: 15px;">
                                            @else
                                            <div style="width: 60px; height: 60px; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                                                <i class="fas fa-box text-muted"></i>
                                            </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-1">{{ $item['name'] }}</h6>
                                                <small class="text-muted">ID: {{ $item['id'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('cart.update', $item['id']) }}" class="d-inline">
                                            @csrf
                                            <div class="input-group input-group-sm" style="width: 120px;">
                                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" 
                                                       min="1" max="10" class="form-control">
                                                <button type="submit" class="btn btn-outline-primary">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                    <td>Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('cart.remove', $item['id']) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus dari keranjang?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td colspan="2">
                                        <h5 class="text-primary mb-0">Rp {{ number_format($total, 0, ',', '.') }}</h5>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Lanjutkan Belanja
                        </a>
                        <div>
                            <form method="POST" action="{{ route('cart.clear') }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger me-2" onclick="return confirm('Kosongkan keranjang?')">
                                    <i class="fas fa-trash"></i> Kosongkan Keranjang
                                </button>
                            </form>
                            <a href="{{ route('checkout.index') }}" class="btn btn-primary">
                                <i class="fas fa-credit-card"></i> Lanjut ke Pembayaran
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        @if(!empty($cart))
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Penting</h5>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Setelah pembayaran berhasil, lisensi akan otomatis dikirim ke dashboard Anda</li>
                    <li>Masa garansi: 7 hari dari tanggal pembelian</li>
                    <li>Garansi hanya berlaku untuk lisensi dengan status "Key Blocked"</li>
                    <li>Setiap lisensi membutuhkan Installation ID yang berbeda untuk aktivasi</li>
                </ul>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-receipt"></i> Ringkasan Belanja</h5>
            </div>
            <div class="card-body">
                @if(empty($cart))
                    <p class="text-muted mb-0">Belum ada item dalam keranjang</p>
                @else
                    <table class="table table-sm">
                        @foreach($cart as $item)
                        <tr>
                            <td>{{ $item['name'] }} (x{{ $item['quantity'] }})</td>
                            <td class="text-end">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="table-primary">
                            <td><strong>Total</strong></td>
                            <td class="text-end"><strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></td>
                        </tr>
                    </table>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-shipping-fast"></i>
                        <strong>Pengiriman Digital</strong><br>
                        Lisensi akan dikirim otomatis setelah pembayaran
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Garansi & Support</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Garansi 7 Hari</strong><br>
                    Klaim garansi hanya berlaku untuk lisensi dengan status "Key Blocked"
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-question-circle"></i>
                    <strong>Butuh Bantuan?</strong><br>
                    Hubungi admin melalui dashboard jika ada masalah
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Update quantity dengan enter
    $('input[name="quantity"]').keypress(function(e) {
        if (e.which === 13) {
            $(this).closest('form').submit();
        }
    });
});
</script>
@endsection