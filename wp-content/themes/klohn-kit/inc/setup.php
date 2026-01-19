<?php
declare(strict_types=1);

namespace KlohnKit\Setup;

defined('ABSPATH') || exit;

function register(): void
{
	add_action('after_setup_theme', __NAMESPACE__ . '\\setup');
}

function setup(): void
{
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	]);

	// Gutenberg-friendly but still classic theme.
	add_theme_support('align-wide');
	add_theme_support('wp-block-styles');

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
}
