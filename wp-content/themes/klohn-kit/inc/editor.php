<?php
declare(strict_types=1);

namespace KlohnKit\Editor;

defined('ABSPATH') || exit;

function register(): void
{
	add_action('after_setup_theme', __NAMESPACE__ . '\\setup_editor');
}

function setup_editor(): void
{
	add_theme_support('editor-styles');
	add_editor_style('assets/dist/css/base.css');
}
