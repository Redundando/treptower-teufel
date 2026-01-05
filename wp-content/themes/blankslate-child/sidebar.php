<aside id="sidebar">
<?php if ( is_active_sidebar( 'primary-widget-area' ) ) : ?>
<div id="primary" class="widget-area">
<ul>
<?php dynamic_sidebar( 'primary-widget-area' ); ?>
</ul>
</div>
<?php endif; ?>

<?php 
if ( is_active_sidebar( 'secondary-sidebar-widget' ) ) : ?>
    <div id="secondary-widgets" class="secondary-widgets widget-area">
    <?php dynamic_sidebar( 'secondary-sidebar-widget' ); ?>
    </div>     
<?php endif; ?>

</aside>