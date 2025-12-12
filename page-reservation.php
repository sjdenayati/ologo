<?php
/*
Template Name: رزرو فضای کار
*/
get_header();

if (!is_user_logged_in()) {
    echo '<script>alert("لطفا ابتدا وارد شوید."); window.location.href = "' . home_url('/login') . '";</script>';
    exit;
}

global $wpdb;
$table_spaces = $wpdb->prefix . 'coworking_spaces';

// دریافت فضاها
$spaces = $wpdb->get_results("SELECT * FROM $table_spaces WHERE status = 'active' ORDER BY type, name");
?>

<div class="container my-5">
    <h1 class="text-center mb-4" style="font-family: 'PeydaWeb'; color: #196ab4;">
        <i class="fas fa-calendar-plus me-2"></i>رزرو فضای کار اشتراکی
    </h1>
    
    <div class="row">
        <!-- سایدبار فیلتر -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="filter-card p-4 rounded shadow-sm" style="background: #f8f9fa; border-right: 3px solid #196ab4;">
                <h5 style="font-family: 'PeydaWeb'; color: #196ab4;">فیلتر فضاها</h5>
                
                <div class="mt-4">
                    <label class="form-label">نوع فضای:</label>
                    <select class="form-select" id="filter-type">
                        <option value="all">همه انواع</option>
                        <option value="shared_desk">صندلی اشتراکی</option>
                        <option value="dedicated_desk">صندلی اختصاصی</option>
                        <option value="private_room">اتاق خصوصی</option>
                        <option value="meeting_room">اتاق جلسه</option>
                        <option value="event_hall">سالن رویداد</option>
                    </select>
                </div>
                
                <div class="mt-3">
                    <label class="form-label">مدت رزرو:</label>
                    <select class="form-select" id="filter-duration">
                        <option value="daily">روزانه</option>
                        <option value="weekly">هفتگی</option>
                        <option value="monthly">ماهانه</option>
                    </select>
                </div>
                
                <div class="mt-3">
                    <label class="form-label">ظرفیت:</label>
                    <select class="form-select" id="filter-capacity">
                        <option value="0">همه ظرفیت‌ها</option>
                        <option value="1">۱ نفر</option>
                        <option value="2">۲ نفر</option>
                        <option value="4">۴ نفر</option>
                        <option value="6">۶ نفر</option>
                        <option value="8">۸ نفر یا بیشتر</option>
                    </select>
                </div>
                
                <div class="mt-4">
                    <label class="form-label">محدوده قیمت:</label>
                    <input type="range" class="form-range" id="price-range" min="0" max="1000000" step="10000">
                    <div class="d-flex justify-content-between">
                        <small>۰ تومان</small>
                        <small id="max-price">۱,۰۰۰,۰۰۰ تومان</small>
                    </div>
                </div>
                
                <button class="btn btn-primary w-100 mt-4" id="apply-filters">
                    <i class="fas fa-filter me-2"></i>اعمال فیلتر
                </button>
                
                <button class="btn btn-outline-secondary w-100 mt-2" id="reset-filters">
                    <i class="fas fa-redo me-2"></i>بازنشانی
                </button>
            </div>
            
            <!-- راهنمای انواع فضا -->
            <div class="info-card p-4 rounded shadow-sm mt-4" style="background: #e3f2fd;">
                <h6 style="font-family: 'PeydaWeb';">راهنمای انواع فضا:</h6>
                <ul class="list-unstyled mt-3" style="font-size: 14px;">
                    <li class="mb-2"><i class="fas fa-chair text-primary me-2"></i><strong>صندلی اشتراکی:</strong> میز کار در فضای باز</li>
                    <li class="mb-2"><i class="fas fa-user-tie text-success me-2"></i><strong>صندلی اختصاصی:</strong> میز کار ثابت</li>
                    <li class="mb-2"><i class="fas fa-door-closed text-warning me-2"></i><strong>اتاق خصوصی:</strong> اتاق اختصاصی</li>
                    <li class="mb-2"><i class="fas fa-users text-info me-2"></i><strong>اتاق جلسه:</strong> اتاق برای جلسات</li>
                    <li><i class="fas fa-calendar-alt text-danger me-2"></i><strong>سالن رویداد:</strong> فضای بزرگ برای رویدادها</li>
                </ul>
            </div>
        </div>
        
        <!-- لیست فضاها -->
        <div class="col-lg-9 col-md-8">
            <!-- جستجو -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" id="search-spaces" placeholder="جستجوی فضاها...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-grid">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#quickReserveModal">
                            <i class="fas fa-bolt me-2"></i>رزرو سریع
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- نمایش فضاها -->
            <div id="spaces-container">
                <?php if ($spaces): ?>
                    <div class="row">
                        <?php foreach ($spaces as $space): 
                            $type_name = '';
                            $type_icon = '';
                            $type_color = '';
                            
                            switch ($space->type) {
                                case 'shared_desk':
                                    $type_name = 'صندلی اشتراکی';
                                    $type_icon = 'fas fa-chair';
                                    $type_color = 'primary';
                                    break;
                                case 'dedicated_desk':
                                    $type_name = 'صندلی اختصاصی';
                                    $type_icon = 'fas fa-user-tie';
                                    $type_color = 'success';
                                    break;
                                case 'private_room':
                                    $type_name = 'اتاق خصوصی';
                                    $type_icon = 'fas fa-door-closed';
                                    $type_color = 'warning';
                                    break;
                                case 'meeting_room':
                                    $type_name = 'اتاق جلسه';
                                    $type_icon = 'fas fa-users';
                                    $type_color = 'info';
                                    break;
                                case 'event_hall':
                                    $type_name = 'سالن رویداد';
                                    $type_icon = 'fas fa-calendar-alt';
                                    $type_color = 'danger';
                                    break;
                            }
                            
                            $features = json_decode($space->features, true);
                        ?>
                        <div class="col-xl-4 col-lg-6 mb-4 space-item" 
                             data-type="<?php echo $space->type; ?>"
                             data-capacity="<?php echo $space->capacity; ?>"
                             data-price-daily="<?php echo $space->price_daily; ?>"
                             data-price-weekly="<?php echo $space->price_weekly; ?>"
                             data-price-monthly="<?php echo $space->price_monthly; ?>">
                            <div class="space-card card h-100 shadow-sm">
                                <div class="card-header bg-<?php echo $type_color; ?> text-white py-3">
                                    <h5 class="mb-0">
                                        <i class="<?php echo $type_icon; ?> me-2"></i>
                                        <?php echo esc_html($space->name); ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="space-meta mb-3">
                                        <span class="badge bg-light text-dark me-2">
                                            <i class="fas fa-users me-1"></i>ظرفیت: <?php echo $space->capacity; ?> نفر
                                        </span>
                                        <span class="badge bg-<?php echo $type_color; ?>">
                                            <i class="fas fa-tag me-1"></i><?php echo $type_name; ?>
                                        </span>
                                    </div>
                                    
                                    <p class="card-text text-muted" style="min-height: 60px;">
                                        <?php echo esc_html(wp_trim_words($space->description, 20)); ?>
                                    </p>
                                    
                                    <?php if ($features): ?>
                                    <div class="space-features mb-3">
                                        <small class="text-muted">امکانات:</small>
                                        <div class="d-flex flex-wrap mt-1">
                                            <?php 
                                            $feature_icons = [
                                                'wifi' => 'fas fa-wifi',
                                                'printer' => 'fas fa-print',
                                                'kitchen' => 'fas fa-utensils',
                                                'parking' => 'fas fa-parking',
                                                'coffee' => 'fas fa-coffee',
                                                'locker' => 'fas fa-lock'
                                            ];
                                            
                                            foreach (array_slice($features, 0, 4) as $feature):
                                                $icon = $feature_icons[$feature] ?? 'fas fa-check';
                                            ?>
                                            <span class="badge bg-light text-dark me-1 mb-1" title="<?php echo esc_attr($feature); ?>">
                                                <i class="<?php echo $icon; ?> me-1"></i><?php echo esc_html($feature); ?>
                                            </span>
                                            <?php endforeach; ?>
                                            
                                            <?php if (count($features) > 4): ?>
                                            <span class="badge bg-light text-dark">
                                                <i class="fas fa-plus"></i> <?php echo count($features) - 4; ?> بیشتر
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="space-pricing mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted d-block">روزانه</small>
                                                <strong class="text-primary"><?php echo number_format($space->price_daily); ?> تومان</strong>
                                            </div>
                                            <div class="text-center">
                                                <small class="text-muted d-block">هفتگی</small>
                                                <strong class="text-success"><?php echo number_format($space->price_weekly); ?> تومان</strong>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted d-block">ماهانه</small>
                                                <strong class="text-warning"><?php echo number_format($space->price_monthly); ?> تومان</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <button class="btn btn-primary w-100 reserve-btn" data-space-id="<?php echo $space->id; ?>">
                                        <i class="fas fa-calendar-check me-2"></i>انتخاب و رزرو
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-building fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">فضایی برای نمایش وجود ندارد</h5>
                        <p class="text-muted">لطفاً با مدیر سیستم تماس بگیرید.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- مودال رزرو -->
<div class="modal fade" id="reservationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">رزرو فضای کار</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="reservation-form">
                <div class="modal-body">
                    <div id="space-details" class="mb-4"></div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">تاریخ شروع</label>
                                <input type="text" class="form-control" id="start-date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">تاریخ پایان</label>
                                <input type="text" class="form-control" id="end-date" name="end_date" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">مدت رزرو</label>
                                <select class="form-select" id="duration-type" name="duration_type" required>
                                    <option value="daily">روزانه</option>
                                    <option value="weekly">هفتگی</option>
                                    <option value="monthly">ماهانه</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">کد تخفیف (اختیاری)</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="discount-code" name="discount_code">
                                    <button class="btn btn-outline-primary" type="button" id="apply-discount">اعمال</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">توضیحات (اختیاری)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="درخواست خاص یا توضیحات اضافه..."></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        پس از ثبت رزرو، ۲۴ ساعت فرصت دارید تا پرداخت را انجام دهید.
                    </div>
                    
                    <div id="price-summary" class="bg-light p-3 rounded d-none">
                        <h6>خلاصه قیمت:</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td>قیمت پایه:</td>
                                <td class="text-end"><span id="base-price">۰</span> تومان</td>
                            </tr>
                            <tr>
                                <td>تخفیف:</td>
                                <td class="text-end text-success"><span id="discount-amount">۰</span> تومان</td>
                            </tr>
                            <tr class="border-top">
                                <td><strong>مبلغ قابل پرداخت:</strong></td>
                                <td class="text-end"><strong><span id="final-price">۰</span> تومان</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="space-id" name="space_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary" id="submit-reservation">
                        <i class="fas fa-check me-2"></i>تأیید و پرداخت
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- مودال رزرو سریع -->
<div class="modal fade" id="quickReserveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">رزرو سریع</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="quick-reserve-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">نوع فضای مورد نظر</label>
                        <select class="form-select" id="quick-space-type" name="space_type">
                            <option value="shared_desk">صندلی اشتراکی</option>
                            <option value="dedicated_desk">صندلی اختصاصی</option>
                            <option value="private_room">اتاق خصوصی</option>
                            <option value="meeting_room">اتاق جلسه</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">از تاریخ</label>
                                <input type="text" class="form-control" id="quick-start-date" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">مدت (روز)</label>
                                <select class="form-select" id="quick-days" name="days">
                                    <option value="1">۱ روز</option>
                                    <option value="3">۳ روز</option>
                                    <option value="7">۱ هفته</option>
                                    <option value="30">۱ ماه</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <small><i class="fas fa-exclamation-triangle me-2"></i>
                        سیستم به صورت خودکار اولین فضای خالی را برای شما رزرو می‌کند.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-bolt me-2"></i>رزرو سریع
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // تاریخ شمسی
    $('#start-date, #end-date, #quick-start-date').persianDatepicker({
        format: 'YYYY/MM/DD',
        autoClose: true,
        initialValue: false,
        minDate: new persianDate().toDate(),
        maxDate: new persianDate().add('month', 6).toDate()
    });
    
    // انتخاب فضا برای رزرو
    $('.reserve-btn').click(function() {
        var spaceId = $(this).data('space-id');
        
        $.ajax({
            url: coworking_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_space_details',
                space_id: spaceId,
                nonce: coworking_ajax.nonce
            },
            beforeSend: function() {
                $('#space-details').html('<div class="text-center py-3"><div class="spinner-border text-primary"></div></div>');
            },
            success: function(response) {
                if (response.success) {
                    $('#space-details').html(response.data.html);
                    $('#space-id').val(spaceId);
                    
                    // محاسبه قیمت بر اساس تاریخ پیش‌فرض
                    var tomorrow = new persianDate().add('day', 1).format('YYYY/MM/DD');
                    var nextWeek = new persianDate().add('day', 7).format('YYYY/MM/DD');
                    
                    $('#start-date').val(tomorrow);
                    $('#end-date').val(nextWeek);
                    
                    calculatePrice(spaceId, tomorrow, nextWeek, 'daily');
                    
                    // نمایش مودال
                    var modal = new bootstrap.Modal(document.getElementById('reservationModal'));
                    modal.show();
                }
            }
        });
    });
    
    // محاسبه قیمت هنگام تغییر تاریخ
    $('#start-date, #end-date, #duration-type').on('change', function() {
        var spaceId = $('#space-id').val();
        var startDate = $('#start-date').val();
        var endDate = $('#end-date').val();
        var durationType = $('#duration-type').val();
        
        if (spaceId && startDate && endDate) {
            calculatePrice(spaceId, startDate, endDate, durationType);
        }
    });
    
    // تابع محاسبه قیمت
    function calculatePrice(spaceId, startDate, endDate, durationType) {
        $.ajax({
            url: coworking_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'calculate_reservation_price',
                space_id: spaceId,
                start_date: startDate,
                end_date: endDate,
                duration_type: durationType,
                nonce: coworking_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#base-price').text(response.data.base_price.toLocaleString());
                    $('#discount-amount').text(response.data.discount.toLocaleString());
                    $('#final-price').text(response.data.final_price.toLocaleString());
                    $('#price-summary').removeClass('d-none');
                }
            }
        });
    }
    
    // اعمال فیلترها
    $('#apply-filters').click(function() {
        var type = $('#filter-type').val();
        var duration = $('#filter-duration').val();
        var capacity = $('#filter-capacity').val();
        var maxPrice = $('#price-range').val();
        
        $('.space-item').each(function() {
            var item = $(this);
            var show = true;
            
            // فیلتر نوع
            if (type !== 'all' && item.data('type') !== type) {
                show = false;
            }
            
            // فیلتر ظرفیت
            if (capacity > 0 && item.data('capacity') < capacity) {
                show = false;
            }
            
            // فیلتر قیمت
            var priceField = 'price-' + duration;
            if (item.data(priceField) > maxPrice) {
                show = false;
            }
            
            if (show) {
                item.fadeIn();
            } else {
                item.fadeOut();
            }
        });
    });
    
    // بازنشانی فیلترها
    $('#reset-filters').click(function() {
        $('#filter-type').val('all');
        $('#filter-duration').val('daily');
        $('#filter-capacity').val('0');
        $('#price-range').val(1000000);
        $('#max-price').text('1,000,000 تومان');
        $('#search-spaces').val('');
        
        $('.space-item').fadeIn();
    });
    
    // جستجو
    $('#search-spaces').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('.space-item').filter(function() {
            var spaceName = $(this).find('.card-header h5').text().toLowerCase();
            var spaceDesc = $(this).find('.card-text').text().toLowerCase();
            $(this).toggle(spaceName.indexOf(value) > -1 || spaceDesc.indexOf(value) > -1);
        });
    });
    
    // تغییر محدوده قیمت
    $('#price-range').on('input', function() {
        var value = parseInt($(this).val()).toLocaleString();
        $('#max-price').text(value + ' تومان');
    });
    
    // رزرو سریع
    $('#quick-reserve-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: coworking_ajax.ajax_url,
            type: 'POST',
            data: formData + '&action=quick_reserve&nonce=' + coworking_ajax.nonce,
            beforeSend: function() {
                $('#quick-reserve-form button[type="submit"]').html('<span class="spinner-border spinner-border-sm"></span>');
            },
            success: function(response) {
                if (response.success) {
                    alert('رزرو سریع با موفقیت انجام شد!');
                    window.location.href = coworking_ajax.home_url + '/dashboard';
                } else {
                    alert('خطا: ' + response.data);
                    $('#quick-reserve-form button[type="submit"]').html('<i class="fas fa-bolt me-2"></i>رزرو سریع');
                }
            }
        });
    });
    
    // اعمال کد تخفیف
    $('#apply-discount').click(function() {
        var discountCode = $('#discount-code').val();
        if (discountCode) {
            // در اینجا کد تخفیف بررسی و اعمال می‌شود
            alert('کد تخفیف اعمال شد!');
        }
    });
    
    // ارسال فرم رزرو
    $('#reservation-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: coworking_ajax.ajax_url,
            type: 'POST',
            data: formData + '&action=make_reservation&nonce=' + coworking_ajax.nonce,
            beforeSend: function() {
                $('#submit-reservation').html('<span class="spinner-border spinner-border-sm"></span> در حال ثبت...');
                $('#submit-reservation').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('reservationModal'));
                    modal.hide();
                    
                    // نمایش پیام موفقیت
                    var successHtml = `
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4 class="text-success">رزرو با موفقیت ثبت شد!</h4>
                            <p>شماره رزرو: <strong>${response.data.reservation_id}</strong></p>
                            <p>شماره فاکتور: <strong>${response.data.invoice_number}</strong></p>
                            <div class="mt-4">
                                <a href="${coworking_ajax.home_url}/dashboard" class="btn btn-primary me-2">
                                    <i class="fas fa-tachometer-alt me-2"></i>مشاهده در داشبورد
                                </a>
                                <a href="${coworking_ajax.home_url}/invoice/${response.data.invoice_id}" class="btn btn-success">
                                    <i class="fas fa-file-invoice me-2"></i>پرداخت فاکتور
                                </a>
                            </div>
                        </div>
                    `;
                    
                    $('#spaces-container').html(successHtml);
                } else {
                    alert('خطا: ' + response.data);
                    $('#submit-reservation').html('<i class="fas fa-check me-2"></i>تأیید و پرداخت');
                    $('#submit-reservation').prop('disabled', false);
                }
            }
        });
    });
});
</script>

<style>
.space-card {
    transition: all 0.3s ease;
    border-radius: 15px;
    overflow: hidden;
    height: 100%;
}

.space-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.space-card .card-header {
    border-radius: 15px 15px 0 0 !important;
}

.space-pricing {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    margin-top: 15px;
}

.filter-card, .info-card {
    border-radius: 15px;
}

#price-summary {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}
</style>

<?php get_footer(); ?>