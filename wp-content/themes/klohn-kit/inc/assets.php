<?php
declare(strict_types=1);

namespace KlohnKit\Assets;

defined('ABSPATH') || exit;

function register(): void
{
	add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue');
}

function enqueue(): void
{
	$theme   = wp_get_theme();
	$version = (string) ($theme->get('Version') ?: '0.1.0');

	// style.css
	wp_enqueue_style('klohn-kit', get_stylesheet_uri(), [], $version);

	// assets/dist/css/base.css (only if present)
	$css_rel  = '/assets/dist/css/base.css';
	$css_path = get_stylesheet_directory() . $css_rel;
	if (file_exists($css_path)) {
		wp_enqueue_style(
			'klohn-kit-base',
			get_stylesheet_directory_uri() . $css_rel,
			[],
			(string) filemtime($css_path)
		);
	}

	// assets/dist/js/main.js (only if present)
	$has_main = false;
	$js_rel   = '/assets/dist/js/main.js';
	$js_path  = get_stylesheet_directory() . $js_rel;
	if (file_exists($js_path)) {
		$has_main = true;
		wp_enqueue_script(
			'klohn-kit-main',
			get_stylesheet_directory_uri() . $js_rel,
			[],
			(string) filemtime($js_path),
			true
		);
	}

	// External links behavior
	$ext_rel  = '/assets/dist/js/external-links.js';
	$ext_path = get_stylesheet_directory() . $ext_rel;
	if (file_exists($ext_path)) {
		wp_enqueue_script(
			'klohn-kit-external-links',
			get_stylesheet_directory_uri() . $ext_rel,
			$has_main ? ['klohn-kit-main'] : [],
			(string) filemtime($ext_path),
			true
		);
	}
}
