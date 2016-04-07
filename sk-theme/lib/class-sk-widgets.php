<?php
/**
 * Theme widget registration.
 *
 * @since 1.0.0
 *
 * @package sk-theme
 */
class SK_Widgets {
	public function __construct() {
		add_action( 'widgets_init', array( &$this, 'widgets_init' ) );
	}

	/**
   * Register custom widgets.
   *
   * @since 1.0.0
   * 
   * @return null
   */
	public function widgets_init() {
		//$this->register_example_widget();
	}

	/**
   * Custom example widget.
   *
   * @since 1.0.0
   * @access private
   * 
   * @return null
   */
	private function register_example_widget() {
		register_sidebar( array(
			'name'          => __( 'Example sidebar', 'sk' ),
			'id'            => 'sidebar-example',
			'before_widget' => '<section class="widget %1$s %2$s"><div class="widget-inner">',
			'after_widget'  => '</div></section>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>',
		) );
	}
}