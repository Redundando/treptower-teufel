<header class="site-header" id="site-header">
    <!-- Row 1: brand -->
    <div class="header-row header-row--brand">
        <div class="container header-brand">
            <div class="brand-left">
                <?php if (function_exists('the_custom_logo') && has_custom_logo()) : ?>
                    <div class="site-logo">
                        <?php the_custom_logo(); ?>
                    </div>
                <?php endif; ?>

                <div class="site-identity">
                    <a class="site-title" href="<?php echo esc_url(home_url('/')); ?>">
                        <?php bloginfo('name'); ?>
                    </a>

                    <?php if (get_bloginfo('description')) : ?>
                        <p class="site-tagline"><?php bloginfo('description'); ?></p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- Row 2: navigation -->
    <div class="header-row header-row--nav">
        <div class="container header-nav">
            <button class="nav-toggle" type="button"
                    aria-controls="primary-nav"
                    aria-expanded="false"
                    aria-label="<?php esc_attr_e('Menu', 'klohn-kit'); ?>">
                <span class="nav-toggle__icon" aria-hidden="true"></span>
            </button>

            <nav id="primary-nav" class="primary-nav" aria-label="<?php esc_attr_e('Primary menu', 'klohn-kit'); ?>">
                <?php
                wp_nav_menu([
                        'theme_location' => 'primary',
                        'container'      => false,
                        'menu_class'     => 'primary-menu',
                        'fallback_cb'    => false,
                ]);
                ?>
            </nav>
        </div>
    </div>
</header>
