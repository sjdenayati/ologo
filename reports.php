<?php
if (!current_user_can('manage_options') && !current_user_can('coworking_manager')) {
    wp_die('شما دسترسی لازم را ندارید.');
}

global $wpdb;
$table_reservations = $wpdb->prefix . 'coworking_reservations';
$table_invoices = $wpdb->prefix . 'coworking_invoices';
$table_spaces = $wpdb->prefix . 'coworking_spaces';
$table_users = $wpdb->prefix . 'users';

// پارامترهای گزارش
$report_type = isset($_GET['report_type']) ? sanitize_text_field($_GET['report_type']) : 'reservations';
$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : date('Y-m-d');
$space_id = isset($_GET['space_id']) ? intval($_GET['space_id']) : 0;

// تولید گزارش
$report_data = array();
$report_title = '';

switch ($report_type) {
    case 'reservations':
        $report_title = 'گزارش رزروها';
        $where_conditions = array("r.created_at BETWEEN '$date_from' AND '$date_to'");
        if ($space_id > 0) {
            $where_conditions[] = "r.space_id = $space_id";
        }
        $where_clause = implode(' AND ', $where_conditions);
        
        $report_data = $wpdb->get_results("
            SELECT r.*, 
                   s.name as space_name,
                   u.display_name as user_name,
                   u.user_email as user_email
            FROM $table_reservations r
            LEFT JOIN $table_spaces s ON r.space_id = s.id
            LEFT JOIN $table_users u ON r.user_id = u.ID
            WHERE $where_clause
            ORDER BY r.created_at DESC
        ");
        break;
        
    case 'revenue':
        $report_title = 'گزارش درآمد';
        $report_data = $wpdb->get_results("
            SELECT 
                DATE(i.created_at) as date,
                COUNT(*) as invoice_count,
                SUM(i.total_amount) as total_revenue,
                SUM(i.discount_amount) as total_discount
            FROM $table_invoices i
            WHERE i.created_at BETWEEN '$date_from' AND '$date_to'
            AND i.payment_status = 'paid'
            GROUP BY DATE(i.created_at)
            ORDER BY date DESC
        ");
        break;
        
    case 'users':
        $report_title = 'گزارش کاربران';
        $report_data = $wpdb->get_results("
            SELECT 
                u.ID,
                u.display_name,
                u.user_email,
                u.user_registered,
                COUNT(r.id) as reservation_count,
                SUM(CASE WHEN r.status IN ('confirmed', 'active') THEN 1 ELSE 0 END) as active_reservations
            FROM $table_users u
            LEFT JOIN $table_reservations r ON u.ID = r.user_id
            WHERE u.user_registered BETWEEN '$date_from' AND '$date_to'
            GROUP BY u.ID
            ORDER BY u.user_registered DESC
        ");
        break;
        
    case 'spaces':
        $report_title = 'گزارش فضاها';
        $report_data = $wpdb->get_results("
            SELECT 
                s.id,
                s.name,
                s.type,
                s.capacity,
                s.price_daily,
                COUNT(r.id) as reservation_count,
                SUM(CASE WHEN r.status IN ('confirmed', 'active') THEN 1 ELSE 0 END) as active_reservations,
                SUM(r.final_amount) as total_revenue
            FROM $table_spaces s
            LEFT JOIN $table_reservations r ON s.id = r.space_id
            WHERE (r.created_at BETWEEN '$date_from' AND '$date_to' OR r.created_at IS NULL)
            GROUP BY s.id
            ORDER BY reservation_count DESC
        ");
        break;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline" style="font-family: 'PeydaWeb';">
        <i class="fas fa-chart-bar me-2"></i>گزارش‌گیری از سامانه
    </h1>
    
    <!-- فرم فیلتر گزارش -->
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>تنظیمات گزارش</h5>
        </div>
        <div class="card-body">
            <form method="get" action="" class="row g-3">
                <input type="hidden" name="page" value="coworking-reports">
                
                <div class="col-md-3">
                    <label class="form-label">نوع گزارش</label>
                    <select name="report_type" class="form-select" id="report-type">
                        <option value="reservations" <?php selected($report_type, 'reservations'); ?>>رزروها</option>
                        <option value="revenue" <?php selected($report_type, 'revenue'); ?>>درآمد</option>
                        <option value="users" <?php selected($report_type, 'users'); ?>>کاربران</option>
                        <option value="spaces" <?php selected($report_type, 'spaces'); ?>>فضاها</option>
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
                    <label class="form-label">فضا (برای گزارش رزروها)</label>
                    <select name="space_id" class="form-select" id="space-filter">
                        <option value="0">همه فضاها</option>
                        <?php 
                        $spaces = $wpdb->get_results("SELECT id, name FROM $table_spaces WHERE status = 'active'");
                        foreach ($spaces as $space): ?>
                            <option value="<?php echo $space->id; ?>" <?php selected($space_id, $space->id); ?>>
                                <?php echo esc_html($space->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-chart-bar me-2"></i>نمایش گزارش
                    </button>
                    
                    <button type="button" class="btn btn-success me-2" onclick="exportReport('excel')">
                        <i class="fas fa-file-excel me-2"></i>خروجی Excel
                    </button>
                    
                    <button type="button" class="btn btn-secondary me-2" onclick="exportReport('pdf')">
                        <i class="fas fa-file-pdf me-2"></i>خروجی PDF
                    </button>
                    
                    <button type="button" class="btn btn-info" onclick="printReport()">
                        <i class="fas fa-print me-2"></i>چاپ گزارش
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- خلاصه گزارش -->
    <div class="row mt-4">
        <?php
        // محاسبه آمار بر اساس نوع گزارش
        $stats = array();
        
        switch ($report_type) {
            case 'reservations':
                $stats['total'] = count($report_data);
                $stats['confirmed'] = count(array_filter($report_data, function($item) {
                    return $item->status === 'confirmed';
                }));
                $stats['active'] = count(array_filter($report_data, function($item) {
                    return $item->status === 'active';
                }));
                $stats['revenue'] = array_sum(array_column($report_data, 'final_amount'));
                break;
                
            case 'revenue':
                $stats['total_invoices'] = array_sum(array_column($report_data, 'invoice_count'));
                $stats['total_revenue'] = array_sum(array_column($report_data, 'total_revenue'));
                $stats['total_discount'] = array_sum(array_column($report_data, 'total_discount'));
                $stats['average'] = $stats['total_revenue'] / max($stats['total_invoices'], 1);
                break;
                
            case 'users':
                $stats['total_users'] = count($report_data);
                $stats['active_users'] = count(array_filter($report_data, function($item) {
                    return $item->active_reservations > 0;
                }));
                $stats['total_reservations'] = array_sum(array_column($report_data, 'reservation_count'));
                $stats['average_per_user'] = $stats['total_reservations'] / max($stats['total_users'], 1);
                break;
                
            case 'spaces':
                $stats['total_spaces'] = count($report_data);
                $stats['occupied_spaces'] = count(array_filter($report_data, function($item) {
                    return $item->active_reservations > 0;
                }));
                $stats['total_reservations'] = array_sum(array_column($report_data, 'reservation_count'));
                $stats['total_revenue'] = array_sum(array_column($report_data, 'total_revenue'));
                break;
        }
        ?>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center text-white bg-primary">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">تعداد کل</h6>
                    <h4><?php echo $stats['total'] ?? $stats['total_users'] ?? $stats['total_spaces'] ?? $stats['total_invoices'] ?? 0; ?></h4>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center text-white bg-success">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">فعال</h6>
                    <h4><?php echo $stats['confirmed'] ?? $stats['active_users'] ?? $stats['occupied_spaces'] ?? 0; ?></h4>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center text-white bg-info">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">درآمد کل</h6>
                    <h4><?php echo isset($stats['total_revenue']) ? number_format($stats['total_revenue']) . ' تومان' : '-'; ?></h4>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center text-white bg-warning">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">میانگین</h6>
                    <h4><?php echo isset($stats['average']) ? number_format($stats['average']) . ' تومان' : 
                               (isset($stats['average_per_user']) ? number_format($stats['average_per_user'], 1) : '-'); ?></h4>
                </div>
            </div>
        </div>
    </div>
    
    <!-- جدول گزارش -->
    <div class="card mt-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?php echo $report_title; ?></h5>
            <small class="text-muted">
                <?php echo jdate('Y/m/d', strtotime($date_from)); ?> - 
                <?php echo jdate('Y/m/d', strtotime($date_to)); ?>
            </small>
        </div>
        <div class="card-body">
            <?php if ($report_data): ?>
                <div class="table-responsive" id="report-table">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <?php 
                                // تعیین سرستون‌ها بر اساس نوع گزارش
                                switch ($report_type) {
                                    case 'reservations': ?>
                                        <th>شماره</th>
                                        <th>کاربر</th>
                                        <th>فضا</th>
                                        <th>تاریخ</th>
                                        <th>مدت</th>
                                        <th>مبلغ</th>
                                        <th>وضعیت</th>
                                        <th>تاریخ ثبت</th>
                                    <?php break;
                                    
                                    case 'revenue': ?>
                                        <th>تاریخ</th>
                                        <th>تعداد فاکتور</th>
                                        <th>درآمد کل</th>
                                        <th>تخفیف</th>
                                        <th>درآمد خالص</th>
                                    <?php break;
                                    
                                    case 'users': ?>
                                        <th>نام</th>
                                        <th>ایمیل</th>
                                        <th>تاریخ عضویت</th>
                                        <th>تعداد رزرو</th>
                                        <th>رزروهای فعال</th>
                                    <?php break;
                                    
                                    case 'spaces': ?>
                                        <th>نام فضا</th>
                                        <th>نوع</th>
                                        <th>ظرفیت</th>
                                        <th>قیمت روزانه</th>
                                        <th>تعداد رزرو</th>
                                        <th>رزروهای فعال</th>
                                        <th>درآمد کل</th>
                                    <?php break;
                                } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $counter = 1;
                            foreach ($report_data as $item): 
                            ?>
                            <tr>
                                <?php switch ($report_type) {
                                    case 'reservations': ?>
                                        <td><?php echo $counter; ?></td>
                                        <td>
                                            <div><strong><?php echo esc_html($item->user_name); ?></strong></div>
                                            <small class="text-muted"><?php echo esc_html($item->user_email); ?></small>
                                        </td>
                                        <td><?php echo esc_html($item->space_name); ?></td>
                                        <td>
                                            <?php echo jdate('Y/m/d', strtotime($item->start_date)); ?> - 
                                            <?php echo jdate('Y/m/d', strtotime($item->end_date)); ?>
                                        </td>
                                        <td><?php echo $item->total_days; ?> روز</td>
                                        <td><?php echo number_format($item->final_amount); ?> تومان</td>
                                        <td>
                                            <?php 
                                            $status_badge = '';
                                            switch ($item->status) {
                                                case 'confirmed':
                                                    $status_badge = '<span class="badge bg-success">تأیید شده</span>';
                                                    break;
                                                case 'active':
                                                    $status_badge = '<span class="badge bg-info">فعال</span>';
                                                    break;
                                                case 'pending':
                                                    $status_badge = '<span class="badge bg-warning">در انتظار</span>';
                                                    break;
                                                case 'cancelled':
                                                    $status_badge = '<span class="badge bg-danger">لغو شده</span>';
                                                    break;
                                            }
                                            echo $status_badge;
                                            ?>
                                        </td>
                                        <td><?php echo jdate('Y/m/d H:i', strtotime($item->created_at)); ?></td>
                                    <?php break;
                                    
                                    case 'revenue': ?>
                                        <td><?php echo jdate('Y/m/d', strtotime($item->date)); ?></td>
                                        <td><?php echo $item->invoice_count; ?></td>
                                        <td><?php echo number_format($item->total_revenue + $item->total_discount); ?> تومان</td>
                                        <td><?php echo number_format($item->total_discount); ?> تومان</td>
                                        <td><strong><?php echo number_format($item->total_revenue); ?> تومان</strong></td>
                                    <?php break;
                                    
                                    case 'users': ?>
                                        <td><?php echo esc_html($item->display_name); ?></td>
                                        <td><?php echo esc_html($item->user_email); ?></td>
                                        <td><?php echo jdate('Y/m/d', strtotime($item->user_registered)); ?></td>
                                        <td><?php echo $item->reservation_count; ?></td>
                                        <td><?php echo $item->active_reservations; ?></td>
                                    <?php break;
                                    
                                    case 'spaces': ?>
                                        <td><?php echo esc_html($item->name); ?></td>
                                        <td>
                                            <?php 
                                            $type_names = array(
                                                'shared_desk' => 'صندلی اشتراکی',
                                                'dedicated_desk' => 'صندلی اختصاصی',
                                                'private_room' => 'اتاق خصوصی',
                                                'meeting_room' => 'اتاق جلسه',
                                                'event_hall' => 'سالن رویداد'
                                            );
                                            echo $type_names[$item->type] ?? $item->type;
                                            ?>
                                        </td>
                                        <td><?php echo $item->capacity; ?> نفر</td>
                                        <td><?php echo number_format($item->price_daily); ?> تومان</td>
                                        <td><?php echo $item->reservation_count; ?></td>
                                        <td><?php echo $item->active_reservations; ?></td>
                                        <td><?php echo number_format($item->total_revenue); ?> تومان</td>
                                    <?php break;
                                } ?>
                            </tr>
                            <?php 
                            $counter++;
                            endforeach; 
                            ?>
                        </tbody>
                        <?php if ($report_type === 'revenue'): ?>
                        <tfoot>
                            <tr class="table-primary">
                                <td><strong>جمع کل</strong></td>
                                <td><strong><?php echo $stats['total_invoices']; ?></strong></td>
                                <td><strong><?php echo number_format($stats['total_revenue'] + $stats['total_discount']); ?> تومان</strong></td>
                                <td><strong><?php echo number_format($stats['total_discount']); ?> تومان</strong></td>
                                <td><strong><?php echo number_format($stats['total_revenue']); ?> تومان</strong></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
                
                <!-- نمودارها -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">نمودار تحلیل</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="reportChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">آمار پیشرفته</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <?php if ($report_type === 'revenue'): ?>
                                        <li class="mb-2">
                                            <i class="fas fa-chart-line text-success me-2"></i>
                                            بیشترین درآمد روزانه: 
                                            <strong>
                                                <?php 
                                                $max_revenue = max(array_column($report_data, 'total_revenue'));
                                                echo number_format($max_revenue); ?> تومان
                                            </strong>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-percentage text-info me-2"></i>
                                            درصد تخفیف: 
                                            <strong>
                                                <?php 
                                                $discount_percent = ($stats['total_discount'] / ($stats['total_revenue'] + $stats['total_discount'])) * 100;
                                                echo number_format($discount_percent, 1); ?>%
                                            </strong>
                                        </li>
                                    <?php elseif ($report_type === 'reservations'): ?>
                                        <li class="mb-2">
                                            <i class="fas fa-users text-primary me-2"></i>
                                            میانگین مدت رزرو: 
                                            <strong>
                                                <?php 
                                                $avg_days = array_sum(array_column($report_data, 'total_days')) / count($report_data);
                                                echo number_format($avg_days, 1); ?> روز
                                            </strong>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-money-bill-wave text-success me-2"></i>
                                            میانگین مبلغ رزرو: 
                                            <strong>
                                                <?php 
                                                $avg_amount = array_sum(array_column($report_data, 'final_amount')) / count($report_data);
                                                echo number_format($avg_amount); ?> تومان
                                            </strong>
                                        </li>
                                    <?php endif; ?>
                                    <li class="mb-2">
                                        <i class="fas fa-calendar-day text-warning me-2"></i>
                                        مدت بازه زمانی: 
                                        <strong>
                                            <?php 
                                            $days_diff = (strtotime($date_to) - strtotime($date_from)) / (60 * 60 * 24) + 1;
                                            echo $days_diff; ?> روز
                                        </strong>
                                    </li>
                                    <li>
                                        <i class="fas fa-clock text-info me-2"></i>
                                        تاریخ تولید گزارش: 
                                        <strong><?php echo jdate('Y/m/d H:i:s'); ?></strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">هیچ داده‌ای برای نمایش وجود ندارد</h5>
                    <p class="text-muted">لطفاً پارامترهای گزارش را تغییر دهید.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- کتابخانه‌های نمودار -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
jQuery(document).ready(function($) {
    // نمودار گزارش
    var ctx = document.getElementById('reportChart').getContext('2d');
    
    <?php if ($report_data): ?>
        var chartData = {
            labels: [],
            datasets: []
        };
        
        <?php switch ($report_type):
            case 'revenue': ?>
                chartData.labels = <?php echo json_encode(array_column($report_data, 'date')); ?>;
                chartData.datasets = [{
                    label: 'درآمد (تومان)',
                    data: <?php echo json_encode(array_column($report_data, 'total_revenue')); ?>,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderWidth: 2,
                    fill: true
                }];
                <?php break;
                
            case 'reservations': ?>
                // گروه‌بندی بر اساس روز
                var groupedData = <?php echo json_encode($report_data); ?>;
                var days = {};
                
                groupedData.forEach(function(item) {
                    var date = item.created_at.split(' ')[0];
                    if (!days[date]) {
                        days[date] = 0;
                    }
                    days[date]++;
                });
                
                chartData.labels = Object.keys(days);
                chartData.datasets = [{
                    label: 'تعداد رزرو',
                    data: Object.values(days),
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    borderWidth: 2,
                    fill: true
                }];
                <?php break;
                
            case 'spaces': ?>
                chartData.labels = <?php echo json_encode(array_column($report_data, 'name')); ?>;
                chartData.datasets = [{
                    label: 'تعداد رزرو',
                    data: <?php echo json_encode(array_column($report_data, 'reservation_count')); ?>,
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                        '#858796', '#6f42c1', '#fd7e14', '#20c997', '#17a2b8'
                    ]
                }];
                <?php break;
        endswitch; ?>
        
        var chartOptions = {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            <?php if ($report_type !== 'spaces'): ?>
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        <?php if ($report_type === 'revenue'): ?>
                        callback: function(value) {
                            return value.toLocaleString() + ' تومان';
                        }
                        <?php endif; ?>
                    }
                }
            }
            <?php endif; ?>
        };
        
        var reportChart = new Chart(ctx, {
            type: <?php echo $report_type === 'spaces' ? "'bar'" : "'line'"; ?>,
            data: chartData,
            options: chartOptions
        });
    <?php endif; ?>
    
    // اکسپورت گزارش
    window.exportReport = function(format) {
        var params = new URLSearchParams(window.location.search);
        params.append('export', format);
        params.append('nonce', '<?php echo wp_create_nonce('coworking_export_report'); ?>');
        
        window.location.href = ajaxurl + '?' + params.toString();
    };
    
    // چاپ گزارش
    window.printReport = function() {
        var printContents = document.getElementById('report-table').innerHTML;
        var originalContents = document.body.innerHTML;
        
        document.body.innerHTML = `
            <!DOCTYPE html>
            <html dir="rtl">
            <head>
                <meta charset="UTF-8">
                <title>گزارش <?php echo $report_title; ?></title>
                <style>
                    body { font-family: 'PeydaWeb', Tahoma, Geneva, sans-serif; }
                    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
                    th { background-color: #f8f9fa; font-weight: bold; }
                    .header { text-align: center; margin-bottom: 30px; }
                    .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
                    @media print {
                        @page { size: landscape; }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h2>گزارش <?php echo $report_title; ?></h2>
                    <p>تاریخ: <?php echo jdate('Y/m/d'); ?></p>
                    <p>بازه زمانی: <?php echo jdate('Y/m/d', strtotime($date_from)); ?> تا <?php echo jdate('Y/m/d', strtotime($date_to)); ?></p>
                </div>
                ${printContents}
                <div class="footer">
                    <p>مرکز نوآوری و کار اشتراکی اکسیژن</p>
                    <p>تاریخ چاپ: <?php echo jdate('Y/m/d H:i:s'); ?></p>
                </div>
            </body>
            </html>
        `;
        
        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    };
    
    // تغییر نوع گزارش
    $('#report-type').on('change', function() {
        var type = $(this).val();
        var spaceFilter = $('#space-filter');
        
        if (type === 'reservations') {
            spaceFilter.prop('disabled', false);
        } else {
            spaceFilter.prop('disabled', true);
            spaceFilter.val(0);
        }
    });
});
</script>

<style>
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.card {
    border-radius: 10px;
    overflow: hidden;
}

.table-bordered {
    border: 1px solid #dee2e6;
}

.badge {
    font-family: 'PeydaWeb';
    padding: 5px 10px;
    border-radius: 20px;
}

.tfoot td {
    font-weight: bold;
    background-color: #e9ecef;
}
</style>