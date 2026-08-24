<?php
declare(strict_types=1);

/**
 * Lightbox mit PhotoSwipe statt der WordPress-Core-Lightbox.
 *
 * Die Core-Lightbox ("Bei Klick vergrößern") kann auf Touch-Geräten weder
 * zoomen noch pannen und zeigt Querformatbilder auf hochkant gehaltenen
 * Handys nur klein. Dieses Modul übernimmt die Einstellung "Bei Klick
 * vergrößern" aus dem Editor, schaltet die Core-Lightbox für den Block ab
 * und verlinkt das Bild stattdessen auf die Originaldatei mit den Maßen,
 * die PhotoSwipe braucht. Alle so markierten Bilder eines Beitrags bilden
 * eine Serie (Wischen zwischen Bildern). Bilder mit "Link zu Mediendatei"
 * werden ebenfalls eingebunden.
 *
 * Assets (PhotoSwipe 5, MIT) liegen unter assets/dist/vendor/photoswipe/
 * und werden nur geladen, wenn die Seite mindestens ein Lightbox-Bild hat.
 */

namespace KlohnKit\Lightbox;

defined('ABSPATH') || exit;

const LINK_CLASS = 'kk-lightbox';

function register(): void
{
	add_action('init', __NAMESPACE__ . '\\register_assets');
	add_filter('render_block_data', __NAMESPACE__ . '\\take_over_core_lightbox', 10, 1);
	add_filter('render_block_core/image', __NAMESPACE__ . '\\link_image', 20, 2);
}

function register_assets(): void
{
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();

	$files = [
		'photoswipe'          => '/assets/dist/vendor/photoswipe/photoswipe.esm.min.js',
		'photoswipe-lightbox' => '/assets/dist/vendor/photoswipe/photoswipe-lightbox.esm.min.js',
		'klohn-kit-lightbox'  => '/assets/dist/js/lightbox.js',
	];
	$deps = [
		'photoswipe'          => [],
		'photoswipe-lightbox' => [],
		'klohn-kit-lightbox'  => ['photoswipe', 'photoswipe-lightbox'],
	];
	foreach ($files as $id => $rel) {
		if (file_exists($dir . $rel)) {
			wp_register_script_module($id, $uri . $rel, $deps[$id], (string) filemtime($dir . $rel));
		}
	}

	$css = [
		'photoswipe'         => ['/assets/dist/vendor/photoswipe/photoswipe.css', []],
		'klohn-kit-lightbox' => ['/assets/dist/css/lightbox.css', ['photoswipe']],
	];
	foreach ($css as $id => [$rel, $d]) {
		if (file_exists($dir . $rel)) {
			wp_register_style($id, $uri . $rel, $d, (string) filemtime($dir . $rel));
		}
	}
}

/**
 * Liest die Lightbox-Einstellung des Bildblocks (Blockattribut oder
 * theme.json), schaltet die Core-Lightbox ab und merkt sich die Absicht.
 */
function take_over_core_lightbox(array $parsed): array
{
	if (($parsed['blockName'] ?? '') !== 'core/image' || is_admin()) {
		return $parsed;
	}
	if (! function_exists('block_core_image_get_lightbox_settings')) {
		return $parsed;
	}

	$link = $parsed['attrs']['linkDestination'] ?? 'none';
	if ($link !== 'none') {
		return $parsed;
	}

	$settings = block_core_image_get_lightbox_settings($parsed);
	if (! empty($settings['enabled'])) {
		$parsed['attrs']['lightbox']    = ['enabled' => false];
		$parsed['attrs']['kkLightbox'] = true;
	}

	return $parsed;
}

/**
 * Verlinkt das Bild auf die Originaldatei (inkl. Maßen und srcset für
 * PhotoSwipe) und stellt sicher, dass die Assets geladen werden.
 */
function link_image(string $content, array $block): string
{
	$attrs      = $block['attrs'] ?? [];
	$wants      = ! empty($attrs['kkLightbox']);
	$media_link = ($attrs['linkDestination'] ?? 'none') === 'media';

	if (! $wants && ! $media_link) {
		return $content;
	}

	$id = isset($attrs['id']) ? (int) $attrs['id'] : 0;
	$full = $id ? wp_get_attachment_image_src($id, 'full') : false;
	if (! $full || empty($full[1]) || empty($full[2])) {
		return $content;
	}
	[$src, $width, $height] = $full;
	$srcset = $id ? wp_get_attachment_image_srcset($id, 'full') : '';

	$data = sprintf(
		' class="%s" data-pswp-width="%d" data-pswp-height="%d"%s',
		esc_attr(LINK_CLASS),
		$width,
		$height,
		$srcset ? ' data-pswp-srcset="' . esc_attr($srcset) . '"' : ''
	);

	if ($media_link) {
		// Vorhandenen Link um die PhotoSwipe-Daten ergänzen.
		$processor = new \WP_HTML_Tag_Processor($content);
		if (! $processor->next_tag('a')) {
			return $content;
		}
		$processor->add_class(LINK_CLASS);
		$processor->set_attribute('data-pswp-width', (string) $width);
		$processor->set_attribute('data-pswp-height', (string) $height);
		if ($srcset) {
			$processor->set_attribute('data-pswp-srcset', $srcset);
		}
		$content = $processor->get_updated_html();
	} else {
		// <img> in einen Link auf das Original einhüllen (erste Fundstelle).
		$open  = '<a href="' . esc_url($src) . '"' . $data . '>';
		$new   = preg_replace('/<img\b[^>]*>/', $open . '$0</a>', $content, 1, $count);
		if (! $count) {
			return $content;
		}
		$content = $new;
	}

	enqueue_assets();

	return $content;
}

function enqueue_assets(): void
{
	static $done = false;
	if ($done) {
		return;
	}
	$done = true;
	wp_enqueue_script_module('klohn-kit-lightbox');
	wp_enqueue_style('photoswipe');
	wp_enqueue_style('klohn-kit-lightbox');
}
