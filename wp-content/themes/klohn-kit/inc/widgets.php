<?php
declare(strict_types=1);

namespace KlohnKit\Widgets;

defined('ABSPATH') || exit;

function register(): void
{
	add_action('widgets_init', __NAMESPACE__ . '\\widgets_init');
}

function widgets_init(): void
{
	register_sidebar([
		'name'          => 'Footer',
		'id'            => 'footer',
		'before_widget' => '<section class="widget %2$s" id="%1$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	]);
}
