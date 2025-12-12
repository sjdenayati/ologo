<?php
if (!current_user_can('manage_options') && !current_user_can('coworking_manager')) {
    wp_die('شما دسترسی لازم را ندارید.');
}

global $wpdb;
$table_invoices = $wpdb->prefix . 'coworking_invoices';
$table_reservations = $wpdb->prefix . 'coworking_reservations';
$table_users = $wpdb->prefix . 'users';
$table_transactions = $wpdb->prefix . 'coworking_transactions';
$table_discounts = $wpdb->prefix . 'coworking_discounts';

// پارامترهای فیلتر
$payment_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';

// ساخت شرط WHERE
$where_conditions = array();
if ($payment_status !== 'all') {
    $where_conditions[] = $wpdb->prepare("i.payment_status = %s", $payment_status);
}
if ($date_from) {
    $where_conditions[] = $wpdb->prepare("i.created_at >= %s", $date_from);
}
if ($date_to) {
    $where_conditions[] = $wpdb->prepare("i.created_at <= %s", $date_to);
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// دریافت فاکتورها
$invoices = $wpdb->get_results("
    SELECT i.*, 
           r.id as reservation_id,
           u.display_name as user_name,
           u.user_email as user_email
    FROM $table_invoices i
    LEFT JOIN $table_reservations r ON i.reservation_id = r.id
    LEFT JOIN $table_users u ON i.user_id = u.ID
    $where_clause
    ORDER BY i.created_at DESC
    LIMIT 50
");

// آمار مالی
$total_invoices = $wpdb->get_var("SELECT COUNT(*) FROM $table_invoices");
$total_revenue = $wpdb->get_var("SELECT SUM(total_amount) FROM $table_invoices WHERE payment_status = 'paid'");
$pending_amount = $wpdb->get_var("SELECT SUM(total_amount) FROM $table_invoices WHERE payment_status = 'pending'");
$total_discounts = $wpdb->get_var("SELECT SUM(discount_amount) FROM $table_invoices");
?>

<div class="wrap">
    <h1 class="wp-heading-inline" style="font-family: 'PeydaWeb';">
        <i class="fas fa-money-bill-wave me-2"></i>مدیریت مالی
    </h1>
    
    <!-- آمار مالی -->
    <div class="row mt-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center text-white bg-primary">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">کل فاکتورها</h6>
                    <h4><?php echo $total_invoices; ?></h4>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center text-white bg-success">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">درآمد کل</h6>
                    <h4><?php echo number_format($total_revenue); ?> تومان</h4>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center text-white bg-warning">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">در انتظار پرداخت</h6>
                    <h4><?php echo number_format($pending_amount); ?> تومان</h4>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center text-white bg-info">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">تخفیف‌های داده شده</h6>
                    <h4><?php echo number_format($total_discounts); ?> تومان</h4>
                </div>
            </div>
        </div>
    </div>
    
    <!-- فرم فیلتر -->
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>فیلتر فاکتورها</h5>
        </div>
        <div class="card-body">
            <form method="get" action="" class="row g-3">
                <input type="hidden" name="page" value="coworking-finance">
                
                <div class="col-md-4">
                    <label class="form-label">وضعیت پرداخت</label>
                    <select name="status" class="form-select">
                        <option value="all" <?php selected($payment_status, 'all'); ?>>همه وضعیت‌ها</option>
                        <option value="pending" <?php selected($payment_status, 'pending'); ?>>در انتظار پرداخت</option>
                        <option value="paid" <?php selected($payment_status, 'paid'); ?>>پرداخت شده</option>
                        <option value="failed" <?php selected($payment_status, 'failed'); ?>>ناموفق</option>
                        <option value="refunded" <?php selected($payment_status, 'refunded'); ?>>عودت داده شده</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">از تاریخ</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">تا تاریخ</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                </div>
                
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-2"></i>اعمال فیلتر
                    </button>
                    <a href="?page=coworking-finance" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-2"></i>بازنشانی
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- تب‌های مدیریت مالی -->
    <nav class="nav nav-tabs mt-4" id="financeTabs" role="tablist">
        <button class="nav-link active" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button">
            فاکتورها
        </button>
        <button class="nav-link" id="transactions-tab" data-bs-toggle="tab" data-bs-target="#transactions" type="button">
            تراکنش‌ها
        </button>
        <button class="nav-link" id="discounts-tab" data-bs-toggle="tab" data-bs-target="#discounts" type="button">
            تخفیف‌ها
        </button>
        <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports" type="button">
            گزارش‌ها
        </button>
    </nav>
    
    <div class="tab-content mt-3" id="financeTabContent">
        <!-- تب فاکتورها -->
        <div class="tab-pane fade show active" id="invoices">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">لیست فاکتورها</h5>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#createInvoiceModal">
                        <i class="fas fa-plus me-2"></i>صدور فاکتور
                    </button>
                </div>
                <div class="card-body">
                    <?php if ($invoices): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>شماره فاکتور</th>
                                        <th>کاربر</th>
                                        <th>مبلغ</th>
                                        <th>تخفیف</th>
                                        <th>مبلغ نهایی</th>
                                        <th>وضعیت پرداخت</th>
                                        <th>تاریخ سررسید</th>
                                        <th>تاریخ ایجاد</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($invoices as $invoice): 
                                        $status_badge = '';
                                        $status_class = '';
                                        
                                        switch ($invoice->payment_status) {
                                            case 'pending':
                                                $status_badge = 'در انتظار';
                                                $status_class = 'warning';
                                                break;
                                            case 'paid':
                                                $status_badge = 'پرداخت شده';
                                                $status_class = 'success';
                                                break;
                                            case 'failed':
                                                $status_badge = 'ناموفق';
                                                $status_class = 'danger';
                                                break;
                                            case 'refunded':
                                                $status_badge = 'عودت داده شده';
                                                $status_class = 'info';
                                                break;
                                        }
                                    ?>
                                    <tr>
                                        <td><code><?php echo $invoice->invoice_number; ?></code></td>
                                        <td>
                                            <div><strong><?php echo esc_html($invoice->user_name); ?></strong></div>
                                            <small class="text-muted"><?php echo esc_html($invoice->user_email); ?></small>
                                        </td>
                                        <td><?php echo number_format($invoice->amount); ?> تومان</td>
                                        <td><?php echo number_format($invoice->discount_amount); ?> تومان</td>
                                        <td><strong><?php echo number_format($invoice->total_amount); ?> تومان</strong></td>
                                        <td>
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <?php echo $status_badge; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($invoice->due_date): ?>
                                                <?php echo jdate('Y/m/d', strtotime($invoice->due_date)); ?>
                                            <?php else: ?>
                                                <span class="text-muted">تعیین نشده</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo jdate('Y/m/d', strtotime($invoice->created_at)); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary view-invoice" 
                                                        data-id="<?php echo $invoice->id; ?>"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#viewInvoiceModal">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <button class="btn btn-outline-success" 
                                                        onclick="window.printInvoice(<?php echo $invoice->id; ?>)">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                
                                                <button class="btn btn-outline-info" 
                                                        onclick="window.location.href='?page=coworking-finance&action=send_invoice&id=<?php echo $invoice->id; ?>'">
                                                    <i class="fas fa-envelope"></i>
                                                </button>
                                                
                                                <?php if ($invoice->payment_status === 'pending'): ?>
                                                    <button class="btn btn-outline-warning mark-paid" 
                                                            data-id="<?php echo $invoice->id; ?>">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">هیچ فاکتوری یافت نشد</h5>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- تب تراکنش‌ها -->
        <div class="tab-pane fade" id="transactions">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">تراکنش‌های پرداخت</h5>
                </div>
                <div class="card-body">
                    <?php
                    $transactions = $wpdb->get_results("
                        SELECT t.*, 
                               i.invoice_number,
                               u.display_name as user_name
                        FROM $table_transactions t
                        LEFT JOIN $table_invoices i ON t.invoice_id = i.id
                        LEFT JOIN $table_users u ON t.user_id = u.ID
                        ORDER BY t.created_at DESC
                        LIMIT 50
                    ");
                    
                    if ($transactions): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>کد تراکنش</th>
                                        <th>فاکتور</th>
                                        <th>کاربر</th>
                                        <th>مبلغ</th>
                                        <th>درگاه</th>
                                        <th>وضعیت</th>
                                        <th>تاریخ</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): 
                                        $status_badge = '';
                                        $status_class = '';
                                        
                                        switch ($transaction->status) {
                                            case 'pending':
                                                $status_badge = 'در انتظار';
                                                $status_class = 'warning';
                                                break;
                                            case 'completed':
                                                $status_badge = 'موفق';
                                                $status_class = 'success';
                                                break;
                                            case 'failed':
                                                $status_badge = 'ناموفق';
                                                $status_class = 'danger';
                                                break;
                                            case 'refunded':
                                                $status_badge = 'عودت داده شده';
                                                $status_class = 'info';
                                                break;
                                        }
                                    ?>
                                    <tr>
                                        <td><code><?php echo $transaction->transaction_code; ?></code></td>
                                        <td><?php echo $transaction->invoice_number; ?></td>
                                        <td><?php echo esc_html($transaction->user_name); ?></td>
                                        <td><?php echo number_format($transaction->amount); ?> تومان</td>
                                        <td><?php echo ucfirst($transaction->gateway); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <?php echo $status_badge; ?>
                                            </span>
                                        </td>
                                        <td><?php echo jdate('Y/m/d H:i', strtotime($transaction->created_at)); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary view-transaction" 
                                                    data-id="<?php echo $transaction->id; ?>">
                                                جزئیات
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">هیچ تراکنشی یافت نشد</h5>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- تب تخفیف‌ها -->
        <div class="tab-pane fade" id="discounts">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">کدهای تخفیف</h5>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#createDiscountModal">
                        <i class="fas fa-plus me-2"></i>افزودن تخفیف
                    </button>
                </div>
                <div class="card-body">
                    <?php
                    $discounts = $wpdb->get_results("SELECT * FROM $table_discounts ORDER BY created_at DESC");
                    
                    if ($discounts): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>کد تخفیف</th>
                                        <th>نوع</th>
                                        <th>مقدار</th>
                                        <th>حداقل سفارش</th>
                                        <th>تعداد استفاده</th>
                                        <th>تاریخ شروع</th>
                                        <th>تاریخ انقضا</th>
                                        <th>وضعیت</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($discounts as $discount): 
                                        $status_badge = $discount->status === 'active' ? 
                                            '<span class="badge bg-success">فعال</span>' : 
                                            '<span class="badge bg-secondary">غیرفعال</span>';
                                        
                                        $type_text = $discount->discount_type === 'percentage' ? 
                                            $discount->discount_value . '%' : 
                                            number_format($discount->discount_value) . ' تومان';
                                    ?>
                                    <tr>
                                        <td><code><?php echo $discount->code; ?></code></td>
                                        <td><?php echo $type_text; ?></td>
                                        <td>
                                            <?php if ($discount->discount_type === 'percentage'): ?>
                                                <?php echo $discount->discount_value; ?>%
                                            <?php else: ?>
                                                <?php echo number_format($discount->discount_value); ?> تومان
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($discount->min_amount > 0): ?>
                                                <?php echo number_format($discount->min_amount); ?> تومان
                                            <?php else: ?>
                                                <span class="text-muted">ندارد</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $discount->used_count; ?>
                                            <?php if ($discount->max_uses > 0): ?>
                                                / <?php echo $discount->max_uses; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($discount->start_date): ?>
                                                <?php echo jdate('Y/m/d', strtotime($discount->start_date)); ?>
                                            <?php else: ?>
                                                <span class="text-muted">ندارد</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($discount->end_date): ?>
                                                <?php echo jdate('Y/m/d', strtotime($discount->end_date)); ?>
                                            <?php else: ?>
                                                <span class="text-muted">ندارد</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $status_badge; ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary edit-discount" 
                                                        data-id="<?php echo $discount->id; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger delete-discount" 
                                                        data-id="<?php echo $discount->id; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-tag fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">هیچ کد تخفیفی یافت نشد</h5>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- تب گزارش‌ها -->
        <div class="tab-pane fade" id="reports">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">گزارش‌های مالی</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6>درآمد ماهانه</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="monthlyRevenueChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6>وضعیت پرداخت‌ها</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="paymentStatusChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">گزارش‌های آماده</h6>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary me-2" onclick="exportReport('daily')">
                                            <i class="fas fa-download me-2"></i>گزارش روزانه
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" onclick="exportReport('monthly')">
                                            <i class="fas fa-download me-2"></i>گزارش ماهانه
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="report-card text-center p-3 border rounded mb-3">
                                                <i class="fas fa-file-invoice-dollar fa-2x text-primary mb-2"></i>
                                                <h6>فاکتورهای پرداخت نشده</h6>
                                                <h4 class="text-warning"><?php echo $wpdb->get_var("SELECT COUNT(*) FROM $table_invoices WHERE payment_status = 'pending'"); ?></h4>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="report-card text-center p-3 border rounded mb-3">
                                                <i class="fas fa-calendar-check fa-2x text-success mb-2"></i>
                                                <h6>رزروهای فعال امروز</h6>
                                                <h4 class="text-success">
                                                    <?php 
                                                    $today = date('Y-m-d');
                                                    echo $wpdb->get_var($wpdb->prepare(
                                                        "SELECT COUNT(*) FROM $table_reservations 
                                                        WHERE start_date <= %s AND end_date >= %s AND status = 'active'",
                                                        $today, $today
                                                    )); 
                                                    ?>
                                                </h4>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="report-card text-center p-3 border rounded mb-3">
                                                <i class="fas fa-users fa-2x text-info mb-2"></i>
                                                <h6>کاربران فعال این ماه</h6>
                                                <h4 class="text-info">
                                                    <?php
                                                    $first_day = date('Y-m-01');
                                                    $last_day = date('Y-m-t');
                                                    echo $wpdb->get_var($wpdb->prepare(
                                                        "SELECT COUNT(DISTINCT user_id) FROM $table_reservations 
                                                        WHERE created_at BETWEEN %s AND %s",
                                                        $first_day, $last_day
                                                    ));
                                                    ?>
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- مودال مشاهده فاکتور -->
<div class="modal fade" id="viewInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">مشاهده فاکتور</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="invoice-details">
                <!-- محتوای داینامیک -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>چاپ فاکتور
                </button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // مشاهده جزئیات فاکتور
    $('.view-invoice').click(function() {
        var invoiceId = $(this).data('id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'coworking_get_invoice_details',
                invoice_id: invoiceId,
                nonce: '<?php echo wp_create_nonce('coworking_nonce'); ?>'
            },
            beforeSend: function() {
                $('#invoice-details').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
            },
            success: function(response) {
                $('#invoice-details').html(response.data);
            }
        });
    });
    
    // علامت‌گذاری به عنوان پرداخت شده
    $('.mark-paid').click(function() {
        if (!confirm('آیا این فاکتور پرداخت شده است؟')) {
            return;
        }
        
        var invoiceId = $(this).data('id');
        var button = $(this);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'coworking_mark_invoice_paid',
                invoice_id: invoiceId,
                nonce: '<?php echo wp_create_nonce('coworking_nonce'); ?>'
            },
            beforeSend: function() {
                button.html('<span class="spinner-border spinner-border-sm"></span>');
                button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    alert('فاکتور با موفقیت پرداخت شده علامت‌گذاری شد.');
                    location.reload();
                } else {
                    alert('خطا: ' + response.data);
                    button.html('<i class="fas fa-check-circle"></i>');
                    button.prop('disabled', false);
                }
            }
        });
    });
    
    // چارت درآمد ماهانه
    var ctx1 = document.getElementById('monthlyRevenueChart').getContext('2d');
    var monthlyRevenueChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور'],
            datasets: [{
                label: 'درآمد (تومان)',
                data: [15000000, 18000000, 22000000, 19000000, 25000000, 28000000],
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
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
                        callback: function(value) {
                            return value.toLocaleString() + ' تومان';
                        }
                    }
                }
            }
        }
    });
    
    // چارت وضعیت پرداخت‌ها
    var ctx2 = document.getElementById('paymentStatusChart').getContext('2d');
    var paymentStatusChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['پرداخت شده', 'در انتظار', 'ناموفق', 'عودت داده شده'],
            datasets: [{
                data: [65, 20, 10, 5],
                backgroundColor: [
                    '#1cc88a',
                    '#f6c23e',
                    '#e74a3b',
                    '#36b9cc'
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
    
    // تابع اکسپورت گزارش
    window.exportReport = function(type) {
        window.location.href = ajaxurl + '?action=coworking_export_finance_report&type=' + type;
    };
    
    // تابع چاپ فاکتور
    window.printInvoice = function(invoiceId) {
        var printWindow = window.open(ajaxurl + '?action=coworking_print_invoice&id=' + invoiceId, '_blank');
        printWindow.onload = function() {
            printWindow.print();
        };
    };
});
</script>

<style>
.nav-tabs .nav-link {
    color: #495057;
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    border-bottom: 3px solid #196ab4;
    color: #196ab4;
}

.report-card {
    transition: all 0.3s ease;
}

.report-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.table th {
    background-color: #f8f9fa;
}

.badge {
    font-family: 'PeydaWeb';
    padding: 5px 10px;
    border-radius: 20px;
}
</style>