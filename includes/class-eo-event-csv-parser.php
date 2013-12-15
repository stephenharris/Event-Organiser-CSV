<?php 

# create new parseCSV object.
//$csv = new  EO_CSV_Parser( file_get_contents( '_books.csv' ) );
//$csv = new  EO_CSV_Parser( '_books.csv' );

class EO_Event_CSV_Parser extends EO_CSV_Parser{
		
	/**
	 * Parses an event value. Maybe overridden by `parse_event_value_{key}`
	 * E.g. `parse_event_value_post_title()` to parse the post title.
	 */
	function parse_value( $value, $key, $item ){
		return $value;
	}
	
	function parse_value_event_category( $value, $item ){
		return explode( ',', $value );
	}
	
	function parse_value_start( $value, &$item ){
		
		if( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ){
			if( !isset( $item['all_day'] ) )
				$item['all_day'] = 1;
			return $this->parse_date( $value );
		}else{
			if( !isset( $item['all_day'] ) )
				$item['all_day'] = 0;
			
			return $this->parse_date_time( $value );
		}
		
	}
	
	function parse_value_end( $value, $item ){
		if( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ){
			return $this->parse_date( $value );
		}else{			
			return $this->parse_date_time( $value );
		}
	}
	
	function parse_value_schedule_last( $value, $item ){
		if( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ){
			return $this->parse_date( $value );
		}else{			
			return $this->parse_date_time( $value );
		}
	}
	
	function parse_date( $value ){
		
		if( !$value )
			return false;
		
		try{
			$value = new DateTime( $value, eo_get_blog_timezone() );
		}catch( Exception $e ){
			$value = false;
		}
	
		return $value;
	}
	
	function parse_date_time( $value ){
	
		//TODO handle timezone
		if( !$value )
			return false;
	
		try{
			$value = new DateTime( $value, eo_get_blog_timezone() );
		}catch( Exception $e ){
			$value = false;
		}
	
		return $value;
	}
	
	
	function parse_value_include( $value ){
		return $this->parse_date_list( $value );
	}
	
	function parse_value_exclude( $value ){
		return $this->parse_date_list( $value );
	}
	
	function parse_date_list( $value ){
		if( !$value )
			return array();
	
		$dates = explode( ',', $value );
		$dates = array_filter( array_map( array( $this, 'parse_date' ), $dates ) );
		$dates =  $dates ? $dates : array();
		return $dates;
	}
	
	function parse_value_schedule_meta( $value ){
		$_value = explode( ',', $value );
		if( count( $_value ) > 1 )
			return $_value;
		return $value;
	}
	
}
