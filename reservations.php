<?php
if (!current_user_can('manage_options') && !current_user_can('coworking_manager')) {
    wp_die('شما دسترسی لازم را ندارید.');
}

global $wpdb;
$table_reservations = $wpdb->prefix . 'coworking_reservations';
$table_spaces = $wpdb->prefix . 'coworking_spaces';
$table_users = $wpdb->prefix . 'users';

// پارامترهای فیلتر
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
$space_filter = isset($_GET['space_id']) ? intval($_GET['space_id']) : 0;
$user_filter = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// ساخت شرط WHERE
$where_conditions = array();
if ($status_filter !== 'all') {
    $where_conditions[] = $wpdb->prepare("r.status = %s", $status_filter);
}
if ($date_from) {
    $where_conditions[] = $wpdb->prepare("r.start_date >= %s", $date_from);
}
if ($date_to) {
    $where_conditions[] = $wpdb->prepare("r.end_date <= %s", $date_to);
}
if ($space_filter > 0) {
    $where_conditions[] = $wpdb->prepare("r.space_id = %d", $space_filter);
}
if ($user_filter > 0) {
    $where_conditions[] = $wpdb->prepare("r.user_id = %d", $user_filter);
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// دریافت رزروها
$reservations = $wpdb->get_results("
    SELECT r.*, 
           s.name as space_name,
           u.display_name as user_name,
           u.user_email as user_email
    FROM $table_reservations r
    LEFT JOIN $table_spaces s ON r.space_id = s.id
    LEFT JOIN $table_users u ON r.user_id = u.ID
    $where_clause
    ORDER BY r.created_at DESC
    LIMIT 50
");

// دریافت فضاها برای فیلتر
$spaces = $wpdb->get_results("SELECT id, name FROM $table_spaces WHERE status = 'active' ORDER BY name");

// دریافت کاربران برای فیلتر
$users = $wpdb->get_results("SELECT ID, display_name, user_email FROM $table_users ORDER BY display_name LIMIT 100");
?>

<div class="wrap">
    <h1 class="wp-heading-inline" style="font-family: 'PeydaWeb';">
        <i class="fas fa-calendar-alt me-2"></i>مدیریت رزروها
    </h1>
    
    <!-- فرم فیلتر -->
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>فیلتر رزروها</h5>
        </div>
        <div class="card-body">
            <form method="get" action="" class="row g-3">
                <input type="hidden" name="page" value="coworking-reservations">
                
                <div class="col-md-3">
                    <label class="form-label">وضعیت</label>
                    <select name="status" class="form-select">
                        <option value="all" <?php selected($status_filter, 'all'); ?>>همه وضعیت‌ها</option>
                        <option value="pending" <?php selected($status_filter, 'pending'); ?>>در انتظار</option>
                        <option value="confirmed" <?php selected($status_filter, 'confirmed'); ?>>تأیید شده</option>
                        <option value="active" <?php selected($status_filter, 'active'); ?>>فعال</option>
                        <option value="completed" <?php selected($status_filter, 'completed'); ?>>اتمام یافته</option>
                        <option value="cancelled" <?php selected($status_filter, 'cancelled'); ?>>لغو شده</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">از تاریخ</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">تا تاریخ</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">فضا</label>
                    <select name="space_id" class="form-select">
                        <option value="0">همه فضاها</option>
                        <?php foreach ($spaces as $space): ?>
                            <option value="<?php echo $space->id; ?>" <?php selected($space_filter, $space->id); ?>>
                                <?php echo esc_html($space->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">کاربر</label>
                    <select name="user_id" class="form-select">
                        <option value="0">همه کاربران</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user->ID; ?>" <?php selected($user_filter, $user->ID); ?>>
                                <?php echo esc_html($user->display_name); ?> (<?php echo $user->user_email; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-8 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-2"></i>اعمال فیلتر
                    </button>
                    <a href="?page=coworking-reservations" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-2"></i>بازنشانی
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- آمار سریع -->
    <div class="row mt-4">
        <?php
        $status_counts = $wpdb->get_results("
            SELECT status, COUNT(*) as count 
            FROM $table_reservations 
            GROUP BY status
        ");
        
        $status_stats = array();
        foreach ($status_counts as $stat) {
            $status_stats[$stat->status] = $stat->count;
        }
        ?>
        
        <div class="col-md-2 col-sm-4 mb-3">
            <div class="card text-center bg-primary text-white">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">کل رزروها</h6>
                    <h4><?php echo array_sum($status_stats); ?></h4>
                </div>
            </div>
        </div>
        
        <div class="col-md-2 col-sm-4 mb-3">
            <div class="card text-center bg-warning text-dark">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">در انتظار</h6>
                    <h4><?php echo $status_stats['pending'] ?? 0; ?></h4>
                </div>
            </div>
        </div>
        
        <div class="col-md-2 col-sm-4 mb-3">
            <div class="card text-center bg-success text-white">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">تأیید شده</h6>
                    <h4><?php echo $status_stats['confirmed'] ?? 0; ?></h4>
                </div>
            </div>
        </div>
        
        <div class="col-md-2 col-sm-4 mb-3">
            <div class="card text-center bg-info text-white">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">فعال</h6>
                    <h4><?php echo $status_stats['active'] ?? 0; ?></h4>
                </div>
            </div>
        </div>
        
        <div class="col-md-2 col-sm-4 mb-3">
            <div class="card text-center bg-secondary text-white">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">اتمام یافته</h6>
                    <h4><?php echo $status_stats['completed'] ?? 0; ?></h4>
                </div>
            </div>
        </div>
        
        <div class="col-md-2 col-sm-4 mb-3">
            <div class="card text-center bg-danger text-white">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">لغو شده</h6>
                    <h4><?php echo $status_stats['cancelled'] ?? 0; ?></h4>
                </div>
            </div>
        </div>
    </div>
    
    <!-- جدول رزروها -->
    <div class="card mt-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">لیست رزروها</h5>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#newReservationModal">
                <i class="fas fa-plus me-2"></i>رزرو جدید
            </button>
        </div>
        <div class="card-body">
            <?php if ($reservations): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>شماره</th>
                                <th>کاربر</th>
                                <th>فضا</th>
                                <th>تاریخ</th>
                                <th>مدت</th>
                                <th>مبلغ</th>
                                <th>وضعیت</th>
                                <th>تاریخ ثبت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $reservation): 
                                $status_badge = '';
                                $status_class = '';
                                
                                switch ($reservation->status) {
                                    case 'pending':
                                        $status_badge = 'در انتظار';
                                        $status_class = 'warning';
                                        break;
                                    case 'confirmed':
                                        $status_badge = 'تأیید شده';
                                        $status_class = 'success';
                                        break;
                                    case 'active':
                                        $status_badge = 'فعال';
                                        $status_class = 'info';
                                        break;
                                    case 'completed':
                                        $status_badge = 'اتمام یافته';
                                        $status_class = 'secondary';
                                        break;
                                    case 'cancelled':
                                        $status_badge = 'لغو شده';
                                        $status_class = 'danger';
                                        break;
                                }
                                
                                $date_range = jdate('Y/m/d', strtotime($reservation->start_date)) . ' تا ' . 
                                            jdate('Y/m/d', strtotime($reservation->end_date));
                            ?>
                            <tr>
                                <td><code>#<?php echo $reservation->id; ?></code></td>
                                <td>
                                    <div><strong><?php echo esc_html($reservation->user_name); ?></strong></div>
                                    <small class="text-muted"><?php echo esc_html($reservation->user_email); ?></small>
                                </td>
                                <td><?php echo esc_html($reservation->space_name); ?></td>
                                <td><?php echo $date_range; ?></td>
                                <td><?php echo $reservation->total_days; ?> روز</td>
                                <td><?php echo number_format($reservation->final_amount); ?> تومان</td>
                                <td>
                                    <span class="badge bg-<?php echo $status_class; ?>">
                                        <?php echo $status_badge; ?>
                                    </span>
                                </td>
                                <td><?php echo jdate('Y/m/d H:i', strtotime($reservation->created_at)); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary view-reservation" 
                                                data-id="<?php echo $reservation->id; ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewReservationModal">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if ($reservation->status === 'pending'): ?>
                                            <button class="btn btn-outline-success confirm-reservation" 
                                                    data-id="<?php echo $reservation->id; ?>">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if (in_array($reservation->status, ['pending', 'confirmed'])): ?>
                                            <button class="btn btn-outline-danger cancel-reservation" 
                                                    data-id="<?php echo $reservation->id; ?>">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-outline-info" 
                                                onclick="window.open('<?php echo admin_url('admin.php?page=coworking-finance&action=create_invoice&reservation_id=' . $reservation->id); ?>', '_blank')">
                                            <i class="fas fa-file-invoice"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- صفحه‌بندی -->
                <nav aria-label="صفحه‌بندی رزروها">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">قبلی</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">بعدی</a>
                        </li>
                    </ul>
                </nav>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">هیچ رزروی یافت نشد</h5>
                    <p class="text-muted">با استفاده از فیلترهای دیگر جستجو کنید یا رزرو جدیدی ایجاد کنید.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- مودال مشاهده رزرو -->
<div class="modal fade" id="viewReservationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">مشاهده جزئیات رزرو</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reservation-details">
                <!-- محتوای داینامیک -->
            </div>
        </div>
    </div>
</div>

<!-- مودال ایجاد رزرو جدید -->
<div class="modal fade" id="newReservationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">ایجاد رزرو جدید</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="admin-reservation-form">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">کاربر *</label>
                                <select class="form-select" id="admin-user-id" name="user_id" required>
                                    <option value="">انتخاب کاربر...</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user->ID; ?>">
                                            <?php echo esc_html($user->display_name); ?> (<?php echo $user->user_email; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">فضا *</label>
                                <select class="form-select" id="admin-space-id" name="space_id" required>
                                    <option value="">انتخاب فضا...</option>
                                    <?php foreach ($spaces as $space): ?>
                                        <option value="<?php echo $space->id; ?>" data-price-daily="<?php echo $space->price_daily; ?>">
                                            <?php echo esc_html($space->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">تاریخ شروع *</label>
                                <input type="date" class="form-control" id="admin-start-date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">تاریخ پایان *</label>
                                <input type="date" class="form-control" id="admin-end-date" name="end_date" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">مدت رزرو *</label>
                                <select class="form-select" id="admin-duration-type" name="duration_type" required>
                                    <option value="daily">روزانه</option>
                                    <option value="weekly">هفتگی</option>
                                    <option value="monthly">ماهانه</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">وضعیت *</label>
                                <select class="form-select" name="status" required>
                                    <option value="pending">در انتظار</option>
                                    <option value="confirmed">تأیید شده</option>
                                    <option value="active">فعال</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">توضیحات</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                    
                    <div class="alert alert-info" id="admin-price-summary" style="display: none;">
                        <h6>خلاصه قیمت:</h6>
                        <p class="mb-1">قیمت پایه: <span id="admin-base-price">۰</span> تومان</p>
                        <p class="mb-0">مبلغ نهایی: <span id="admin-final-price">۰</span> تومان</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>ایجاد رزرو
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // مشاهده جزئیات رزرو
    $('.view-reservation').click(function() {
        var reservationId = $(this).data('id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'coworking_get_reservation_details',
                reservation_id: reservationId,
                nonce: '<?php echo wp_create_nonce('coworking_nonce'); ?>'
            },
            beforeSend: function() {
                $('#reservation-details').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
            },
            success: function(response) {
                $('#reservation-details').html(response.data);
            }
        });
    });
    
    // تأیید رزرو
    $('.confirm-reservation').click(function() {
        if (!confirm('آیا از تأیید این رزرو مطمئن هستید؟')) {
            return;
        }
        
        var reservationId = $(this).data('id');
        var button = $(this);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'coworking_confirm_reservation',
                reservation_id: reservationId,
                nonce: '<?php echo wp_create_nonce('coworking_nonce'); ?>'
            },
            beforeSend: function() {
                button.html('<span class="spinner-border spinner-border-sm"></span>');
                button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    alert('رزرو با موفقیت تأیید شد.');
                    location.reload();
                } else {
                    alert('خطا: ' + response.data);
                    button.html('<i class="fas fa-check"></i>');
                    button.prop('disabled', false);
                }
            }
        });
    });
    
    // لغو رزرو
    $('.cancel-reservation').click(function() {
        if (!confirm('آیا از لغو این رزرو مطمئن هستید؟')) {
            return;
        }
        
        var reservationId = $(this).data('id');
        var button = $(this);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'coworking_cancel_reservation',
                reservation_id: reservationId,
                nonce: '<?php echo wp_create_nonce('coworking_nonce'); ?>'
            },
            beforeSend: function() {
                button.html('<span class="spinner-border spinner-border-sm"></span>');
                button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    alert('رزرو با موفقیت لغو شد.');
                    location.reload();
                } else {
                    alert('خطا: ' + response.data);
                    button.html('<i class="fas fa-times"></i>');
                    button.prop('disabled', false);
                }
            }
        });
    });
    
    // محاسبه قیمت در فرم ایجاد رزرو
    $('#admin-space-id, #admin-start-date, #admin-end-date, #admin-duration-type').on('change', function() {
        var spaceId = $('#admin-space-id').val();
        var startDate = $('#admin-start-date').val();
        var endDate = $('#admin-end-date').val();
        var durationType = $('#admin-duration-type').val();
        
        if (spaceId && startDate && endDate) {
            // محاسبه تعداد روزها
            var start = new Date(startDate);
            var end = new Date(endDate);
            var diffTime = Math.abs(end - start);
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            // دریافت قیمت روزانه
            var dailyPrice = parseFloat($('#admin-space-id option:selected').data('price-daily')) || 0;
            var totalPrice = dailyPrice * diffDays;
            
            // نمایش قیمت
            $('#admin-base-price').text(totalPrice.toLocaleString());
            $('#admin-final-price').text(totalPrice.toLocaleString());
            $('#admin-price-summary').show();
        }
    });
    
    // ایجاد رزرو توسط مدیر
    $('#admin-reservation-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData + '&action=coworking_admin_create_reservation&nonce=<?php echo wp_create_nonce("coworking_nonce"); ?>',
            beforeSend: function() {
                $('#admin-reservation-form button[type="submit"]').html('<span class="spinner-border spinner-border-sm"></span>');
            },
            success: function(response) {
                if (response.success) {
                    alert('رزرو با موفقیت ایجاد شد!');
                    location.reload();
                } else {
                    alert('خطا: ' + response.data);
                    $('#admin-reservation-form button[type="submit"]').html('<i class="fas fa-check me-2"></i>ایجاد رزرو');
                }
            }
        });
    });
    
    // اکسپورت به اکسل
    $('#export-excel').click(function() {
        window.location.href = ajaxurl + '?action=coworking_export_reservations&' + $('form').serialize();
    });
});
</script>

<style>
.wp-heading-inline {
    color: #196ab4;
}

.card {
    border-radius: 10px;
    overflow: hidden;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.modal-header {
    border-radius: 10px 10px 0 0;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}
</style>