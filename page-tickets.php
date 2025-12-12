<?php
/*
Template Name: تیکت‌های پشتیبانی
*/
get_header();

if (!is_user_logged_in()) {
    wp_redirect(home_url('/login'));
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

global $wpdb;
$table_tickets = $wpdb->prefix . 'coworking_tickets';
$table_ticket_messages = $wpdb->prefix . 'coworking_ticket_messages';

// دریافت تیکت‌های کاربر
$tickets = $wpdb->get_results($wpdb->prepare(
    "SELECT t.*, 
    (SELECT COUNT(*) FROM $table_ticket_messages tm WHERE tm.ticket_id = t.id) as message_count,
    (SELECT MAX(created_at) FROM $table_ticket_messages tm WHERE tm.ticket_id = t.id) as last_reply
    FROM $table_tickets t 
    WHERE t.user_id = %d 
    ORDER BY 
        CASE t.status 
            WHEN 'open' THEN 1
            WHEN 'in_progress' THEN 2
            WHEN 'resolved' THEN 3
            WHEN 'closed' THEN 4
        END,
        t.created_at DESC",
    $user_id
));
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
                        <i class="fas fa-headset me-2"></i>تیکت‌های پشتیبانی
                    </h5>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#newTicketModal">
                        <i class="fas fa-plus me-2"></i>تیکت جدید
                    </button>
                </div>
                
                <div class="card-body">
                    <!-- فیلتر و جستجو -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" class="form-control" id="search-tickets" placeholder="جستجو در تیکت‌ها...">
                                <button class="btn btn-outline-primary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="filter-status">
                                <option value="all">همه وضعیت‌ها</option>
                                <option value="open">باز</option>
                                <option value="in_progress">در دست بررسی</option>
                                <option value="resolved">حل شده</option>
                                <option value="closed">بسته شده</option>
                            </select>
                        </div>
                    </div>
                    
                    <?php if ($tickets): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>شماره تیکت</th>
                                        <th>موضوع</th>
                                        <th>اولویت</th>
                                        <th>وضعیت</th>
                                        <th>تاریخ ایجاد</th>
                                        <th>آخرین پاسخ</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tickets as $ticket): 
                                        $priority_badge = '';
                                        switch ($ticket->priority) {
                                            case 'urgent':
                                                $priority_badge = '<span class="badge bg-danger">فوری</span>';
                                                break;
                                            case 'high':
                                                $priority_badge = '<span class="badge bg-warning text-dark">بالا</span>';
                                                break;
                                            case 'medium':
                                                $priority_badge = '<span class="badge bg-info">متوسط</span>';
                                                break;
                                            case 'low':
                                                $priority_badge = '<span class="badge bg-secondary">پایین</span>';
                                                break;
                                        }
                                        
                                        $status_badge = '';
                                        switch ($ticket->status) {
                                            case 'open':
                                                $status_badge = '<span class="badge bg-primary">باز</span>';
                                                break;
                                            case 'in_progress':
                                                $status_badge = '<span class="badge bg-warning text-dark">در دست بررسی</span>';
                                                break;
                                            case 'resolved':
                                                $status_badge = '<span class="badge bg-success">حل شده</span>';
                                                break;
                                            case 'closed':
                                                $status_badge = '<span class="badge bg-secondary">بسته شده</span>';
                                                break;
                                        }
                                    ?>
                                    <tr>
                                        <td><code><?php echo $ticket->ticket_number; ?></code></td>
                                        <td>
                                            <strong><?php echo esc_html($ticket->subject); ?></strong>
                                            <?php if ($ticket->message_count > 0): ?>
                                                <small class="text-muted d-block">
                                                    <?php echo $ticket->message_count; ?> پیام
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $priority_badge; ?></td>
                                        <td><?php echo $status_badge; ?></td>
                                        <td><?php echo jdate('Y/m/d', strtotime($ticket->created_at)); ?></td>
                                        <td>
                                            <?php if ($ticket->last_reply): ?>
                                                <?php echo human_time_diff(strtotime($ticket->last_reply), current_time('timestamp')) . ' پیش'; ?>
                                            <?php else: ?>
                                                <span class="text-muted">بدون پاسخ</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo home_url('/ticket/' . $ticket->id); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>مشاهده
                                            </a>
                                            <?php if ($ticket->status === 'open'): ?>
                                                <button class="btn btn-sm btn-outline-danger close-ticket" data-id="<?php echo $ticket->id; ?>">
                                                    <i class="fas fa-times me-1"></i>بستن
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- صفحه‌بندی -->
                        <nav aria-label="صفحه‌بندی تیکت‌ها">
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
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">هیچ تیکتی یافت نشد</h5>
                            <p class="text-muted mb-4">شما تاکنون تیکتی ایجاد نکرده‌اید.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTicketModal">
                                <i class="fas fa-plus me-2"></i>ایجاد اولین تیکت
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- راهنمای اولویت‌ها -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>راهنمای اولویت تیکت‌ها</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <span class="badge bg-danger d-block mb-2">فوری</span>
                            <small>مشکلات حیاتی سیستم</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <span class="badge bg-warning text-dark d-block mb-2">بالا</span>
                            <small>مشکلات مهم</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <span class="badge bg-info d-block mb-2">متوسط</span>
                            <small>سؤالات و درخواست‌ها</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <span class="badge bg-secondary d-block mb-2">پایین</span>
                            <small>پیشنهادات و انتقادات</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- مودال ایجاد تیکت جدید -->
<div class="modal fade" id="newTicketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">ایجاد تیکت جدید</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="new-ticket-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">موضوع *</label>
                        <input type="text" class="form-control" id="ticket-subject" name="subject" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">اولویت *</label>
                            <select class="form-select" id="ticket-priority" name="priority" required>
                                <option value="low">پایین (پیشنهاد/انتقاد)</option>
                                <option value="medium" selected>متوسط (سؤال/درخواست)</option>
                                <option value="high">بالا (مشکل مهم)</option>
                                <option value="urgent">فوری (مشکل حیاتی)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">دسته‌بندی</label>
                            <select class="form-select" id="ticket-category" name="category">
                                <option value="reservation">مشکل رزرو</option>
                                <option value="payment">مشکل پرداخت</option>
                                <option value="facility">امکانات مجموعه</option>
                                <option value="technical">مشکل فنی</option>
                                <option value="other">سایر</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">پیام *</label>
                        <textarea class="form-control" id="ticket-message" name="message" rows="6" required placeholder="لطفاً مشکل یا درخواست خود را به طور کامل شرح دهید..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">پیوست (اختیاری)</label>
                        <input type="file" class="form-control" id="ticket-attachment" name="attachment">
                        <div class="form-text">حداکثر حجم فایل: 5MB. فرمت‌های مجاز: JPG, PNG, PDF, DOC</div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-clock me-2"></i>
                        <small>پاسخ به تیکت‌ها معمولاً در کمتر از ۲۴ ساعت کاری انجام می‌شود.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-success" id="submit-ticket">
                        <i class="fas fa-paper-plane me-2"></i>ارسال تیکت
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // ایجاد تیکت جدید
    $('#new-ticket-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'create_ticket');
        formData.append('nonce', coworking_ajax.nonce);
        
        $.ajax({
            url: coworking_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#submit-ticket').html('<span class="spinner-border spinner-border-sm"></span> در حال ارسال...');
                $('#submit-ticket').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    alert('تیکت با موفقیت ایجاد شد! شماره تیکت: ' + response.data.ticket_number);
                    location.reload();
                } else {
                    alert('خطا در ایجاد تیکت: ' + response.data);
                    $('#submit-ticket').html('<i class="fas fa-paper-plane me-2"></i>ارسال تیکت');
                    $('#submit-ticket').prop('disabled', false);
                }
            }
        });
    });
    
    // بستن تیکت
    $('.close-ticket').click(function() {
        if (!confirm('آیا از بستن این تیکت مطمئن هستید؟')) {
            return;
        }
        
        var ticketId = $(this).data('id');
        var button = $(this);
        
        $.ajax({
            url: coworking_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'close_ticket',
                ticket_id: ticketId,
                nonce: coworking_ajax.nonce
            },
            beforeSend: function() {
                button.html('<span class="spinner-border spinner-border-sm"></span>');
                button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    alert('تیکت با موفقیت بسته شد.');
                    location.reload();
                } else {
                    alert('خطا: ' + response.data);
                    button.html('<i class="fas fa-times me-1"></i>بستن');
                    button.prop('disabled', false);
                }
            }
        });
    });
    
    // فیلتر وضعیت
    $('#filter-status').on('change', function() {
        var status = $(this).val();
        
        if (status === 'all') {
            $('tbody tr').show();
        } else {
            $('tbody tr').each(function() {
                var rowStatus = $(this).find('td:nth-child(4) span').text().trim();
                var statusMap = {
                    'باز': 'open',
                    'در دست بررسی': 'in_progress',
                    'حل شده': 'resolved',
                    'بسته شده': 'closed'
                };
                
                if (statusMap[rowStatus] === status) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });
    
    // جستجو
    $('#search-tickets').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});
</script>

<style>
.table th {
    font-family: 'PeydaWeb';
    font-weight: bold;
}

.badge {
    font-family: 'PeydaWeb';
    padding: 5px 10px;
    border-radius: 20px;
}

.modal-header {
    border-radius: 15px 15px 0 0;
}

textarea {
    resize: vertical;
}
</style>

<?php get_footer(); ?>