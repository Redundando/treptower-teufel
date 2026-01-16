<footer class="site-footer">
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
</footer>