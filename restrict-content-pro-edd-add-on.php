<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class RESTRICT_CONTENT_PRO_EDD_ADD_ON {

	private static $instance;

	public function __construct() {
	
		load_plugin_textdomain( 'edd_rcp', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
		
		add_action( 'admin_notices', array( $this, 'admin_notices') );
	
		require_once plugin_dir_path( __FILE__ ) . '/edd_hooks.php';
		$this->EDD_RCP = new EDD_RCP_HANNANStd();
		
		require_once plugin_dir_path( __FILE__ ) . '/rcp_hooks.php';
		$this->RCP_EDD = new RCP_EDD_HANNANStd();
			
	}

	public static function instance() {
	
		self::$instance = is_null( self::$instance ) ? new self() : self::$instance;
		
		return self::$instance;
	}
	
	public function admin_notices() {
	
		if( ! function_exists( 'rcp_is_active' ) && ! class_exists( 'Easy_Digital_Downloads' ) ) {
			echo '<div class="error"><p>' . __( ' Restrict Content Pro - EDD Add On requires Easy Digital Downloads and Restrict Content Pro. Please install or activate them to continue.' , 'edd_rcp') . '</p></div>';
		}
		else if(  ! class_exists( 'Easy_Digital_Downloads' ) ) {
			echo '<div class="error"><p>' . __( ' Restrict Content Pro - EDD Add On requires Easy Digital Downloads. Please install or activate it to continue.' , 'edd_rcp') . '</p></div>';
		}
		else if( ! function_exists( 'rcp_is_active' )  ) {
			echo '<div class="error"><p>' . __( ' Restrict Content Pro - EDD Add On requires Restrict Content Pro. Please install or activate it to continue.' , 'edd_rcp') . '</p></div>';
		}
	}

}

if ( ! function_exists( 'RESTRICT_CONTENT_PRO_EDD_ADD_ON' ) ) :

 	function RESTRICT_CONTENT_PRO_EDD_ADD_ON() {
		return RESTRICT_CONTENT_PRO_EDD_ADD_ON::instance();
	}

endif;

RESTRICT_CONTENT_PRO_EDD_ADD_ON();