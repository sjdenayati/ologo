<?php
/**
 * فوتر صفحات مدیریت
 */
?>
</div><!-- /.wrap -->

<!-- اسکریپت‌های عمومی -->
<script>
// تابع نمایش پیام
function showAlert(message, type = 'success') {
    var alertClass = type === 'success' ? 'alert-success' : 
                    type === 'error' ? 'alert-danger' : 
                    type === 'warning' ? 'alert-warning' : 'alert-info';
    
    var alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // اضافه کردن به بالای صفحه
    jQuery('.wrap').prepend(alertHtml);
    
    // حذف خودکار بعد از 5 ثانیه
    setTimeout(function() {
        jQuery('.alert').alert('close');
    }, 5000);
}

// تابع تأیید حذف
function confirmDelete(message = 'آیا از حذف این مورد مطمئن هستید؟') {
    return confirm(message);
}

// تابع بارگذاری مجدد صفحه
function reloadPage(delay = 0) {
    if (delay > 0) {
        setTimeout(function() {
            location.reload();
        }, delay);
    } else {
        location.reload();
    }
}

// تابع بازگشت
function goBack() {
    window.history.back();
}

// تابع فرمت تاریخ شمسی
function toPersianDate(date) {
    if (!date) return '';
    
    var d = new Date(date);
    var options = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        calendar: 'persian',
        numberingSystem: 'arab'
    };
    
    return new Intl.DateTimeFormat('fa-IR', options).format(d);
}

// تابع فرمت عدد
function formatNumber(number) {
    if (!number) return '0';
    return new Intl.NumberFormat('fa-IR').format(number);
}

// تابع فرمت پول
function formatCurrency(amount) {
    return formatNumber(amount) + ' تومان';
}

// تابع کپی به کلیپ‌بورد
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showAlert('متن با موفقیت کپی شد.', 'success');
    }).catch(function(err) {
        showAlert('خطا در کپی متن: ' + err, 'error');
    });
}

// تنظیمات تاریخ پیش‌فرض
jQuery(document).ready(function($) {
    // تنظیم تاریخ امروز در فیلدهای تاریخ
    $('input[type="date"]:not([value])').each(function() {
        $(this).val(new Date().toISOString().split('T')[0]);
    });
    
    // اعتبارسنجی فیلدهای عددی
    $('input[type="number"]').on('input', function() {
        var min = parseFloat($(this).attr('min'));
        var max = parseFloat($(this).attr('max'));
        var value = parseFloat($(this).val());
        
        if (!isNaN(min) && value < min) {
            $(this).val(min);
        }
        
        if (!isNaN(max) && value > max) {
            $(this).val(max);
        }
    });
    
    // نمایش پیش‌نمایش تصاویر
    $('input[type="file"]').on('change', function(e) {
        var file = e.target.files[0];
        var preview = $(this).siblings('.image-preview');
        
        if (file && preview.length) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                preview.html('<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px;">');
            }
            
            reader.readAsDataURL(file);
        }
    });
});
</script>

<!-- وابستگی‌های Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Font Awesome -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

<!-- Persian Date -->
<script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- اسکریپت‌های اختصاصی -->
<script src="<?php echo get_template_directory_uri(); ?>/admin/js/admin.js"></script>