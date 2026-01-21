<?php
declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/inc/setup.php';
require_once __DIR__ . '/inc/assets.php';
require_once __DIR__ . '/inc/widgets.php';
require_once __DIR__ . '/inc/editor.php';
require_once __DIR__ . '/inc/calendar-shortcode.php';
require_once __DIR__ . '/inc/blocks.php';
require_once __DIR__ . '/inc/performance.php';
require_once __DIR__ . '/inc/block-styles.php';

KlohnKit\Setup\register();
KlohnKit\Assets\register();
KlohnKit\Widgets\register();
KlohnKit\Editor\register();
KlohnKit\Blocks\register();
KlohnKit\Performance\register();
KlohnKit\BlockStyles\register();