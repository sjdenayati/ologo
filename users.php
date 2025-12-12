<?php
if (!current_user_can('manage_options') && !current_user_can('coworking_manager')) {
    wp_die('شما دسترسی لازم را ندارید.');
}

global $wpdb;
$table_users = $wpdb->prefix . 'users';
$table_reservations = $wpdb->prefix . 'coworking_reservations';
$table_invoices = $wpdb->prefix . 'coworking_invoices';

// پارامترهای فیلتر
$role_filter = isset($_GET['role']) ? sanitize_text_field($_GET['role']) : 'all';
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// ساخت شرط WHERE
$where_conditions = array("1=1");
if ($role_filter !== 'all') {
    $where_conditions[] = $wpdb->prepare("u.ID IN (SELECT user_id FROM {$wpdb->prefix}usermeta WHERE meta_key = '{$wpdb->prefix}capabilities' AND meta_value LIKE %s)", '%' . $role_filter . '%');
}
if ($search_query) {
    $where_conditions[] = $wpdb->prepare("(u.display_name LIKE %s OR u.user_email LIKE %s OR u.user_login LIKE %s)", 
        '%' . $search_query . '%', 
        '%' . $search_query . '%', 
        '%' . $search_query . '%');
}

$where_clause = implode(' AND ', $where_conditions);

// دریافت کاربران
$users_per_page = 20;
$current_page = max(1, isset($_GET['paged']) ? intval($_GET['paged']) : 1);
$offset = ($current_page - 1) * $users_per_page;

$total_users = $wpdb->get_var("SELECT COUNT(*) FROM $table_users u WHERE $where_clause");

$users = $wpdb->get_results($wpdb->prepare(
    "SELECT u.*, 
            COUNT(DISTINCT r.id) as reservation_count,
            COUNT(DISTINCT i.id) as invoice_count,
            SUM(CASE WHEN r.status IN ('confirmed', 'active') THEN 1 ELSE 0 END) as active_reservations,
            SUM(CASE WHEN i.payment_status = 'paid' THEN i.total_amount ELSE 0 END) as total_spent
    FROM $table_users u
    LEFT JOIN $table_reservations r ON u.ID = r.user_id
    LEFT JOIN $table_invoices i ON u.ID = i.user_id
    WHERE $where_clause
    GROUP BY u.ID
    ORDER BY u.user_registered DESC
    LIMIT %d OFFSET %d",
    $users_per_page, $offset
));
?>

<div class="wrap">
    <h1 class="wp-heading-inline" style="font-family: 'PeydaWeb';">
        <i class="fas fa-users me-2"></i>مدیریت کاربران
    </h1>
    
    <!-- فرم جستجو و فیلتر -->
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>فیلتر کاربران</h5>
        </div>
        <div class="card-body">
            <form method="get" action="" class="row g-3">
                <input type="hidden" name="page" value="coworking-users">
                
                <div class="col-md-4">
                    <label class="form-label">جستجو</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" value="<?php echo esc_attr($search_query); ?>" placeholder="نام، ایمیل یا نام کاربری...">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">نقش کاربری</label>
                    <select name="role" class="form-select">
                        <option value="all" <?php selected($role_filter, 'all'); ?>>همه نقش‌ها</option>
                        <option value="administrator" <?php selected($role_filter, 'administrator'); ?>>مدیر سیستم</option>
                        <option value="coworking_manager" <?php selected($role_filter, 'coworking_manager'); ?>>مدیر فضای کار</option>
                        <option value="coworking_member" <?php selected($role_filter, 'coworking_member'); ?>>عضو فضای کار</option>
                        <option value="subscriber" <?php selected($role_filter, 'subscriber'); ?>>مشترک</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">وضعیت</label>
                    <select name="status" class="form-select">
                        <option value="all" <?php selected($status_filter, 'all'); ?>>همه وضعیت‌ها</option>
                        <option value="active" <?php selected($status_filter, 'active'); ?>>کاربران فعال</option>
                        <option value="inactive" <?php selected($status_filter, 'inactive'); ?>>غیرفعال</option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>اعمال فیلتر
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- آمار کاربران -->
    <div class="row mt-4">
        <?php
        // آمار کلی
        $total_members = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}usermeta WHERE meta_key = '{$wpdb->prefix}capabilities' AND meta_value LIKE '%coworking_member%'");
        $total_managers = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}usermeta WHERE meta_key = '{$wpdb->prefix}capabilities' AND meta_value LIKE '%coworking_manager%'");
        $new_users_today = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_users WHERE DATE(user_registered) = %s",
            date('Y-m-d')
        ));
        $active_users = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM $table_reservations WHERE status IN ('confirmed', 'active')");
        ?>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center text-white bg-primary">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">کل کاربران</h6>
                    <h4><?php echo $total_users; ?></h4>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center text-white bg-success">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">اعضا</h6>
                    <h4><?php echo $total_members; ?></h4>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center text-white bg-info">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">مدیران</h6>
                    <h4><?php echo $total_managers; ?></h4>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center text-white bg-warning">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">عضویت‌های امروز</h6>
                    <h4><?php echo $new_users_today; ?></h4>
                </div>
            </div>
        </div>
    </div>
    
    <!-- جدول کاربران -->
    <div class="card mt-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">لیست کاربران</h5>
            <div>
                <button class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-user-plus me-2"></i>افزودن کاربر
                </button>
                <button class="btn btn-outline-primary btn-sm" id="export-users">
                    <i class="fas fa-download me-2"></i>خروجی Excel
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if ($users): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>کاربر</th>
                                <th>ایمیل</th>
                                <th>عضویت</th>
                                <th>نقش</th>
                                <th>رزروها</th>
                                <th>فعال</th>
                                <th>هزینه کل</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): 
                                // تعیین نقش کاربر
                                $user_data = get_userdata($user->ID);
                                $user_roles = $user_data->roles;
                                $primary_role = !empty($user_roles) ? $user_roles[0] : 'مشترک';
                                
                                $role_badge = '';
                                switch ($primary_role) {
                                    case 'administrator':
                                        $role_badge = '<span class="badge bg-danger">مدیر سیستم</span>';
                                        break;
                                    case 'coworking_manager':
                                        $role_badge = '<span class="badge bg-warning text-dark">مدیر فضای کار</span>';
                                        break;
                                    case 'coworking_member':
                                        $role_badge = '<span class="badge bg-success">عضو فضای کار</span>';
                                        break;
                                    default:
                                        $role_badge = '<span class="badge bg-secondary">مشترک</span>';
                                }
                                
                                // وضعیت فعالیت
                                $last_login = get_user_meta($user->ID, 'last_login', true);
                                $is_active = $last_login && strtotime($last_login) > strtotime('-30 days');
                                $status_badge = $is_active ? 
                                    '<span class="badge bg-success">فعال</span>' : 
                                    '<span class="badge bg-secondary">غیرفعال</span>';
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3">
                                            <?php echo get_avatar($user->ID, 40, '', '', array('class' => 'rounded-circle')); ?>
                                        </div>
                                        <div>
                                            <strong><?php echo esc_html($user->display_name); ?></strong>
                                            <div class="text-muted small">@<?php echo esc_html($user->user_login); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo esc_html($user->user_email); ?></td>
                                <td>
                                    <?php echo jdate('Y/m/d', strtotime($user->user_registered)); ?>
                                    <div class="text-muted small">
                                        <?php echo human_time_diff(strtotime($user->user_registered), current_time('timestamp')) . ' پیش'; ?>
                                    </div>
                                </td>
                                <td><?php echo $role_badge; ?></td>
                                <td>
                                    <div class="text-center">
                                        <strong><?php echo $user->reservation_count; ?></strong>
                                        <div class="text-muted small">رزرو</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <strong class="text-success"><?php echo $user->active_reservations; ?></strong>
                                        <div class="text-muted small">فعال</div>
                                    </div>
                                </td>
                                <td>
                                    <strong><?php echo number_format($user->total_spent); ?> تومان</strong>
                                </td>
                                <td><?php echo $status_badge; ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary view-user" 
                                                data-id="<?php echo $user->ID; ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewUserModal">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-info edit-user" 
                                                data-id="<?php echo $user->ID; ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editUserModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-warning change-role" 
                                                data-id="<?php echo $user->ID; ?>"
                                                data-role="<?php echo $primary_role; ?>">
                                            <i class="fas fa-user-cog"></i>
                                        </button>
                                        <?php if ($user->ID != get_current_user_id()): ?>
                                            <button class="btn btn-outline-danger delete-user" 
                                                    data-id="<?php echo $user->ID; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- صفحه‌بندی -->
                <nav aria-label="صفحه‌بندی کاربران">
                    <ul class="pagination justify-content-center">
                        <?php
                        $total_pages = ceil($total_users / $users_per_page);
                        $pagination_args = array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo; قبلی',
                            'next_text' => 'بعدی &raquo;',
                            'total' => $total_pages,
                            'current' => $current_page
                        );
                        
                        if ($total_pages > 1) {
                            echo paginate_links($pagination_args);
                        }
                        ?>
                    </ul>
                </nav>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">هیچ کاربری یافت نشد</h5>
                    <p class="text-muted">لطفاً از فیلترهای دیگر استفاده کنید.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- کاربران جدید امروز -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">کاربران جدید امروز</h5>
                </div>
                <div class="card-body">
                    <?php
                    $new_users = $wpdb->get_results($wpdb->prepare(
                        "SELECT u.* FROM $table_users u 
                        WHERE DATE(u.user_registered) = %s 
                        ORDER BY u.user_registered DESC 
                        LIMIT 5",
                        date('Y-m-d')
                    ));
                    
                    if ($new_users): ?>
                        <div class="list-group">
                            <?php foreach ($new_users as $new_user): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo esc_html($new_user->display_name); ?></strong>
                                        <div class="text-muted small">@<?php echo esc_html($new_user->user_login); ?></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-muted small">
                                            <?php echo human_time_diff(strtotime($new_user->user_registered), current_time('timestamp')) . ' پیش'; ?>
                                        </div>
                                        <small><?php echo $new_user->user_email; ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted py-3">هیچ کاربر جدیدی امروز ثبت نام نکرده است.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">کاربران فعال اخیر</h5>
                </div>
                <div class="card-body">
                    <?php
                    $active_users_list = $wpdb->get_results("
                        SELECT DISTINCT u.* 
                        FROM $table_users u
                        INNER JOIN $table_reservations r ON u.ID = r.user_id
                        WHERE r.status IN ('confirmed', 'active')
                        ORDER BY r.created_at DESC
                        LIMIT 5
                    ");
                    
                    if ($active_users_list): ?>
                        <div class="list-group">
                            <?php foreach ($active_users_list as $active_user): 
                                $reservation_count = $wpdb->get_var($wpdb->prepare(
                                    "SELECT COUNT(*) FROM $table_reservations WHERE user_id = %d AND status IN ('confirmed', 'active')",
                                    $active_user->ID
                                ));
                            ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo esc_html($active_user->display_name); ?></strong>
                                        <div class="text-muted small"><?php echo $reservation_count; ?> رزرو فعال</div>
                                    </div>
                                    <div>
                                        <span class="badge bg-success">فعال</span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted py-3">هیچ کاربر فعالی یافت نشد.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- مودال مشاهده کاربر -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">مشاهده اطلاعات کاربر</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="user-details">
                <!-- محتوای داینامیک -->
            </div>
        </div>
    </div>
</div>

<!-- مودال افزودن کاربر -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">افزودن کاربر جدید</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="add-user-form" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">نام نمایشی *</label>
                        <input type="text" class="form-control" name="display_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">نام کاربری *</label>
                        <input type="text" class="form-control" name="user_login" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">ایمیل *</label>
                        <input type="email" class="form-control" name="user_email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">رمز عبور *</label>
                        <input type="password" class="form-control" name="user_pass" required minlength="6">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">تکرار رمز عبور *</label>
                        <input type="password" class="form-control" name="confirm_pass" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">نقش کاربری *</label>
                        <select class="form-select" name="user_role" required>
                            <option value="coworking_member">عضو فضای کار</option>
                            <option value="coworking_manager">مدیر فضای کار</option>
                            <option value="subscriber">مشترک</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle me-2"></i>
                        پس از ایجاد کاربر، ایمیل خوشامدگویی برای او ارسال خواهد شد.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-user-plus me-2"></i>ایجاد کاربر
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- مودال ویرایش کاربر -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">ویرایش کاربر</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="edit-user-form" method="post">
                <div class="modal-body" id="edit-user-content">
                    <!-- محتوای داینامیک -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>ذخیره تغییرات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // مشاهده اطلاعات کاربر
    $('.view-user').click(function() {
        var userId = $(this).data('id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'coworking_get_user_details',
                user_id: userId,
                nonce: '<?php echo wp_create_nonce('coworking_nonce'); ?>'
            },
            beforeSend: function() {
                $('#user-details').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
            },
            success: function(response) {
                if (response.success) {
                    $('#user-details').html(response.data);
                }
            }
        });
    });
    
    // ویرایش کاربر
    $('.edit-user').click(function() {
        var userId = $(this).data('id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'coworking_get_edit_user_form',
                user_id: userId,
                nonce: '<?php echo wp_create_nonce('coworking_nonce'); ?>'
            },
            beforeSend: function() {
                $('#edit-user-content').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
            },
            success: function(response) {
                if (response.success) {
                    $('#edit-user-content').html(response.data);
                }
            }
        });
    });
    
    // تغییر نقش کاربر
    $('.change-role').click(function() {
        var userId = $(this).data('id');
        var currentRole = $(this).data('role');
        
        var newRole = prompt('لطفاً نقش جدید را وارد کنید:\n1. coworking_member\n2. coworking_manager\n3. subscriber', currentRole);
        
        if (newRole && newRole !== currentRole) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'coworking_change_user_role',
                    user_id: userId,
                    new_role: newRole,
                    nonce: '<?php echo wp_create_nonce('coworking_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        alert('نقش کاربر با موفقیت تغییر کرد.');
                        location.reload();
                    } else {
                        alert('خطا: ' + response.data);
                    }
                }
            });
        }
    });
    
    // حذف کاربر
    $('.delete-user').click(function() {
        if (!confirm('آیا از حذف این کاربر مطمئن هستید؟ این عمل قابل بازگشت نیست.')) {
            return;
        }
        
        var userId = $(this).data('id');
        var button = $(this);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'coworking_delete_user',
                user_id: userId,
                nonce: '<?php echo wp_create_nonce('coworking_nonce'); ?>'
            },
            beforeSend: function() {
                button.html('<span class="spinner-border spinner-border-sm"></span>');
                button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    alert('کاربر با موفقیت حذف شد.');
                    location.reload();
                } else {
                    alert('خطا در حذف کاربر: ' + response.data);
                    button.html('<i class="fas fa-trash"></i>');
                    button.prop('disabled', false);
                }
            }
        });
    });
    
    // افزودن کاربر جدید
    $('#add-user-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData + '&action=coworking_add_user&nonce=<?php echo wp_create_nonce("coworking_nonce"); ?>',
            beforeSend: function() {
                $('#add-user-form button[type="submit"]').html('<span class="spinner-border spinner-border-sm"></span>');
            },
            success: function(response) {
                if (response.success) {
                    alert('کاربر جدید با موفقیت ایجاد شد.');
                    location.reload();
                } else {
                    alert('خطا: ' + response.data);
                    $('#add-user-form button[type="submit"]').html('<i class="fas fa-user-plus me-2"></i>ایجاد کاربر');
                }
            }
        });
    });
    
    // ویرایش کاربر
    $('#edit-user-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData + '&action=coworking_edit_user&nonce=<?php echo wp_create_nonce("coworking_nonce"); ?>',
            beforeSend: function() {
                $('#edit-user-form button[type="submit"]').html('<span class="spinner-border spinner-border-sm"></span>');
            },
            success: function(response) {
                if (response.success) {
                    alert('اطلاعات کاربر با موفقیت به‌روزرسانی شد.');
                    location.reload();
                } else {
                    alert('خطا: ' + response.data);
                    $('#edit-user-form button[type="submit"]').html('<i class="fas fa-save me-2"></i>ذخیره تغییرات');
                }
            }
        });
    });
    
    // اکسپورت کاربران
    $('#export-users').click(function() {
        window.location.href = ajaxurl + '?action=coworking_export_users&' + $('form').serialize();
    });
});
</script>

<style>
.wrap {
    padding: 20px;
}

.card {
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-header {
    border-bottom: none;
    padding: 15px 20px;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}

.user-avatar img {
    width: 40px;
    height: 40px;
}

.badge {
    font-family: 'PeydaWeb';
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
}

.modal-header {
    border-radius: 10px 10px 0 0;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.list-group-item {
    border: 1px solid rgba(0,0,0,.125);
    margin-bottom: -1px;
}
</style>