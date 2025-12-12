<?php
/*
Template Name: ورود به سیستم
*/
get_header();

// اگر کاربر قبلاً وارد شده باشد
if (is_user_logged_in()) {
    wp_redirect(home_url('/dashboard'));
    exit;
}
?>

<div class="main2">
    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 80vh;">
            <div class="col-lg-5 col-md-7">
                <div class="login-wrapper p-5 rounded shadow" style="background: white; border-radius: 20px;">
                    <div class="text-center mb-4">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/logo.png" alt="Logo" class="mb-3" style="max-width: 150px;">
                        <h3 style="font-family: 'PeydaWeb'; color: #196ab4;">ورود به سامانه</h3>
                        <p class="text-muted">مرکز نوآوری و کار اشتراکی اکسیژن</p>
                    </div>
                    
                    <?php
                    // نمایش خطاها
                    if (isset($_GET['login']) && $_GET['login'] == 'failed') {
                        echo '<div class="alert alert-danger">نام کاربری یا رمز عبور اشتباه است.</div>';
                    } elseif (isset($_GET['loggedout']) && $_GET['loggedout'] == 'true') {
                        echo '<div class="alert alert-success">با موفقیت خارج شدید.</div>';
                    }
                    ?>
                    
                    <form id="login-form" method="post" action="<?php echo wp_login_url(); ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label">نام کاربری یا ایمیل</label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" class="form-control" id="username" name="log" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">رمز عبور</label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="pwd" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="rememberme" name="rememberme">
                            <label class="form-check-label" for="rememberme">مرا به خاطر بسپار</label>
                        </div>
                        
                        <input type="hidden" name="redirect_to" value="<?php echo home_url('/dashboard'); ?>">
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" name="wp-submit">
                                <i class="fas fa-sign-in-alt me-2"></i>ورود به سیستم
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="<?php echo wp_lostpassword_url(); ?>" class="text-decoration-none">
                            <i class="fas fa-key me-2"></i>فراموشی رمز عبور؟
                        </a>
                    </div>
                    
                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="mb-0">حساب کاربری ندارید؟</p>
                        <a href="<?php echo home_url('/register'); ?>" class="btn btn-outline-success mt-2">
                            <i class="fas fa-user-plus me-2"></i>ثبت نام جدید
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // نمایش/مخفی کردن رمز عبور
    $('#toggle-password').click(function() {
        var passwordInput = $('#password');
        var icon = $(this).find('i');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // اعتبارسنجی فرم
    $('#login-form').on('submit', function(e) {
        var username = $('#username').val();
        var password = $('#password').val();
        
        if (!username || !password) {
            e.preventDefault();
            alert('لطفاً تمام فیلدها را پر کنید.');
        }
    });
});
</script>

<style>
.login-wrapper {
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.input-group-text {
    border-radius: 10px 0 0 10px;
}

.form-control {
    border-radius: 0 10px 10px 0;
}

.btn-lg {
    border-radius: 10px;
    padding: 12px;
}
</style>

<?php get_footer(); ?>