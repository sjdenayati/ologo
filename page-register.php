<?php
/*
Template Name: ثبت نام
*/
get_header();

if (is_user_logged_in()) {
    wp_redirect(home_url('/dashboard'));
    exit;
}
?>

<div class="main2">
    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 80vh;">
            <div class="col-lg-6 col-md-8">
                <div class="register-wrapper p-5 rounded shadow" style="background: white; border-radius: 20px;">
                    <div class="text-center mb-4">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/logo.png" alt="Logo" class="mb-3" style="max-width: 150px;">
                        <h3 style="font-family: 'PeydaWeb'; color: #196ab4;">عضویت در فضای کار اشتراکی</h3>
                        <p class="text-muted">فرم ثبت نام جدید</p>
                    </div>
                    
                    <?php
                    // نمایش خطاها
                    if (isset($_GET['registration'])) {
                        if ($_GET['registration'] == 'success') {
                            echo '<div class="alert alert-success">ثبت نام با موفقیت انجام شد. لطفاً وارد شوید.</div>';
                        } elseif ($_GET['registration'] == 'error') {
                            echo '<div class="alert alert-danger">خطا در ثبت نام. لطفاً مجدد تلاش کنید.</div>';
                        }
                    }
                    ?>
                    
                    <form id="register-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                        <input type="hidden" name="action" value="coworking_register">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">نام</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">نام خانوادگی</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">ایمیل</label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="form-text">لطفاً ایمیل معتبر وارد کنید.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">تلفن همراه</label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="tel" class="form-control" id="phone" name="phone" required pattern="09[0-9]{9}">
                            </div>
                            <div class="form-text">مثال: 09123456789</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="company" class="form-label">شرکت / استارتاپ (اختیاری)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="fas fa-building"></i>
                                </span>
                                <input type="text" class="form-control" id="company" name="company">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">نام کاربری</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="fas fa-at"></i>
                                        </span>
                                        <input type="text" class="form-control" id="username" name="username" required minlength="3">
                                    </div>
                                    <div class="form-text">حداقل ۳ کاراکتر</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="job_title" class="form-label">سمت شغلی (اختیاری)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="fas fa-briefcase"></i>
                                        </span>
                                        <input type="text" class="form-control" id="job_title" name="job_title">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">رمز عبور</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                                        <button class="btn btn-outline-secondary" type="button" id="toggle-password1">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">حداقل ۶ کاراکتر</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">تکرار رمز عبور</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        <button class="btn btn-outline-secondary" type="button" id="toggle-password2">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="referral" class="form-label">کد معرف (اختیاری)</label>
                            <input type="text" class="form-control" id="referral" name="referral">
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                <a href="<?php echo home_url('/terms'); ?>" target="_blank">قوانین و شرایط</a> را مطالعه کرده‌ام و می‌پذیرم.
                            </label>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="newsletter" name="newsletter" checked>
                            <label class="form-check-label" for="newsletter">
                                مایل به دریافت خبرنامه و اطلاعیه‌ها هستم.
                            </label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg" id="register-submit">
                                <i class="fas fa-user-plus me-2"></i>ثبت نام
                            </button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="<?php echo home_url('/login'); ?>" class="text-decoration-none">
                                <i class="fas fa-sign-in-alt me-2"></i>قبلاً ثبت نام کرده‌ام
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // نمایش/مخفی کردن رمز عبور
    $('#toggle-password1, #toggle-password2').click(function() {
        var inputId = $(this).attr('id') === 'toggle-password1' ? '#password' : '#confirm_password';
        var passwordInput = $(inputId);
        var icon = $(this).find('i');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // اعتبارسنجی رمز عبور
    $('#confirm_password').on('keyup', function() {
        var password = $('#password').val();
        var confirmPassword = $(this).val();
        
        if (password !== confirmPassword) {
            $(this).addClass('is-invalid');
            $(this).removeClass('is-valid');
            $('#password').addClass('is-invalid');
            $('#password').removeClass('is-valid');
        } else {
            $(this).removeClass('is-invalid');
            $(this).addClass('is-valid');
            $('#password').removeClass('is-invalid');
            $('#password').addClass('is-valid');
        }
    });
    
    // بررسی نام کاربری
    $('#username').on('blur', function() {
        var username = $(this).val();
        
        if (username.length >= 3) {
            $.ajax({
                url: coworking_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'check_username',
                    username: username,
                    nonce: coworking_ajax.nonce
                },
                success: function(response) {
                    if (response.available) {
                        $('#username').addClass('is-valid');
                        $('#username').removeClass('is-invalid');
                    } else {
                        $('#username').addClass('is-invalid');
                        $('#username').removeClass('is-valid');
                        alert('این نام کاربری قبلاً ثبت شده است.');
                    }
                }
            });
        }
    });
    
    // بررسی ایمیل
    $('#email').on('blur', function() {
        var email = $(this).val();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (emailRegex.test(email)) {
            $.ajax({
                url: coworking_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'check_email',
                    email: email,
                    nonce: coworking_ajax.nonce
                },
                success: function(response) {
                    if (response.available) {
                        $('#email').addClass('is-valid');
                        $('#email').removeClass('is-invalid');
                    } else {
                        $('#email').addClass('is-invalid');
                        $('#email').removeClass('is-valid');
                        alert('این ایمیل قبلاً ثبت شده است.');
                    }
                }
            });
        }
    });
    
    // ارسال فرم
    $('#register-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var password = $('#password').val();
        var confirmPassword = $('#confirm_password').val();
        
        if (password !== confirmPassword) {
            alert('رمز عبور و تکرار آن مطابقت ندارند.');
            return;
        }
        
        if (!password || password.length < 6) {
            alert('رمز عبور باید حداقل ۶ کاراکتر باشد.');
            return;
        }
        
        if (!$('#terms').is(':checked')) {
            alert('لطفاً قوانین و شرایط را بپذیرید.');
            return;
        }
        
        $.ajax({
            url: coworking_ajax.ajax_url,
            type: 'POST',
            data: formData + '&nonce=' + coworking_ajax.nonce,
            beforeSend: function() {
                $('#register-submit').html('<span class="spinner-border spinner-border-sm"></span> در حال ثبت...');
                $('#register-submit').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = coworking_ajax.home_url + '/login?registration=success';
                } else {
                    alert('خطا در ثبت نام: ' + response.data);
                    $('#register-submit').html('<i class="fas fa-user-plus me-2"></i>ثبت نام');
                    $('#register-submit').prop('disabled', false);
                }
            }
        });
    });
});
</script>

<style>
.register-wrapper {
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.input-group-text {
    border-radius: 10px 0 0 10px;
}

.form-control {
    border-radius: 0 10px 10px 0;
}

.form-control.is-valid {
    border-color: #28a745;
}

.form-control.is-invalid {
    border-color: #dc3545;
}

.btn-lg {
    border-radius: 10px;
    padding: 12px;
}
</style>

<?php get_footer(); ?>