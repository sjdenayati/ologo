<?php
/**
 * قالب صفحات عادی
 */
get_header();
?>

<div class="container" style="margin-top: 40px; margin-bottom: 60px;">
    
    <!-- عنوان صفحه -->
    <div class="page-header" style="margin-bottom: 40px;">
        <h1 style="color: #196ab4; text-align: center; margin-bottom: 20px;">
            <?php the_title(); ?>
        </h1>
        
        <?php if (has_post_thumbnail()): ?>
            <div style="text-align: center; margin-bottom: 30px;">
                <?php the_post_thumbnail('large', array(
                    'style' => 'max-width: 100%; height: auto; border-radius: 10px;'
                )); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- محتوای صفحه -->
    <div class="page-content">
        <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
            <?php
            while (have_posts()) : the_post();
                the_content();
                
                // صفحه‌بندی محتوا
                wp_link_pages(array(
                    'before' => '<div class="page-links" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">',
                    'after'  => '</div>',
                    'pagelink' => '<span class="page-link">%</span>',
                ));
            endwhile;
            ?>
        </div>
    </div>

</div>

<?php get_footer(); ?>