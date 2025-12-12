<?php
/*
Template Name: پنل کاربری
*/
get_header();

if (!is_user_logged_in()) {
    wp_redirect(home_url('/login'));
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

global $wpdb;
$table_reservations = $wpdb->prefix . 'coworking_reservations';
$table_invoices = $wpdb->prefix . 'coworking_invoices';
$table_tickets = $wpdb->prefix . 'coworking_tickets';

// آمار کاربر
$active_reservations = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM $table_reservations 
    WHERE user_id = %d AND status IN ('confirmed', 'active')",
    $user_id
));

$pending_invoices = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM $table_invoices 
    WHERE user_id = %d AND payment_status = 'pending'",
    $user_id
));

$open_tickets = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM $table_tickets 
    WHERE user_id = %d AND status IN ('open', 'in_progress')",
    $user_id
));

// رزروهای اخیر
$recent_reservations = $wpdb->get_results($wpdb->prepare(
    "SELECT r.*, s.name as space_name 
    FROM $table_reservations r
    LEFT JOIN {$wpdb->prefix}coworking_spaces s ON r.space_id = s.id
    WHERE r.user_id = %d 
    ORDER BY r.created_at DESC 
    LIMIT 5",
    $user_id
));
?>

<div class="container my-5">
    <div class="row">
        <!-- سایدبار -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="user-sidebar p-4 rounded shadow-sm" style="background: #f8f9fa; border-right: 3px solid #196ab4;">
                <div class="user-info text-center mb-4">
                    <div class="user-avatar mb-3">
                        <?php echo get_avatar($user_id, 100, '', '', array('class' => 'rounded-circle border border-3 border-primary')); ?>
                    </div>
                    <h4 style="font-family: 'PeydaWeb'; color: #196ab4;"><?php echo esc_html($current_user->display_name); ?></h4>
                    <p class="text-muted"><?php echo esc_html($current_user->user_email); ?></p>
                    <span class="badge bg-primary">عضو فضای کار</span>
                </div>
                
                <nav class="user-menu">
                    <ul class="list-unstyled" style="font-family: 'PeydaWeb';">
                        <li class="mb-2">
                            <a href="<?php echo home_url('/dashboard'); ?>" class="d-block p-3 rounded text-decoration-none <?php echo is_page('dashboard') ? 'bg-primary text-white' : 'text-dark hover-bg-light'; ?>">
                                <i class="fas fa-tachometer-alt me-2"></i> پیشخوان
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo home_url('/reservation'); ?>" class="d-block p-3 rounded text-decoration-none text-dark hover-bg-light">
                                <i class="fas fa-calendar-plus me-2"></i> رزرو جدید
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo home_url('/my-reservations'); ?>" class="d-block p-3 rounded text-decoration-none text-dark hover-bg-light">
                                <i class="fas fa-calendar-check me-2"></i> رزروهای من
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo home_url('/invoices'); ?>" class="d-block p-3 rounded text-decoration-none text-dark hover-bg-light">
                                <i class="fas fa-file-invoice me-2"></i> فاکتورها
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo home_url('/tickets'); ?>" class="d-block p-3 rounded text-decoration-none text-dark hover-bg-light">
                                <i class="fas fa-headset me-2"></i> تیکت‌ها
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo home_url('/profile'); ?>" class="d-block p-3 rounded text-decoration-none text-dark hover-bg-light">
                                <i class="fas fa-user-edit me-2"></i> ویرایش پروفایل
                            </a>
                        </li>
                        <li class="mt-4 pt-3 border-top">
                            <a href="<?php echo wp_logout_url(home_url()); ?>" class="d-block p-3 rounded text-decoration-none text-danger hover-bg-light">
                                <i class="fas fa-sign-out-alt me-2"></i> خروج
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        
        <!-- محتوای اصلی -->
        <div class="col-lg-9 col-md-8">
            <!-- آمار -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="stat-card text-center p-4 rounded shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <i class="fas fa-calendar-alt fa-2x mb-3"></i>
                        <h3><?php echo $active_reservations; ?></h3>
                        <p>رزروهای فعال</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="stat-card text-center p-4 rounded shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                        <i class="fas fa-file-invoice-dollar fa-2x mb-3"></i>
                        <h3><?php echo $pending_invoices; ?></h3>
                        <p>فاکتورهای پرداخت نشده</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="stat-card text-center p-4 rounded shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                        <i class="fas fa-headset fa-2x mb-3"></i>
                        <h3><?php echo $open_tickets; ?></h3>
                        <p>تیکت‌های باز</p>
                    </div>
                </div>
            </div>
            
            <!-- رزروهای اخیر -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white" style="font-family: 'PeydaWeb';">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>رزروهای اخیر</h5>
                </div>
                <div class="card-body">
                    <?php if ($recent_reservations): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>فضا</th>
                                        <th>تاریخ شروع</th>
                                        <th>تاریخ پایان</th>
                                        <th>وضعیت</th>
                                        <th>مبلغ</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_reservations as $reservation): 
                                        $status_badge = '';
                                        switch ($reservation->status) {
                                            case 'confirmed':
                                                $status_badge = '<span class="badge bg-success">تأیید شده</span>';
                                                break;
                                            case 'pending':
                                                $status_badge = '<span class="badge bg-warning text-dark">در انتظار</span>';
                                                break;
                                            case 'active':
                                                $status_badge = '<span class="badge bg-info">فعال</span>';
                                                break;
                                            case 'cancelled':
                                                $status_badge = '<span class="badge bg-danger">لغو شده</span>';
                                                break;
                                            default:
                                                $status_badge = '<span class="badge bg-secondary">' . $reservation->status . '</span>';
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html($reservation->space_name); ?></td>
                                        <td><?php echo jdate('Y/m/d', strtotime($reservation->start_date)); ?></td>
                                        <td><?php echo jdate('Y/m/d', strtotime($reservation->end_date)); ?></td>
                                        <td><?php echo $status_badge; ?></td>
                                        <td><?php echo number_format($reservation->final_amount); ?> تومان</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reservationModal<?php echo $reservation->id; ?>">
                                                جزئیات
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- مودال جزئیات -->
                                    <div class="modal fade" id="reservationModal<?php echo $reservation->id; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">جزئیات رزرو</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>فضا:</strong> <?php echo esc_html($reservation->space_name); ?></p>
                                                    <p><strong>مدت:</strong> <?php echo $reservation->total_days; ?> روز</p>
                                                    <p><strong>از تاریخ:</strong> <?php echo jdate('Y/m/d', strtotime($reservation->start_date)); ?></p>
                                                    <p><strong>تا تاریخ:</strong> <?php echo jdate('Y/m/d', strtotime($reservation->end_date)); ?></p>
                                                    <p><strong>مبلغ کل:</strong> <?php echo number_format($reservation->total_amount); ?> تومان</p>
                                                    <p><strong>تخفیف:</strong> <?php echo number_format($reservation->discount_amount); ?> تومان</p>
                                                    <p><strong>مبلغ نهایی:</strong> <?php echo number_format($reservation->final_amount); ?> تومان</p>
                                                    <p><strong>وضعیت:</strong> <?php echo $status_badge; ?></p>
                                                    <?php if ($reservation->notes): ?>
                                                        <p><strong>یادداشت:</strong> <?php echo esc_html($reservation->notes); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                                                    <?php if ($reservation->status === 'pending'): ?>
                                                        <button class="btn btn-danger cancel-reservation" data-id="<?php echo $reservation->id; ?>">لغو رزرو</button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">هیچ رزروی یافت نشد.</p>
                            <a href="<?php echo home_url('/reservation'); ?>" class="btn btn-primary">رزرو جدید</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- تیکت‌های اخیر -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white" style="font-family: 'PeydaWeb';">
                    <h5 class="mb-0"><i class="fas fa-headset me-2"></i>آخرین تیکت‌ها</h5>
                </div>
                <div class="card-body">
                    <?php
                    $recent_tickets = $wpdb->get_results($wpdb->prepare(
                        "SELECT * FROM $table_tickets 
                        WHERE user_id = %d 
                        ORDER BY created_at DESC 
                        LIMIT 3",
                        $user_id
                    ));
                    
                    if ($recent_tickets): ?>
                        <div class="list-group">
                            <?php foreach ($recent_tickets as $ticket): 
                                $priority_color = '';
                                switch ($ticket->priority) {
                                    case 'urgent': $priority_color = 'danger'; break;
                                    case 'high': $priority_color = 'warning'; break;
                                    case 'medium': $priority_color = 'info'; break;
                                    default: $priority_color = 'secondary';
                                }
                                
                                $status_color = '';
                                switch ($ticket->status) {
                                    case 'open': $status_color = 'primary'; break;
                                    case 'in_progress': $status_color = 'warning'; break;
                                    case 'resolved': $status_color = 'success'; break;
                                    default: $status_color = 'secondary';
                                }
                            ?>
                            <a href="<?php echo home_url('/ticket/' . $ticket->id); ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo esc_html($ticket->subject); ?></h6>
                                    <small class="text-muted"><?php echo human_time_diff(strtotime($ticket->created_at), current_time('timestamp')) . ' پیش'; ?></small>
                                </div>
                                <p class="mb-1"><?php echo wp_trim_words($ticket->message, 20); ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div>
                                        <span class="badge bg-<?php echo $priority_color; ?> me-2">اولویت: <?php echo $ticket->priority; ?></span>
                                        <span class="badge bg-<?php echo $status_color; ?>"><?php echo $ticket->status; ?></span>
                                    </div>
                                    <small>شماره تیکت: <?php echo $ticket->ticket_number; ?></small>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="<?php echo home_url('/tickets'); ?>" class="btn btn-outline-info">مشاهده همه تیکت‌ها</a>
                            <a href="<?php echo home_url('/new-ticket'); ?>" class="btn btn-info">تیکت جدید</a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <p class="text-muted">هیچ تیکتی یافت نشد.</p>
                            <a href="<?php echo home_url('/new-ticket'); ?>" class="btn btn-info">ایجاد تیکت جدید</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // لغو رزرو
    $('.cancel-reservation').click(function() {
        if (!confirm('آیا از لغو این رزرو مطمئن هستید؟')) {
            return;
        }
        
        var reservationId = $(this).data('id');
        var button = $(this);
        
        $.ajax({
            url: coworking_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'cancel_reservation',
                reservation_id: reservationId,
                nonce: coworking_ajax.nonce
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
                    alert('خطا در لغو رزرو: ' + response.data);
                    button.html('لغو رزرو');
                    button.prop('disabled', false);
                }
            }
        });
    });
});
</script>

<?php get_footer(); ?>