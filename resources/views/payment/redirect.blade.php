@extends('layouts.app')

@section('title', 'Redirect ke Pembayaran - e-License')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card text-center">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-credit-card"></i> Menuju Halaman Pembayaran</h4>
            </div>
            
            <div class="card-body">
                <div class="mb-4">
                    <i class="fas fa-external-link-alt fa-5x text-primary mb-3"></i>
                    <h5>Order #{{ $order->order_number }}</h5>
                    <p class="text-muted">Total: <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></p>
                </div>
                
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle"></i>
                    Anda akan diarahkan ke halaman pembayaran Tripay. 
                    Silakan selesaikan pembayaran sesuai instruksi.
                </div>
                
                <div class="mb-4">
                    <p>Jika tidak otomatis redirect dalam 10 detik, klik tombol di bawah:</p>
                    
                    <a href="{{ $checkoutUrl }}" class="btn btn-primary btn-lg" target="_blank" id="redirectBtn">
                        <i class="fas fa-external-link-alt"></i> Ke Halaman Pembayaran
                    </a>
                </div>
                
                <div class="text-start">
                    <h6><i class="fas fa-lightbulb"></i> Tips:</h6>
                    <ul>
                        <li>Selesaikan pembayaran sebelum waktu habis</li>
                        <li>Jangan tutup halaman ini selama proses pembayaran</li>
                        <li>Setelah pembayaran, lisensi akan muncul di dashboard</li>
                        <li>Jika ada masalah, hubungi admin</li>
                    </ul>
                </div>
            </div>
            
            <div class="card-footer">
                <small class="text-muted">
                    <i class="fas fa-shield-alt"></i> Pembayaran aman diproses oleh Tripay
                </small>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-redirect after 3 seconds
    setTimeout(function() {
        window.location.href = "{{ $checkoutUrl }}";
    }, 3000);
    
    // Monitor payment status
    function checkPaymentStatus() {
        $.ajax({
            url: "{{ route('payment.checkStatus', $order->order_number) }}",
            type: 'GET',
            success: function(response) {
                if (response.status === 'paid') {
                    // Redirect to success page
                    window.location.href = "{{ route('checkout.success', $order->order_number) }}";
                }
            }
        });
    }
    
    // Check every 10 seconds
    setInterval(checkPaymentStatus, 10000);
});
</script>
@endsection