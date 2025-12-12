<?php get_header(); ?>

<div class="main43">
    <!-- هیرو بخش -->
    <section class="hero-section">
        <div class="parallax">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="hero-content text-white">
                            <h1 style="font-family: 'PeydaWeb'; font-size: 2.5rem; margin-bottom: 20px;">
                                مرکز نوآوری و کار اشتراکی اکسیژن
                            </h1>
                            <p class="lead" style="font-size: 1.2rem; margin-bottom: 30px;">
                                فضای کار مدرن و پویا برای استارتاپ‌ها، فریلنسرها و تیم‌های خلاق
                            </p>
                            <div class="hero-buttons">
                                <?php if (is_user_logged_in()): ?>
                                    <a href="<?php echo home_url('/reservation'); ?>" class="btn btn-primary btn-lg me-3">
                                        <i class="fas fa-calendar-plus me-2"></i>رزرو فضای کار
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo home_url('/register'); ?>" class="btn btn-primary btn-lg me-3">
                                        <i class="fas fa-user-plus me-2"></i>عضویت رایگان
                                    </a>
                                <?php endif; ?>
                                <a href="#features" class="btn btn-outline-light btn-lg">
                                    <i class="fas fa-info-circle me-2"></i>معرفی امکانات
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-wrapper">
                            <div class="card shadow-lg" style="border-radius: 15px; overflow: hidden;">
                                <div class="card-header bg-primary text-white text-center py-3">
                                    <h4 class="mb-0">جستجوی فضای کار</h4>
                                </div>
                                <div class="card-body p-4">
                                    <form id="home-search-form">
                                        <div class="mb-3">
                                            <label class="form-label">نوع فضای مورد نظر</label>
                                            <select class="form-select" name="space_type">
                                                <option value="all">همه انواع</option>
                                                <option value="shared_desk">صندلی اشتراکی</option>
                                                <option value="dedicated_desk">صندلی اختصاصی</option>
                                                <option value="private_room">اتاق خصوصی</option>
                                                <option value="meeting_room">اتاق جلسه</option>
                                            </select>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">تاریخ شروع</label>
                                                <input type="date" class="form-control" name="start_date">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">مدت (روز)</label>
                                                <input type="number" class="form-control" name="duration" min="1" value="1">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">تعداد نفرات</label>
                                            <input type="number" class="form-control" name="capacity" min="1" value="1">
                                        </div>
                                        
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fas fa-search me-2"></i>جستجوی فضاهای موجود
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- بخش معرفی -->
    <section class="about-section py-5" style="background: #fff;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 style="font-family: 'PeydaWeb'; color: #196ab4; margin-bottom: 20px;">
                        درباره فضای کار اشتراکی اکسیژن
                    </h2>
                    <p class="tozihda mt-3" style="font-size: 1.1rem; line-height: 1.8;">
                        مرکز نوآوری و کار اشتراکی اکسیژن محیطی پویا و خلاقانه برای کارآفرینان، فریلنسرها، استارتاپ‌ها و تیم‌های کاری است. 
                        ما با فراهم آوردن فضایی مدرن، امکانات کامل و شبکه‌ای از متخصصان، بستری ایده‌آل برای رشد و توسعه کسب‌وکار شما ایجاد کرده‌ایم.
                    </p>
                    <div class="mt-4">
                        <a href="<?php echo home_url('/about'); ?>" class="btn btn-outline-primary">
                            <i class="fas fa-info-circle me-2"></i>مشاهده بیشتر
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <img src="<?php bloginfo('template_url');?>/images/space1.jpg" class="img-fluid rounded shadow" alt="فضای کار اشتراکی">
                        </div>
                        <div class="col-6 mb-3">
                            <img src="<?php bloginfo('template_url');?>/images/space2.jpg" class="img-fluid rounded shadow" alt="اتاق جلسات">
                        </div>
                        <div class="col-6">
                            <img src="<?php bloginfo('template_url');?>/images/space3.jpg" class="img-fluid rounded shadow" alt="آشپزخانه">
                        </div>
                        <div class="col-6">
                            <img src="<?php bloginfo('template_url');?>/images/space4.jpg" class="img-fluid rounded shadow" alt="سالن استراحت">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- امکانات -->
    <section id="features" class="py-5" style="background: #f8f9fa;">
        <div class="container">
            <div class="text-center mb-5">
                <h2 style="font-family: 'PeydaWeb'; color: #196ab4;">امکانات مجموعه</h2>
                <p class="text-muted">تمام آنچه برای کار مؤثر و راحت نیاز دارید</p>
            </div>
            
            <div class="row">
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="feature-item text-center p-4 rounded shadow-sm" style="background: #fff; height: 100%; transition: transform 0.3s;">
                        <div class="feature-icon mb-3" style="font-size: 2.5rem; color: #196ab4;">
                            <i class="fas fa-wifi"></i>
                        </div>
                        <h5 style="font-family: 'PeydaWeb';">اینترنت پرسرعت</h5>
                        <p class="text-muted">اینترنت فیبر نوری با سرعت بالا</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="feature-item text-center p-4 rounded shadow-sm" style="background: #fff; height: 100%; transition: transform 0.3s;">
                        <div class="feature-icon mb-3" style="font-size: 2.5rem; color: #28a745;">
                            <i class="fas fa-coffee"></i>
                        </div>
                        <h5 style="font-family: 'PeydaWeb';">چای و قهوه رایگان</h5>
                        <p class="text-muted">دسترسی آزاد به چای و قهوه باکیفیت</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="feature-item text-center p-4 rounded shadow-sm" style="background: #fff; height: 100%; transition: transform 0.3s;">
                        <div class="feature-icon mb-3" style="font-size: 2.5rem; color: #ffc107;">
                            <i class="fas fa-print"></i>
                        </div>
                        <h5 style="font-family: 'PeydaWeb';">پرینتر و اسکنر</h5>
                        <p class="text-muted">پرینتر رنگی و سیاه‌سفید</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="feature-item text-center p-4 rounded shadow-sm" style="background: #fff; height: 100%; transition: transform 0.3s;">
                        <div class="feature-icon mb-3" style="font-size: 2.5rem; color: #dc3545;">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 style="font-family: 'PeydaWeb';">اتاق جلسه</h5>
                        <p class="text-muted">اتاق‌های مجهز برای جلسات</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="feature-item text-center p-4 rounded shadow-sm" style="background: #fff; height: 100%; transition: transform 0.3s;">
                        <div class="feature-icon mb-3" style="font-size: 2.5rem; color: #17a2b8;">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h5 style="font-family: 'PeydaWeb';">آشپزخانه</h5>
                        <p class="text-muted">آشپزخانه مجهز با یخچال و مایکروویو</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="feature-item text-center p-4 rounded shadow-sm" style="background: #fff; height: 100%; transition: transform 0.3s;">
                        <div class="feature-icon mb-3" style="font-size: 2.5rem; color: #6f42c1;">
                            <i class="fas fa-parking"></i>
                        </div>
                        <h5 style="font-family: 'PeydaWeb';">پارکینگ اختصاصی</h5>
                        <p class="text-muted">پارکینگ رایگان برای اعضا</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="feature-item text-center p-4 rounded shadow-sm" style="background: #fff; height: 100%; transition: transform 0.3s;">
                        <div class="feature-icon mb-3" style="font-size: 2.5rem; color: #20c997;">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h5 style="font-family: 'PeydaWeb';">کمد اختصاصی</h5>
                        <p class="text-muted">کمد شخصی برای وسایل</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="feature-item text-center p-4 rounded shadow-sm" style="background: #fff; height: 100%; transition: transform 0.3s;">
                        <div class="feature-icon mb-3" style="font-size: 2.5rem; color: #fd7e14;">
                            <i class="fas fa-pray"></i>
                        </div>
                        <h5 style="font-family: 'PeydaWeb';">نمازخانه</h5>
                        <p class="text-muted">نمازخانه مجزا برای آقایان و بانوان</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- پلن‌ها -->
    <section id="pricing" class="py-5" style="background: #fff;">
        <div class="container">
            <div class="text-center mb-5">
                <h2 style="font-family: 'PeydaWeb'; color: #196ab4;">پلن‌های رزرو</h2>
                <p class="text-muted">انعطاف‌پذیر و متناسب با نیاز شما</p>
            </div>
            
            <!-- تب‌ها -->
            <ul class="nav nav-tabs justify-content-center mb-4" id="spaceTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="shared-tab" data-bs-toggle="tab" data-bs-target="#shared" type="button">
                        صندلی اشتراکی
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="dedicated-tab" data-bs-toggle="tab" data-bs-target="#dedicated" type="button">
                        صندلی اختصاصی
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="private-tab" data-bs-toggle="tab" data-bs-target="#private" type="button">
                        اتاق خصوصی
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="meeting-tab" data-bs-toggle="tab" data-bs-target="#meeting" type="button">
                        اتاق جلسه
                    </button>
                </li>
            </ul>
            
            <!-- محتوای تب‌ها -->
            <div class="tab-content" id="spaceTabContent">
                <!-- صندلی اشتراکی -->
                <div class="tab-pane fade show active" id="shared">
                    <div class="row">
                        <?php
                        // دریافت فضاهای صندلی اشتراکی
                        global $wpdb;
                        $table_spaces = $wpdb->prefix . 'coworking_spaces';
                        $shared_spaces = $wpdb->get_results("
                            SELECT * FROM $table_spaces 
                            WHERE type = 'shared_desk' AND status = 'active'
                            ORDER BY price_daily
                            LIMIT 3
                        ");
                        
                        if ($shared_spaces): 
                            foreach ($shared_spaces as $space): ?>
                            <div class="col-md-4 mb-4">
                                <div class="pricing-card card h-100 text-center shadow-sm">
                                    <div class="card-header bg-primary text-white py-3">
                                        <h4 class="mb-0"><?php echo esc_html($space->name); ?></h4>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="price mb-3">
                                            <span class="h2 text-primary"><?php echo number_format($space->price_daily); ?></span>
                                            <span class="text-muted">تومان / روز</span>
                                        </div>
                                        
                                        <ul class="list-unstyled mb-4">
                                            <li class="mb-2">
                                                <i class="fas fa-check text-success me-2"></i>
                                                دسترسی ۲۴ ساعته
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-check text-success me-2"></i>
                                                اینترنت پرسرعت
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-check text-success me-2"></i>
                                                چای و قهوه رایگان
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-check text-success me-2"></i>
                                                استفاده از پرینتر
                                            </li>
                                        </ul>
                                        
                                        <?php if (is_user_logged_in()): ?>
                                            <a href="<?php echo home_url('/reservation'); ?>" class="btn btn-primary w-100">
                                                <i class="fas fa-calendar-check me-2"></i>رزرو کنید
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo home_url('/register'); ?>" class="btn btn-outline-primary w-100">
                                                <i class="fas fa-user-plus me-2"></i>عضویت و رزرو
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; 
                        endif; ?>
                    </div>
                </div>
                
                <!-- سایر تب‌ها (مشابه بالا) -->
                <div class="tab-pane fade" id="dedicated">
                    <!-- محتوای صندلی اختصاصی -->
                </div>
                <div class="tab-pane fade" id="private">
                    <!-- محتوای اتاق خصوصی -->
                </div>
                <div class="tab-pane fade" id="meeting">
                    <!-- محتوای اتاق جلسه -->
                </div>
            </div>
        </div>
    </section>
    
    <!-- ساعت کاری -->
    <section class="working-hours py-5" style="background: #f8f9fa;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 style="font-family: 'PeydaWeb'; color: #196ab4; margin-bottom: 20px;">ساعت کاری مجموعه</h2>
                    <div class="hours-list">
                        <div class="hour-item d-flex justify-content-between align-items-center mb-3 p-3 rounded" style="background: #fff;">
                            <span style="font-family: 'PeydaWeb';">شنبه تا چهارشنبه</span>
                            <span class="badge bg-primary">۸:۰۰ - ۲۲:۰۰</span>
                        </div>
                        <div class="hour-item d-flex justify-content-between align-items-center mb-3 p-3 rounded" style="background: #fff;">
                            <span style="font-family: 'PeydaWeb';">پنجشنبه</span>
                            <span class="badge bg-warning">۸:۰۰ - ۱۸:۰۰</span>
                        </div>
                        <div class="hour-item d-flex justify-content-between align-items-center p-3 rounded" style="background: #fff;">
                            <span style="font-family: 'PeydaWeb';">جمعه و تعطیلات رسمی</span>
                            <span class="badge bg-secondary">تعطیل</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="calendar-info p-4 rounded" style="background: #196ab4; color: white; border-radius: 15px;">
                        <h4 style="font-family: 'PeydaWeb'; margin-bottom: 20px;">
                            <i class="fas fa-calendar-alt me-2"></i>تقویم تعطیلات
                        </h4>
                        <p>برای مشاهده تعطیلات و رویدادهای ویژه مجموعه، لطفاً به صفحه تقویم مراجعه کنید.</p>
                        <a href="<?php echo home_url('/calendar'); ?>" class="btn btn-light mt-2">
                            <i class="fas fa-calendar me-2"></i>مشاهده تقویم
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- آمار -->
    <section class="stats-section py-5" style="background: #196ab4; color: white;">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-item">
                        <h2 class="display-4 mb-2">۵۰۰+</h2>
                        <p style="font-family: 'PeydaWeb';">عضو فعال</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-item">
                        <h2 class="display-4 mb-2">۵۰+</h2>
                        <p style="font-family: 'PeydaWeb';">فضای کاری</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-item">
                        <h2 class="display-4 mb-2">۱۰۰+</h2>
                        <p style="font-family: 'PeydaWeb';">رویداد برگزار شده</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-item">
                        <h2 class="display-4 mb-2">۹۸%</h2>
                        <p style="font-family: 'PeydaWeb';">رضایت مشتریان</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA -->
    <section class="cta-section py-5" style="background: #fff;">
        <div class="container text-center">
            <h2 style="font-family: 'PeydaWeb'; color: #196ab4; margin-bottom: 20px;">
                آماده شروع کار در فضای حرفه‌ای هستید؟
            </h2>
            <p class="lead mb-4">همین حالا عضو شوید و از امکانات ویژه مجموعه بهره‌مند شوید.</p>
            
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <?php if (is_user_logged_in()): ?>
                    <a href="<?php echo home_url('/dashboard'); ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-tachometer-alt me-2"></i>ورود به پنل کاربری
                    </a>
                    <a href="<?php echo home_url('/reservation'); ?>" class="btn btn-success btn-lg">
                        <i class="fas fa-calendar-plus me-2"></i>رزرو فضای کار
                    </a>
                <?php else: ?>
                    <a href="<?php echo home_url('/register'); ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus me-2"></i>عضویت رایگان
                    </a>
                    <a href="<?php echo home_url('/login'); ?>" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>ورود اعضا
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<script>
jQuery(document).ready(function($) {
    // جستجو در صفحه اصلی
    $('#home-search-form').on('submit', function(e) {
        e.preventDefault();
        
        <?php if (!is_user_logged_in()): ?>
            alert('برای جستجوی فضاهای موجود لطفاً ابتدا وارد شوید.');
            window.location.href = '<?php echo home_url("/login"); ?>';
            return;
        <?php endif; ?>
        
        var formData = $(this).serialize();
        
        // ذخیره داده‌ها در localStorage و انتقال به صفحه رزرو
        localStorage.setItem('searchParams', formData);
        window.location.href = '<?php echo home_url("/reservation"); ?>';
    });
    
    // انیمیشن آیتم‌های امکانات
    $('.feature-item').hover(
        function() {
            $(this).css('transform', 'translateY(-10px)');
        },
        function() {
            $(this).css('transform', 'translateY(0)');
        }
    );
});
</script>

<style>
.hero-section {
    padding: 80px 0;
}

.parallax {
    background-image: url("<?php bloginfo('template_url');?>/images/back.jpg");
    min-height: 600px;
    background-attachment: fixed;
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
    position: relative;
}

.parallax::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
}

.hero-content {
    position: relative;
    z-index: 2;
}

.form-wrapper {
    position: relative;
    z-index: 2;
}

.feature-item:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.pricing-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.pricing-card:hover {
    border-color: #196ab4;
    transform: translateY(-5px);
}

.hour-item {
    transition: all 0.3s ease;
}

.hour-item:hover {
    transform: translateX(10px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.stat-item h2 {
    font-weight: bold;
}

.nav-tabs .nav-link {
    color: #495057;
    font-weight: 500;
    border: none;
    padding: 10px 20px;
}

.nav-tabs .nav-link.active {
    color: #196ab4;
    border-bottom: 3px solid #196ab4;
}

@media (max-width: 768px) {
    .hero-section {
        padding: 40px 0;
    }
    
    .parallax {
        background-attachment: scroll;
        min-height: auto;
    }
}
</style>

<?php get_footer(); ?>