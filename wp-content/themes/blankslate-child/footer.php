<?php
/**
 * The template for displaying the footer. 
 */
?>

	</div><!-- #content -->


<?php 
if ( is_active_sidebar( 'custom-footer-widget' ) ) : ?>
    <div id="footer-widget-area" class="footer-widget-area" role="complementary">
    <?php dynamic_sidebar( 'custom-footer-widget' ); ?>
    </div>     
<?php endif; ?>

	<footer id="colophon" class="site-footer">
	<p>© Treptower Teufel Tennis Club 1991 - <?php echo date("Y"); ?></p>
		<?php if ( has_nav_menu ( 'footer' ) ) : ?>
			<?php wp_nav_menu( array( 'theme_location' => 'footer', 'menu_class' => 'footer-menu', 'depth' => 1) ); ?>
		<?php endif; ?>

	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

<script type="text/javascript">

$(document).ready(function() {

    $('.menu-icon').click(function() {
        $(this).parent().toggleClass('is-tapped');
        $('#hamburger').toggleClass('open');
		if ($('#hamburger').hasClass('open')){
			$(".mobile-nav").show();			
		} else {
			$(".mobile-nav").hide();
		}
    });

	
});	
	
</script>

</body>
</html>
