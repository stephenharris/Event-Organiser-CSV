<?php

//TODO UID?
//TODO Handle double submissions?
//TODO Venue meta?
//TODO Filters?

class EO_CSV_Import_Admin_Page{
	
	/**
	 * Singleton model
	 * @ignore
	 * @var array
	 */
	private static $instances = array();
	
	private $import_data = false;
	
	private $errors;
	
	/**
	 * Constructor.
	 *
	 * Checks that an instance hasn't already been created and checks the gateway identifier.
	 * Adds callbacks to the appropriate hooks.
	 */
	final function __construct() {
		
		//Eurgh, php5.2 support..., bring on late static binding
		$class = get_class( $this );
			
		//Singletons!
		if ( array_key_exists( $class, self::$instances ) )
			trigger_error( "Tried to construct a second instance of class \"$class\"", E_USER_WARNING );
		
		//Add page to menu
		add_action( 'admin_menu', array( $this, 'register_page' ) );
		
		//Enqueue scripts on this page
		add_action( 'load-tools_page_eo-csv-import', array( $this, 'maybe_export_csv' ) );
		
		add_action( 'load-tools_page_eo-csv-import', array( $this, 'enqueue_scripts' ) );
	}
	
	/**
	 * Get the instance (or create one) of the child class.
	 * @return multitype:
	 */
	public static function getInstance() {
		$class = get_called_class();
		if ( array_key_exists( $class, self::$instances ) === false)
			self::$instances[$class] = new $class();
		return self::$instances[$class];
	}

	/**
	 * Register admin page
	 */
	public function register_page(){
		
		add_management_page(
			__( 'Import Events', 'event-organiser-csv' ),
			__( 'Import Events', 'event-organiser-csv' ),
			'manage_options',
			'eo-csv-import',
			array( $this, 'render' )
		);
	}
	
	/**
	 * Export action listener
	 */
	function maybe_export_csv(){
		if( !empty( $_POST['action'] ) && 'eo-export-csv' == $_POST['action'] ){
			check_admin_referer( 'eo-export-csv' );
			$this->export_csv();
		}
	}
	
	/**
	 * Enqueue scripts
	 */
	function enqueue_scripts(){
		wp_enqueue_script( 'eo_csv_admin' );
		wp_enqueue_style( 'eo_csv_admin' );
	}
	
	/**
	 * Export CSV
	 */
	function export_csv(){
		require_once( EVENT_ORGANISER_CSV_DIR.'includes/class-eo-csv-events-export.php');
		$csv = new EO_Export_Events_CSV();
	}

	/**
	 * Render the admin page
	 */
	function render() {
		$this->header();

		$step = empty( $_GET['step'] ) ? 0 : (int) $_GET['step'];
			
		switch ( $step ) {

			case 0: //Ask to upload file
				$this->greet();
				break;
			
			case 1://Display a bit of uploaded file, ask for column associations
			
				check_admin_referer( 'import-upload' );
				
				if( $file = $this->handle_upload() )
					$this->import_options();
				 
				break;
				
			case 2: //Import and provide feedback
				
				check_admin_referer( 'eo-import-csv' );//Todo
				
				$this->id = $_POST['import_id'];
				$file = get_attached_file( $this->id );
				
				$headers = $_POST['column_map'];
				$delimiter = $_POST['delimiter'];
				$first_row_is_header = !empty( $_POST['first_row_is_header'] );
				$import_venues = (bool) !empty( $_POST['import_venues'] );
				$import_categories = (bool) !empty( $_POST['import_categories'] );
						
				$this->import( $file, compact( 'headers', 'delimiter', 'first_row_is_header', 'import_venues', 'import_categories' ) );
				
				$this->display_feedback();
				break;
		}

		$this->footer();
	}
	

	/**
	 * Display introductory text and file upload form
	 */
	function greet() {
		
		echo '<div class="narrow">';
		
		echo '<p>'.__( 'Import events from a CSV file', 'event-organiser-csv' ).'</p>';
		echo '<p>'.__( 'Choose a .csv file to upload, then click Upload file and import.', 'event-organiser-csv' ).'</p>';
		
		wp_import_upload_form( 'tools.php?page=eo-csv-import&amp;step=1' );
		
		echo '</div>';
		
		echo '<h2> Export Events </h2>';
		
		echo '<form action="' . admin_url( 'tools.php?page=eo-csv-import' ). '" method="post">';
			echo '<input type="hidden" name="action" value="eo-export-csv" />';
			wp_nonce_field( 'eo-export-csv' );
			submit_button( __( 'Export Events int CSV', 'event-organiser-csv' ), 'button' );
		echo '</form>';
	}

	// Display import page title
	function header() {
		echo '<div class="wrap">';
		screen_icon();
		echo '<h2>' . __( 'Import Events', 'event-organiser-csv' ) . '</h2>';
	}
	
	// Close div.wrap
	function footer() {
		echo '</div>';//.wrap
	}

	/**
	 * Import events
	 */
	function import( $file, $args = array() ) {
		
		$map = array();
		
		foreach( $args['headers'] as $i => $header ){
			if( $header['col'] == 'post_meta' ){
				$map['meta'][$header['other']] = $i;
			}elseif( !empty( $header['col'] ) ){
				$map[$header['col']] = $i;
			}
		}
		
		$delim_map = array( 'comma' => ',', 'space' => " ", 'tab' => "\t" );
		
		$csv = new EO_Event_CSV_Parser();
		$csv->first_row_is_header = empty( $args['first_row_is_header'] ) ? false : true;
		$csv->delimiter = isset( $delim_map[$args['delimiter']] ) ? $delim_map[$args['delimiter']] : ","; 
		$csv->set_map( $map );
		
		$csv->parse( $file );
		
		$this->errors = new WP_Error();
		$this->events = array();
		$this->events_imported = 0;
		
		foreach( $csv->items as $event ){
			
			$event_meta = isset( $event['meta'] ) ? $event['meta'] : false;
			
			$found_event = false;//TODO enable UID look-up

			if( $found_event ){
				//$event_id = eo_update_event( $event );
			}else{
				$event['post_status'] = 'publish';
				$event_id = eo_insert_event( $event );
			}
			
			$this->events[] = $event_id;
						
			if( !$event_id || is_wp_error( $event_id ) ){
				continue;
			}
			
			$this->events_imported++;
			
			//Import venue
			if( !empty( $event['event-venue'] ) ){
			
				$found_venue = eo_get_venue_by( 'name', $event['event-venue'] );
			
				if( $found_venue ){
					$venue_id = (int) $found_venue->term_id;
						
				}elseif( !empty( $args['import_venues'] ) ){
					$new_venue = eo_insert_venue( $event['event-venue'], $args );
			
					if( !is_wp_error( $new_venue ) && !$new_venue ){
						$venue_id = (int) $new_venue['term_id'];
					}
					
				}else{
					$venue_id = false;
				}
			
				if( $venue_id ){
					wp_set_object_terms( $event_id, array( $venue_id ), 'event-venue' );
				}
			}
			
			
			//Import categories
			if( !empty( $event['event-category'] ) ){
				
				$cats = array();
				
				foreach( $event['event-category'] as $category_name ){
			
					$found_cat = get_term_by( 'name', $category_name, 'event-category' );
			
					if( $found_cat ){
						$cats[] = (int) $found_cat->term_id;
						
					}elseif( !empty( $args['import_categories'] ) ){
						$new_cat = wp_insert_term( $category_name, 'event-category', array() );
			
						if( !is_wp_error( $new_cat ) && !$new_cat ){
							$cats[] = (int) $new_cat['term_id'];
						}
						
					}
				}
				
				wp_set_object_terms( $event_id, $cats, 'event-category' );
			}
			
			if( !empty( $event['meta'] ) ){
				foreach( $event['meta'] as $meta_key => $value ){
					if( !isset( $value ) || '' === $value ){
						continue;
					}
					
					update_post_meta( $event_id, $meta_key, $value );
				}
			}
			
		}
		
	}


	/**
	 * Handle the upload and store the imported data
	 *
	 * @return bool False if error uploading or invalid file, true otherwise
	 */
	function handle_upload() {
		
		$file = wp_import_handle_upload();

		if ( isset( $file['error'] ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'event-organiser-csv' ) . '</strong><br />';
			echo esc_html( $file['error'] ) . '</p>';
			return false;
			
		} else if ( ! file_exists( $file['file'] ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'event-organiser-csv' ) . '</strong><br />';
			printf( __( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'event-organiser-csv' ), esc_html( $file['file'] ) );
			echo '</p>';
			return false;
		}

		$this->id = (int) $file['id'];
		
	
		if ( !file_exists( $file['file'] ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'event-organiser-csv' ) . '</strong><br />';
			return false;
		}
		
		$this->import_data = file_get_contents( $file['file'] );
		return true;
	}


	/**
	 * Display pre-import options (labelling columns etc).
	 */
	function import_options() {

		$input = html_entity_decode( (string) $this->import_data, ENT_QUOTES, 'UTF-8');
		?>
		<script>
		<?php echo "var eo_csv_data = " . json_encode( array( 'input' => $input ) ) . ';'; ?>
		</script>

		<form action="<?php echo admin_url( 'tools.php?page=eo-csv-import&amp;step=2' ); ?>" method="post">
		
			<input type="hidden" name="import_id" value="<?php echo $this->id; ?>" />
			<?php wp_nonce_field( 'eo-import-csv' ); ?>
			
			<p>
				<label><input type="radio" name="delimiter" value="comma" /><?php esc_html_e( 'Comma', 'event-organiser-csv' ); ?></label>
				<label><input type="radio" name="delimiter" value="tab" /><?php esc_html_e( 'Tab', 'event-organiser-csv' ); ?></label>
				<label><input type="radio" name="delimiter" value="space" /><?php esc_html_e( 'Space', 'event-organiser-csv' ); ?></label>
			</p>			
			
			<p>
				<label>
					<input type="checkbox" name="first_row_is_header" value="1" class="eo-first-row-is-header" />
					<?php esc_html_e( 'First row is header', 'event-organiser-csv' ); ?>
				</label>
			</p>
			
			<div class="error hide-if-js below-h2" id="eo-csv-error">
				<p><?php esc_html_e( 'You must have javascript enabled for the importer to work.', 'event-organiser-csv' ); ?></p>
			</div>
		
			
			<div id="example" class="handsontable eo-csv-table-wrap">
    			<table class="htCore">
        			<thead></thead>
        			<tbody></tbody>
		        	<tfoot></tfoot>
    			</table>
			</div>
			
			<?php submit_button( __( 'Import', 'event-organiser-csv' ), 'button', 'submit', true, array( 'id' => 'eo-csv-submit')  ); ?>
			
		</form>
		<?php 
	}

	/**
	 * Display feedback on imported events
	 */
	function display_feedback(){
				
		if( $this->events ){
			foreach( $this->events as $row => $event ){

				if( is_wp_error( $event ) ){
					$this->display_error( $event->get_error_message() );

				}elseif( $event ){
					$this->display_notice( sprintf(
						'[Row %d] Successfully imported <strong>%s</strong>. <a href="%s">Edit</a> | <a href="%s">View</a>.',
						$row+1,
						get_the_title( $event ),
						get_edit_post_link( $event ),
						get_permalink( $event )
					) );
				}
			}

		}else{
			$this->display_error( 'No events found' );
		}
		
		printf( '<p> %d events successfuly imported </p>', $this->events_imported );
	}
	
	/**
	 * Display an error message
	 * @param string $message
	 */
	function display_error( $message ){
		printf( '<div class="error"><p>%s</p></div>', $message );
	}

	/**
	 * Display a notice message
	 * @param string $message
	 */
	function display_notice( $message ){
		printf( '<div class="updated"><p>%s</p></div>', $message );
	}

}