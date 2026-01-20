<?php
declare(strict_types=1);

namespace KlohnKit\Performance;

defined('ABSPATH') || exit;

function register(): void {
	// Head cleanup.
	add_action( 'init', __NAMESPACE__ . '\cleanup_head' );

	// Front-end CSS bloat.
	#add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\dequeue_frontend_styles', 100 );

	// Block CSS strategy (reduce inline tags).
	add_filter( 'should_load_separate_core_block_assets', '__return_false' );
	add_filter( 'should_load_block_assets_on_demand', '__return_false' );

	// Strip WP core default presets from global styles.
	add_filter( 'wp_theme_json_data_default', __NAMESPACE__ . '\strip_core_theme_json_defaults', 10, 1 );
}

function cleanup_head(): void
{
	// Emojis.
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_action('admin_print_styles', 'print_emoji_styles');

	// oEmbed / REST / misc head links.
	remove_action('wp_head', 'wp_oembed_add_discovery_links');
	remove_action('wp_head', 'wp_oembed_add_host_js');
	remove_action('wp_head', 'rest_output_link_wp_head', 10);
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'wp_generator');
}

function dequeue_frontend_styles(): void
{
	wp_dequeue_style('wp-block-library');
	wp_dequeue_style('wp-block-library-theme');
	wp_dequeue_style('global-styles');
	wp_dequeue_style('classic-theme-styles'); // if present
}

function strip_core_theme_json_defaults($theme_json)
{
	if (!$theme_json instanceof \WP_Theme_JSON_Data) {
		return $theme_json;
	}

	$data = $theme_json->get_data();

	unset($data['settings']['color']['palette']['default']);
	unset($data['settings']['color']['gradients']['default']);
	unset($data['settings']['color']['duotone']['default']);
	unset($data['settings']['typography']['fontSizes']['default']);
	unset($data['settings']['spacing']['spacingSizes']['default']);

	return new \WP_Theme_JSON_Data($data, 'default');
}
