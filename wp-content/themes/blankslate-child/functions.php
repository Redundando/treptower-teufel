<?php
add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles' );
function enqueue_parent_styles() {
	$color = isset($_GET['color']) ? sanitize_key($_GET['color']) : '';


	if ($color=="")
	{
		wp_enqueue_style( 'color-style', get_stylesheet_directory_uri().'/colors.css' );
	} 
	else
	{
		wp_enqueue_style( 'color-style', get_stylesheet_directory_uri().'/'.$color.'.css' );
	}
	wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
}

function child_theme_setup() {
	register_nav_menus( array( 
			'footer'	=> __( 'Footer Menu', 'footer' ),
		) );
}

add_action( 'after_setup_theme', 'child_theme_setup' );



function wpb_widgets_init() {
 
    register_sidebar( array(
        'name'          => 'Custom Footer Widget Area',
        'id'            => 'custom-footer-widget',
        'before_widget' => '<div class="footer-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="footer-title">',
        'after_title'   => '</h2>',
    ) );

    register_sidebar( array(
        'name'          => 'Secondary Sidebar',
        'id'            => 'secondary-sidebar-widget',
        'before_widget' => '<div class="secondary-sidebar-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="secondary-sidebar-widget">',
        'after_title'   => '</h2>',
    ) );

 
}
add_action( 'widgets_init', 'wpb_widgets_init' );

function gutenberg_editor_styles() {
    wp_enqueue_style(
        'admin-styles',
        get_stylesheet_directory_uri().'/style-editor.css'
    );
}
add_action(
    'admin_enqueue_scripts',
    'gutenberg_editor_styles'
);

// Disable Gutenberg Custom Colors
add_theme_support( 'disable-custom-colors' );

// Disable Gutenberg Custom Gradients
add_theme_support( 'disable-custom-gradients' );

// Editor Color Palette
	add_theme_support( 'editor-color-palette', array(
array('name'  => __( 'White', 'textdomain' ), 'slug'  => 'white-color', 'color' => 'var(--white-color)'),
array('name'  => __( 'Black', 'textdomain' ), 'slug'  => 'black-color', 'color' => 'var(--black-color)'),
array('name'  => __( 'Primary Lightest', 'textdomain' ), 'slug'  => 'primary-lightest-color', 'color' => 'var(--primary-lightest-color)'),
array('name'  => __( 'Primary Light', 'textdomain' ), 'slug'  => 'primary-light-color', 'color' => 'var(--primary-light-color)'),
array('name'  => __( 'Primary Middle', 'textdomain' ), 'slug'  => 'primary-middle-color', 'color' => 'var(--primary-middle-color)'),
array('name'  => __( 'Primary Dark', 'textdomain' ), 'slug'  => 'primary-dark-color', 'color' => 'var(--primary-dark-color)'),
array('name'  => __( 'Primary Darkest', 'textdomain' ), 'slug'  => 'primary-darkest-color', 'color' => 'var(--primary-darkest-color)'),
array('name'  => __( 'Secondary Lightest', 'textdomain' ), 'slug'  => 'secondary-lightest-color', 'color' => 'var(--secondary-lightest-color)'),
array('name'  => __( 'Secondary Light', 'textdomain' ), 'slug'  => 'secondary-light-color', 'color' => 'var(--secondary-light-color)'),
array('name'  => __( 'Secondary Middle', 'textdomain' ), 'slug'  => 'secondary-middle-color', 'color' => 'var(--secondary-middle-color)'),
array('name'  => __( 'Secondary Dark', 'textdomain' ), 'slug'  => 'secondary-dark-color', 'color' => 'var(--secondary-dark-color)'),
array('name'  => __( 'Secondary Darkest', 'textdomain' ), 'slug'  => 'secondary-darkest-color', 'color' => 'var(--secondary-darkest-color)'),
array('name'  => __( 'Tertiary Lightest', 'textdomain' ), 'slug'  => 'tertiary-lightest-color', 'color' => 'var(--tertiary-lightest-color)'),
array('name'  => __( 'Tertiary Light', 'textdomain' ), 'slug'  => 'tertiary-light-color', 'color' => 'var(--tertiary-light-color)'),
array('name'  => __( 'Tertiary Middle', 'textdomain' ), 'slug'  => 'tertiary-middle-color', 'color' => 'var(--tertiary-middle-color)'),
array('name'  => __( 'Tertiary Dark', 'textdomain' ), 'slug'  => 'tertiary-dark-color', 'color' => 'var(--tertiary-dark-color)'),
array('name'  => __( 'Tertiary Darkest', 'textdomain' ), 'slug'  => 'tertiary-darkest-color', 'color' => 'var(--tertiary-darkest-color)'),


        ) );