<?php

class csvTest extends WP_UnitTestCase
{
	function setUp() {
		parent::setUp();
		require_once( EVENT_ORGANISER_CSV_DIR.'includes/class-eo-csv.php');
	}
		
    public function testIncorrectNumberOfColumns()
    {
    	$csv = new EO_CSV();
    	
    	$input = "A,B,C,D,E \n A,B \n A,B,C,D,E,F,G";
    	
    	$csv->parse( $input );
    	
    	$this->assertEquals( 7, $csv->column_num );
		$this->assertNull( $csv->get_cell( 1, 6 ) );    	
    }
    
    public function testIncorrectNumberOfColumnsFileParse()
    {
    	$csv = new EO_CSV();
    	
    	$csv->parse( EO_CSV_DIR_TESTDATA . '/incorrect-columns.csv' );
    	
    	$this->assertEquals( 7, $csv->column_num );
		$this->assertNull( $csv->get_cell( 1, 6 ) );		
    }
      
    
	public function testParse()
    {
    	$csv = new EO_CSV();
    	
    	$input = "A,B,C,D,E\n A,B\nA,B,C,D,E,F,G";
    	
    	$csv->parse( $input );
    	
    	$this->assertEquals( "A", $csv->get_cell( 0, 0 ) );
		$this->assertEquals( "B", $csv->get_cell( 1, 1 ) );
		$this->assertEquals( "D", $csv->get_cell( 2, 3 ) );    	
    }
    
    public function testParseFileParse()
    {
    	$csv = new EO_CSV();
    	
    	$csv->parse( EO_CSV_DIR_TESTDATA . '/incorrect-columns.csv' );
    	    	
    	$this->assertEquals( "A", $csv->get_cell( 0, 0 ) );
		$this->assertEquals( "B", $csv->get_cell( 1, 1 ) );
		$this->assertEquals( "D", $csv->get_cell( 2, 3 ) );	
    }
      
	public function testSemiColonDelimitator()
    {
    	$csv = new EO_CSV();
    	
    	$input = "A;B;C;D;E\n A;B\nA;B;C;D;E;F;G";
    	
    	$csv->delimiter = ";";
    	$csv->parse( $input );
    	
    	$this->assertEquals( "A", $csv->get_cell( 0, 0 ) );
		$this->assertEquals( "B", $csv->get_cell( 1, 1 ) );
		$this->assertEquals( "D", $csv->get_cell( 2, 3 ) );    	
    }
    
}
