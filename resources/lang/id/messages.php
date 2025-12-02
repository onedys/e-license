<?php

return [
    // Authentication
    'auth' => [
        'failed' => 'Username atau password salah.',
        'throttle' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam :seconds detik.',
        'logout' => 'Berhasil logout.',
    ],
    
    // Registration
    'registration' => [
        'success' => 'Registrasi berhasil! Silakan login.',
        'username_taken' => 'Username sudah digunakan.',
        'password_reset' => 'Password berhasil direset.',
    ],
    
    // Products
    'products' => [
        'added_to_cart' => 'Produk berhasil ditambahkan ke keranjang.',
        'out_of_stock' => 'Stok produk habis.',
        'not_found' => 'Produk tidak ditemukan.',
    ],
    
    // Cart
    'cart' => [
        'empty' => 'Keranjang belanja kosong.',
        'updated' => 'Keranjang berhasil diperbarui.',
        'cleared' => 'Keranjang berhasil dikosongkan.',
        'item_removed' => 'Produk berhasil dihapus dari keranjang.',
    ],
    
    // Checkout
    'checkout' => [
        'success' => 'Pesanan berhasil dibuat.',
        'payment_redirect' => 'Anda akan diarahkan ke halaman pembayaran.',
        'payment_success' => 'Pembayaran berhasil! Lisensi sedang diproses.',
        'payment_failed' => 'Pembayaran gagal. Silakan coba lagi.',
        'payment_pending' => 'Pembayaran sedang diproses.',
    ],
    
    // Licenses
    'licenses' => [
        'assigned' => 'Lisensi berhasil dikirim ke dashboard Anda.',
        'activation_success' => 'Aktivasi berhasil! Confirmation ID: :cid',
        'activation_failed' => 'Aktivasi gagal: :error',
        'activation_blocked' => 'Key blocked! Silakan klaim garansi.',
        'activation_error' => 'Error sistem. Silakan coba lagi. Kuota tidak berkurang.',
        'license_not_found' => 'Lisensi tidak ditemukan.',
        'license_invalid' => 'Lisensi tidak valid.',
        'license_expired' => 'Masa aktif lisensi sudah habis.',
    ],
    
    // Activation
    'activation' => [
        'installation_id_invalid' => 'Installation ID harus 54 atau 63 digit angka.',
        'installation_id_used' => 'Installation ID ini sudah digunakan untuk lisensi lain.',
        'quota_reduced' => 'Kuota aktivasi berkurang.',
        'already_activated' => 'Lisensi ini sudah diaktivasi sebelumnya.',
        'max_attempts' => 'Anda telah mencapai batas percobaan aktivasi.',
    ],
    
    // Warranty
    'warranty' => [
        'claim_success' => 'Klaim garansi berhasil! Lisensi pengganti telah dikirim.',
        'claim_failed' => 'Klaim garansi gagal: :reason',
        'not_eligible' => 'Lisensi tidak eligible untuk garansi.',
        'warranty_expired' => 'Masa garansi sudah habis.',
        'already_replaced' => 'Lisensi ini sudah diganti sebelumnya.',
        'max_claims_reached' => 'Anda telah mencapai batas klaim garansi.',
        'pending_approval' => 'Klaim garansi menunggu persetujuan admin.',
        'approved' => 'Klaim garansi disetujui.',
        'rejected' => 'Klaim garansi ditolak.',
    ],
    
    // Admin
    'admin' => [
        'license_uploaded' => ':count lisensi berhasil diupload.',
        'license_validated' => 'Lisensi berhasil divalidasi.',
        'license_deleted' => 'Lisensi berhasil dihapus.',
        'user_updated' => 'User berhasil diperbarui.',
        'order_updated' => 'Order berhasil diperbarui.',
        'warranty_approved' => 'Klaim garansi disetujui.',
        'warranty_rejected' => 'Klaim garansi ditolak.',
        'product_created' => 'Produk berhasil dibuat.',
        'product_updated' => 'Produk berhasil diperbarui.',
        'product_deleted' => 'Produk berhasil dihapus.',
    ],
    
    // Errors
    'errors' => [
        'csrf' => 'Session expired. Please refresh the page.',
        '404' => 'Halaman tidak ditemukan.',
        '500' => 'Terjadi kesalahan server.',
        '403' => 'Akses ditolak.',
        '429' => 'Terlalu banyak request. Silakan coba lagi nanti.',
        'maintenance' => 'Sistem sedang dalam perawatan.',
    ],
    
    // Success messages
    'success' => [
        'saved' => 'Data berhasil disimpan.',
        'updated' => 'Data berhasil diperbarui.',
        'deleted' => 'Data berhasil dihapus.',
        'copied' => 'Berhasil disalin ke clipboard.',
    ],
    
    // Validation messages
    'validation' => [
        'required' => 'Field :attribute wajib diisi.',
        'unique' => ':attribute sudah digunakan.',
        'email' => ':attribute harus berupa email yang valid.',
        'min' => ':attribute minimal :min karakter.',
        'max' => ':attribute maksimal :max karakter.',
        'confirmed' => 'Konfirmasi :attribute tidak cocok.',
        'exists' => ':attribute tidak valid.',
        'regex' => 'Format :attribute tidak valid.',
    ],
    
    // Attributes
    'attributes' => [
        'username' => 'username',
        'password' => 'password',
        'name' => 'nama',
        'email' => 'email',
        'phone' => 'telepon',
        'license_key' => 'kunci lisensi',
        'installation_id' => 'installation ID',
        'order_number' => 'nomor order',
        'product_id' => 'produk',
        'payment_method' => 'metode pembayaran',
    ],
];