					</main>
					<footer role="contentinfo" class="global-footer">
						<div class="of-wrap">
							<?php if ( have_rows ( 'footer_columns', 'option' ) ) : ?>
								<div class="footer-columns -columns-<?php echo count( get_field( 'footer_columns', 'option') ); ?>">
								  <?php while ( have_rows( 'footer_columns', 'option' ) ) : the_row(); ?>

							      <div class="footer-column"> 
							      	<?php the_sub_field( 'footer_column', 'option'); ?>
							      </div>

								  <?php endwhile; ?>
								</div>
							<?php else : ?>
								<p>&copy; Copyright <?php echo date( 'Y' ); ?></p>
							<?php endif; ?>
						</div>
					</footer>

					<div class="of-modal-backdrop"></div>
				</div>

				<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
				<script>window.jQuery || document.write('<script src="' + '<?php bloginfo( 'template_directory' ) ;?>' + '/assets/js/lib/jquery-2.1.1.min.js"><\/script>')</script>
				
				<script>
						var ajax_url = '<?php bloginfo( 'url' ); ?>/wp-admin/admin-ajax.php';
				</script>
				<!--<script src="<?php echo get_stylesheet_directory_uri();?>/assets/js/app<?php echo PRODUCTION_MODE ? '.min' : ''; ?>.js"></script>-->
				<?php wp_footer(); ?>
		</body>
</html>