<?php if ( !isWorldMapShown() ) : ?>
	<footer id="main-footer">
		<div class="container">
			<?php get_sidebar( 'footer' ); ?>

			<p id="copyright"><?php printf( __( 'Designed by Sabine & Thorsten based on %1$s | Powered by %2$s', 'Explorable' ), '<a href="http://www.elegantthemes.com" title="Premium WordPress Themes">Explorable by Elegant Themes</a>', '<a href="http://www.wordpress.org">WordPress</a>' ); ?></p>
		</div> <!-- end .container -->
	</footer> <!-- end #main-footer -->
<?php endif; ?>

	<?php wp_footer(); ?>
</body>
</html>
