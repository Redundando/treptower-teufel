<?php
declare(strict_types=1);

add_action('after_setup_theme', function () {
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

	// Gutenberg-friendly but still classic theme.
	add_theme_support('align-wide');
	add_theme_support('wp-block-styles');

	// Classic widgets screen (optional, but matches “classic WP” feel)
	remove_theme_support('widgets-block-editor');

	// Optional logo (Customize > Site Identity)
	add_theme_support('custom-logo', [
		'height'      => 80,
		'width'       => 80,
		'flex-height' => true,
		'flex-width'  => true,
	]);

	register_nav_menus([
		'primary' => __('Primary Menu', 'klohn-kit'),
		'footer'  => __('Footer Menu', 'klohn-kit'),
	]);
});

add_action('wp_enqueue_scripts', function () {
	wp_enqueue_style('klohn-kit', get_stylesheet_uri(), [], '0.1.0');
});

add_action('wp_enqueue_scripts', function () {
	$css_rel  = '/assets/dist/css/base.css';
	$css_path = get_stylesheet_directory() . $css_rel;
	if (file_exists($css_path)) {
		wp_enqueue_style('klohn-kit-base', get_stylesheet_directory_uri() . $css_rel, [], (string) filemtime($css_path));
	}

	$js_rel  = '/assets/dist/js/main.js';
	$js_path = get_stylesheet_directory() . $js_rel;
	if (file_exists($js_path)) {
		wp_enqueue_script('klohn-kit-main', get_stylesheet_directory_uri() . $js_rel, [], (string) filemtime($js_path), true);
	}
});

add_action('after_setup_theme', function () {
	add_theme_support('editor-styles');
	add_editor_style('assets/dist/css/base.css');
});
