<?php
if (!current_user_can('manage_options') && !current_user_can('coworking_manager')) {
    wp_die('شما دسترسی لازم را ندارید.');
}

global $wpdb;
$table_tickets = $wpdb->prefix . 'coworking_tickets';
$table_ticket_messages = $wpdb->prefix . 'coworking_ticket_messages';
$table_users = $wpdb->prefix . 'users';

// پارامترهای فیلتر
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
$priority_filter = isset($_GET['priority']) ? sanitize_text_field($_GET['priority']) : 'all';
$assigned_to = isset($_GET['assigned_to']) ? intval($_GET['assigned_to']) : 0;

// ساخت شرط WHERE
$where_conditions = array("1=1");
if ($status_filter !== 'all') {
    $where_conditions[] = $wpdb->prepare("t.status = %s", $status_filter);
}
if ($priority_filter !== 'all') {
    $where_conditions[] = $wpdb->prepare("t.priority = %s", $priority_filter);
}
if ($assigned_to > 0) {
    $where_conditions[] = $wpdb->prepare("t.assigned_to = %d", $assigned_to);
}

$where_clause = implode(' AND ', $where_conditions);

// دریافت تیکت‌ها
$tickets_per_page = 20;
$current_page = max(1, isset($_GET['paged']) ? intval($_GET['paged']) : 1);
$offset = ($current_page - 1) * $tickets_per_page;

$total_tickets = $wpdb->get_var("SELECT COUNT(*) FROM $table_tickets t WHERE $where_clause");

$tickets = $wpdb->get_results($wpdb->prepare(
    "SELECT t.*, 
            u.display_name as user_name,
            u.user_email as user_email,
            a.display_name as assigned_name,
            (SELECT COUNT(*) FROM $table_ticket_messages tm WHERE tm.ticket_id = t.id) as message_count,
            (SELECT MAX(created_at) FROM $table_ticket_messages tm WHERE tm.ticket_id = t.id) as last_reply
    FROM $table_tickets t
    LEFT JOIN $table_users u ON t.user_id = u.ID
    LEFT JOIN $table_users a ON t.assigned_to = a.ID
    WHERE $where_clause
    ORDER BY 
        CASE t.priority
            WHEN 'urgent' THEN 1
            WHEN 'high' THEN 2
            WHEN 'medium' THEN 3
            WHEN 'low' THEN 4
        END,
        t.created_at DESC
    LIMIT %d OFFSET %d",
    $tickets_per_page, $offset
));
?>

<div class="wrap">
    <h1 class="wp-heading-inline" style="font-family: 'PeydaWeb';">
        <i class="fas fa-headset me-2"></i>مدیریت تیکت‌های پشتیبانی
    </h1>
    
    <!-- فرم فیلتر -->
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>فیلتر تیکت‌ها</h5>
        </div>
        <div class="card-body">
            <form method="get" action="" class="row g-3">
                <input type="hidden" name="page" value="coworking-tickets">
                
                <div class="col-md-3">
                    <label class="form-label">وضعیت</label>
                    <select name="status" class="form-select">
                        <option value="all" <?php selected($status_filter, 'all'); ?>>همه وضعیت‌ها</option>
                        <option value="open" <?php selected($status_filter, 'open'); ?>>باز</option>
                        <option value="in_progress" <?php selected($status_filter, 'in_progress'); ?>>در دست بررسی</option>
                        <option value="resolved" <?php selected($status_filter, 'resolved'); ?>>حل شده</option>
                        <option value="closed" <?php selected($status_filter, 'closed'); ?>>بسته شده</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">اولویت</label>
                    <select name="priority" class="form-select">
                        <option value="all" <?php selected($priority_filter, 'all'); ?>>همه اولویت‌ها</option>
                        <option value="urgent" <?php selected($priority_filter, 'urgent'); ?>>فوری</option>
                        <option value="high" <?php selected($priority_filter, 'high'); ?>>بالا</option>
                        <option value="medium" <?php selected($priority_filter, 'medium'); ?>>متوسط</option>
                        <option value="low" <?php selected($priority_filter, 'low'); ?>>پایین</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">اختصاص به</label>
                    <select name="assigned_to" class="form-select">
                        <option value="0">همه کارشناسان</option>
                        <?php 
                        // دریافت مدیران و کارشناسان
                        $admins = $wpdb->get_results("
                            SELECT u.ID, u.display_name 
                            FROM $table_users u 
                            INNER JOIN {$wpdb->prefix}usermeta um ON u.ID = um.user_id 
                            WHERE um.meta_key = '{$wpdb->prefix}capabilities' 
                            AND (um.meta_value LIKE '%administrator%' OR um.meta_value LIKE '%coworking_manager%')
                            ORDER BY u.display_name
                        ");
                        
                        foreach ($admins as $admin): ?>
                            <option value="<?php echo $admin->ID; ?>" <?php selected($assigned_to, $admin->ID); ?>>
                                <?php echo esc_html($admin->display_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>اعمال فیلتر
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- آمار تیکت‌ها -->
    <div class="row mt-4">
        <?php
        // آمار تیکت‌ها
        $open_tickets = $wpdb->get_var("SELECT COUNT(*) FROM $table_tickets WHERE status = 'open'");
        $urgent_tickets = $wpdb->get_var("SELECT COUNT(*) FROM $table_tickets WHERE priority = 'urgent' AND status IN ('open', 'in_progress')");
        $unassigned_tickets = $wpdb->get_var("SELECT COUNT(*) FROM $table_tickets WHERE assigned_to IS NULL AND status IN ('open', 'in_progress')");
        $today_tickets = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_tickets WHERE DATE(created_at) = %s",
            date('Y-m-d')
        ));
        ?>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center text-white bg-primary">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">تیکت‌های باز</h6>
                    <h4><?php echo $open_tickets; ?></h4>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center text-white bg-danger">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">تیکت‌های فوری</h6>
                    <h4><?php echo $urgent_tickets; ?></h4>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center text-white bg-warning">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">بدون اختصاص</h6>
                    <h4><?php echo $unassigned_tickets; ?></h4>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center text-white bg-info">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1">امروز</h6>
                    <h4><?php echo $today_tickets; ?></h4>
                </div>
            </div>
        </div>
    </div>
    
    <!-- جدول تیکت‌ها -->
    <div class="card mt-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">لیست تیکت‌ها</h5>
            <div>
                <button class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#assignTicketsModal">
                    <i class="fas fa-user-check me-2"></i>اختصاص گروهی
                </button>
                <button class="btn btn-outline-primary btn-sm" id="export-tickets">
                    <i class="fas fa-download me-2"></i>خروجی Excel
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if ($tickets): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>شماره تیکت</th>
                                <th>کاربر</th>
                                <th>موضوع</th>
                                <th>اولویت</th>
                                <th>وضعیت</th>
                                <th>اختصاص یافته به</th>
                                <th>آخرین پاسخ</th>
                                <th>تاریخ ایجاد</th>
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
                                    <div><strong><?php echo esc_html($ticket->user_name); ?></strong></div>
                                    <small class="text-muted"><?php echo esc_html($ticket->user_email); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo esc_html(wp_trim_words($ticket->subject, 5)); ?></strong>
                                    <?php if ($ticket->message_count > 0): ?>
                                        <small class="text-muted d-block"><?php echo $ticket->message_count; ?> پیام</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $priority_badge; ?></td>
                                <td><?php echo $status_badge; ?></td>
                                <td>
                                    <?php if ($ticket->assigned_name): ?>
                                        <?php echo esc_html($ticket->assigned_name); ?>
                                    <?php else: ?>
                                        <span class="text-muted">بدون اختصاص</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($ticket->last_reply): ?>
                                        <?php echo human_time_diff(strtotime($ticket->last_reply), current_time('timestamp')) . ' پیش'; ?>
                                    <?php else: ?>
                                        <span class="text-muted">بدون پاسخ</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo jdate('Y/m/d', strtotime($ticket->created_at)); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary view-ticket" 
                                                data-id="<?php echo $ticket->id; ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewTicketModal">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <button class="btn btn-outline-info reply-ticket" 
                                                data-id="<?php echo $ticket->id; ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#replyTicketModal">
                                            <i class="fas fa-reply"></i>
                                        </button>
                                        
                                        <button class="btn btn-outline-warning assign-ticket" 
                                                data-id="<?php echo $ticket->id; ?>"
                                                data-assigned="<?php echo $ticket->assigned_to; ?>">
                                            <i class="fas fa-user-tag"></i>
                                        </button>
                                        
                                        <?php if ($ticket->status !== 'closed'): ?>
                                            <button class="btn btn-outline-success close-ticket" 
                                                    data-id="<?php echo $ticket->id; ?>">
                                                <i class="fas fa-check"></i>
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
                <nav aria-label="صفحه‌بندی تیکت‌ها">
                    <ul class="pagination justify-content-center">
                        <?php
                        $total_pages = ceil($total_tickets / $tickets_per_page);
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
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">هیچ تیکتی یافت نشد</h5>
                    <p class="text-muted">لطفاً از فیلترهای دیگر استفاده کنید.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- تیکت‌های فوری -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">تیکت‌های فوری نیازمند اقدام</h5>
                </div>
                <div class="card-body">
                    <?php
                    $urgent_tickets_list = $wpdb->get_results("
                        SELECT t.*, u.display_name as user_name
                        FROM $table_tickets t
                        LEFT JOIN $table_users u ON t.user_id = u.ID
                        WHERE t.priority = 'urgent' AND t.status IN ('open', 'in_progress')
                        ORDER BY t.created_at DESC
                        LIMIT 5
                    ");
                    
                    if ($urgent_tickets_list): ?>
                        <div class="list-group">
                            <?php foreach ($urgent_tickets_list as $urgent): ?>
                            <div class="list-group-item border-danger">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo esc_html(wp_trim_words($urgent->subject, 5)); ?></strong>
                                        <div class="text-muted small"><?php echo esc_html($urgent->user_name); ?></div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-danger">فوری</span>
                                        <div class="text-muted small mt-1">
                                            <?php echo human_time_diff(strtotime($urgent->created_at), current_time('timestamp')) . ' پیش'; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted py-3">هیچ تیکت فوری وجود ندارد.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">تیکت‌های بدون پاسخ بیش از ۲۴ ساعت</h5>
                </div>
                <div class="card-body">
                    <?php
                    $old_tickets = $wpdb->get_results("
                        SELECT t.*, u.display_name as user_name
                        FROM $table_tickets t
                        LEFT JOIN $table_users u ON t.user_id = u.ID
                        WHERE t.status IN ('open', 'in_progress') 
                        AND t.created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
                        AND NOT EXISTS (
                            SELECT 1 FROM $table_ticket_messages tm 
                            WHERE tm.ticket_id = t.id 
                            AND tm.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                        )
                        ORDER BY t.created_at
                        LIMIT 5
                    ");
                    
                    if ($old_tickets): ?>
                        <div class="list-group">
                            <?php foreach ($old_tickets as $old): ?>
                            <div class="list-group-item border-warning">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo esc_html(wp_trim_words($old->subject, 5)); ?></strong>
                                        <div class="text-muted small"><?php echo esc_html($old->user_name); ?></div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-warning text-dark">قدیمی</span>
                                        <div class="text-muted small mt-1">
                                            <?php echo human_time_diff(strtotime($old->created_at), current_time('timestamp')) . ' پیش'; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted py-3">همه تیکت‌ها پاسخ داده شده‌اند.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- مودال مشاهده تیکت -->
<div class="modal fade" id="viewTicketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">مشاهده تیکت</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="ticket-details">
                <!-- محتوای داینامیک -->
            </div>
        </div>
    </div>
</div>

<!-- مودال پاسخ به تیکت -->
<div class="modal fade" id="replyTicketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">پاسخ به تیکت</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="reply-ticket-form">
                <div class="modal-body" id="reply-ticket-content">
                    <!-- محتوای داینامیک -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-paper-plane me-2"></i>ارسال پاسخ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- مودال اختصاص گروهی -->
<div class="modal fade" id="assignTicketsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">اختصاص گروهی تیکت‌ها</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assign-tickets-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">کارشناس</label>
                        <select class="form-select" id="bulk-assign-to" name="assign_to">
                            <option value="">انتخاب کارشناس...</option>
                            <?php foreach ($admins as $admin): ?>
                                <option value="<?php echo $admin->ID; ?>">
                                    <?php echo esc_html($admin->display_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">تیکت‌های انتخاب شده</label>
                        <div id="selected-tickets" class="border p-2 rounded bg-light" style="min-height: 100px;">
                            <p class="text-muted text-center mb-0">ابتدا تیکت‌ها را از جدول انتخاب کنید.</p>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle me-2"></i>
                        با کلیک بر روی دکمه اختصاص در هر تیکت، آن تیکت به این لیست اضافه می‌شود.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-user-check me-2"></i>اختصاص انتخاب شده‌ها
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var selectedTickets = [];
    
    // مشاهده تیکت
    $('.view-ticket').click(function() {
        var ticketId = $(this).data('id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'coworking_get_ticket_details',
                ticket_id: ticketId,
                nonce: '<?php echo wp_create_nonce('coworking_nonce'); ?>'
            },
            beforeSend: function() {
                $('#ticket-details').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
            },
            success: function(response) {
                if (response.success) {
                    $('#ticket-details').html(response.data);
                }
            }
        });
    });
    
    // پاسخ به تیکت
    $('.reply-ticket').click(function() {
        var ticketId = $(this).data('id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'coworking_get_reply_ticket_form',
                ticket_id: ticketId,
                nonce: '<?php echo wp_create_nonce('coworking_nonce'); ?>'
            },
            beforeSend: function() {
                $('#reply-ticket-content').html('<div class="text-center py-5"><div class="spinner-border text-info"></div></div>');
            },
            success: function(response) {
                if (response.success) {
                    $('#reply-ticket-content').html(response.data);
                }
            }
        });
    });
    
    // اختصاص تیکت
    $('.assign-ticket').click(function() {
        var ticketId = $(this).data('id');
        var currentAssigned = $(this).data('assigned');
        
        // برای اختصاص گروهی
        if ($('#assignTicketsModal').hasClass('show')) {
            if (!selectedTickets.includes(ticketId)) {
                selectedTickets.push(ticketId);
                updateSelectedTicketsList();
                $(this).addClass('btn-success').removeClass('btn-outline-warning');
            }
            return;
        }
        
        // برای اختصاص فردی
        var assigneeId = prompt('لطفاً شناسه کارشناس را وارد کنید (یا برای حذف اختصاص خالی بگذارید):', currentAssigned || '');
        
        if (assigneeId !== null) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'coworking_assign_ticket',
                    ticket_id: ticketId,
                    assignee_id: assigneeId || 0,
                    nonce: '<?php echo wp_create_nonce('coworking_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        alert('تیکت با موفقیت اختصاص داده شد.');
                        location.reload();
                    } else {
                        alert('خطا: ' + response.data);
                    }
                }
            });
        }
    });
    
    // بستن تیکت
    $('.close-ticket').click(function() {
        if (!confirm('آیا می‌خواهید این تیکت را ببندید؟')) {
            return;
        }
        
        var ticketId = $(this).data('id');
        var button = $(this);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'coworking_close_ticket',
                ticket_id: ticketId,
                nonce: '<?php echo wp_create_nonce('coworking_nonce'); ?>'
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
                    button.html('<i class="fas fa-check"></i>');
                    button.prop('disabled', false);
                }
            }
        });
    });
    
    // به‌روزرسانی لیست تیکت‌های انتخاب شده
    function updateSelectedTicketsList() {
        if (selectedTickets.length === 0) {
            $('#selected-tickets').html('<p class="text-muted text-center mb-0">ابتدا تیکت‌ها را از جدول انتخاب کنید.</p>');
            return;
        }
        
        var html = '<div class="selected-tickets-list">';
        selectedTickets.forEach(function(ticketId) {
            html += '<div class="selected-ticket d-flex justify-content-between align-items-center mb-2 p-2 border rounded">';
            html += '<span>تیکت #' + ticketId + '</span>';
            html += '<button type="button" class="btn btn-sm btn-outline-danger remove-ticket" data-id="' + ticketId + '">';
            html += '<i class="fas fa-times"></i>';
            html += '</button>';
            html += '</div>';
        });
        html += '</div>';
        
        $('#selected-tickets').html(html);
    }
    
    // حذف تیکت از لیست انتخاب شده
    $(document).on('click', '.remove-ticket', function() {
        var ticketId = $(this).data('id');
        var index = selectedTickets.indexOf(ticketId);
        
        if (index > -1) {
            selectedTickets.splice(index, 1);
            updateSelectedTicketsList();
            
            // برگرداندن دکمه به حالت اول
            $('.assign-ticket[data-id="' + ticketId + '"]').removeClass('btn-success').addClass('btn-outline-warning');
        }
    });
    
    // اختصاص گروهی تیکت‌ها
    $('#assign-tickets-form').on('submit', function(e) {
        e.preventDefault();
        
        var assigneeId = $('#bulk-assign-to').val();
        
        if (!assigneeId) {
            alert('لطفاً کارشناس را انتخاب کنید.');
            return;
        }
        
        if (selectedTickets.length === 0) {
            alert('هیچ تیکتی انتخاب نشده است.');
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'coworking_bulk_assign_tickets',
                ticket_ids: selectedTickets,
                assignee_id: assigneeId,
                nonce: '<?php echo wp_create_nonce('coworking_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('تیکت‌ها با موفقیت اختصاص داده شدند.');
                    location.reload();
                } else {
                    alert('خطا: ' + response.data);
                }
            }
        });
    });
    
    // اکسپورت تیکت‌ها
    $('#export-tickets').click(function() {
        window.location.href = ajaxurl + '?action=coworking_export_tickets&' + $('form').serialize();
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
    border-radius: 5px;
}

.selected-tickets-list {
    max-height: 200px;
    overflow-y: auto;
}

.selected-ticket {
    background-color: #fff;
    transition: background-color 0.2s;
}

.selected-ticket:hover {
    background-color: #f8f9fa;
}
</style>