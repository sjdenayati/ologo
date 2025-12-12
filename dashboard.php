<?php
if (!current_user_can('manage_options') && !current_user_can('coworking_manager')) {
    wp_die('شما دسترسی لازم را ندارید.');
}

global $wpdb;
$table_reservations = $wpdb->prefix . 'coworking_reservations';
$table_invoices = $wpdb->prefix . 'coworking_invoices';
$table_tickets = $wpdb->prefix . 'coworking_tickets';
$table_spaces = $wpdb->prefix . 'coworking_spaces';

// آمار کلی
$total_reservations = $wpdb->get_var("SELECT COUNT(*) FROM $table_reservations");
$active_reservations = $wpdb->get_var("SELECT COUNT(*) FROM $table_reservations WHERE status IN ('confirmed', 'active')");
$pending_invoices = $wpdb->get_var("SELECT COUNT(*) FROM $table_invoices WHERE payment_status = 'pending'");
$total_revenue = $wpdb->get_var("SELECT SUM(total_amount) FROM $table_invoices WHERE payment_status = 'paid'");
$open_tickets = $wpdb->get_var("SELECT COUNT(*) FROM $table_tickets WHERE status IN ('open', 'in_progress')");
$active_spaces = $wpdb->get_var("SELECT COUNT(*) FROM $table_spaces WHERE status = 'active'");

// رزروهای امروز
$today = date('Y-m-d');
$today_reservations = $wpdb->get_results($wpdb->prepare(
    "SELECT r.*, u.display_name, s.name as space_name 
    FROM $table_reservations r
    LEFT JOIN {$wpdb->prefix}users u ON r.user_id = u.ID
    LEFT JOIN $table_spaces s ON r.space_id = s.id
    WHERE r.start_date <= %s AND r.end_date >= %s AND r.status IN ('confirmed', 'active')
    ORDER BY r.start_date",
    $today, $today
));
?>

<div class="wrap">
    <h1 class="wp-heading-inline" style="font-family: 'PeydaWeb'; color: #196ab4;">
        <i class="fas fa-building me-2"></i>داشبورد مدیریت فضای کار اشتراکی
    </h1>
    
    <!-- آمار کلی -->
    <div class="row mt-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">کل رزروها</h6>
                            <h2><?php echo $total_reservations; ?></h2>
                        </div>
                        <i class="fas fa-calendar-alt fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">رزروهای فعال</h6>
                            <h2><?php echo $active_reservations; ?></h2>
                        </div>
                        <i class="fas fa-check-circle fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">فاکتورهای پرداخت نشده</h6>
                            <h2><?php echo $pending_invoices; ?></h2>
                        </div>
                        <i class="fas fa-file-invoice-dollar fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">درآمد کل</h6>
                            <h2><?php echo number_format($total_revenue); ?> تومان</h2>
                        </div>
                        <i class="fas fa-chart-line fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- رزروهای امروز -->
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>رزروهای امروز (<?php echo jdate('Y/m/d'); ?>)</h5>
        </div>
        <div class="card-body">
            <?php if ($today_reservations): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>کاربر</th>
                                <th>فضا</th>
                                <th>مدت</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($today_reservations as $reservation): ?>
                            <tr>
                                <td><?php echo esc_html($reservation->display_name); ?></td>
                                <td><?php echo esc_html($reservation->space_name); ?></td>
                                <td><?php echo $reservation->total_days; ?> روز</td>
                                <td>
                                    <span class="badge bg-<?php echo $reservation->status === 'active' ? 'success' : 'info'; ?>">
                                        <?php echo $reservation->status === 'active' ? 'فعال' : 'تأیید شده'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="button button-small view-reservation" data-id="<?php echo $reservation->id; ?>">مشاهده</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-muted py-3">هیچ رزروی برای امروز وجود ندارد.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- چارت‌های آماری -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>آمار رزروها در هفته گذشته</h5>
                </div>
                <div class="card-body">
                    <canvas id="reservationsChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>وضعیت فضاها</h5>
                </div>
                <div class="card-body">
                    <canvas id="spacesChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- لینک‌های سریع -->
    <div class="row mt-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <a href="?page=coworking-reservations" class="card quick-link-card text-decoration-none">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-plus fa-2x text-primary mb-2"></i>
                    <h6>مدیریت رزروها</h6>
                </div>
            </a>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <a href="?page=coworking-spaces" class="card quick-link-card text-decoration-none">
                <div class="card-body text-center">
                    <i class="fas fa-th-large fa-2x text-success mb-2"></i>
                    <h6>مدیریت فضاها</h6>
                </div>
            </a>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <a href="?page=coworking-finance" class="card quick-link-card text-decoration-none">
                <div class="card-body text-center">
                    <i class="fas fa-money-bill-wave fa-2x text-warning mb-2"></i>
                    <h6>مدیریت مالی</h6>
                </div>
            </a>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <a href="?page=coworking-tickets" class="card quick-link-card text-decoration-none">
                <div class="card-body text-center">
                    <i class="fas fa-headset fa-2x text-info mb-2"></i>
                    <h6>مدیریت تیکت‌ها</h6>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
.quick-link-card {
    transition: transform 0.3s;
    border: 1px solid #dee2e6;
}

.quick-link-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.card {
    border-radius: 10px;
    overflow: hidden;
}
</style>

<script>
jQuery(document).ready(function($) {
    // چارت رزروها
    var ctx1 = document.getElementById('reservationsChart').getContext('2d');
    var reservationsChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'],
            datasets: [{
                label: 'تعداد رزرو',
                data: [12, 19, 8, 15, 12, 6, 3],
                borderColor: '#196ab4',
                backgroundColor: 'rgba(25, 106, 180, 0.1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    
    // چارت فضاها
    var ctx2 = document.getElementById('spacesChart').getContext('2d');
    var spacesChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['صندلی اشتراکی', 'صندلی اختصاصی', 'اتاق خصوصی', 'اتاق جلسه'],
            datasets: [{
                data: [25, 15, 8, 4],
                backgroundColor: [
                    '#4e73df',
                    '#1cc88a',
                    '#36b9cc',
                    '#f6c23e'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>