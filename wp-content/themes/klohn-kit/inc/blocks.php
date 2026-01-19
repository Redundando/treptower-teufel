<?php
declare(strict_types=1);

namespace KlohnKit\Blocks;

defined('ABSPATH') || exit;

function register(): void
{
	add_action('init', __NAMESPACE__ . '\\register_blocks');
}

function register_blocks(): void
{
	register_calendar_block();
}

function register_calendar_block(): void
{
	$handle = 'klohn-kit-calendar-block';

	// Editor script lives in assets/dist like your other JS. :contentReference[oaicite:2]{index=2}
	$rel  = '/assets/dist/js/kk-calendar-block.js';
	$path = get_stylesheet_directory() . $rel;

	if (file_exists($path)) {
		wp_register_script(
			$handle,
			get_stylesheet_directory_uri() . $rel,
			[
				'wp-blocks',
				'wp-element',
				'wp-i18n',
				'wp-components',
				'wp-block-editor',
				'wp-server-side-render',
			],
			(string) filemtime($path),
			true
		);
	} else {
		// Register anyway so it doesn't fatally fail; the block just won't show until the file exists.
		wp_register_script(
			$handle,
			get_stylesheet_directory_uri() . $rel,
			[
				'wp-blocks',
				'wp-element',
				'wp-i18n',
				'wp-components',
				'wp-block-editor',
				'wp-server-side-render',
			],
			'1.0.0',
			true
		);
	}

	register_block_type('klohn-kit/calendar', [
		'api_version'     => 2,
		'editor_script'   => $handle,
		'render_callback' => __NAMESPACE__ . '\\render_calendar_block',
		'attributes'      => [
			'icsUrl'        => ['type' => 'string',  'default' => ''],
			'start'         => ['type' => 'string',  'default' => ''],
			'end'           => ['type' => 'string',  'default' => ''],
			'max'           => ['type' => 'number',  'default' => 999],
			'groupByMonth'  => ['type' => 'boolean', 'default' => true],
			'cacheMinutes'  => ['type' => 'number',  'default' => 0],
			'linkText'      => ['type' => 'string',  'default' => '» Details'],
		],
		'supports' => [
			'html'  => false,
			'align' => true,
		],
	]);
}

function render_calendar_block(array $attrs): string
{
	$ics = trim((string)($attrs['icsUrl'] ?? ''));
	if ($ics === '') {
		return '<div class="kk-calendar kk-calendar--error">Missing ICS URL.</div>';
	}

	if (!function_exists('kk_calendar_shortcode')) {
		return '<div class="kk-calendar kk-calendar--error">Calendar shortcode not available.</div>';
	}

	$atts = [
		'ics_url'        => $ics,
		'start'          => (string)($attrs['start'] ?? ''),
		'end'            => (string)($attrs['end'] ?? ''),
		'max'            => (string)max(1, (int)($attrs['max'] ?? 999)),
		'group_by_month' => !empty($attrs['groupByMonth']) ? '1' : '0',
		'cache_minutes'  => (string)max(0, (int)($attrs['cacheMinutes'] ?? 0)),
		'link_text'      => (string)($attrs['linkText'] ?? '» Details'),
	];

	// Reuse your existing renderer.
	return kk_calendar_shortcode($atts);
}
