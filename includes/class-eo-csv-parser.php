<?php 

# create new parseCSV object.
//$csv = new  EO_CSV_Parser( file_get_contents( '_books.csv' ) );
//$csv = new  EO_CSV_Parser( '_books.csv' );

class EO_CSV_Parser{
	
	// Parsed data
	var $data = array();		

	// Data mapped to items array
	var $items = array();
	
	var $first_row_is_header = false;

	//Constructor
	function __construct( $input = false, $delimiter = ',' ){
 		$this->delimiter = $delimiter;
		$this->input = $input;
	}
	
	function set_map( $map ){
		// removes all NULL, FALSE and Empty Strings but leaves 0 (zero) values
		$this->column_map = $map; //array_filter( $map, 'strlen' );
	}
	
	function read( $input = false, $first_row_is_header = false ){
		
		$this->input = ( $input ? $input : $this->input );
		
		//Local file
		if( is_file( $this->input ) && file_exists( $this->input )  ){
		
			$this->parse_file( $this->input );
			
		//Remote file
		}elseif( preg_match('!^(http|https|ftp)://!i', $this->input ) ){
			$content = wp_remote_retrieve_body( wp_remote_get( $this->input ) );
			if( $content )
				$this->parse_string( $content, $first_row_is_header );
		
		//String
		}else{
			$this->parse_string( $this->input, $first_row_is_header );
		}
		
	}

	function parse( $input = false ){
		
		$this->input = ( $input ? $input : $this->input );
		
		//Maps CSV header to event/post details
		$this->meta_map = isset( $this->column_map['meta'] ) ? $this->column_map['meta'] : array();
		unset( $this->column_map['meta'] );
		
		$this->flipped_column_map = array_flip( $this->column_map );
		$this->flipped_meta_map = array_flip( $this->meta_map );
		
		$this->read( $this->input );
		
		$this->map_columns();
		
	}

	function parse_string( $input ){

		$this->raw_data = $input;
		$lines = str_getcsv( $input, "\n" );
		
		$this->header = false;
		$data = array();
		
		foreach( $lines as $line ){
			if( !$this->header ){
				$this->header = str_getcsv( $line, $this->delimiter );
				$header_size = count( $this->header );
			}else{ 
				$line_data = str_getcsv( $line, $this->delimiter );
				if( array_filter( $line_data, 'trim' ) ) {
					$line_data = array_slice( $line_data, 0, $header_size ); 
					$data[] = array_combine( $this->header, $line_data );
				}
			 } 
		}
		$this->data = $data; 
	}


	function parse_file( $filename ){

		$this->header = false;
		$data = array();
		$this->raw_data = array();

		if ( ( $handle = fopen( $filename, 'r' ) ) !== FALSE ){
						
			while ( ( $row = fgetcsv ($handle, 1000, $this->delimiter) ) !== FALSE ){
		
				if( !$this->header ){
					
					$header_size = count( $row );
					
					if( $this->first_row_is_header ){
						//$this->header = $row;
						$this->header = range( 0, $header_size - 1 );
					}else{
						$this->header = range( 0, $header_size - 1 );
						$data[] = array_combine( $this->header, $row );
					}
				
				}else{
					
					if( array_filter( $row, 'trim' ) ) {
						$row = array_slice( $row, 0, $header_size );
						$data[] = array_combine( $this->header, $row );
					}
				}
        	}
		
			fclose( $handle );
    	}

		$this->data = $data;
	}
	
	/**
	 * Converts parsed data into object array
	 */
	function map_columns(){
		if( $this->data ){
			foreach( $this->data as $index => $row ){
	
				//Initialise item array
				$item = array();
	
				foreach( $row as $header => $value ){
						
					if( isset( $this->flipped_column_map[ $header ] ) ){
	
						//Maps the CSV header to the event key
						$key = $this->flipped_column_map[ $header ];
	
						//Allow sub classes to override parsing of values
						$method = 'parse_value_'. str_replace( '-', '_', $key );
						if( method_exists( $this, $method ) ){
							$item[ $key ] = $this->$method( $value, $item );
						}else{
							$item[ $key ] = $this->parse_value( $value, $key, $item );
						}
						
					}elseif( isset( $this->flipped_meta_map[ $header ] ) ) {
						
						//Maps the CSV header to the event key
						$key = $this->flipped_meta_map[ $header ];
						
						//Allow sub classes to override parsing of values
						$method = 'parse_meta_value_'. str_replace( '-', '_', $key );
						
						if( method_exists( $this, $method ) ){	
							$item[ 'meta' ][ $key ] = $this->$method( $value, $item );
						}else{
							$item[ 'meta' ][ $key ] = $this->parse_meta_value( $value, $key, $item );
						}
						
					}else{
						//Store as post data?
					}
				}
	
				//Add event array
				$this->items[] = $item;
			}
		}
	}
	
	/**
	 * Parses an event value. Maybe overridden by `parse_event_value_{key}`
	 * E.g. `parse_event_value_post_title()` to parse the post title.
	 */
	function parse_value( $value, $key, $item ){
		return $value;
	}

	function parse_meta_value( $value, $key, $item ){
		return $value;
	}
}
