<div class="content">
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <article <?php post_class(); ?>>

                <h1 class="entry-title"><?php the_title(); ?></h1>

                <div class="entry-meta">
                    <time class="entry-meta__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                        <?php echo esc_html( get_the_date() ); ?>
                    </time>

                    <span class="entry-meta__sep">·</span>

                    <span class="entry-meta__author">
						<?php echo esc_html( get_the_author() ); ?>
					</span>

                    <?php
                    edit_post_link(
                            esc_html__( 'Edit', 'klohn-kit' ),
                            ' <span class="entry-meta__sep">·</span><span class="entry-meta__edit">',
                            '</span>'
                    );
                    ?>
                </div>

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
