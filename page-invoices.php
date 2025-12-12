<?php
/*
Template Name: فاکتورهای من
*/
get_header();

if (!is_user_logged_in()) {
    wp_redirect(home_url('/login'));
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

global $wpdb;
$table_invoices = $wpdb->prefix . 'coworking_invoices';
$table_reservations = $wpdb->prefix . 'coworking_reservations';

// دریافت فاکتورهای کاربر
$invoices = $wpdb->get_results($wpdb->prepare(
    "SELECT i.*, 
            r.start_date,
            r.end_date,
            r.duration_type,
            r.status as reservation_status
    FROM $table_invoices i
    LEFT JOIN $table_reservations r ON i.reservation_id = r.id
    WHERE i.user_id = %d 
    ORDER BY i.created_at DESC",
    $user_id
));

// آمار فاکتورها
$total_invoices = count($invoices);
$paid_invoices = array_filter($invoices, function($inv) {
    return $inv->payment_status === 'paid';
});
$pending_invoices = array_filter($invoices, function($inv) {
    return $inv->payment_status === 'pending';
});
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
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice me-2"></i>فاکتورهای من
                    </h5>
                    <button class="btn btn-light btn-sm" onclick="printAllInvoices()">
                        <i class="fas fa-print me-2"></i>چاپ همه
                    </button>
                </div>
                
                <div class="card-body">
                    <!-- آمار فاکتورها -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="stat-card text-center p-3 rounded bg-primary text-white">
                                <h6 class="mb-1">کل فاکتورها</h6>
                                <h4><?php echo $total_invoices; ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="stat-card text-center p-3 rounded bg-success text-white">
                                <h6 class="mb-1">پرداخت شده</h6>
                                <h4><?php echo count($paid_invoices); ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="stat-card text-center p-3 rounded bg-warning text-white">
                                <h6 class="mb-1">در انتظار پرداخت</h6>
                                <h4><?php echo count($pending_invoices); ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="stat-card text-center p-3 rounded bg-info text-white">
                                <h6 class="mb-1">جمع مبالغ</h6>
                                <h4>
                                    <?php 
                                    $total_amount = array_sum(array_column($invoices, 'total_amount'));
                                    echo number_format($total_amount); 
                                    ?> تومان
                                </h4>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($invoices): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>شماره فاکتور</th>
                                        <th>مبلغ</th>
                                        <th>تخفیف</th>
                                        <th>مبلغ نهایی</th>
                                        <th>تاریخ</th>
                                        <th>وضعیت</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($invoices as $invoice): 
                                        $status_badge = '';
                                        $status_class = '';
                                        $action_button = '';
                                        
                                        switch ($invoice->payment_status) {
                                            case 'pending':
                                                $status_badge = 'در انتظار پرداخت';
                                                $status_class = 'warning';
                                                $action_button = '
                                                    <button class="btn btn-sm btn-success pay-invoice" 
                                                            data-id="' . $invoice->id . '">
                                                        <i class="fas fa-credit-card me-1"></i>پرداخت
                                                    </button>
                                                ';
                                                break;
                                            case 'paid':
                                                $status_badge = 'پرداخت شده';
                                                $status_class = 'success';
                                                $action_button = '
                                                    <button class="btn btn-sm btn-outline-primary download-invoice" 
                                                            data-id="' . $invoice->id . '">
                                                        <i class="fas fa-download me-1"></i>دانلود
                                                    </button>
                                                ';
                                                break;
                                            case 'failed':
                                                $status_badge = 'ناموفق';
                                                $status_class = 'danger';
                                                $action_button = '
                                                    <button class="btn btn-sm btn-warning retry-payment" 
                                                            data-id="' . $invoice->id . '">
                                                        <i class="fas fa-redo me-1"></i>تلاش مجدد
                                                    </button>
                                                ';
                                                break;
                                            case 'refunded':
                                                $status_badge = 'عودت داده شده';
                                                $status_class = 'info';
                                                $action_button = '';
                                                break;
                                        }
                                    ?>
                                    <tr>
                                        <td>
                                            <code><?php echo $invoice->invoice_number; ?></code>
                                            <?php if ($invoice->reservation_status): ?>
                                                <small class="text-muted d-block">
                                                    رزرو: <?php echo ucfirst($invoice->reservation_status); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo number_format($invoice->amount); ?> تومان</td>
                                        <td><?php echo number_format($invoice->discount_amount); ?> تومان</td>
                                        <td>
                                            <strong><?php echo number_format($invoice->total_amount); ?> تومان</strong>
                                        </td>
                                        <td>
                                            <?php echo jdate('Y/m/d', strtotime($invoice->created_at)); ?>
                                            <?php if ($invoice->due_date): ?>
                                                <small class="d-block text-muted">
                                                    سررسید: <?php echo jdate('Y/m/d', strtotime($invoice->due_date)); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <?php echo $status_badge; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary view-invoice" 
                                                        data-id="<?php echo $invoice->id; ?>"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#viewInvoiceModal">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-info print-invoice" 
                                                        data-id="<?php echo $invoice->id; ?>">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                <?php echo $action_button; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- صفحه‌بندی -->
                        <nav aria-label="صفحه‌بندی فاکتورها">
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
                            <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">هیچ فاکتوری یافت نشد</h5>
                            <p class="text-muted mb-4">شما تاکنون فاکتوری ندارید.</p>
                            <a href="<?php echo home_url('/reservation'); ?>" class="btn btn-primary">
                                <i class="fas fa-calendar-plus me-2"></i>رزرو جدید
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- راهنمای وضعیت فاکتورها -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>راهنمای وضعیت فاکتورها</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <span class="badge bg-warning d-block mb-2">در انتظار پرداخت</span>
                            <small>لطفاً ظرف ۷۲ ساعت پرداخت را انجام دهید.</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <span class="badge bg-success d-block mb-2">پرداخت شده</span>
                            <small>فاکتور با موفقیت پرداخت شده است.</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <span class="badge bg-danger d-block mb-2">ناموفق</span>
                            <small>پرداخت با مشکل مواجه شده است.</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <span class="badge bg-info d-block mb-2">عودت داده شده</span>
                            <small>مبلغ به حساب شما بازگشت داده شده است.</small>
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
            <div class="modal-body" id="invoice-details-content">
                <!-- محتوای داینامیک -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                <button type="button" class="btn btn-primary" onclick="printInvoice()">
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
            url: coworking_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_invoice_details',
                invoice_id: invoiceId,
                nonce: coworking_ajax.nonce
            },
            beforeSend: function() {
                $('#invoice-details-content').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
            },
            success: function(response) {
                if (response.success) {
                    $('#invoice-details-content').html(response.data);
                }
            }
        });
    });
    
    // پرداخت فاکتور
    $('.pay-invoice').click(function() {
        var invoiceId = $(this).data('id');
        var button = $(this);
        
        $.ajax({
            url: coworking_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'process_payment',
                invoice_id: invoiceId,
                nonce: coworking_ajax.nonce
            },
            beforeSend: function() {
                button.html('<span class="spinner-border spinner-border-sm"></span>');
                button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.redirect) {
                        window.location.href = response.data.redirect;
                    } else {
                        alert('پرداخت با موفقیت انجام شد.');
                        location.reload();
                    }
                } else {
                    alert('خطا در پرداخت: ' + response.data);
                    button.html('<i class="fas fa-credit-card me-1"></i>پرداخت');
                    button.prop('disabled', false);
                }
            }
        });
    });
    
    // تلاش مجدد پرداخت
    $('.retry-payment').click(function() {
        var invoiceId = $(this).data('id');
        window.location.href = coworking_ajax.home_url + '/payment/' + invoiceId;
    });
    
    // چاپ فاکتور
    $('.print-invoice').click(function() {
        var invoiceId = $(this).data('id');
        var printWindow = window.open(coworking_ajax.ajax_url + '?action=print_invoice&id=' + invoiceId, '_blank');
        printWindow.onload = function() {
            printWindow.print();
        };
    });
    
    // دانلود فاکتور
    $('.download-invoice').click(function() {
        var invoiceId = $(this).data('id');
        window.open(coworking_ajax.ajax_url + '?action=download_invoice&id=' + invoiceId);
    });
    
    // چاپ همه فاکتورها
    window.printAllInvoices = function() {
        if (confirm('آیا می‌خواهید همه فاکتورها را چاپ کنید؟')) {
            var printWindow = window.open(coworking_ajax.home_url + '/print-all-invoices', '_blank');
            printWindow.onload = function() {
                printWindow.print();
            };
        }
    };
    
    // تابع چاپ فاکتور از مودال
    window.printInvoice = function() {
        window.print();
    };
});
</script>

<style>
.stat-card {
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
}

.badge {
    font-family: 'PeydaWeb';
    padding: 5px 10px;
    border-radius: 20px;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.modal-header {
    border-radius: 15px 15px 0 0;
}
</style>

<?php get_footer(); ?>