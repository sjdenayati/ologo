<?php
// ================================
// فعال‌سازی قابلیت‌های اصلی وردپرس
// ================================
add_theme_support('post-thumbnails');
add_theme_support('title-tag');

// ثبت منوها
function coworking_register_menus() {
    register_nav_menus(array(
        'primary' => 'منوی اصلی',
        'footer' => 'منوی فوتر'
    ));
}
add_action('init', 'coworking_register_menus');

// ================================
// نقش‌های کاربری
// ================================
function coworking_add_user_roles() {
    // حذف نقش‌های غیرضروری
    if (get_role('subscriber')) {
        remove_role('subscriber');
    }
    if (get_role('contributor')) {
        remove_role('contributor');
    }
    
    // ایجاد نقش عضو فضای کار اشتراکی
    if (!get_role('coworking_member')) {
        add_role('coworking_member', 'عضو فضای کار', array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'upload_files' => true,
        ));
    }
    
    // ایجاد نقش مدیر فضای کار
    if (!get_role('coworking_manager')) {
        add_role('coworking_manager', 'مدیر فضای کار', array(
            'read' => true,
            'edit_posts' => true,
            'delete_posts' => true,
            'publish_posts' => true,
            'upload_files' => true,
            'manage_categories' => true,
            'moderate_comments' => true,
            'manage_options' => false,
        ));
    }
}
add_action('init', 'coworking_add_user_roles');

// ================================
// ایجاد جداول دیتابیس
// ================================
function coworking_create_database_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // جدول فضاها
    $table_spaces = $wpdb->prefix . 'coworking_spaces';
    $sql_spaces = "CREATE TABLE IF NOT EXISTS $table_spaces (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        type ENUM('shared_desk', 'dedicated_desk', 'private_room', 'meeting_room', 'event_hall') NOT NULL,
        capacity INT(11) DEFAULT 1,
        price_daily DECIMAL(10,2) DEFAULT 0,
        price_weekly DECIMAL(10,2) DEFAULT 0,
        price_monthly DECIMAL(10,2) DEFAULT 0,
        description TEXT,
        features TEXT,
        images TEXT,
        status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY slug (slug)
    ) $charset_collate;";
    
    // جدول رزروها
    $table_reservations = $wpdb->prefix . 'coworking_reservations';
    $sql_reservations = "CREATE TABLE IF NOT EXISTS $table_reservations (
        id INT(11) NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        space_id INT(11) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        duration_type ENUM('daily', 'weekly', 'monthly') NOT NULL,
        total_days INT(11) NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        discount_amount DECIMAL(10,2) DEFAULT 0,
        final_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'confirmed', 'active', 'completed', 'cancelled') DEFAULT 'pending',
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY space_id (space_id),
        KEY status (status)
    ) $charset_collate;";
    
    // جدول فاکتورها
    $table_invoices = $wpdb->prefix . 'coworking_invoices';
    $sql_invoices = "CREATE TABLE IF NOT EXISTS $table_invoices (
        id INT(11) NOT NULL AUTO_INCREMENT,
        invoice_number VARCHAR(50) NOT NULL,
        reservation_id INT(11) NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        tax_amount DECIMAL(10,2) DEFAULT 0,
        discount_amount DECIMAL(10,2) DEFAULT 0,
        total_amount DECIMAL(10,2) NOT NULL,
        payment_method ENUM('cash', 'card', 'online', 'bank_transfer') DEFAULT 'cash',
        payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
        due_date DATE,
        paid_date DATETIME,
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY invoice_number (invoice_number),
        KEY reservation_id (reservation_id),
        KEY user_id (user_id)
    ) $charset_collate;";
    
    // جدول تیکت‌ها
    $table_tickets = $wpdb->prefix . 'coworking_tickets';
    $sql_tickets = "CREATE TABLE IF NOT EXISTS $table_tickets (
        id INT(11) NOT NULL AUTO_INCREMENT,
        ticket_number VARCHAR(50) NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
        status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
        assigned_to BIGINT(20) UNSIGNED DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY ticket_number (ticket_number),
        KEY user_id (user_id),
        KEY status (status)
    ) $charset_collate;";
    
    // جدول پیام‌های تیکت
    $table_ticket_messages = $wpdb->prefix . 'coworking_ticket_messages';
    $sql_ticket_messages = "CREATE TABLE IF NOT EXISTS $table_ticket_messages (
        id INT(11) NOT NULL AUTO_INCREMENT,
        ticket_id INT(11) NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        message TEXT NOT NULL,
        attachment VARCHAR(255) DEFAULT NULL,
        is_admin_reply TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY ticket_id (ticket_id)
    ) $charset_collate;";
    
    // جدول تعطیلات و غیرفعال‌سازی‌ها
    $table_holidays = $wpdb->prefix . 'coworking_holidays';
    $sql_holidays = "CREATE TABLE IF NOT EXISTS $table_holidays (
        id INT(11) NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        description TEXT,
        type ENUM('holiday', 'maintenance', 'private_event') DEFAULT 'holiday',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    // جدول تراکنش‌ها
    $table_transactions = $wpdb->prefix . 'coworking_transactions';
    $sql_transactions = "CREATE TABLE IF NOT EXISTS $table_transactions (
        id INT(11) NOT NULL AUTO_INCREMENT,
        transaction_code VARCHAR(100) NOT NULL,
        invoice_id INT(11) NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        gateway VARCHAR(50) NOT NULL,
        status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
        gateway_data TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY transaction_code (transaction_code),
        KEY invoice_id (invoice_id),
        KEY user_id (user_id)
    ) $charset_collate;";
    
    // جدول تخفیف‌ها
    $table_discounts = $wpdb->prefix . 'coworking_discounts';
    $sql_discounts = "CREATE TABLE IF NOT EXISTS $table_discounts (
        id INT(11) NOT NULL AUTO_INCREMENT,
        code VARCHAR(50) NOT NULL,
        discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
        discount_value DECIMAL(10,2) NOT NULL,
        min_amount DECIMAL(10,2) DEFAULT 0,
        max_uses INT(11) DEFAULT 0,
        used_count INT(11) DEFAULT 0,
        start_date DATE,
        end_date DATE,
        status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY code (code)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    dbDelta($sql_spaces);
    dbDelta($sql_reservations);
    dbDelta($sql_invoices);
    dbDelta($sql_tickets);
    dbDelta($sql_ticket_messages);
    dbDelta($sql_holidays);
    dbDelta($sql_transactions);
    dbDelta($sql_discounts);
    
    // افزودن داده‌های نمونه برای فضاها
    coworking_add_sample_data();
}

// اجرای ایجاد جداول هنگام فعال‌سازی قالب
add_action('after_switch_theme', 'coworking_create_database_tables');

// بررسی و ایجاد جداول هنگام بارگذاری ادمین
function coworking_admin_table_check() {
    global $wpdb;
    
    $table_spaces = $wpdb->prefix . 'coworking_spaces';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_spaces'");
    
    if ($table_exists != $table_spaces) {
        // اگر جدول وجود ندارد، آن را ایجاد کن
        coworking_create_database_tables();
        
        // نمایش پیام موفقیت
        if (!get_transient('coworking_tables_created')) {
            set_transient('coworking_tables_created', true, 5);
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible">
                    <p>✅ جداول سیستم مدیریت فضای کار با موفقیت ایجاد شدند.</p>
                </div>';
            });
        }
    }
}
add_action('admin_init', 'coworking_admin_table_check');

// افزودن داده‌های نمونه
function coworking_add_sample_data() {
    global $wpdb;
    
    $table_spaces = $wpdb->prefix . 'coworking_spaces';
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_spaces");
    
    if ($count == 0) {
        $sample_spaces = array(
            array(
                'name' => 'میز اشتراکی روزانه',
                'slug' => 'shared-desk-daily',
                'type' => 'shared_desk',
                'capacity' => 1,
                'price_daily' => 50000,
                'price_weekly' => 300000,
                'price_monthly' => 1000000,
                'description' => 'میز اشتراکی در فضای باز با وای‌فای پرسرعت',
                'features' => 'وای‌فای, قهوه رایگان, پرینتر',
                'status' => 'active'
            ),
            array(
                'name' => 'اتاق خصوصی ۲ نفره',
                'slug' => 'private-room-2',
                'type' => 'private_room',
                'capacity' => 2,
                'price_daily' => 150000,
                'price_weekly' => 900000,
                'price_monthly' => 3000000,
                'description' => 'اتاق خصوصی مناسب برای تیم‌های کوچک',
                'features' => 'وای‌فای, تلویزیون, کمد شخصی',
                'status' => 'active'
            ),
            array(
                'name' => 'اتاق جلسات ۱۰ نفره',
                'slug' => 'meeting-room-10',
                'type' => 'meeting_room',
                'capacity' => 10,
                'price_daily' => 300000,
                'price_weekly' => 1800000,
                'price_monthly' => 6000000,
                'description' => 'اتاق جلسات مجهز به ویدئو پروژکتور و وایت‌برد',
                'features' => 'ویدئو پروژکتور, وایت‌برد, سیستم صوتی',
                'status' => 'active'
            )
        );
        
        foreach ($sample_spaces as $space) {
            $wpdb->insert($table_spaces, $space);
        }
    }
}

// ================================
// استایل‌ها و اسکریپت‌ها
// ================================
function coworking_enqueue_assets() {
    // استایل‌ها
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css');
    wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
    wp_enqueue_style('peyda-font', get_template_directory_uri() . '/fonts/peyda.css');
    wp_enqueue_style('main-style', get_stylesheet_uri());
    wp_enqueue_style('coworking-style', get_template_directory_uri() . '/css/custom.css', array(), '1.0.0');
    
    // اسکریپت‌ها
    wp_enqueue_script('jquery');
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true);
    wp_enqueue_script('persian-datepicker', 'https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js', array('jquery'), '1.2.0', true);
    wp_enqueue_script('persian-date', 'https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js', array(), '1.1.0', true);
    wp_enqueue_script('coworking-script', get_template_directory_uri() . '/js/custom.js', array('jquery'), '1.0.0', true);
    
    // داده‌های Ajax
    wp_localize_script('coworking-script', 'coworking_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('coworking_nonce'),
        'is_user_logged_in' => is_user_logged_in(),
        'user_id' => get_current_user_id(),
        'home_url' => home_url(),
    ));
    
    // استایل‌ها و اسکریپت‌های ادمین
    if (is_admin()) {
        wp_enqueue_style('coworking-admin-style', get_template_directory_uri() . '/admin/css/admin.css', array(), '1.0.0');
        wp_enqueue_script('coworking-admin-script', get_template_directory_uri() . '/admin/js/admin.js', array('jquery'), '1.0.0', true);
    }
}
add_action('wp_enqueue_scripts', 'coworking_enqueue_assets');
add_action('admin_enqueue_scripts', 'coworking_enqueue_assets');

// ================================
// صفحات مدیریت
// ================================
function coworking_admin_pages() {
    if (current_user_can('manage_options') || current_user_can('coworking_manager')) {
        // منوی اصلی
        add_menu_page(
            'مدیریت فضای کار اشتراکی',
            'فضای کار',
            'manage_options',
            'coworking-dashboard',
            'coworking_admin_dashboard_page',
            'dashicons-building',
            30
        );
        
        // زیرمنوها
        add_submenu_page(
            'coworking-dashboard',
            'داشبورد',
            'داشبورد',
            'manage_options',
            'coworking-dashboard',
            'coworking_admin_dashboard_page'
        );
        
        add_submenu_page(
            'coworking-dashboard',
            'مدیریت رزروها',
            'رزروها',
            'manage_options',
            'coworking-reservations',
            'coworking_reservations_page'
        );
        
        add_submenu_page(
            'coworking-dashboard',
            'مدیریت فضاها',
            'فضاها',
            'manage_options',
            'coworking-spaces',
            'coworking_spaces_page'
        );
        
        add_submenu_page(
            'coworking-dashboard',
            'تعطیلات و تقویم',
            'تقویم',
            'manage_options',
            'coworking-calendar',
            'coworking_calendar_page'
        );
        
        add_submenu_page(
            'coworking-dashboard',
            'مدیریت مالی',
            'مالی',
            'manage_options',
            'coworking-finance',
            'coworking_finance_page'
        );
        
        add_submenu_page(
            'coworking-dashboard',
            'تیکت‌ها',
            'تیکت‌ها',
            'manage_options',
            'coworking-tickets',
            'coworking_tickets_page'
        );
        
        add_submenu_page(
            'coworking-dashboard',
            'گزارش‌ها',
            'گزارش‌ها',
            'manage_options',
            'coworking-reports',
            'coworking_reports_page'
        );
        
        add_submenu_page(
            'coworking-dashboard',
            'کاربران',
            'کاربران',
            'manage_options',
            'coworking-users',
            'coworking_users_page'
        );
    }
}
add_action('admin_menu', 'coworking_admin_pages');

// ================================
// توابع Ajax
// ================================
// دریافت فضاها
add_action('wp_ajax_get_spaces', 'coworking_ajax_get_spaces');
add_action('wp_ajax_nopriv_get_spaces', 'coworking_ajax_get_spaces');

function coworking_ajax_get_spaces() {
    check_ajax_referer('coworking_nonce', 'nonce');
    
    global $wpdb;
    $table_spaces = $wpdb->prefix . 'coworking_spaces';
    
    $type = sanitize_text_field($_POST['type'] ?? 'all');
    $duration = sanitize_text_field($_POST['duration'] ?? 'daily');
    
    $where = "WHERE status = 'active'";
    if ($type !== 'all') {
        $where .= $wpdb->prepare(" AND type = %s", $type);
    }
    
    $spaces = $wpdb->get_results("SELECT * FROM $table_spaces $where ORDER BY created_at DESC");
    
    if ($spaces) {
        foreach ($spaces as $space) {
            echo '<div class="col-md-4 mb-4">';
            echo '<div class="space-card card h-100">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . esc_html($space->name) . '</h5>';
            echo '<p class="card-text">' . esc_html($space->description) . '</p>';
            
            // نمایش قیمت براساس مدت
            $price = 0;
            $price_field = 'price_' . $duration;
            if (property_exists($space, $price_field)) {
                $price = $space->$price_field;
            }
            
            echo '<p class="price">قیمت: ' . number_format($price) . ' تومان</p>';
            echo '<button class="btn btn-primary btn-reserve" data-space-id="' . $space->id . '">رزرو</button>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="col-12"><p class="text-center">فضایی یافت نشد.</p></div>';
    }
    
    wp_die();
}

// ثبت رزرو
add_action('wp_ajax_make_reservation', 'coworking_ajax_make_reservation');

function coworking_ajax_make_reservation() {
    check_ajax_referer('coworking_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error('لطفا ابتدا وارد شوید.');
    }
    
    $user_id = get_current_user_id();
    $space_id = intval($_POST['space_id']);
    $start_date = sanitize_text_field($_POST['start_date']);
    $end_date = sanitize_text_field($_POST['end_date']);
    $duration_type = sanitize_text_field($_POST['duration_type']);
    
    global $wpdb;
    $table_spaces = $wpdb->prefix . 'coworking_spaces';
    $table_reservations = $wpdb->prefix . 'coworking_reservations';
    
    // بررسی موجود بودن فضا
    $space = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_spaces WHERE id = %d", $space_id));
    
    if (!$space) {
        wp_send_json_error('فضا یافت نشد.');
    }
    
    // بررسی تداخل رزرو
    $conflict = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_reservations 
        WHERE space_id = %d 
        AND status IN ('pending', 'confirmed', 'active')
        AND (
            (start_date <= %s AND end_date >= %s) OR
            (start_date <= %s AND end_date >= %s) OR
            (start_date >= %s AND end_date <= %s)
        )",
        $space_id, $start_date, $start_date, $end_date, $end_date, $start_date, $end_date
    ));
    
    if ($conflict > 0) {
        wp_send_json_error('این فضا در تاریخ انتخابی رزرو شده است.');
    }
    
    // محاسبه قیمت
    $price_field = 'price_' . $duration_type;
    $daily_price = $space->$price_field ?? $space->price_daily;
    
    // محاسبه تعداد روزها
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    $total_days = $interval->days + 1;
    
    $total_amount = $daily_price * $total_days;
    
    // ذخیره رزرو
    $result = $wpdb->insert($table_reservations, array(
        'user_id' => $user_id,
        'space_id' => $space_id,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'duration_type' => $duration_type,
        'total_days' => $total_days,
        'total_amount' => $total_amount,
        'final_amount' => $total_amount,
        'status' => 'pending'
    ));
    
    if ($result) {
        // ایجاد فاکتور
        $reservation_id = $wpdb->insert_id;
        $invoice_number = 'INV-' . date('Ymd') . '-' . str_pad($reservation_id, 5, '0', STR_PAD_LEFT);
        
        coworking_create_invoice($reservation_id, $invoice_number, $total_amount);
        
        wp_send_json_success(array(
            'message' => 'رزرو با موفقیت ثبت شد.',
            'reservation_id' => $reservation_id,
            'invoice_number' => $invoice_number
        ));
    } else {
        wp_send_json_error('خطا در ثبت رزرو.');
    }
}

// ================================
// توابع کمکی
// ================================
function coworking_create_invoice($reservation_id, $invoice_number, $amount) {
    global $wpdb;
    $table_invoices = $wpdb->prefix . 'coworking_invoices';
    
    $reservation = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}coworking_reservations WHERE id = %d", 
        $reservation_id
    ));
    
    if ($reservation) {
        $wpdb->insert($table_invoices, array(
            'invoice_number' => $invoice_number,
            'reservation_id' => $reservation_id,
            'user_id' => $reservation->user_id,
            'amount' => $amount,
            'total_amount' => $amount,
            'payment_status' => 'pending',
            'due_date' => date('Y-m-d', strtotime('+3 days'))
        ));
    }
}

// ================================
// صفحات مدیریت - توابع نمایش
// ================================
function coworking_admin_dashboard_page() {
    if (!current_user_can('manage_options') && !current_user_can('coworking_manager')) {
        wp_die('شما دسترسی لازم را ندارید.');
    }
    include get_template_directory() . '/admin/dashboard.php';
}

function coworking_reservations_page() {
    if (!current_user_can('manage_options') && !current_user_can('coworking_manager')) {
        wp_die('شما دسترسی لازم را ندارید.');
    }
    include get_template_directory() . '/admin/reservations.php';
}

function coworking_spaces_page() {
    if (!current_user_can('manage_options') && !current_user_can('coworking_manager')) {
        wp_die('شما دسترسی لازم را ندارید.');
    }
    include get_template_directory() . '/admin/spaces.php';
}

function coworking_calendar_page() {
    if (!current_user_can('manage_options') && !current_user_can('coworking_manager')) {
        wp_die('شما دسترسی لازم را ندارید.');
    }
    include get_template_directory() . '/admin/calendar.php';
}

function coworking_finance_page() {
    if (!current_user_can('manage_options') && !current_user_can('coworking_manager')) {
        wp_die('شما دسترسی لازم را ندارید.');
    }
    include get_template_directory() . '/admin/finance.php';
}

function coworking_tickets_page() {
    if (!current_user_can('manage_options') && !current_user_can('coworking_manager')) {
        wp_die('شما دسترسی لازم را ندارید.');
    }
    include get_template_directory() . '/admin/tickets.php';
}

function coworking_reports_page() {
    if (!current_user_can('manage_options') && !current_user_can('coworking_manager')) {
        wp_die('شما دسترسی لازم را ندارید.');
    }
    include get_template_directory() . '/admin/reports.php';
}

function coworking_users_page() {
    if (!current_user_can('manage_options') && !current_user_can('coworking_manager')) {
        wp_die('شما دسترسی لازم را ندارید.');
    }
    include get_template_directory() . '/admin/users.php';
}

// ================================
// ریدایرکت بعد از لاگین
// ================================
function coworking_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('administrator', $user->roles) || in_array('coworking_manager', $user->roles)) {
            return admin_url('admin.php?page=coworking-dashboard');
        } else {
            return home_url('/dashboard');
        }
    }
    return $redirect_to;
}
add_filter('login_redirect', 'coworking_login_redirect', 10, 3);

// ================================
// فرم ثبت نام سفارشی
// ================================
function coworking_registration_form() {
    ?>
    <form id="coworking-register-form" method="post" action="<?php echo esc_url(site_url('wp-login.php?action=register', 'login_post')); ?>">
        <div class="form-group mb-3">
            <label for="first_name">نام</label>
            <input type="text" name="first_name" id="first_name" class="form-control" required>
        </div>
        
        <div class="form-group mb-3">
            <label for="last_name">نام خانوادگی</label>
            <input type="text" name="last_name" id="last_name" class="form-control" required>
        </div>
        
        <div class="form-group mb-3">
            <label for="user_email">ایمیل</label>
            <input type="email" name="user_email" id="user_email" class="form-control" required>
        </div>
        
        <div class="form-group mb-3">
            <label for="user_phone">تلفن همراه</label>
            <input type="tel" name="user_phone" id="user_phone" class="form-control" required>
        </div>
        
        <div class="form-group mb-3">
            <label for="user_login">نام کاربری</label>
            <input type="text" name="user_login" id="user_login" class="form-control" required>
        </div>
        
        <div class="form-group mb-3">
            <label for="user_pass">رمز عبور</label>
            <input type="password" name="user_pass" id="user_pass" class="form-control" required>
        </div>
        
        <div class="form-group mb-3">
            <label for="confirm_pass">تکرار رمز عبور</label>
            <input type="password" name="confirm_pass" id="confirm_pass" class="form-control" required>
        </div>
        
        <input type="hidden" name="redirect_to" value="<?php echo home_url('/dashboard'); ?>">
        <input type="hidden" name="user_role" value="coworking_member">
        
        <button type="submit" name="wp-submit" class="btn btn-primary w-100">ثبت نام</button>
    </form>
    <?php
}

// ================================
// ایجاد صفحات هنگام فعال‌سازی قالب
// ================================
function coworking_create_pages_on_activation() {
    $pages = array(
        'داشبورد' => array(
            'content' => '[coworking_dashboard]',
            'template' => 'page-dashboard.php'
        ),
        'رزرو فضای کار' => array(
            'content' => '[coworking_reservation]',
            'template' => 'page-reservation.php'
        ),
        'ورود' => array(
            'content' => '[coworking_login]',
            'template' => 'page-login.php'
        ),
        'ثبت نام' => array(
            'content' => '[coworking_register]',
            'template' => 'page-register.php'
        ),
        'تیکت‌ها' => array(
            'content' => '[coworking_tickets]',
            'template' => 'page-tickets.php'
        ),
        'فاکتورها' => array(
            'content' => '[coworking_invoices]',
            'template' => 'page-invoices.php'
        ),
        'پروفایل' => array(
            'content' => '[coworking_profile]',
            'template' => 'page-profile.php'
        ),
    );
    
    foreach ($pages as $title => $page_data) {
        if (!get_page_by_title($title)) {
            $page_id = wp_insert_post(array(
                'post_title' => $title,
                'post_content' => $page_data['content'],
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => 1,
            ));
            
            if ($page_id && isset($page_data['template'])) {
                update_post_meta($page_id, '_wp_page_template', $page_data['template']);
            }
        }
    }
}
add_action('after_switch_theme', 'coworking_create_pages_on_activation');

// ================================
// شورتکدها
// ================================
function coworking_dashboard_shortcode() {
    ob_start();
    if (is_user_logged_in()) {
        include get_template_directory() . '/templates/dashboard-content.php';
    } else {
        echo '<p>لطفا ابتدا <a href="' . home_url('/login') . '">وارد شوید</a>.</p>';
    }
    return ob_get_clean();
}
add_shortcode('coworking_dashboard', 'coworking_dashboard_shortcode');

function coworking_reservation_shortcode() {
    ob_start();
    include get_template_directory() . '/templates/reservation-form.php';
    return ob_get_clean();
}
add_shortcode('coworking_reservation', 'coworking_reservation_shortcode');

function coworking_login_shortcode() {
    ob_start();
    if (!is_user_logged_in()) {
        wp_login_form(array(
            'redirect' => home_url('/dashboard'),
            'label_username' => 'نام کاربری یا ایمیل',
            'label_password' => 'رمز عبور',
            'label_remember' => 'مرا به خاطر بسپار',
            'label_log_in' => 'ورود',
            'remember' => true
        ));
    } else {
        echo '<p>شما قبلا وارد شده‌اید. <a href="' . home_url('/dashboard') . '">رفتن به داشبورد</a></p>';
    }
    return ob_get_clean();
}
add_shortcode('coworking_login', 'coworking_login_shortcode');

function coworking_register_shortcode() {
    ob_start();
    if (!is_user_logged_in()) {
        coworking_registration_form();
    } else {
        echo '<p>شما قبلا ثبت نام کرده‌اید. <a href="' . home_url('/dashboard') . '">رفتن به داشبورد</a></p>';
    }
    return ob_get_clean();
}
add_shortcode('coworking_register', 'coworking_register_shortcode');

// ================================
// اضافه کردن فیلدهای سفارشی به پروفایل کاربر
// ================================
function coworking_user_profile_fields($user) {
    ?>
    <h3>اطلاعات فضای کار اشتراکی</h3>
    
    <table class="form-table">
        <tr>
            <th><label for="phone">تلفن همراه</label></th>
            <td>
                <input type="tel" name="phone" id="phone" 
                       value="<?php echo esc_attr(get_the_author_meta('phone', $user->ID)); ?>" 
                       class="regular-text">
            </td>
        </tr>
        <tr>
            <th><label for="company">شرکت/استارتاپ</label></th>
            <td>
                <input type="text" name="company" id="company" 
                       value="<?php echo esc_attr(get_the_author_meta('company', $user->ID)); ?>" 
                       class="regular-text">
            </td>
        </tr>
        <tr>
            <th><label for="job_title">سمت شغلی</label></th>
            <td>
                <input type="text" name="job_title" id="job_title" 
                       value="<?php echo esc_attr(get_the_author_meta('job_title', $user->ID)); ?>" 
                       class="regular-text">
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'coworking_user_profile_fields');
add_action('edit_user_profile', 'coworking_user_profile_fields');

function coworking_save_user_profile_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
    update_user_meta($user_id, 'company', sanitize_text_field($_POST['company']));
    update_user_meta($user_id, 'job_title', sanitize_text_field($_POST['job_title']));
}
add_action('personal_options_update', 'coworking_save_user_profile_fields');
add_action('edit_user_profile_update', 'coworking_save_user_profile_fields');

// ================================
// نمایش خطاهای وردپرس
// ================================
function coworking_show_admin_notices() {
    if ($message = get_transient('coworking_admin_notice')) {
        $type = get_transient('coworking_admin_notice_type') ?: 'info';
        echo '<div class="notice notice-' . $type . ' is-dismissible"><p>' . $message . '</p></div>';
        delete_transient('coworking_admin_notice');
        delete_transient('coworking_admin_notice_type');
    }
}
add_action('admin_notices', 'coworking_show_admin_notices');

// ================================
// تابع دیباگ برای بررسی وجود جداول
// ================================
function coworking_debug_tables() {
    if (current_user_can('manage_options') && isset($_GET['debug_tables'])) {
        global $wpdb;
        
        $tables = array(
            'coworking_spaces',
            'coworking_reservations',
            'coworking_invoices',
            'coworking_tickets',
            'coworking_ticket_messages',
            'coworking_holidays',
            'coworking_transactions',
            'coworking_discounts'
        );
        
        echo '<div style="padding: 20px; background: #f5f5f5; margin: 20px;">';
        echo '<h3>بررسی جداول دیتابیس</h3>';
        
        foreach ($tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
            
            if ($exists) {
                $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                echo "<p style='color: green;'>✅ جدول $table_name موجود است (تعداد رکوردها: $count)</p>";
            } else {
                echo "<p style='color: red;'>❌ جدول $table_name وجود ندارد</p>";
            }
        }
        
        echo '</div>';
    }
}
add_action('admin_notices', 'coworking_debug_tables');

// ================================
// ایجاد دستی جداول از طریق آدرس
// ================================
function coworking_manual_install() {
    if (isset($_GET['install_coworking_tables']) && current_user_can('manage_options')) {
        coworking_create_database_tables();
        wp_die('جداول با موفقیت ایجاد شدند. <a href="' . admin_url() . '">بازگشت به پیشخوان</a>');
    }
}
add_action('init', 'coworking_manual_install');
?>