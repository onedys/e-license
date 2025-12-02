// Activation Page JavaScript

$(document).ready(function() {
    let selectedLicenseId = null;
    let selectedLicenseKey = null;
    let selectedOrderNumber = null;
    
    // License selection
    $('.license-option').click(function() {
        $('.license-option').removeClass('selected');
        $(this).addClass('selected');
        
        const radio = $(this).find('input[type="radio"]');
        radio.prop('checked', true);
        
        selectedLicenseId = $(this).data('id');
        selectedLicenseKey = $(this).data('license-key');
        selectedOrderNumber = $(this).data('order-number');
        
        // Update hidden fields
        $('#selectedLicenseKey').val(selectedLicenseKey);
        $('#selectedOrderNumber').val(selectedOrderNumber);
        
        checkFormValidity();
    });
    
    // Auto-select if there's only one license
    if ($('.license-option').length === 1) {
        $('.license-option').first().click();
    }
    
    // Installation ID validation
    $('#installation_id').on('input', function() {
        const value = $(this).val();
        const digitsOnly = value.replace(/\D/g, '');
        
        // Auto-format if user types dashes or spaces
        if (value !== digitsOnly) {
            $(this).val(digitsOnly);
        }
        
        // Show format hint
        const length = digitsOnly.length;
        const formatHint = $('#formatHint');
        
        if (length === 54 || length === 63) {
            formatHint.removeClass('text-danger').addClass('text-success')
                .html('<i class="fas fa-check-circle"></i> Format valid');
        } else if (length > 0) {
            formatHint.removeClass('text-success').addClass('text-danger')
                .html('<i class="fas fa-times-circle"></i> Harus 54 atau 63 digit');
        } else {
            formatHint.removeClass('text-danger text-success').html('');
        }
        
        checkFormValidity();
    });
    
    // Form validation
    function checkFormValidity() {
        const installationId = $('#installation_id').val().trim();
        const confirmationChecked = $('#confirmationCheck').is(':checked');
        const hasLicense = selectedLicenseId !== null;
        const isValidLength = [54, 63].includes(installationId.replace(/\D/g, '').length);
        
        const isValid = hasLicense && 
                       installationId.length > 0 && 
                       isValidLength &&
                       confirmationChecked;
        
        $('#activateBtn').prop('disabled', !isValid);
    }
    
    $('#confirmationCheck').change(checkFormValidity);
    
    // Check installation ID availability
    $('#checkInstallationIdBtn').click(function() {
        const installationId = $('#installation_id').val().trim();
        
        if (!installationId) {
            toastr.warning('Masukkan Installation ID terlebih dahulu');
            return;
        }
        
        const digitsOnly = installationId.replace(/\D/g, '');
        if (![54, 63].includes(digitsOnly.length)) {
            toastr.error('Installation ID harus 54 atau 63 digit angka');
            return;
        }
        
        eLicense.showLoading($(this));
        
        $.ajax({
            url: $('#checkInstallationIdUrl').val(),
            type: 'POST',
            data: {
                installation_id: installationId
            },
            success: function(response) {
                if (response.valid) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Gagal memeriksa. Coba lagi.');
            },
            complete: function() {
                eLicense.hideLoading($('#checkInstallationIdBtn'));
            }
        });
    });
    
    // Form submission
    $('#activationForm').submit(function(e) {
        if (!selectedLicenseId) {
            e.preventDefault();
            toastr.error('Pilih lisensi terlebih dahulu');
            return false;
        }
        
        const installationId = $('#installation_id').val().trim();
        const digitsOnly = installationId.replace(/\D/g, '');
        
        if (![54, 63].includes(digitsOnly.length)) {
            e.preventDefault();
            toastr.error('Installation ID harus 54 atau 63 digit angka');
            return false;
        }
        
        // Show loading
        eLicense.showLoading($('#activateBtn'));
    });
    
    // Auto-focus installation ID field when license is selected
    $(document).on('licenseSelected', function() {
        $('#installation_id').focus();
    });
    
    // Trigger custom event when license is selected
    $('.license-option').click(function() {
        $(document).trigger('licenseSelected');
    });
});

// Function to show activation result modal
function showActivationResult(success, message, cid = null) {
    let html = '';
    
    if (success) {
        html = `
            <div class="text-center">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h5>Aktivasi Berhasil!</h5>
                <p>${message}</p>
                <div class="alert alert-success">
                    <strong>Confirmation ID:</strong><br>
                    <code id="confirmationIdText" class="d-block p-2 mt-2">${cid}</code>
                </div>
                <p>Salin Confirmation ID untuk aktivasi Windows/Office.</p>
            </div>
        `;
    } else {
        html = `
            <div class="text-center">
                <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                <h5>Aktivasi Gagal</h5>
                <div class="alert alert-danger">
                    ${message}
                </div>
                <p>Silakan coba lagi atau hubungi admin jika masalah berlanjut.</p>
            </div>
        `;
    }
    
    $('#activationResultContent').html(html);
    
    // Store CID for copy function
    if (cid) {
        $('#activationResultModal').data('cid', cid);
    }
    
    $('#activationResultModal').modal('show');
}

// Function to copy confirmation ID
function copyConfirmationId() {
    const cid = $('#activationResultModal').data('cid');
    if (cid) {
        eLicense.copyToClipboard(cid, 'Confirmation ID berhasil disalin!');
    }
}