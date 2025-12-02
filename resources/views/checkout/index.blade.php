@extends('layouts.app')

@section('title', 'Checkout - e-License')

@section('styles')
<style>
    .payment-channel {
        cursor: pointer;
        transition: all 0.3s;
    }
    .payment-channel:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .payment-channel.selected {
        border-color: #0d6efd !important;
        background-color: rgba(13, 110, 253, 0.05);
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-shopping-cart"></i> Ringkasan Pesanan</h4>
            </div>
            <div class="card-body">
                @foreach($items as $item)
                <div class="row mb-3 border-bottom pb-3">
                    <div class="col-md-8">
                        <h6>{{ $item['product']->name }}</h6>
                        <small class="text-muted">Quantity: {{ $item['quantity'] }}</small>
                    </div>
                    <div class="col-md-4 text-end">
                        <strong>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</strong>
                    </div>
                </div>
                @endforeach
                
                <div class="row mt-3">
                    <div class="col-md-8">
                        <h5>Total</h5>
                    </div>
                    <div class="col-md-4 text-end">
                        <h4 class="text-primary">Rp {{ number_format($total, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <form method="POST" action="{{ route('checkout.process') }}" id="checkoutForm">
            @csrf
            
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-credit-card"></i> Metode Pembayaran</h4>
                </div>
                <div class="card-body">
                    <input type="hidden" name="payment_method" id="selectedPaymentMethod" required>
                    
                    <div class="mb-3">
                        @foreach($paymentChannels as $channel)
                        <div class="payment-channel card mb-2 p-3 border" 
                             data-code="{{ $channel['code'] }}"
                             data-name="{{ $channel['name'] }}">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" 
                                       name="payment_method_radio" 
                                       id="method_{{ $channel['code'] }}"
                                       value="{{ $channel['code'] }}">
                                <label class="form-check-label d-flex align-items-center" 
                                       for="method_{{ $channel['code'] }}">
                                    @if($channel['icon_url'])
                                        <img src="{{ $channel['icon_url'] }}" 
                                             alt="{{ $channel['name'] }}" 
                                             style="height: 24px; margin-right: 10px;">
                                    @endif
                                    <div>
                                        <strong>{{ $channel['name'] }}</strong>
                                        @if(isset($channel['fee_merchant']))
                                        @php
                                            $fee = $channel['fee_merchant'];
                                            // Handle array or object fee structure
                                            if (is_array($fee)) {
                                                $fee = $fee['flat'] ?? $fee['merchant'] ?? $fee[0] ?? 0;
                                            }
                                            $fee = (float) $fee;
                                        @endphp
                                        <br>
                                        <small class="text-muted">
                                            Biaya: Rp {{ number_format($fee, 0, ',', '.') }}
                                        </small>
                                    @endif
                                    </div>
                                </label>
                            </div>
                        </div>
                        @endforeach
                        
                        @if(empty($paymentChannels))
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Tidak ada metode pembayaran yang tersedia saat ini.
                        </div>
                        @endif
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Informasi Penting:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Lisensi akan dikirim otomatis setelah pembayaran berhasil</li>
                            <li>Batasan waktu pembayaran: 24 jam</li>
                            <li>Garansi penggantian: 7 hari jika lisensi bermasalah</li>
                        </ul>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg" id="checkoutBtn">
                            <i class="fas fa-lock"></i> Bayar Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </form>
        
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-user"></i> Informasi Pembeli</h4>
            </div>
            <div class="card-body">
                <p><strong>Nama:</strong> {{ Auth::user()->name }}</p>
                <p><strong>Username:</strong> {{ Auth::user()->username }}</p>
                @if(Auth::user()->email)
                <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                @endif
                @if(Auth::user()->phone)
                <p><strong>Telepon:</strong> {{ Auth::user()->phone }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Payment channel selection
    $('.payment-channel').click(function() {
        $('.payment-channel').removeClass('selected');
        $(this).addClass('selected');
        
        const code = $(this).data('code');
        const name = $(this).data('name');
        
        $('#selectedPaymentMethod').val(code);
        $('#checkoutBtn').prop('disabled', false);
        $('#checkoutBtn').html(`<i class="fas fa-lock"></i> Bayar dengan ${name}`);
    });
    
    // Radio button synchronization
    $('input[name="payment_method_radio"]').change(function() {
        const code = $(this).val();
        $(`.payment-channel[data-code="${code}"]`).click();
    });
    
    // Form submission
    $('#checkoutForm').submit(function(e) {
        if (!$('#selectedPaymentMethod').val()) {
            e.preventDefault();
            alert('Pilih metode pembayaran terlebih dahulu.');
            return false;
        }
        
        // Disable button and show loading
        $('#checkoutBtn').prop('disabled', true).html(
            '<i class="fas fa-spinner fa-spin"></i> Memproses...'
        );
    });
});
</script>
@endsection
