<?php
if (!current_user_can('manage_options') && !current_user_can('coworking_manager')) {
    wp_die('شما دسترسی لازم را ندارید.');
}

global $wpdb;
$table_spaces = $wpdb->prefix . 'coworking_spaces';

// عملیات CRUD
if (isset($_POST['action'])) {
    $action = sanitize_text_field($_POST['action']);
    $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
    
    if (!wp_verify_nonce($nonce, 'coworking_nonce')) {
        wp_die('درخواست غیرمجاز');
    }
    
    if ($action === 'add_space') {
        // افزودن فضای جدید
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'slug' => sanitize_title($_POST['name']),
            'type' => sanitize_text_field($_POST['type']),
            'capacity' => intval($_POST['capacity']),
            'price_daily' => floatval($_POST['price_daily']),
            'price_weekly' => floatval($_POST['price_weekly']),
            'price_monthly' => floatval($_POST['price_monthly']),
            'description' => sanitize_textarea_field($_POST['description']),
            'features' => json_encode(array_map('sanitize_text_field', explode(',', $_POST['features']))),
            'status' => sanitize_text_field($_POST['status'])
        );
        
        $result = $wpdb->insert($table_spaces, $data);
        
        if ($result) {
            echo '<div class="notice notice-success is-dismissible"><p>فضای جدید با موفقیت اضافه شد.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>خطا در افزودن فضای جدید.</p></div>';
        }
    } elseif ($action === 'edit_space') {
        // ویرایش فضای موجود
        $space_id = intval($_POST['space_id']);
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'type' => sanitize_text_field($_POST['type']),
            'capacity' => intval($_POST['capacity']),
            'price_daily' => floatval($_POST['price_daily']),
            'price_weekly' => floatval($_POST['price_weekly']),
            'price_monthly' => floatval($_POST['price_monthly']),
            'description' => sanitize_textarea_field($_POST['description']),
            'features' => json_encode(array_map('sanitize_text_field', explode(',', $_POST['features']))),
            'status' => sanitize_text_field($_POST['status'])
        );
        
        $result = $wpdb->update($table_spaces, $data, array('id' => $space_id));
        
        if ($result !== false) {
            echo '<div class="notice notice-success is-dismissible"><p>فضا با موفقیت ویرایش شد.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>خطا در ویرایش فضا.</p></div>';
        }
    } elseif ($action === 'delete_space') {
        // حذف فضا
        $space_id = intval($_POST['space_id']);
        $result = $wpdb->delete($table_spaces, array('id' => $space_id));
        
        if ($result) {
            echo '<div class="notice notice-success is-dismissible"><p>فضا با موفقیت حذف شد.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>خطا در حذف فضا.</p></div>';
        }
    }
}

// دریافت فضاها
$spaces = $wpdb->get_results("SELECT * FROM $table_spaces ORDER BY type, name");
?>

<div class="wrap">
    <h1 class="wp-heading-inline" style="font-family: 'PeydaWeb';">
        <i class="fas fa-th-large me-2"></i>مدیریت فضاها
    </h1>
    
    <!-- دکمه اضافه کردن -->
    <button class="btn btn-success float-end" data-bs-toggle="modal" data-bs-target="#addSpaceModal">
        <i class="fas fa-plus me-2"></i>افزودن فضای جدید
    </button>
    
    <div class="clearfix"></div>
    
    <!-- لیست فضاها -->
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">لیست فضاها</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>نام</th>
                            <th>نوع</th>
                            <th>ظرفیت</th>
                            <th>قیمت روزانه</th>
                            <th>قیمت هفتگی</th>
                            <th>قیمت ماهانه</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($spaces as $space): 
                            $type_names = array(
                                'shared_desk' => 'صندلی اشتراکی',
                                'dedicated_desk' => 'صندلی اختصاصی',
                                'private_room' => 'اتاق خصوصی',
                                'meeting_room' => 'اتاق جلسه',
                                'event_hall' => 'سالن رویداد'
                            );
                            
                            $type_name = $type_names[$space->type] ?? $space->type;
                            $status_badge = $space->status === 'active' ? 
                                '<span class="badge bg-success">فعال</span>' : 
                                '<span class="badge bg-secondary">غیرفعال</span>';
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($space->name); ?></strong>
                                <?php if ($space->description): ?>
                                    <small class="text-muted d-block"><?php echo wp_trim_words($space->description, 5); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $type_name; ?></td>
                            <td><?php echo $space->capacity; ?> نفر</td>
                            <td><?php echo number_format($space->price_daily); ?> تومان</td>
                            <td><?php echo number_format($space->price_weekly); ?> تومان</td>
                            <td><?php echo number_format($space->price_monthly); ?> تومان</td>
                            <td><?php echo $status_badge; ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary edit-space" 
                                            data-id="<?php echo $space->id; ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editSpaceModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger delete-space" 
                                            data-id="<?php echo $space->id; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- آمار انواع فضا -->
    <div class="row mt-4">
        <?php
        $type_counts = $wpdb->get_results("
            SELECT type, COUNT(*) as count 
            FROM $table_spaces 
            WHERE status = 'active'
            GROUP BY type
        ");
        
        foreach ($type_counts as $type):
            $type_name = $type_names[$type->type] ?? $type->type;
            $colors = array(
                'shared_desk' => 'primary',
                'dedicated_desk' => 'success',
                'private_room' => 'warning',
                'meeting_room' => 'info',
                'event_hall' => 'danger'
            );
            $color = $colors[$type->type] ?? 'secondary';
        ?>
        <div class="col-md-2 col-6 mb-3">
            <div class="card text-center text-white bg-<?php echo $color; ?>">
                <div class="card-body py-3">
                    <h6 class="card-title mb-1"><?php echo $type_name; ?></h6>
                    <h4><?php echo $type->count; ?></h4>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- مودال افزودن فضای جدید -->
<div class="modal fade" id="addSpaceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">افزودن فضای جدید</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="add-space-form" method="post">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">نام فضای *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">نوع فضای *</label>
                                <select class="form-select" name="type" required>
                                    <option value="shared_desk">صندلی اشتراکی</option>
                                    <option value="dedicated_desk">صندلی اختصاصی</option>
                                    <option value="private_room">اتاق خصوصی</option>
                                    <option value="meeting_room">اتاق جلسه</option>
                                    <option value="event_hall">سالن رویداد</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">ظرفیت (نفر) *</label>
                                <input type="number" class="form-control" name="capacity" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">قیمت روزانه (تومان) *</label>
                                <input type="number" class="form-control" name="price_daily" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">وضعیت *</label>
                                <select class="form-select" name="status" required>
                                    <option value="active">فعال</option>
                                    <option value="inactive">غیرفعال</option>
                                    <option value="maintenance">در حال تعمیر</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">قیمت هفتگی (تومان)</label>
                                <input type="number" class="form-control" name="price_weekly" min="0">
                                <div class="form-text">در صورت خالی بودن، ۷ برابر قیمت روزانه محاسبه می‌شود.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">قیمت ماهانه (تومان)</label>
                                <input type="number" class="form-control" name="price_monthly" min="0">
                                <div class="form-text">در صورت خالی بودن، ۳۰ برابر قیمت روزانه محاسبه می‌شود.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">توضیحات</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">امکانات</label>
                        <input type="text" class="form-control" name="features" placeholder="wifi, printer, coffee, parking, ...">
                        <div class="form-text">امکانات را با کاما جدا کنید (انگلیسی بنویسید).</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="action" value="add_space">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('coworking_nonce'); ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>افزودن فضا
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- مودال ویرایش فضا -->
<div class="modal fade" id="editSpaceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">ویرایش فضا</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="edit-space-form" method="post">
                <div class="modal-body" id="edit-space-content">
                    <!-- محتوای داینامیک -->
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="action" value="edit_space">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('coworking_nonce'); ?>">
                    <input type="hidden" name="space_id" id="edit-space-id">
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
    // ویرایش فضا
    $('.edit-space').click(function() {
        var spaceId = $(this).data('id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'coworking_get_space_details',
                space_id: spaceId,
                nonce: '<?php echo wp_create_nonce('coworking_nonce'); ?>'
            },
            beforeSend: function() {
                $('#edit-space-content').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
            },
            success: function(response) {
                if (response.success) {
                    $('#edit-space-content').html(response.data.html);
                    $('#edit-space-id').val(spaceId);
                } else {
                    $('#edit-space-content').html('<div class="alert alert-danger">خطا در دریافت اطلاعات فضا</div>');
                }
            }
        });
    });
    
    // حذف فضا
    $('.delete-space').click(function() {
        if (!confirm('آیا از حذف این فضا مطمئن هستید؟')) {
            return;
        }
        
        var spaceId = $(this).data('id');
        var button = $(this);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'coworking_delete_space',
                space_id: spaceId,
                nonce: '<?php echo wp_create_nonce('coworking_nonce'); ?>'
            },
            beforeSend: function() {
                button.html('<span class="spinner-border spinner-border-sm"></span>');
                button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    alert('فضا با موفقیت حذف شد.');
                    location.reload();
                } else {
                    alert('خطا در حذف فضا: ' + response.data);
                    button.html('<i class="fas fa-trash"></i>');
                    button.prop('disabled', false);
                }
            }
        });
    });
    
    // محاسبه خودکار قیمت‌ها
    $('input[name="price_daily"]').on('blur', function() {
        var dailyPrice = parseFloat($(this).val()) || 0;
        
        var weeklyInput = $('input[name="price_weekly"]');
        var monthlyInput = $('input[name="price_monthly"]');
        
        if (!weeklyInput.val()) {
            weeklyInput.val(Math.round(dailyPrice * 7));
        }
        
        if (!monthlyInput.val()) {
            monthlyInput.val(Math.round(dailyPrice * 30));
        }
    });
});
</script>

<style>
.modal-content {
    border-radius: 15px;
    overflow: hidden;
}

.modal-header {
    border-radius: 15px 15px 0 0;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}
</style>