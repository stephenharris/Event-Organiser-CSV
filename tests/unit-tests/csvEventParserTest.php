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
      
    
}