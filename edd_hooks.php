<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class EDD_RCP_HANNANStd {

	public function __construct() {
	
		if ( is_admin() ) {
			add_action( 'edd_after_price_field', 		 	array( $this, 'edd_rcp_after_price_field' ) );
			add_filter( 'edd_save_download', 			 	array( $this, 'edd_rcp_save_download' ) , 10, 2 );
			add_action( 'edd_download_price_table_head', 	array( $this, 'edd_rcp_download_price_table_head' ) );
			add_filter( 'edd_price_row_args', 			 	array( $this, 'edd_rcp_price_row_args' ), 10, 2 );
			add_action( 'edd_download_price_table_row',  	array( $this, 'edd_rcp_download_price_table_row' ), 5, 3 );
			add_action( 'edd_meta_box_settings_fields',  	array( $this, 'edd_rcp_meta_box_settings_fields' ), 100 );
		}
		
		add_filter( 'edd_get_download_price', 			 	array( $this, 'edd_rcp_get_download_price' ), 10, 2 );
		add_filter( 'edd_get_variable_prices', 			 	array( $this, 'edd_rcp_get_variable_prices' ), 10, 2 );
		add_filter( 'edd_download_price_after_html', 	 	array( $this, 'edd_rcp_download_price_after_html' ), 10, 4 );
		add_filter( 'edd_purchase_link_args',			 	array( $this, 'edd_rcp_purchase_link_args' ) );
		add_filter( 'edd_cart_item_price_label',		 	array( $this, 'edd_rcp_cart_item_price_label' ), 10, 3 );
		remove_action( 'edd_purchase_link_top',						   	  'edd_purchase_variable_pricing' );
		add_action( 'edd_purchase_link_top',			 	array( $this, 'edd_rcp_purchase_link_top' ), 10, 2 );
		add_filter( 'edd_get_cart_total', 			 		array( $this, 'edd_rcp_get_cart_total' ), 99, 1 );
	}

	public function edd_rcp_meta_box_settings_fields( $post_id ) {
		?>
		<p><strong><?php _e( 'Restrict Content Pro', 'rcp' ) ?> :</strong></p>
		
		<p>
			<label for="edd_rcp_ignore_download">
			
				<?php echo EDD()->html->checkbox( array( 
					'name' => '_edd_rcp_ignore_download',
					'current' =>  get_post_meta( $post_id, '_edd_rcp_ignore_download', true ) ? (bool) get_post_meta( $post_id, '_edd_rcp_ignore_download', true ) : false 
				) );
				
				_e( 'Disable RCP settigns for this download', 'edd_rcp' ); ?>
				
			</label>
			
		</p>
		
		<p>
			<label for="edd_rcp_dont_show_main_price">
			
				<?php echo EDD()->html->checkbox( array( 
					'name' => '_edd_rcp_dont_show_main_price',
					'current' =>  get_post_meta( $post_id, '_edd_rcp_dont_show_main_price', true ) ? (bool) get_post_meta( $post_id, '_edd_rcp_dont_show_main_price', true ) : false 
				) ); 
				
				_e( 'Don\'t show main price for this download (if RCP settings for this download is enabled)', 'edd_rcp' ); ?>
				
			</label>
			
		</p>
		<?php
	}
	
	
	public function edd_rcp_after_price_field( $post_id ) {
		
		$price_display      = edd_has_variable_prices( $post_id ) ? ' style="display:none;"' : '';
		$currency_position 	= edd_get_option( 'currency_position' );
		$currency_filter	= edd_currency_filter( '' );
		$rcp_meta_box		= rcp_get_metabox_fields();
		$levels 			= rcp_get_subscription_levels( 'all' );
				
		foreach ( (array) $rcp_meta_box['fields'] as $field ) {
			
			if ( $field['type'] == 'levels' ) {
					
				foreach ( (array) $levels as $level ) { ?>
					
					<div id="edd_regular_rcp_price_field" class="edd_pricing_fields" <?php echo $price_display; ?> >
					<hr/>
					<?php

						$rcp_price	= get_post_meta( $post_id, '_edd_rcp_price_level_' . $level->id, true );
						$rcp_args = array(
							'name'	=> '_edd_rcp_price_level_' . $level->id,
							'value' => isset( $rcp_price ) && $rcp_price != '' ? esc_attr( edd_format_amount( $rcp_price ) ) : '',
							'class'	=> 'edd-price-field edd-rcp-price-field'
						);

						if ( empty( $currency_position ) || $currency_position == 'before' ) {
							echo $currency_filter . ' ' . EDD()->html->text( $rcp_args ) . ' ';
						} else {
							echo EDD()->html->text( $rcp_args ) . ' ' . $currency_filter . ' ';
						} ?>
						<span style="width:50%;display:inline-table">
							<label class="edd-label" for="'_edd_rcp_price_level_<?php echo $level->id; ?>"><?php echo sprintf( __( ' - price for "%s" subscription level', 'edd_rcp' ) , $level->name ); ?></label>&nbsp;
						</span>
						
						<?php echo EDD()->html->checkbox( array( 'name' => '_edd_rcp_paid_only_level_' . $level->id, 'current' =>  get_post_meta( $post_id, '_edd_rcp_paid_only_level_' . $level->id, true ) ? (bool) get_post_meta( $post_id, '_edd_rcp_paid_only_level_' . $level->id, true ) : false ) ); ?>
						<label for="_edd_rcp_paid_only_level_<?php echo $level->id; ?>"><?php _e( 'Paid Only?', 'rcp' ) ?></label>
		
					</div>
					
					<?php
				}
				
				break;
			}
			
		}
		
		echo '<hr/>'; ?>
		
		<span style="width:57%;display:inline-table">
			<label for="_edd_rcp_select_blank_status">
			<?php _e( 'If subscription price fields was left blank (first priority) : ', 'edd_rcp' ); ?>
			</label>
		</span>
		<?php
		$selected = get_post_meta( $post_id, '_edd_rcp_select_blank_status', true ) ? get_post_meta( $post_id, '_edd_rcp_select_blank_status', true ) : 'edd_discount';
		echo EDD()->html->select( array(
			'options' => array(
				'edd_discount'      => __( 'Use the adjusted discount for subscription levels', 'edd_rcp' ),
				'main_price' => __( 'Use the main price of this download', 'edd_rcp' ),
			),
			'name'             => '_edd_rcp_select_blank_status',
			'selected'         => $selected,
			'show_option_all'  => false,
			'show_option_none' => false,
			'chosen'           => is_rtl() ? false : true,
		) );
		?>
		
		<br/>
		<br/>
		
		
		<span style="width:57%;display:inline-table">
			<label for="_edd_rcp_select_paid_only_status">
			<?php _e( 'If the prices weren\'t blank and adjusted for "Paid Only" and the user was not active paid (second priority) : ', 'edd_rcp' ); ?>
			</label>
		</span>
		<?php
		$selected = get_post_meta( $post_id, '_edd_rcp_select_paid_only_status', true ) ? get_post_meta( $post_id, '_edd_rcp_select_paid_only_status', true ) : 'edd_discount';
		echo EDD()->html->select( array(
			'options' => array(
				'edd_discount'      => __( 'Use the adjusted discount for subscription levels', 'edd_rcp' ),
				'main_price' => __( 'Use the main price of this download', 'edd_rcp' )
			),
			'name'             => '_edd_rcp_select_paid_only_status',
			'selected'         => $selected,
			'show_option_all'  => false,
			'show_option_none' => false,
			'chosen'           => is_rtl() ? false : true,
		) );
		
	}
	
	public function edd_rcp_save_download( $download_id, $post ) {
		
		$rcp_meta_box = rcp_get_metabox_fields();
		$levels = rcp_get_subscription_levels( 'all' );
		
		if ( isset($_POST['_edd_rcp_select_paid_only_status']) ) {
			update_post_meta( $download_id, '_edd_rcp_select_paid_only_status' , $_POST['_edd_rcp_select_paid_only_status'] );
		}
		else {
			delete_post_meta( $download_id, '_edd_rcp_select_paid_only_status' );
		}
		
		if ( isset($_POST['_edd_rcp_select_blank_status']) ) {
			update_post_meta( $download_id, '_edd_rcp_select_blank_status' , $_POST['_edd_rcp_select_blank_status'] );
		}
		else {
			delete_post_meta( $download_id, '_edd_rcp_select_blank_status' );
		}
		
		if ( isset($_POST['_edd_rcp_dont_show_main_price']) ) {
			update_post_meta( $download_id, '_edd_rcp_dont_show_main_price' , $_POST['_edd_rcp_dont_show_main_price'] );
		}
		else {
			delete_post_meta( $download_id, '_edd_rcp_dont_show_main_price' );
		}
		
		if ( isset($_POST['_edd_rcp_ignore_download']) ) {
			update_post_meta( $download_id, '_edd_rcp_ignore_download' , $_POST['_edd_rcp_ignore_download'] );
		}
		else {
			delete_post_meta( $download_id, '_edd_rcp_ignore_download' );
		}
		
		foreach ( (array) $rcp_meta_box['fields'] as $field ) {
			
			if ( $field['type'] == 'levels' ) {
				
				foreach ( (array) $levels as $level ) {
					
					$level_id = $level->id;
					
					if ( isset($_POST['_edd_rcp_price_level_' . $level_id]) && $_POST['_edd_rcp_price_level_' . $level_id] != '' ) {
						update_post_meta( $download_id, '_edd_rcp_price_level_' . $level_id , $_POST['_edd_rcp_price_level_' . $level_id] );
					}
					else{
						delete_post_meta( $download_id, '_edd_rcp_price_level_' . $level_id );
					}
					
					if ( isset($_POST['_edd_rcp_paid_only_level_' . $level_id]) ){
						update_post_meta( $download_id, '_edd_rcp_paid_only_level_' . $level_id , $_POST['_edd_rcp_paid_only_level_' . $level_id] );
					}
					else{
						delete_post_meta( $download_id, '_edd_rcp_paid_only_level_' . $level_id );
					}
					
				}
				break;
			}
		}
		
	}
	
	public function edd_rcp_download_price_table_head() {
		
		$rcp_meta_box = rcp_get_metabox_fields();
		$levels = rcp_get_subscription_levels( 'all' );
				
		foreach ( (array) $rcp_meta_box['fields'] as $field ) {
			if ( $field['type'] == 'levels' ) {
				foreach ( (array) $levels as $level ) {
					echo '<th style="width: 100px;">' . $level->name . '</th>';
				}
			}
		}
	}
	
	public function edd_rcp_price_row_args( $args, $values ) {
		
		$rcp_meta_box = rcp_get_metabox_fields();
		$levels = rcp_get_subscription_levels( 'all' );
		
		foreach ( (array) $rcp_meta_box['fields'] as $field ) {
			if ( $field['type'] == 'levels' ) {
				foreach ( (array) $levels as $level ) {
					$args['_edd_rcp_price_level_' . $level->id] = isset( $values['_edd_rcp_price_level_' . $level->id] ) && $values['_edd_rcp_price_level_' . $level->id] !='' ? $values['_edd_rcp_price_level_' . $level->id] : '';
					$args['_edd_rcp_paid_only_level_' . $level->id] =  !empty($values['_edd_rcp_paid_only_level_' . $level->id]) ? (bool) $values['_edd_rcp_paid_only_level_' . $level->id] : false;
				}
			}
		}
		return $args;
	}
	
	public function edd_rcp_download_price_table_row( $post_id, $key, $args ) {

		$args_2 = $args_1 = $args;
		$rcp_meta_box = rcp_get_metabox_fields();
		$levels = rcp_get_subscription_levels( 'all' );
				
		foreach ( (array) $rcp_meta_box['fields'] as $field ) {
			
			if ( $field['type'] == 'levels' ) {
				
				foreach ( (array) $levels as $level ) { ?>
					<td>
						<?php
						$arg = '_edd_rcp_price_level_' . $level->id;
						$args_1 = wp_parse_args( $args_1, array( $arg => null) ); 
						$rcp_args = array(
							'name'			=> 'edd_variable_prices[' . $key . '][' . $arg . ']',
							'value' 		=> isset( $args_1[$arg] ) && $args_1[$arg] !='' ? esc_attr( edd_format_amount( $args_1[$arg] ) ) : '',
							'class'			=> 'edd-price-field edd-rcp-price-field',
							'placeholder'	=> edd_currency_filter( '' )
						);
						echo '<span>' . EDD()->html->text( $rcp_args ) . '</span>';
						

						$arg = '_edd_rcp_paid_only_level_' . $level->id;
						$args_2 = wp_parse_args( $args_2, array( $arg => null) ); 
						$rcp_args = array(
							'name'			=> 'edd_variable_prices[' . $key . '][' . $arg . ']',
							'current' 		=> !empty($args_2[$arg]) ? (bool) $args_2[$arg] : false,
							'class'			=> 'edd-price-field edd-rcp-price-field edd-rcp-price-field-checkbox edd-checkbox'
						);
						echo '<span>' . EDD()->html->checkbox( $rcp_args ) . '</span>';	?>
						<label for="edd_variable_prices['<?php echo $key ?>']['<?php echo $arg ?>']"><?php _e( 'Paid Only?', 'rcp' ) ?></label>
					</td>
					<?php
				}
				break;
			}			
		}
		?>
		<script type="text/javascript">
			var EDD_RCP = jQuery.noConflict();
			EDD_RCP( document ).on( "click", ".edd_add_repeatable", function() {
				EDD_RCP(".edd-rcp-price-field-checkbox").val('1');
			});
			</script>
		<?php
	}

	
	//front end
	public function edd_rcp_price_level_id() {
		global $user_ID;
		return '_edd_rcp_price_level_' . rcp_get_subscription_id( $user_ID );
	}
	
	public function edd_rcp_paid_only_id() {
		global $user_ID;	
		return '_edd_rcp_paid_only_level_' . rcp_get_subscription_id( $user_ID );
	}
	
	public function edd_rcp_dont_show_main_price( $download_id ) {
		return get_post_meta( $download_id, '_edd_rcp_dont_show_main_price', true ) ?  (bool) get_post_meta( $download_id, '_edd_rcp_dont_show_main_price', true ) : false;
	}
	
	public function edd_rcp_ignore_download( $download_id ) {
		return get_post_meta( $download_id, '_edd_rcp_ignore_download', true ) ?  (bool) get_post_meta( $download_id, '_edd_rcp_ignore_download', true ) : false;
	}
	
	public function edd_rcp_is_paid_user() {
		global $user_ID;
		return rcp_is_paid_user( $user_ID );
	}
	
	public function edd_rcp_get_level() {
		global $user_ID;
		$levels = new RCP_Levels();
		$level = $levels->get_level( rcp_get_subscription_id( $user_ID ) );
		return ( ! empty($level) && $level ) ? $level : false;
	}
	
	public function edd_rcp_price_after_discount( $price ) {
		$price 	= intval( str_replace(',', '', $price ));	
		if ( ! is_user_logged_in() )
			return $price;
		
		$level = self::edd_rcp_get_level();
		if ( ! $level )
			return $price;
			
		$paid_only 		= !empty( $level->edd_discount_paid_only ) ? $level->edd_discount_paid_only : '';
		if ( (bool) $paid_only && ! self::edd_rcp_is_paid_user() ) 
			return $price;
		
		$value 			= !empty( $level->edd_discount_value ) ? $level->edd_discount_value : '';
		$type 			= !empty( $level->edd_discount_type ) ? $level->edd_discount_type : '';
		if ( isset($value) && $value !='' ) {
			$value = intval( str_replace(',', '', $level->edd_discount_value ));
			if ( $value ==0 || $value == '' )
				return $price;
				
			if ( $type == 'flat' ) {
				$price = $price-$value;
			}
			else if ( $type == 'percentage' ){
				$price = $price*(100-$value)/100;	
			}
			
		}
		return $price > 0 ? $price : 0;
	}
	
	public function edd_rcp_select_paid_only_status( $download_id, $main_price ) {
		return get_post_meta( $download_id, '_edd_rcp_select_paid_only_status', true ) == 'main_price' ? $main_price : self::edd_rcp_price_after_discount($main_price);
	}
	
	public function edd_rcp_select_blank_status( $download_id, $main_price ) {
		return get_post_meta( $download_id, '_edd_rcp_select_blank_status', true ) == 'main_price' ? $main_price : self::edd_rcp_price_after_discount($main_price);
	}
	
	//edd functions
	public function edd_rcp_get_cart_total( $total ) {
		if ( $total <=0 || ! $total )
			$total = 0.00;
		return $total;
	}
	
	public function edd_rcp_get_download_price( $price, $download_id ) {
		
		if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) || ! is_user_logged_in() || self::edd_rcp_ignore_download($download_id))
			return $price;
			
		$download_is_paid_only = get_post_meta( $download_id, self::edd_rcp_paid_only_id() , true );
		
		if ( (bool) $download_is_paid_only && ! self::edd_rcp_is_paid_user() && get_post_meta( $download_id, self::edd_rcp_price_level_id() , true ) != '' )
			$rcp_price = self::edd_rcp_select_paid_only_status($download_id , $price);
		else
			$rcp_price = get_post_meta( $download_id, self::edd_rcp_price_level_id() , true );
				
		if ( isset($rcp_price) && $rcp_price  !=='' )
			return $price = $rcp_price;
		else
			return $price = self::edd_rcp_select_blank_status( $download_id ,$price );
	}
	
	public function edd_rcp_get_variable_prices( $prices, $download_id ) {

		if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) || ! is_user_logged_in() || self::edd_rcp_ignore_download( $download_id ) )
			return $prices;
		
		foreach ( (array) $prices as $key => $value ) {

			$prices[ $key ]['main_amount'] 		= $value['amount'];
			
			$download_is_paid_only = !empty($value[self::edd_rcp_paid_only_id()]) ? $value[self::edd_rcp_paid_only_id()] : false;
		
			if ( (bool) $download_is_paid_only && ! self::edd_rcp_is_paid_user() && isset($value[self::edd_rcp_price_level_id()]) && $value[self::edd_rcp_price_level_id()] != '' )
				$rcp_price = self::edd_rcp_select_paid_only_status($download_id , $value['amount'] );
			else 
				$rcp_price = isset($value[self::edd_rcp_price_level_id()]) && $value[self::edd_rcp_price_level_id()] != '' ? $value[self::edd_rcp_price_level_id()] : '';
				
			if ( isset($rcp_price) && $rcp_price !=='' ) {
				$prices[ $key ]['amount'] 			= $rcp_price;
			}
			else {
				$prices[ $key ]['amount'] 			= self::edd_rcp_select_blank_status( $download_id , $value['amount'] );
			}
			
		}
		
		return $prices;
	}


	public function edd_rcp_purchase_link_args( $args ) {

		if ( ! is_user_logged_in() || self::edd_rcp_dont_show_main_price( $args['download_id'] ) || self::edd_rcp_ignore_download( $args['download_id'] ) )
			return $args;
		
		$add_to_cart_text 	= edd_get_option( 'add_to_cart_text' );
		$default_args 		= apply_filters( 'edd_purchase_link_defaults', array(
			'text' => ! empty( $add_to_cart_text ) ? $add_to_cart_text : __( 'Purchase', 'edd' ),
		) );

		$download 			= new EDD_Download( $args['download_id'] );
		$variable_pricing	= $download->has_variable_prices();
		if ( $variable_pricing ) 
			return $args;
		
		if ( $args['price'] && $args['price'] !== 'no' ) {
			
			$main_price 	= get_post_meta( $args['download_id'], 'edd_price', true );
			
			$download_is_paid_only =  get_post_meta( $args['download_id'], self::edd_rcp_paid_only_id(), true );
		
			if ( (bool) $download_is_paid_only && ! self::edd_rcp_is_paid_user() && get_post_meta( $args['download_id'], self::edd_rcp_price_level_id(), true ) != '' ) 
				$rcp_price = self::edd_rcp_select_paid_only_status($args['download_id'] , $main_price );
			else
				$rcp_price 	= get_post_meta( $args['download_id'], self::edd_rcp_price_level_id(), true );
		
			if ( ! ( isset( $rcp_price ) && $rcp_price  !=='' )  )
				$rcp_price = self::edd_rcp_select_blank_status( $args['download_id'] ,$main_price );
		
		}


		if ( isset( $rcp_price ) && $rcp_price !=='' && $rcp_price != $main_price ) {
			
			$button_text = ! empty( $args['text'] ) ? '&nbsp;&ndash;&nbsp;' . $default_args['text'] : '';
			
			if ( 0 == intval($rcp_price) )
				$args['text'] = '<s>' . edd_currency_filter( edd_format_amount( $main_price ) ) . '</s>&nbsp;' . __( 'Free', 'edd' ) . $button_text;
			else
				$args['text'] = '<s>' . edd_currency_filter( edd_format_amount( $main_price ) ) . '</s>&nbsp;' . edd_currency_filter( edd_format_amount( $rcp_price ) ) . $button_text;
		
		}

		return $args;
	}
	
	//?
	public function edd_rcp_download_price_after_html( $formatted_price, $download_id, $price, $price_id ) {

		if ( ! is_user_logged_in() || self::edd_rcp_dont_show_main_price( $download_id ) || self::edd_rcp_ignore_download( $download_id ) )
			return $formatted_price;
		
		if ( edd_has_variable_prices( $download_id ) ) {

			$prices = edd_get_variable_prices( $download_id );

			if ( false !== $price_id && isset( $prices[ $price_id ] ) ) {
				
				$main_price 	= isset($prices[ $price_id ]['main_amount']) && $prices[ $price_id ]['main_amount'] !='' ? (float) $prices[ $price_id ]['main_amount'] : '';
				
				$download_is_paid_only = !empty($prices[ $price_id ][self::edd_rcp_paid_only_id()]) ? $prices[ $price_id ][self::edd_rcp_paid_only_id()] : false;
				
				if ( (bool) $download_is_paid_only && ! self::edd_rcp_is_paid_user() && isset($prices[ $price_id ][self::edd_rcp_price_level_id()]) && $prices[ $price_id ][self::edd_rcp_price_level_id()] !=''  )
					$rcp_price  = self::edd_rcp_select_paid_only_status( $download_id , $main_price );
				else
					$rcp_price 	= isset($prices[ $price_id ][self::edd_rcp_price_level_id()]) && $prices[ $price_id ][self::edd_rcp_price_level_id()] !='' ? (float) $prices[ $price_id ][self::edd_rcp_price_level_id()] : '';
				
				if ( ! ( isset( $rcp_price ) && $rcp_price  !=='' ) )
					$rcp_price = self::edd_rcp_select_blank_status( $download_id , $main_price );
				
			} else {

				foreach ( (array) $prices as $key => $price ) {

					if ( empty( $price['amount'] ) ) {
						continue;
					}

					if ( ! isset( $min ) ) {
						$min = $price['amount'];
					} else {
						$min = min( $min, $price['amount'] );
					}

					if ( $price['amount'] == $min ) {
						$min_id = $key;
					}

				}
				
				$main_price 	= isset( $prices[ $min_id ]['main_amount'] ) ? $prices[ $min_id ]['main_amount'] : $prices[ $min_id ]['amount'];
				
				$download_is_paid_only = !empty($prices[ $min_id ][self::edd_rcp_paid_only_id()]) ? $prices[ $min_id ][self::edd_rcp_paid_only_id()] : false;
				
				if ( (bool) $download_is_paid_only && ! self::edd_rcp_is_paid_user() &&  isset( $prices[ $min_id ][self::edd_rcp_price_level_id()] ) && $prices[ $min_id ][self::edd_rcp_price_level_id()] !='' )
					$rcp_price  = self::edd_rcp_select_paid_only_status( $download_id , $main_price );
				else
					$rcp_price 	= isset( $prices[ $min_id ][self::edd_rcp_price_level_id()] ) && $prices[ $min_id ][self::edd_rcp_price_level_id()] !='' ? $prices[ $min_id ][self::edd_rcp_price_level_id()] : '';
				
				if ( ! ( isset( $rcp_price ) && $rcp_price  !=='' ) )
					$rcp_price = self::edd_rcp_select_blank_status( $download_id , $main_price );

			}

		} else {

			$main_price 	= get_post_meta( $download_id, 'edd_price', true );
			
			$download_is_paid_only = get_post_meta( $download_id, self::edd_rcp_paid_only_id() , true );
			
			if ( (bool) $download_is_paid_only && ! self::edd_rcp_is_paid_user() && get_post_meta( $download_id, self::edd_rcp_price_level_id() , true ) !='' )
				$rcp_price  = self::edd_rcp_select_paid_only_status( $download_id , $main_price );
			else
				$rcp_price 	= get_post_meta( $download_id, self::edd_rcp_price_level_id() , true );
			
			if ( ! ( isset( $rcp_price ) && $rcp_price  !== '') )
				$rcp_price = self::edd_rcp_select_blank_status( $download_id , $main_price );
		
		}

		if ( isset( $rcp_price ) &&  $rcp_price !=='' && $rcp_price != $main_price ) {
			$formatted_price = '<del>' . edd_currency_filter( edd_format_amount( $main_price ) ) . '</del>&nbsp;' . edd_currency_filter( edd_format_amount( $rcp_price ) );
		}

		return $formatted_price;

	}

	
	public function edd_rcp_cart_item_price_label( $label, $item_id, $options ) {
		
		if ( ! is_user_logged_in() || self::edd_rcp_dont_show_main_price( $item_id ) || self::edd_rcp_ignore_download( $item_id ) )
			return $label;
		
		global $edd_options;	
		
		$download		= new EDD_Download( $item_id );
		$main_price 	= get_post_meta( $item_id, 'edd_price', true );
		$price 			= edd_get_cart_item_price( $item_id, $options );

		if ( $download->has_variable_prices() ) {
			
			$prices = $download->get_prices();
			
			$main_price 	= isset( $prices[ $options['price_id'] ]['main_amount'] ) ? $prices[ $options['price_id'] ]['main_amount'] : $main_price;
			
			$download_is_paid_only = !empty($prices[ $options['price_id'] ][self::edd_rcp_paid_only_id()]) ? $prices[ $options['price_id'] ][self::edd_rcp_paid_only_id()] : false;
		
			if ( (bool) $download_is_paid_only && ! self::edd_rcp_is_paid_user() && isset($prices[ $options['price_id'] ][self::edd_rcp_price_level_id()]) && $prices[ $options['price_id'] ][self::edd_rcp_price_level_id()] !=''  ) 
				$rcp_price  = self::edd_rcp_select_paid_only_status( $item_id , $main_price );
			else
				$rcp_price 	= isset($prices[ $options['price_id'] ][self::edd_rcp_price_level_id()]) && $prices[ $options['price_id'] ][self::edd_rcp_price_level_id()] !='' ? $prices[ $options['price_id'] ][self::edd_rcp_price_level_id()] : '';
			
			if ( ! ( isset( $rcp_price ) && $rcp_price  !=='' ) )
				$rcp_price = self::edd_rcp_select_blank_status( $item_id , $main_price );
			
		} else {
			
			$download_is_paid_only	= get_post_meta( $item_id, self::edd_rcp_paid_only_id(), true );
				
			if ( (bool) $download_is_paid_only && ! self::edd_rcp_is_paid_user() && get_post_meta( $item_id, self::edd_rcp_price_level_id(), true ) != '' ) 
				$rcp_price  = self::edd_rcp_select_paid_only_status( $item_id , $main_price );
			else
				$rcp_price	= get_post_meta( $item_id, self::edd_rcp_price_level_id(), true );
			
			if ( ! ( isset( $rcp_price ) && $rcp_price  !=='' ) )
				$rcp_price = self::edd_rcp_select_blank_status( $item_id , $main_price );
		
		}

		if ( ! ( isset( $rcp_price ) &&  $rcp_price  !== '' ) || $rcp_price == $main_price )
			return $label;

		$label 		= '';
		$price_id 	= isset( $options['price_id'] ) ? $options['price_id'] : false;

		if ( ! edd_is_free_download( $item_id, $price_id ) && ! edd_download_is_tax_exclusive( $item_id ) ) {

			if ( edd_prices_show_tax_on_checkout() && ! edd_prices_include_tax() ) {

				$main_price 	+= edd_get_cart_item_tax( $item_id, $options, $main_price );
				$price 			+= edd_get_cart_item_tax( $item_id, $options, $price );

			} if ( ! edd_prices_show_tax_on_checkout() && edd_prices_include_tax() ) {

				$main_price 	-= edd_get_cart_item_tax( $item_id, $options, $main_price );
				$price 			-= edd_get_cart_item_tax( $item_id, $options, $price );

			}

			if ( edd_display_tax_rate() ) {

				$label = '&nbsp;&ndash;&nbsp;';

				if ( edd_prices_show_tax_on_checkout() ) {
					$label .= sprintf( __( 'includes %s tax', 'edd' ), edd_get_formatted_tax_rate() );
				} else {
					$label .= sprintf( __( 'excludes %s tax', 'edd' ), edd_get_formatted_tax_rate() );
				}

				$label = apply_filters( 'edd_cart_item_tax_description', $label, $item_id, $options );

			}
		}

		$main_price 	= '<del>' . edd_currency_filter( edd_format_amount( $main_price ) ) . '</del>';
		$price 			= edd_currency_filter( edd_format_amount( $price ) );

		return $main_price . ' ' . $price . $label;

	}


	public function edd_rcp_purchase_link_top( $download_id = 0, $args = array() ) {
		
		global $edd_options;	
		
		$variable_pricing = edd_has_variable_prices( $download_id );
		$prices = apply_filters( 'edd_purchase_variable_prices', edd_get_variable_prices( $download_id ), $download_id );

		if ( ! $variable_pricing || ( false !== $args['price_id'] && isset( $prices[$args['price_id']] ) ) ) {
			return;
		}

		if ( edd_item_in_cart( $download_id ) && ! edd_single_price_option_mode( $download_id ) ) {
			return;
		}

		$type = edd_single_price_option_mode( $download_id ) ? 'checkbox' : 'radio';
		$mode = edd_single_price_option_mode( $download_id ) ? 'multi' : 'single';

		do_action( 'edd_before_price_options', $download_id ); ?>
		<div class="edd_price_options edd_<?php echo esc_attr( $mode ); ?>_mode">
			<ul>
				<?php
				if ( $prices ) {
					
					$checked_key = isset( $_GET['price_option'] ) ? absint( $_GET['price_option'] ) : edd_get_default_variable_price( $download_id );
					
					foreach ( (array) $prices as $key => $price ) { ?>
					
						<li id="edd_price_option_<?php echo $download_id . '_' . sanitize_key( $price['name'] ); ?>" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
						
							<label for="<?php echo esc_attr( 'edd_price_option_' . $download_id . '_' . $key ); ?>">
							
								<input type="<?php echo $type; ?>" <?php checked( apply_filters( 'edd_price_option_checked', $checked_key, $download_id, $key ), $key ); ?>
									name="edd_options[price_id][]" id="<?php echo esc_attr( 'edd_price_option_' . $download_id . '_' . $key ); ?>"
									class="<?php echo esc_attr( 'edd_price_option_' . $download_id ); ?>" value="<?php echo esc_attr( $key ); ?>"/>
									
									<span class='edd_price_option_wrap'>
									
										<span class="edd_price_option_name" itemprop="description"><?php echo esc_html( $price['name'] ); ?></span>
										
										<span class="edd_price_option_sep">&ndash;</span>&nbsp;<?php
										
										if ( is_user_logged_in() && isset( $price['main_amount'] ) && !self::edd_rcp_ignore_download( $download_id ) && !self::edd_rcp_dont_show_main_price( $download_id )  ) {
										
											$main_price = isset($price['main_amount']) && $price['main_amount'] !='' ? $price['main_amount'] : '';
										
											$download_is_paid_only	= !empty($price[self::edd_rcp_paid_only_id()]) ? $price[self::edd_rcp_paid_only_id()] : false;
				
											if ( (bool) $download_is_paid_only && ! self::edd_rcp_is_paid_user() && isset( $price[self::edd_rcp_price_level_id()] ) && $price[self::edd_rcp_price_level_id()] != '' )
												$rcp_price  = self::edd_rcp_select_paid_only_status( $download_id , $main_price );
											else
												$rcp_price	= isset( $price[self::edd_rcp_price_level_id()] ) && $price[self::edd_rcp_price_level_id()] != '' ? $price[self::edd_rcp_price_level_id()] : '';
			
											if ( ! ( isset( $rcp_price ) &&  $rcp_price  !== '' ) )
												$rcp_price = self::edd_rcp_select_blank_status( $download_id , $main_price );
											
											if ( isset( $rcp_price ) && $rcp_price  !=='' && isset( $main_price ) &&  $main_price  !=='' && $rcp_price != $main_price ) { ?>
										
												<span class="edd_price_option_price main_price" itemprop="price">
											
													<del>
														<?php echo edd_currency_filter( edd_format_amount( $main_price ) ); ?>
													</del>
												
												</span>&nbsp;
											
											<?php
											}
										}
										?>
										<span class="edd_price_option_price" itemprop="price"><?php echo edd_currency_filter( edd_format_amount( $price['amount'] ) ); ?></span>
									</span>
							</label><?php
							do_action( 'edd_after_price_option', $key, $price, $download_id );
						?></li><?php
					}
				}
				do_action( 'edd_after_price_options_list', $download_id, $prices, $type );
				?>
			</ul>
		</div>
		<?php
		do_action( 'edd_after_price_options', $download_id );
	}
	
	
}