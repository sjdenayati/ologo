<?php
/*
Template Name: پروفایل کاربری
*/
get_header();

if (!is_user_logged_in()) {
    wp_redirect(home_url('/login'));
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// ذخیره اطلاعات پروفایل
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
    
    if (wp_verify_nonce($nonce, 'coworking_update_profile')) {
        // به‌روزرسانی اطلاعات کاربر
        $userdata = array(
            'ID' => $user_id,
            'display_name' => sanitize_text_field($_POST['display_name'])
        );
        
        if (!empty($_POST['user_email'])) {
            $userdata['user_email'] = sanitize_email($_POST['user_email']);
        }
        
        if (!empty($_POST['password'])) {
            $userdata['user_pass'] = $_POST['password'];
        }
        
        wp_update_user($userdata);
        
        // ذخیره متادیتاهای سفارشی
        update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
        update_user_meta($user_id, 'company', sanitize_text_field($_POST['company']));
        update_user_meta($user_id, 'job_title', sanitize_text_field($_POST['job_title']));
        
        echo '<script>alert("پروفایل با موفقیت به‌روزرسانی شد.");</script>';
    }
}

// دریافت اطلاعات کاربر
$user_phone = get_user_meta($user_id, 'phone', true);
$user_company = get_user_meta($user_id, 'company', true);
$user_job_title = get_user_meta($user_id, 'job_title', true);
?>

<div class="container my-5">
    <div class="row">
        <!-- سایدبار -->
        <div class="col-lg-3 col-md-4 mb-4">
            <?php include get_template_directory() . '/templates/user-sidebar.php'; ?>
        </div>
        
        <!-- محتوای اصلی -->
        <div class="col-lg-9 col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>ویرایش پروفایل
                    </h5>
                </div>
                
                <div class="card-body">
                    <form method="post" id="profile-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">نام نمایشی *</label>
                                    <input type="text" class="form-control" name="display_name" 
                                           value="<?php echo esc_attr($current_user->display_name); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">ایمیل *</label>
                                    <input type="email" class="form-control" name="user_email" 
                                           value="<?php echo esc_attr($current_user->user_email); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">تلفن همراه *</label>
                                    <input type="tel" class="form-control" name="phone" 
                                           value="<?php echo esc_attr($user_phone); ?>" pattern="09[0-9]{9}">
                                    <div class="form-text">مثال: 09123456789</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">نام کاربری</label>
                                    <input type="text" class="form-control" value="<?php echo esc_attr($current_user->user_login); ?>" disabled>
                                    <div class="form-text">نام کاربری قابل تغییر نیست</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">شرکت / استارتاپ</label>
                                    <input type="text" class="form-control" name="company" 
                                           value="<?php echo esc_attr($user_company); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">سمت شغلی</label>
                                    <input type="text" class="form-control" name="job_title" 
                                           value="<?php echo esc_attr($user_job_title); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">تغییر رمز عبور (اختیاری)</label>
                            <input type="password" class="form-control" name="password" 
                                   placeholder="در صورت تمایل به تغییر رمز عبور، این فیلد را پر کنید">
                            <div class="form-text">حداقل ۶ کاراکتر</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">تکرار رمز عبور</label>
                            <input type="password" class="form-control" name="confirm_password" 
                                   placeholder="تکرار رمز عبور جدید">
                        </div>
                        
                        <input type="hidden" name="update_profile" value="1">
                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('coworking_update_profile'); ?>">
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>ذخیره تغییرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- بخش امنیت -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-alt me-2"></i>تنظیمات امنیتی
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6>نشست‌های فعال</h6>
                                <p class="text-muted">شما از ۳ دستگاه وارد شده‌اید.</p>
                                <button class="btn btn-outline-danger btn-sm" onclick="logoutAllSessions()">
                                    <i class="fas fa-sign-out-alt me-2"></i>خروج از همه دستگاه‌ها
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6>اعلان‌ها</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="email-notifications" checked>
                                    <label class="form-check-label" for="email-notifications">
                                        دریافت اعلان از طریق ایمیل
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sms-notifications">
                                    <label class="form-check-label" for="sms-notifications">
                                        دریافت اعلان از طریق پیامک
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- تاریخچه فعالیت -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>تاریخچه فعالیت
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>فعالیت</th>
                                    <th>تاریخ</th>
                                    <th>آیپی</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>ورود به سیستم</td>
                                    <td>۱۴۰۳/۰۱/۱۵ - ۱۰:۳۰</td>
                                    <td>185.105.236.102</td>
                                </tr>
                                <tr>
                                    <td>رزرو فضای کار</td>
                                    <td>۱۴۰۳/۰۱/۱۴ - ۱۵:۲۰</td>
                                    <td>185.105.236.102</td>
                                </tr>
                                <tr>
                                    <td>پرداخت فاکتور</td>
                                    <td>۱۴۰۳/۰۱/۱۳ - ۱۱:۴۵</td>
                                    <td>185.105.236.102</td>
                                </tr>
                                <tr>
                                    <td>ایجاد تیکت</td>
                                    <td>۱۴۰۳/۰۱/۱۲ - ۰۹:۱۵</td>
                                    <td>185.105.236.102</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // اعتبارسنجی فرم
    $('#profile-form').on('submit', function(e) {
        var password = $('input[name="password"]').val();
        var confirmPassword = $('input[name="confirm_password"]').val();
        
        if (password && password.length < 6) {
            alert('رمز عبور باید حداقل ۶ کاراکتر باشد.');
            e.preventDefault();
            return;
        }
        
        if (password && password !== confirmPassword) {
            alert('رمز عبور و تکرار آن مطابقت ندارند.');
            e.preventDefault();
            return;
        }
        
        var phone = $('input[name="phone"]').val();
        if (phone && !phone.match(/^09[0-9]{9}$/)) {
            alert('شماره تلفن همراه معتبر نیست.');
            e.preventDefault();
            return;
        }
    });
    
    // خروج از همه دستگاه‌ها
    window.logoutAllSessions = function() {
        if (confirm('آیا می‌خواهید از همه دستگاه‌ها خارج شوید؟')) {
            $.ajax({
                url: coworking_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'logout_all_sessions',
                    nonce: coworking_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('شما از همه دستگاه‌ها خارج شدید. لطفاً مجدد وارد شوید.');
                        window.location.href = coworking_ajax.home_url + '/login';
                    }
                }
            });
        }
    };
});
</script>

<style>
.form-control:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
}

.btn-lg {
    border-radius: 10px;
    padding: 12px;
}

.table-sm th, .table-sm td {
    padding: 0.5rem;
    font-size: 0.875rem;
}
</style>

<?php get_footer(); ?>