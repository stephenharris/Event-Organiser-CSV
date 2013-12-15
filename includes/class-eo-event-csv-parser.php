<?php 

class EO_Event_CSV_Parser extends EO_CSV_Parser{
		
	/**
	 * An event category column should contain a comma-delimited list of category slugs
	 * 
	 * @param string $value Comma-delimited list of category slugs
	 * @param array $item
	 * @return array Array of category slugs
	 */
	function parse_value_event_category( $value, $item ){
		return explode( ',', $value );
	}
	
	/**
	 * An start date column should be of the format:
	 * * Y-m-d 			( for all-day events)
	 * * Y-m-d H:i:s 	( for non-all-day events)
	 *
	 * @param string $value Formatted date/date-time
	 * @param array $item
	 * @return array Array of category slugs
	 */
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
	
	/**
	 * An end date column should be of the format Y-m-d or Y-m-d H:i:s
	 * 
	 * @param string $value Formatted date/date-time
	 * @param array $item
	 * @return array Array of category slugs
	 */
	function parse_value_end( $value, $item ){
		if( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ){
			return $this->parse_date( $value );
		}else{			
			return $this->parse_date_time( $value );
		}
	}
	
	/**
	 * An schedule last date column should be of the format Y-m-d or Y-m-d H:i:s
	 * 
	 * @param string $value Formatted date/date-time
	 * @param array $item
	 * @return array Array of category slugs
	 */
	function parse_value_schedule_last( $value, $item ){
		if( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ){
			return $this->parse_date( $value );
		}else{			
			return $this->parse_date_time( $value );
		}
	}
	
	/**
	 * Parses a date object (assumed to be Y-m-d format).
	 * 
	 * @param string $value
	 * @return boolean|DateTime False if date could not be interpreted or a DateTime object.
	 */
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
	
	/**
	 * Parses a date object (assumed to be Y-m-d H:i:s format).
	 *
	 * @param string $value
	 * @return boolean|DateTime False if date could not be interpreted or a DateTime object.
	 */
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
	
	/**
	 * Include column should be a comma-delimited list of 'Y-m-d'
	 * formatted dates.
	 * @param string $value
	 * @return array
	 */
	function parse_value_include( $value ){
		return $this->parse_date_list( $value );
	}
	
	/**
	 * Exclude column should be a comma-delimited list of 'Y-m-d'
	 * formatted dates.
	 * @param string $value
	 * @return array
	 */
	function parse_value_exclude( $value ){
		return $this->parse_date_list( $value );
	}
	
	/**
	 * Parse a list of dates given as comma-delimited list of 'Y-m-d'
	 * formatted dates.
	 * 
	 * @param string $value Comma-delimited list of dates
	 * @return array An array of DateTime objects
	 */
	function parse_date_list( $value ){
		if( !$value )
			return array();
	
		$dates = explode( ',', $value );
		$dates = array_filter( array_map( array( $this, 'parse_date' ), $dates ) );
		$dates =  $dates ? $dates : array();
		return $dates;
	}
	
	/**
	 * Schedule meta column should be of the form
	 * 
	 * **Weekly recurrence**
	 * Comma delimited list of days of of days given by their two-letter identifier. 
	 * E.g: "MO,TU,FR". 
	 * 
	 * **Montly recurrence**
	 * Either "BYDAY=" followed by  an integer (-1,1-4) and two-letter day identifier, 
	 * e.g. "BYDAY=2TH" for 2nd Thursday of every month. 
	 * 
	 * Or "BYMONTHDAY=" followed an integer (1 - 31) indicating the date on which 
	 * the even should repeat. E.g. "BYMONTHDATE=16" for every month on the 16th.
	 *
	 * @param string $value 
	 * @return array
	 */
	function parse_value_schedule_meta( $value ){
		$_value = explode( ',', $value );
		if( count( $_value ) > 1 )
			return $_value;
		return $value;
	}
	
}