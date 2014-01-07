<?php

class acf_field_oembed extends acf_field {

	// vars
	var $settings, // will hold info such as dir / path
		$defaults; // will hold default field options

	/*
	 * Set name / label needed for actions / filters
	 */
	function __construct() {
		// vars
		$this->name = 'oembed';
		$this->label = __('oEmbed');
		$this->category = __("Content",'acf'); // Basic, Content, Choice, etc
		$this->defaults = array(
			// add default here to merge into your field.
			// This makes life easy when creating the field options as you don't need to use any if( isset('') ) logic. eg:
			//'preview_size' => 'thumbnail'
		);

		// do not delete!
		parent::__construct();

		// settings
		$this->settings = array(
			'path' => apply_filters('acf/helpers/get_path', __FILE__),
			'dir' => apply_filters('acf/helpers/get_dir', __FILE__),
			'version' => '1.0.0'
		);

		add_action( 'wp_ajax_acf_field_oembed_fetch', array( $this, 'ajax_fetch' ) );

	}

	public function ajax_fetch() {

		$url    = esc_url_raw( wp_unslash( $_POST['url'] ) );
		$oembed = self::fetch_oembed( $url );

		if ( empty( $oembed ) ) {
			wp_send_json_error( array(
				'error_code'    => 'no_response',
				'error_message' => __( 'No response from media provider.', 'acf_oembed_field' )
			) );
		} else {
			wp_send_json_success( $oembed );
		}

	}

	/*
	 * Create the HTML interface for your field
	 *
	 * @param $field - an array holding all the field's data
	 */
	function create_field( $field ) {
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/

		// perhaps use $field['preview_size'] to alter the markup?

		if ( !empty( $field['value'] ) ) {
			$value = esc_url( $field['value']->raw_url );
			$title = esc_html( $field['value']->title );
		} else {
			$value = $title = '';
		}

		// create Field HTML
		echo '<input type="text" value="' . $value . '" id="' . esc_attr( $field['id'] ) . '" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" autocomplete="off">';
		echo '<p class="description" id="' . esc_attr( $field['id'] ) . '-title">' . $title . '</p>';
	}

	/*
	 * This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	 * Use this action to add css + javascript to assist your create_field() action.
	 */
	function input_admin_enqueue_scripts() {
		// Note: This function can be removed if not used

		// register acf scripts
		wp_register_script('acf-input-oembed', $this->settings['dir'] . 'js/input.js', array('acf-input','underscore'), $this->settings['version']);
		wp_register_style('acf-input-oembed', $this->settings['dir'] . 'css/input.css', array('acf-input'), $this->settings['version']);

		// scripts
		wp_enqueue_script(array(
			'acf-input-oembed',
		));
		// styles
		wp_enqueue_style(array(
			'acf-input-oembed',
		));

	}

	/*
	 * This action is called in the admin_head action on the edit screen where your field is created.
	 * Use this action to add css and javascript to assist your create_field() action.
	 */
	function input_admin_head() {
		// Note: This function can be removed if not used
	}

	/*
	 * This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	 * Use this action to add css + javascript to assist your create_field_options() action.
	 */
	function field_group_admin_enqueue_scripts() {
		// Note: This function can be removed if not used
	}

	/*
	 * This action is called in the admin_head action on the edit screen where your field is edited.
	 * Use this action to add css and javascript to assist your create_field_options() action.
	 */
	function field_group_admin_head() {
		// Note: This function can be removed if not used
	}

	/*
	 * This filter is appied to the $value after it is loaded from the db
	 *
	 * @param	$value - the value found in the database
	 * @param	$post_id - the $post_id from which the value was loaded from
	 * @param	$field - the field array holding all the field options
	 * @return	$value - the value to be saved in te database
	 */
	function load_value($value, $post_id, $field) {
		// Note: This function can be removed if not used
		return $value;
	}

	/*
	*  This filter is applied to the $value before it is updated in the db
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*  @return	$value - the modified value
	*/
	function update_value($value, $post_id, $field) {
		$value = trim( $value );
		if ( empty( $value ) ) {
			$value = array();
		} else {
			$url = esc_url_raw( $value );
			$oembed = self::fetch_oembed( $url );
			if ( empty( $oembed ) ) {
				$value = array();
			} else {
				$value = $oembed;
			}
		}
		return $value;
	}

	public static function fetch_oembed( $url ) {

		require_once ABSPATH . WPINC . '/class-oembed.php';

		if (  ! $provider = self::get_oembed_provider( $url ) )
			return false;

		$details = _wp_oembed_get_object()->fetch( $provider, $url, array(
			'width'  => 1000,
			'height' => 1000,
		) );

		if ( $details ) {
			$details->html = self::get_html( $details );
			$details->raw_url = $url;
			$details->src = self::get_src( $details );
		}

		return $details;

	}

	public static function get_src( $details ) {
		if ( isset( $details->url ) and !empty( $details->url ) )
			return $details->url;
		else if ( isset( $details->html ) and preg_match( '#src="(?P<url>[^"]+)"#', $details->html, $matches ) )
			return $matches['url'];
		return null;
	}

	public static function get_oembed_provider( $url ) {

		$provider = false;

		if ( ! trim( $url ) )
			return $provider;

		require_once ABSPATH . WPINC . '/class-oembed.php';

		$providers = _wp_oembed_get_object()->providers;

		# See http://core.trac.wordpress.org/ticket/24381

		foreach ( $providers as $matchmask => $data ) {
			list( $providerurl, $regex ) = $data;

			// Turn the asterisk-type provider URLs into regex
			if ( !$regex ) {
				$matchmask = '#' . str_replace( '___wildcard___', '(.+)', preg_quote( str_replace( '*', '___wildcard___', $matchmask ), '#' ) ) . '#i';
				$matchmask = preg_replace( '|^#http\\\://|', '#https?\://', $matchmask );
			}

			if ( preg_match( $matchmask, $url ) ) {
				$provider = str_replace( '{format}', 'json', $providerurl ); // JSON is easier to deal with than XML
				break;
			}
		}

		return $provider;

	}

	public function get_html( stdClass $details ) {

		if ( isset( $details->html ) and ! empty( $details->html ) )
			return $details->html;

		switch ( $details->type ) {

			case 'photo':
				if ( isset( $details->web_page ) and ! empty( $details->web_page ) ) {
					return sprintf( '<a href="%s"><img src="%s" alt="" /></a>',
						esc_url( $details->web_page ),
						esc_url( $details->url )
					);
				} else {
					return sprintf( '<img src="%s" alt="" />',
						esc_url( $details->url )
					);
				}
				break;

			case 'link':
				return sprintf( '<a href="%s">%s</a>',
					esc_url( $details->url ),
					esc_html( $details->title )
				);
				break;

		}

		return null;

	}

	/*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed to the create_field action
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*  @return	$value	- the modified value
	*/
	function format_value($value, $post_id, $field) {
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/

		// Note: This function can be removed if not used
		return $value;
	}

	/*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed back to the api functions such as the_field
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*  @return	$value	- the modified value
	*/
	function format_value_for_api($value, $post_id, $field) {
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/

		// Note: This function can be removed if not used
		return $value;
	}

	/*
	*  This filter is appied to the $field after it is loaded from the database
	*
	*  @param	$field - the field array holding all the field options
	*  @return	$field - the field array holding all the field options
	*/
	function load_field($field) {
		// Note: This function can be removed if not used
		return $field;
	}

	/*
	*  This filter is appied to the $field before it is saved to the database
	*
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the field group ID (post_type = acf)
	*  @return	$field - the modified field
	*/
	function update_field($field, $post_id) {
		// Note: This function can be removed if not used
		return $field;
	}

}

// create field
new acf_field_oembed();
