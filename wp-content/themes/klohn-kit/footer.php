<footer class="site-footer">

    <?php if ( is_active_sidebar('footer') ) : ?>
        <div class="site-footer__widgets">
            <div class="container">
                <?php dynamic_sidebar('footer'); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="site-footer__nav">
        <div class="container">

            <nav class="footer-nav" aria-label="<?php esc_attr_e('Footer menu', 'klohn-kit'); ?>">
                <?php
                wp_nav_menu([
                        'theme_location' => 'footer',
                        'container'      => false,
                        'menu_class'     => 'footer-menu',
                        'fallback_cb'    => false,
                ]);
                ?>
            </nav>

            <p class="site-footer__meta">
                &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>
            </p>

        </div>
    </div>

</footer>
