<?php

	class Util {	
		/**
		 * 
		 * Write any number and any argument given
		 * 
		 */
		static function debug() {
			$args = func_get_args();
			
			if( !empty( $args ) ) {
				foreach( $args as $arg ) {
					echo '<pre>'.print_r( $arg, true ).'</pre><br />';
				}
			}
			
		}
	}