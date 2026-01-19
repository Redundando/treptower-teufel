<?php
defined('ABSPATH') || exit;

$recent = new WP_Query([
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 5,
	'no_found_rows'       => true,
	'ignore_sticky_posts' => true,
]);
?>

<div class="content">
	<article class="entry entry--404">
		<h1 class="entry-title">404</h1>

		<p>
			<a href="<?php echo esc_url( home_url('/') ); ?>">
				<?php echo esc_html__( 'Zur Startseite', 'klohn-kit' ); ?>
			</a>
		</p>

		<?php if ( $recent->have_posts() ) : ?>
			<h2><?php echo esc_html__( 'Neueste Beiträge', 'klohn-kit' ); ?></h2>
			<ul>
				<?php while ( $recent->have_posts() ) : $recent->the_post(); ?>
					<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
				<?php endwhile; ?>
			</ul>
			<?php wp_reset_postdata(); ?>
		<?php endif; ?>
	</article>
</div>
