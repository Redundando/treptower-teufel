<?php
declare(strict_types=1);

namespace KlohnKit\BlockStyles;

defined('ABSPATH') || exit;

function register(): void
{
	add_action('init', __NAMESPACE__ . '\\register_block_styles');
}

function register_block_styles(): void
{
	\register_block_style('core/group', [
		'name'  => 'prose-content',
		'label' => \__('Prose width (content only)', 'klohn-kit'),
	]);

	\register_block_style('core/group', [
		'name'  => 'prose-box',
		'label' => \__('Prose width (box)', 'klohn-kit'),
	]);
	\register_block_style('core/table', [
		'name'  => 'compact',
		'label' => __('Compact', 'klohn-kit'),
	]);

}

