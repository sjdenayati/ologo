<?php
if (!current_user_can('manage_options') && !current_user_can('coworking_manager')) {
    wp_die('شما دسترسی لازم را ندارید.');
}

global $wpdb;
$table_holidays = $wpdb->prefix . 'coworking_holidays';
$table_reservations = $wpdb->prefix . 'coworking_reservations';
$table_spaces = $wpdb->prefix . 'coworking_spaces';

// عملیات CRUD برای تعطیلات
if (isset($_POST['action'])) {
    $action = sanitize_text_field($_POST['action']);
    $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
    
    if (!wp_verify_nonce($nonce, 'coworking_nonce')) {
        wp_die('درخواست غیرمجاز');
    }
    
    if ($action === 'add_holiday') {
        // افزودن تعطیلات جدید
        $data = array(
            'title' => sanitize_text_field($_POST['title']),
            'start_date' => sanitize_text_field($_POST['start_date']),
            'end_date' => sanitize_text_field($_POST['end_date']),
            'description' => sanitize_textarea_field($_POST['description']),
            'type' => sanitize_text_field($_POST['type'])
        );
        
        $result = $wpdb->insert($table_holidays, $data);
        
        if ($result) {
            echo '<div class="notice notice-success is-dismissible"><p>تعطیلی جدید با موفقیت اضافه شد.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>خطا در افزودن تعطیلی.</p></div>';
        }
    } elseif ($action === 'delete_holiday') {
        // حذف تعطیلات
        $holiday_id = intval($_POST['holiday_id']);
        $result = $wpdb->delete($table_holidays, array('id' => $holiday_id));
        
        if ($result) {
            echo '<div class="notice notice-success is-dismissible"><p>تعطیلی با موفقیت حذف شد.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>خطا در حذف تعطیلی.</p></div>';
        }
    }
}

// دریافت تعطیلات
$holidays = $wpdb->get_results("SELECT * FROM $table_holidays ORDER BY start_date DESC");
?>

<div class="wrap">
    <h1 class="wp-heading-inline" style="font-family: 'PeydaWeb';">
        <i class="fas fa-calendar-alt me-2"></i>مدیریت تقویم و تعطیلات
    </h1>
    
    <!-- تقویم -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">تقویم رزروها</h5>
                    <div>
                        <select class="form-select form-select-sm d-inline-block w-auto" id="calendar-space">
                            <option value="all">همه فضاها</option>
                            <?php 
                            $spaces = $wpdb->get_results("SELECT id, name FROM $table_spaces WHERE status = 'active'");
                            foreach ($spaces as $space): ?>
                                <option value="<?php echo $space->id; ?>"><?php echo esc_html($space->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-light btn-sm ms-2" id="today-btn">
                            <i class="fas fa-calendar-day"></i> امروز
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- افزودن تعطیلات -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>افزودن تعطیلات</h5>
                </div>
                <div class="card-body">
                    <form id="add-holiday-form" method="post">
                        <div class="mb-3">
                            <label class="form-label">عنوان *</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">تاریخ شروع *</label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">تاریخ پایان *</label>
                                <input type="date" class="form-control" name="end_date" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">نوع</label>
                            <select class="form-select" name="type">
                                <option value="holiday">تعطیلی رسمی</option>
                                <option value="maintenance">تعمیرات</option>
                                <option value="private_event">رویداد خصوصی</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">توضیحات</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        
                        <input type="hidden" name="action" value="add_holiday">
                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('coworking_nonce'); ?>">
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-plus me-2"></i>افزودن به تقویم
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- لیست تعطیلات آینده -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-calendar-times me-2"></i>تعطیلات آینده</h5>
                </div>
                <div class="card-body">
                    <?php 
                    $today = date('Y-m-d');
                    $future_holidays = $wpdb->get_results($wpdb->prepare(
                        "SELECT * FROM $table_holidays 
                        WHERE end_date >= %s 
                        ORDER BY start_date 
                        LIMIT 5",
                        $today
                    ));
                    
                    if ($future_holidays): ?>
                        <div class="list-group">
                            <?php foreach ($future_holidays as $holiday): 
                                $type_badge = '';
                                switch ($holiday->type) {
                                    case 'holiday':
                                        $type_badge = '<span class="badge bg-danger">تعطیلی</span>';
                                        break;
                                    case 'maintenance':
                                        $type_badge = '<span class="badge bg-warning text-dark">تعمیرات</span>';
                                        break;
                                    case 'private_event':
                                        $type_badge = '<span class="badge bg-info">رویداد</span>';
                                        break;
                                }
                            ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo esc_html($holiday->title); ?></h6>
                                        <small class="text-muted">
                                            <?php echo jdate('Y/m/d', strtotime($holiday->start_date)); ?> - 
                                            <?php echo jdate('Y/m/d', strtotime($holiday->end_date)); ?>
                                        </small>
                                    </div>
                                    <div>
                                        <?php echo $type_badge; ?>
                                        <button class="btn btn-sm btn-outline-danger delete-holiday" 
                                                data-id="<?php echo $holiday->id; ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php if ($holiday->description): ?>
                                    <p class="mt-2 mb-0 small"><?php echo esc_html($holiday->description); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted py-3">هیچ تعطیلی آینده‌ای ثبت نشده است.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ساعت کاری -->
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>تنظیمات ساعت کاری</h5>
        </div>
        <div class="card-body">
            <form id="working-hours-form" method="post">
                <div class="row">
                    <?php 
                    $days = array(
                        'saturday' => 'شنبه',
                        'sunday' => 'یکشنبه',
                        'monday' => 'دوشنبه',
                        'tuesday' => 'سه‌شنبه',
                        'wednesday' => 'چهارشنبه',
                        'thursday' => 'پنجشنبه',
                        'friday' => 'جمعه'
                    );
                    
                    foreach ($days as $key => $day): 
                        $working_hours = get_option('coworking_' . $key . '_hours', array('from' => '08:00', 'to' => '18:00'));
                        $is_holiday = get_option('coworking_' . $key . '_holiday', false);
                    ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input holiday-checkbox" 
                                           type="checkbox" 
                                           name="<?php echo $key; ?>_holiday" 
                                           id="<?php echo $key; ?>_holiday"
                                           <?php checked($is_holiday, true); ?>>
                                    <label class="form-check-label" for="<?php echo $key; ?>_holiday">
                                        <strong><?php echo $day; ?></strong>
                                    </label>
                                </div>
                                
                                <div class="row <?php echo $is_holiday ? 'opacity-50' : ''; ?>">
                                    <div class="col-6">
                                        <label class="form-label small">از</label>
                                        <input type="time" 
                                               class="form-control form-control-sm time-input" 
                                               name="<?php echo $key; ?>_from" 
                                               value="<?php echo $working_hours['from']; ?>"
                                               <?php disabled($is_holiday); ?>>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">تا</label>
                                        <input type="time" 
                                               class="form-control form-control-sm time-input" 
                                               name="<?php echo $key; ?>_to" 
                                               value="<?php echo $working_hours['to']; ?>"
                                               <?php disabled($is_holiday); ?>>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>ذخیره ساعت کاری
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- آمار رزروهای امروز -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">رزروهای امروز</h5>
                </div>
                <div class="card-body">
                    <?php
                    $today = date('Y-m-d');
                    $today_reservations = $wpdb->get_results($wpdb->prepare(
                        "SELECT r.*, s.name as space_name, u.display_name 
                        FROM $table_reservations r
                        LEFT JOIN $table_spaces s ON r.space_id = s.id
                        LEFT JOIN {$wpdb->prefix}users u ON r.user_id = u.ID
                        WHERE r.start_date <= %s AND r.end_date >= %s AND r.status IN ('confirmed', 'active')
                        ORDER BY r.start_date",
                        $today, $today
                    ));
                    
                    if ($today_reservations): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>فضا</th>
                                        <th>کاربر</th>
                                        <th>ساعت</th>
                                        <th>وضعیت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($today_reservations as $reservation): ?>
                                    <tr>
                                        <td><?php echo esc_html($reservation->space_name); ?></td>
                                        <td><?php echo esc_html($reservation->display_name); ?></td>
                                        <td>تمام روز</td>
                                        <td>
                                            <span class="badge bg-<?php echo $reservation->status === 'active' ? 'success' : 'info'; ?>">
                                                <?php echo $reservation->status === 'active' ? 'فعال' : 'تأیید شده'; ?>
                                            </span>
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
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">رزروهای فردا</h5>
                </div>
                <div class="card-body">
                    <?php
                    $tomorrow = date('Y-m-d', strtotime('+1 day'));
                    $tomorrow_reservations = $wpdb->get_results($wpdb->prepare(
                        "SELECT r.*, s.name as space_name, u.display_name 
                        FROM $table_reservations r
                        LEFT JOIN $table_spaces s ON r.space_id = s.id
                        LEFT JOIN {$wpdb->prefix}users u ON r.user_id = u.ID
                        WHERE r.start_date <= %s AND r.end_date >= %s AND r.status IN ('confirmed', 'active')
                        ORDER BY r.start_date",
                        $tomorrow, $tomorrow
                    ));
                    
                    if ($tomorrow_reservations): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>فضا</th>
                                        <th>کاربر</th>
                                        <th>ساعت</th>
                                        <th>وضعیت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tomorrow_reservations as $reservation): ?>
                                    <tr>
                                        <td><?php echo esc_html($reservation->space_name); ?></td>
                                        <td><?php echo esc_html($reservation->display_name); ?></td>
                                        <td>تمام روز</td>
                                        <td>
                                            <span class="badge bg-<?php echo $reservation->status === 'active' ? 'success' : 'info'; ?>">
                                                <?php echo $reservation->status === 'active' ? 'فعال' : 'تأیید شده'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted py-3">هیچ رزروی برای فردا وجود ندارد.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS و JS تقویم -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fa.js'></script>

<script>
jQuery(document).ready(function($) {
    // تقویم
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fa',
        direction: 'rtl',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            var spaceId = $('#calendar-space').val();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'coworking_get_calendar_events',
                    space_id: spaceId,
                    start: fetchInfo.startStr,
                    end: fetchInfo.endStr,
                    nonce: '<?php echo wp_create_nonce('coworking_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        successCallback(response.data);
                    }
                }
            });
        },
        eventClick: function(info) {
            var event = info.event;
            
            if (event.extendedProps.type === 'reservation') {
                var modalHtml = `
                    <div class="modal fade" id="eventModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">جزئیات رزرو</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>فضا:</strong> ${event.extendedProps.space_name}</p>
                                    <p><strong>کاربر:</strong> ${event.extendedProps.user_name}</p>
                                    <p><strong>تاریخ:</strong> ${event.start.toLocaleDateString('fa-IR')} تا ${event.end.toLocaleDateString('fa-IR')}</p>
                                    <p><strong>وضعیت:</strong> ${event.extendedProps.status}</p>
                                    <p><strong>مبلغ:</strong> ${event.extendedProps.amount.toLocaleString()} تومان</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                $('body').append(modalHtml);
                var modal = new bootstrap.Modal(document.getElementById('eventModal'));
                modal.show();
            }
        }
    });
    
    calendar.render();
    
    // تغییر فیلتر فضا
    $('#calendar-space').on('change', function() {
        calendar.refetchEvents();
    });
    
    // دکمه امروز
    $('#today-btn').click(function() {
        calendar.today();
    });
    
    // حذف تعطیلات
    $('.delete-holiday').click(function() {
        if (!confirm('آیا از حذف این تعطیلی مطمئن هستید؟')) {
            return;
        }
        
        var holidayId = $(this).data('id');
        var button = $(this);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'coworking_delete_holiday',
                holiday_id: holidayId,
                nonce: '<?php echo wp_create_nonce('coworking_nonce'); ?>'
            },
            beforeSend: function() {
                button.html('<span class="spinner-border spinner-border-sm"></span>');
                button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('خطا در حذف تعطیلی');
                    button.html('<i class="fas fa-times"></i>');
                    button.prop('disabled', false);
                }
            }
        });
    });
    
    // فعال/غیرفعال کردن فیلدهای ساعت کاری
    $('.holiday-checkbox').on('change', function() {
        var card = $(this).closest('.card-body');
        var timeInputs = card.find('.time-input');
        
        if ($(this).is(':checked')) {
            timeInputs.prop('disabled', true);
            card.find('.row').addClass('opacity-50');
        } else {
            timeInputs.prop('disabled', false);
            card.find('.row').removeClass('opacity-50');
        }
    });
    
    // ذخیره ساعت کاری
    $('#working-hours-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData + '&action=coworking_save_working_hours&nonce=<?php echo wp_create_nonce('coworking_nonce'); ?>',
            success: function(response) {
                if (response.success) {
                    alert('ساعت کاری با موفقیت ذخیره شد.');
                }
            }
        });
    });
});
</script>

<style>
#calendar {
    font-family: 'PeydaWeb';
}

.fc-toolbar-title {
    font-size: 1.5em;
}

.fc-daygrid-day-number {
    font-family: 'PeydaWeb';
}

.opacity-50 {
    opacity: 0.5;
}

.badge {
    font-family: 'PeydaWeb';
    padding: 5px 10px;
    border-radius: 20px;
}

.card {
    border-radius: 10px;
    overflow: hidden;
}

.list-group-item {
    border-radius: 8px;
    margin-bottom: 5px;
}
</style>