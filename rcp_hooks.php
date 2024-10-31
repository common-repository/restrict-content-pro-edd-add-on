<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class RCP_EDD_HANNANStd {

	public function __construct() {
		
		register_activation_hook( __FILE__ , array( $this , 'rcp_edd_alter_table') );
			
		if ( is_admin() ) {	
			add_action( 'admin_init', 						array( $this, 'rcp_edd_alter_table') );
			add_action( 'admin_notices', 					array( $this, 'rcp_edd_admin_notices') );
			add_action( 'rcp_add_subscription_form', 		array( $this, 'rcp_edd_add_subscription_form') );
			add_action( 'rcp_edit_subscription_form', 		array( $this, 'rcp_edd_edit_subscription_form') );
			add_action( 'rcp_pre_add_subscription', 		array( $this, 'rcp_edd_pre_add_edit_subscription') );
			add_action( 'rcp_add_subscription', 			array( $this, 'rcp_edd_add_edit_subscription') );
			add_action( 'rcp_pre_edit_subscription_level', 	array( $this, 'rcp_edd_pre_add_edit_subscription') );
			add_action( 'rcp_edit_subscription_level', 		array( $this, 'rcp_edd_add_edit_subscription') );
			add_action( 'rcp_levels_page_table_header', 	array( $this, 'rcp_edd_levels_page_table_header') );
			add_action( 'rcp_levels_page_table_footer', 	array( $this, 'rcp_edd_levels_page_table_footer') );
			add_action( 'rcp_levels_page_table_column', 	array( $this, 'rcp_edd_levels_page_table_column') );
		}
		
	}
	
	public function rcp_edd_alter_table(){
		
		global $wpdb, $rcp_db_name;

		if ( $wpdb->get_var( "show tables like '$rcp_db_name'" ) == $rcp_db_name && ! get_option( 'rcp_edd_installed') ) {
		
			$rcp_edd_columns = array( 'edd_discount_value', 'edd_discount_type' , 'edd_discount_paid_only' );
		
			foreach ( (array) $rcp_edd_columns as $rcp_edd_column ) {
			
				if( ! $wpdb->query( "SHOW COLUMNS FROM `" . $rcp_db_name . "` LIKE '" . $rcp_edd_column . "'" ) ) {
					$wpdb->query( "ALTER TABLE `" . $rcp_db_name . "` ADD `" . $rcp_edd_column . "`  tinytext" );
				}
				
			}
	
			add_option( 'rcp_edd_installed', '1' );
	
		}	
	
	}
	
	public function rcp_edd_add_subscription_form( ) {
		?>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="rcp-edd-discount-value"><?php _e( 'EDD Discount', 'edd_rcp' ); ?></label>
			</th>
			<td>
				<input type="text" id="rcp-edd-discount-value" name="edd_discount_value" value="" style="width: 40px;"/>
			
				<select name="edd_discount_type" id="rcp-edd-discount-type">
					<option value="percentage"><?php _e( 'Percentage', 'rcp' ); ?></option>
					<option value="flat"><?php _e( 'Flat amount', 'rcp' ); ?></option>
				</select>
				
				<input name="edd_discount_paid_only" id="rcp-edd-discount-paid-only" type="checkbox" checked="checked" />
				<span class="description"><?php _e( 'Paid Only?', 'rcp' ); ?></span>
					
				<p class="description">
				<?php echo sprintf(__( 'Enter the EDD default discount value for this subscription level. Values should be entered in EDD currency (%s). Note : the final price of each download can be defined for each subscription level from your download edit screens .', 'edd_rcp' ) , edd_get_currency() ); ?>
				</p>
			</td>
		</tr>
		<?php
	}
	
	public function rcp_edd_edit_subscription_form( $level ) {
		?>		
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="rcp-edd-discount-value"><?php _e( 'EDD Discount', 'edd_rcp' ); ?></label>
			</th>
			<td>
				<input type="text" id="rcp-edd-discount-value" name="edd_discount_value" value="<?php echo isset($level->edd_discount_value) ? esc_attr( $level->edd_discount_value ) : 0; ?>" style="width: 40px;"/>
			
				<select name="edd_discount_type" id="rcp-edd-discount-type">
					<option value="percentage" <?php selected( $level->edd_discount_type, 'percentage' ); ?>><?php _e( 'Percentage', 'rcp' ); ?></option>
					<option value="flat" <?php selected( $level->edd_discount_type, 'flat' ); ?>><?php _e( 'Flat amount', 'rcp' ); ?></option>
				</select>
					
				<input name="edd_discount_paid_only" id="rcp-edd-discount-paid-only" type="checkbox"  <?php checked( true, ( !empty( $level->edd_discount_paid_only ) ? (bool) $level->edd_discount_paid_only : false ) ); ?> />
				<span class="description"><?php _e( 'Paid Only?', 'rcp' ); ?></span>
				
				<p class="description">
				<?php echo sprintf(__( 'Enter the EDD default discount value for this subscription level. Values should be entered in EDD currency (%s). Note : the final price of each download can be defined for each subscription level from your download edit screens .', 'edd_rcp' ) , edd_get_currency() ); ?>
				</p>
			</td>
		</tr>
		<?php
	}
	
	public function rcp_edd_pre_add_edit_subscription( $args ) {
		
		if ( isset($_POST['edd_discount_value']) && !empty($_POST['edd_discount_value']) ) {
			
			$query = ( isset( $_POST['rcp-action'] ) && $_POST['rcp-action'] == 'add-level' ) ? 'level_not_added' : 'level_not_updated';
			
			$url = '';
			
			if ( ! is_numeric( $_POST['edd_discount_value'] ) ||( $_POST['edd_discount_value'] < 0 ) ) {
				$url = get_bloginfo('wpurl') . '/wp-admin/admin.php?page=rcp-member-levels&rcp_message=' . $query . '&edd_discount=invalid';
			}
			else if ( $_POST['edd_discount_type'] == 'percentage' && ( $_POST['edd_discount_value'] > 100 ) ) {
				$url = get_bloginfo('wpurl') . '/wp-admin/admin.php?page=rcp-member-levels&rcp_message=' . $query . '&edd_discount=percentage';
			}
			
			if ( $url != '' ) {
				wp_safe_redirect( $url ); 
				exit;
			}
		}
	}	
	
	
	public function rcp_edd_admin_notices() {
	
		if ( isset( $_GET['edd_discount'] ) ) {
			
			switch ( $_GET['edd_discount'] ) {
				
				case 'invalid' :
					$text = __( 'EDD Discount value must be numeric and positive .' , 'edd_rcp');
					break;
					
				case 'percentage' :
					$text = __( 'EDD Discount percent must be between 0 and 100 .' , 'edd_rcp');
					break;			
			}
			
			echo '<div class="error"><p>' . $text . '</p></div>';
		}
		
	}

	public function rcp_edd_add_edit_subscription( $id = 0, $args = array() ) {
		
		if ( empty( $id ) || isset( $_GET['activate_subscription'] ) || isset( $_GET['deactivate_subscription'] ) ) 
			return;
		
		global $wpdb, $rcp_db_name;
	
		$add = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$rcp_db_name} SET
					`edd_discount_value`        = '%s',
					`edd_discount_type`         = '%s',
					`edd_discount_paid_only`    = '%s'
					WHERE `id`      = '%d'
				;",
				sanitize_text_field( isset($_POST['edd_discount_value']) ? $_POST['edd_discount_value'] : '' ),
				sanitize_text_field( isset($_POST['edd_discount_type']) ? $_POST['edd_discount_type'] : '' ),
				sanitize_text_field( isset($_POST['edd_discount_paid_only']) ? $_POST['edd_discount_paid_only'] : '' ),
				$id
			)
		);
		
	}
	
	public function rcp_edd_levels_page_table_header(){
		?><th class="rcp-sub-edd-col"><?php _e('EDD Discount', 'edd_rcp'); ?></th><?php
	}
	
	public function rcp_edd_levels_page_table_footer(){
		?><th><?php _e('EDD Discount', 'edd_rcp'); ?></th><?php
	}
	
	public function rcp_edd_levels_page_table_column( $level_id ){
		$levels   	  	= new RCP_Levels();
		$level	  		= $levels->get_level( $level_id );
		$value 			= isset( $level->edd_discount_value ) ? esc_attr( $level->edd_discount_value ) : '';
		$paid_only 		= isset( $level->edd_discount_paid_only) ? $level->edd_discount_paid_only : '';
		$type 			= isset($level->edd_discount_type) ? $level->edd_discount_type : ''; 
		
		if ( !empty($value) ) {
			if ( $type == 'flat' )
				$discount = edd_currency_filter( edd_format_amount(( $value )) );
			else if ( $type == 'percentage' ) 
				$discount = $value . '&#37;';
		}
		else {
			$discount = '';
		}
		?>
		<td>
			<?php echo $discount . ( $discount != '' && ! empty($paid_only) && $paid_only ?  ' - '. __( 'Paid Only', 'edd_rcp' ) : '' ); ?>
		</td>
		<?php
	}
	
	
	
}