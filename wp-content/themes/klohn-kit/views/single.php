<div class="content">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class(); ?>>
				<h1 class="entry-title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h1>

				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
		<?php endwhile; ?>

		<div class="pagination">
			<?php the_posts_pagination(); ?>
		</div>

	<?php else : ?>
		<p><?php esc_html_e( 'No posts found.', 'klohn-kit' ); ?></p>
	<?php endif; ?>
</div>