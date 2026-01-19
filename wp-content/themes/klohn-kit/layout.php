<?php
// layout.php expects $kk_view to be set (path relative to theme root).
if (!isset($kk_view)) {
	$kk_view = 'views/page.php';
}
?>

<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>


<?php get_header(); ?>

<main class="site-main">

	<?php require locate_template($kk_view); ?>


</main>

<?php get_footer(); ?>

<?php wp_footer(); ?>


</body>
</html>
