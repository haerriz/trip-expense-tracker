$(document).ready(function() {
    M.AutoInit();
    loadUserProfile();
    
    $('#avatar-upload').on('change', function() {
        handleAvatarUpload(this);
    });
    
    $('.profile-avatar__image').on('click', function() {
        $('#avatar-upload').click();
    });
    
    $('#verify-phone').on('click', function(e) {
        e.preventDefault();
        sendOTP();
    });
    
    $('#resend-otp').on('click', function(e) {
        e.preventDefault();
        sendOTP();
    });
    
    $('#verify-otp').on('click', function() {
        verifyOTP();
    });
    
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        updateProfile();
    });
    
    $('#profilePhone').on('input', function() {
        const phone = $(this).val();
        if (phone && phone.length >= 10) {
            $('#verify-phone').show();
            $('#phone-status').text('Not verified').removeClass('green-text').addClass('red-text');
        } else {
            $('#verify-phone').hide();
        }
    });
});

function loadUserProfile() {
    $.get('api/get_profile.php')
        .done(function(data) {
            if (data.success) {
                $('#profilePhone').val(data.user.phone || '');
                if (data.user.phone_verified) {
                    $('#phone-status').text('Verified ✓').removeClass('red-text').addClass('green-text');
                    $('#verify-phone').hide();
                }
                M.updateTextFields();
            }
        });
}

function handleAvatarUpload(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#profile-picture').attr('src', e.target.result);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function sendOTP() {
    const phone = $('#profilePhone').val();
    if (!phone) {
        M.toast({html: 'Please enter a phone number'});
        return;
    }
    
    $.post('api/send_otp.php', { phone: phone })
        .done(function(response) {
            if (response.success) {
                $('#otp-modal').modal('open');
                startOTPTimer();
                
                // Show OTP for demo purposes
                if (response.demo_otp || response.otp) {
                    const otp = response.demo_otp || response.otp;
                    M.toast({html: `Demo OTP: ${otp}`, displayLength: 10000});
                    
                    // Auto-fill OTP for demo
                    setTimeout(() => {
                        $('#otp-code').val(otp);
                        M.updateTextFields();
                    }, 1000);
                }
                
                M.toast({html: response.message});
            } else {
                M.toast({html: response.message || 'Failed to send OTP'});
            }
        })
        .fail(function() {
            M.toast({html: 'Error sending OTP'});
        });
}

function verifyOTP() {
    const otp = $('#otp-code').val();
    const phone = $('#profilePhone').val();
    
    if (!otp || otp.length !== 6) {
        M.toast({html: 'Please enter 6-digit OTP'});
        return;
    }
    
    $.post('api/verify_otp.php', { phone: phone, otp: otp })
        .done(function(response) {
            if (response.success) {
                $('#otp-modal').modal('close');
                $('#phone-status').text('Verified ✓').removeClass('red-text').addClass('green-text');
                $('#verify-phone').hide();
                M.toast({html: 'Phone number verified successfully!'});
            } else {
                M.toast({html: response.message || 'Invalid OTP'});
            }
        })
        .fail(function() {
            M.toast({html: 'Error verifying OTP'});
        });
}

function updateProfile() {
    const formData = new FormData();
    formData.append('name', $('#profileName').val());
    formData.append('email', $('#profileEmail').val());
    formData.append('phone', $('#profilePhone').val());
    formData.append('current_password', $('#currentPassword').val());
    formData.append('new_password', $('#newPassword').val());
    formData.append('confirm_password', $('#confirmPassword').val());
    
    const avatarFile = $('#avatar-upload')[0].files[0];
    if (avatarFile) {
        formData.append('avatar', avatarFile);
    }
    
    // Validate passwords
    const newPassword = $('#newPassword').val();
    const confirmPassword = $('#confirmPassword').val();
    
    if (newPassword && newPassword !== confirmPassword) {
        M.toast({html: 'New passwords do not match'});
        return;
    }
    
    $.ajax({
        url: 'api/update_profile.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                M.toast({html: 'Profile updated successfully!'});
                // Clear password fields
                $('#currentPassword, #newPassword, #confirmPassword').val('');
            } else {
                M.toast({html: response.message || 'Error updating profile'});
            }
        },
        error: function() {
            M.toast({html: 'Error updating profile'});
        }
    });
}

let otpTimer;
function startOTPTimer() {
    let seconds = 60;
    $('#timer').text(seconds);
    $('#timer-text').show();
    $('#resend-otp').hide();
    
    otpTimer = setInterval(function() {
        seconds--;
        $('#timer').text(seconds);
        
        if (seconds <= 0) {
            clearInterval(otpTimer);
            $('#timer-text').hide();
            $('#resend-otp').show();
        }
    }, 1000);
}