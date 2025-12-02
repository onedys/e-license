// Warranty Claim Page JavaScript

$(document).ready(function() {
    let selectedLicenseKey = null;
    
    // License selection
    $('.license-option').click(function() {
        $('.license-option').removeClass('selected');
        $(this).addClass('selected');
        
        const radio = $(this).find('input[type="radio"]');
        radio.prop('checked', true);
        
        selectedLicenseKey = $(this).data('license-key');
        $('#selectedLicenseKey').val(selectedLicenseKey);
        
        // Show license details
        showLicenseDetails($(this).data('details'));
        
        checkFormValidity();
    });
    
    // Auto-select if there's only one license
    if ($('.license-option').length === 1) {
        $('.license-option').first().click();
    }
    
    // Show license details
    function showLicenseDetails(details) {
        const detailsHtml = `
            <div class="card mt-3">
                <div class="card-body">
                    <h6>Detail Lisensi</h6>
                    <table class="table table-sm">
                        <tr>
                            <td width="40%">Lisensi</td>
                            <td><code>${details.license_key}</code></td>
                        </tr>
                        <tr>
                            <td>Produk</td>
                            <td>${details.product_name}</td>
                        </tr>
                        <tr>
                            <td>Order</td>
                            <td>${details.order_number}</td>
                        </tr>
                        <tr>
                            <td>Masa Garansi</td>
                            <td>${details.warranty_until}</td>
                        </tr>
                        <tr>
                            <td>Sisa Waktu</td>
                            <td><span class="badge bg-success">${details.warranty_remaining}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        `;
        
        $('#licenseDetails').html(detailsHtml);
    }
    
    // Form validation
    function checkFormValidity() {
        const confirmationChecked = $('#confirmationCheck').is(':checked');
        const hasLicense = selectedLicenseKey !== null;
        
        $('#claimBtn').prop('disabled', !(confirmationChecked && hasLicense));
    }
    
    $('#confirmationCheck').change(checkFormValidity);
    
    // Form submission
    $('#warrantyForm').submit(function(e) {
        if (!selectedLicenseKey) {
            e.preventDefault();
            toastr.error('Pilih lisensi terlebih dahulu');
            return false;
        }
        
        if (!confirm('Yakin ingin mengklaim garansi untuk lisensi ini?')) {
            e.preventDefault();
            return false;
        }
        
        // Show loading
        eLicense.showLoading($('#claimBtn'));
    });
    
    // Check warranty eligibility
    $('#checkEligibilityBtn').click(function() {
        if (!selectedLicenseKey) {
            toastr.warning('Pilih lisensi terlebih dahulu');
            return;
        }
        
        eLicense.showLoading($(this));
        
        $.ajax({
            url: $('#checkEligibilityUrl').val(),
            type: 'POST',
            data: {
                license_key: selectedLicenseKey
            },
            success: function(response) {
                if (response.eligible) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Gagal memeriksa kelayakan');
            },
            complete: function() {
                eLicense.hideLoading($('#checkEligibilityBtn'));
            }
        });
    });
});

// Function to show warranty claim result
function showWarrantyResult(success, message, replacementKey = null) {
    let html = '';
    
    if (success) {
        html = `
            <div class="text-center">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h5>Klaim Garansi Berhasil!</h5>
                <p>${message}</p>
                <div class="alert alert-success">
                    <strong>Lisensi Pengganti:</strong><br>
                    <code id="replacementKeyText" class="d-block p-2 mt-2">${replacementKey}</code>
                </div>
                <p>Lisensi baru telah ditambahkan ke dashboard Anda.</p>
            </div>
        `;
    } else {
        html = `
            <div class="text-center">
                <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                <h5>Klaim Garansi Gagal</h5>
                <div class="alert alert-danger">
                    ${message}
                </div>
                <p>Silakan hubungi admin untuk bantuan lebih lanjut.</p>
            </div>
        `;
    }
    
    $('#warrantyResultContent').html(html);
    
    // Store replacement key for copy function
    if (replacementKey) {
        $('#warrantyResultModal').data('replacementKey', replacementKey);
    }
    
    $('#warrantyResultModal').modal('show');
}

// Function to copy replacement key
function copyReplacementKey() {
    const key = $('#warrantyResultModal').data('replacementKey');
    if (key) {
        eLicense.copyToClipboard(key, 'Lisensi berhasil disalin!');
    }
}