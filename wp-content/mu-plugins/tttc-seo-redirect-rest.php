<?php
/**
 * Plugin Name: TTTC – SEO-Weiterleitung über die REST-API
 * Description: Gibt das Feld "301-Weiterleitung" von The SEO Framework für Seiten in der REST-API frei.
 *
 * The SEO Framework speichert die Weiterleitung einer Seite im Post-Meta-Feld
 * "redirect" (im Editor: SEO-Einstellungen → Sichtbarkeit → 301-Weiterleitung)
 * und führt sie selbst aus. Dieses Plugin macht nur das Feld über
 * /wp-json/wp/v2/pages/<ID> lesbar und für Nutzer mit Bearbeitungsrecht an der
 * Seite schreibbar. Die Weiterleitung bleibt damit eine Einstellung an der
 * Seite und ist im Backend sichtbar und änderbar.
 */

add_action('init', function () {
    register_post_meta('page', 'redirect', [
        'type'              => 'string',
        'single'            => true,
        'default'           => '',
        'show_in_rest'      => true,
        'sanitize_callback' => 'esc_url_raw',
        'auth_callback'     => function ($allowed, $meta_key, $post_id) {
            return current_user_can('edit_post', $post_id);
        },
    ]);
});
