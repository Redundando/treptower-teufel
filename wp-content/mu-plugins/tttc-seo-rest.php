<?php
/**
 * Plugin Name: TTTC – SEO-Felder über die REST-API
 * Description: Gibt SEO-Titel, Meta-Beschreibung und 301-Weiterleitung von The SEO Framework für Seiten in der REST-API frei.
 *
 * The SEO Framework speichert diese Einstellungen einer Seite als Post-Meta
 * (im Editor: SEO-Einstellungen der Seite) und wertet sie selbst aus:
 *
 *   _genesis_title        Meta-Titel (Reiter Allgemein)
 *   _genesis_description  Meta-Beschreibung (Reiter Allgemein)
 *   redirect              301-Weiterleitung (Reiter Sichtbarkeit)
 *
 * Dieses Plugin macht nur die drei Felder über /wp-json/wp/v2/pages/<ID>
 * lesbar und für Nutzer mit Bearbeitungsrecht an der Seite schreibbar. Sie
 * bleiben Einstellungen an der Seite und sind im Backend sichtbar und änderbar.
 */

add_action('init', function () {
    $fields = [
        '_genesis_title'       => 'sanitize_text_field',
        '_genesis_description' => 'sanitize_text_field',
        'redirect'             => 'esc_url_raw',
    ];

    foreach ($fields as $key => $sanitize) {
        register_post_meta('page', $key, [
            'type'              => 'string',
            'single'            => true,
            'default'           => '',
            'show_in_rest'      => true,
            'sanitize_callback' => $sanitize,
            'auth_callback'     => function ($allowed, $meta_key, $post_id) {
                return current_user_can('edit_post', $post_id);
            },
        ]);
    }
});
