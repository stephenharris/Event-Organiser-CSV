<?php 
class csvEventParserTest extends WP_UnitTestCase
{
	function setUp() {
		parent::setUp();
		require_once( EVENT_ORGANISER_CSV_DIR.'includes/class-eo-csv.php');
		require_once( EVENT_ORGANISER_CSV_DIR.'includes/class-eo-csv-parser.php');
		require_once( EVENT_ORGANISER_CSV_DIR.'includes/class-eo-event-csv-parser.php');
	}
		
    public function testShortcodeInContent()
    {
    	
    	$csv    = new EO_CSV();
    	$parser = new EO_Event_CSV_Parser();
    	
    	$csv->parse( EO_CSV_DIR_TESTDATA . '/shortcode-in-content.csv' );
    	
    	$parser->set_column_map( array(
			'post_title',
    		'start',
    		'end',
    		'post_content',
    		'event-venue',
    		'event-category',
    		'event-tag',    	
    		'schedule',
    		'schedule_meta',
    		'frequency',
    		'schedule_last',
    		'include',
    		'exclude',
    	) );
    	$parser->first_row_is_header = true;
    	$parser->map( $csv );
    	
    	$this->assertEquals( "shortcode [hello attr=\"value\"]world[/hello] This event is a single all day event with a shortcode", $parser->items[0]['post_content'] );		
    }

    public function testWeeklyEventContent()
    {
    	 
    	$csv    = new EO_CSV();
    	$parser = new EO_Event_CSV_Parser();
    	 
    	$csv->parse( EO_CSV_DIR_TESTDATA . '/weekly-event.csv' );
    	 
    	$parser->set_column_map( array(
    		'post_title',
    		'start',
    		'end',
    		'post_content',
    		'event-venue',
    		'event-category',
    		'event-tag',
    		'schedule',
    		'schedule_meta',
    		'frequency',
    		'schedule_last',
    		'include',
    		'exclude',
    	) );
    	$parser->first_row_is_header = true;
    	$parser->map( $csv );
    	 
    	$this->assertEquals( array( "SA" ), $parser->items[0]['schedule_meta'] );
    }
    
    
    public function testScheduleGuess(){
    	
    	//If we parse the schedule meta before the schedule we have 
    	//to make a best guess. The right choice should be obvious
    	//with a correctly formatted value.
    	
    	$parser = new EO_Event_CSV_Parser();
    	
    	$actual = $parser->parse_value_schedule_meta( 'BYDAY=2TH', array() );
    	$this->assertEquals( 'BYDAY=2TH', $actual );

    	$actual = $parser->parse_value_schedule_meta( 'BYMONTHDAY=31', array() );
    	$this->assertEquals( 'BYMONTHDAY=31', $actual );
    	
    	$actual = $parser->parse_value_schedule_meta( 'MO', array() );
    	$this->assertEquals( array( 'MO' ), $actual );
    	
    	$actual = $parser->parse_value_schedule_meta( 'MO,TU', array() );
    	$this->assertEquals( array( 'MO', 'TU' ), $actual );
    	
    }
    
}