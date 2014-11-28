/*! Event Organiser CSV - v0.1.0
 * http://wp-event-organiser.com/
 * Copyright (c) 2013; * Licensed GPLv2+ */
/*global $:false, jQuery:false, eo_csv:false, eo_csv_data:false, document:false, alert:false, console:false*/
(function ($) {
	$(document).ready(function(){
		
		var eventorganisercsv = {};
		
		eventorganisercsv.errorMessage = function( message ){
			$('#eo-csv-error p').text( message );
			$('#eo-csv-error').show();
			$('#eo-csv-submit').attr('disabled','disabled').hide();
		};
		
		eventorganisercsv.clearError = function(){
			$('#eo-csv-error p').text( '' );
			$('#eo-csv-error').hide();
			$('#eo-csv-submit').removeAttr('disabled' ).show();
		};
		
		//Toggle visibility of first row
		$('.eo-first-row-is-header').on( 'change', function(e){
			//$('.eo-csv-row-0').toggle(  !$(this).is(":checked") );
			$('.eo-csv-row-0').toggleClass( 'eo-csv-row-is-header',  $(this).is(":checked") );
		});

		//Toggle visibility of additional input for column map
		$('.eo-csv-table-wrap').on( 'change', '.eo-csv-col-map', function(e){
			var $input = $(this).parent('td').find('.eo-csv-col-map-meta');
			$input.toggle( $(this).val() === 'post_meta' );
			$input.attr( "placeholder", $(this).find('option:selected').text() );
		});

		//Listen for delimiter change
		$('input[name="delimiter"]').change(function(e){
			
			eventorganisercsv.clearError();
			var delimiter;
			switch( $(this).val() ){
				case 'space':
					delimiter = " ";
				break;
				
				case 'tab':
					delimiter = "\t";
					break;
				
				case 'semicolon':
					delimiter = ";";
					break;
					
				default:
					delimiter = ",";
					break;
			}
			
			if( !eo_csv_data.hasOwnProperty( 'input' ) || !eo_csv_data.input ){
				eventorganisercsv.errorMessage( "Cannot read file content. Please check that the uploaded file is CSV encoded." );
				return;
			}
			
			var rows;
			try{
				rows = $.csv.toArrays(eo_csv_data.input, {
					delimiter:"\"", // sets a custom value delimiter character
					separator:delimiter // sets a custom field separator character
				});
			}catch( exception ){
				eventorganisercsv.errorMessage( "The CSV file is invalid with the chosen delimiter. Please try a different one.");
				console.log( exception );
				$('.eo-csv-table-wrap table thead').html('');
				$('.eo-csv-table-wrap table tfoot').html('');
				$('.eo-csv-table-wrap table tbody').html('');
				return;
			}
			
			var header_size = rows[0].length;
			
			
			//Generate table header
			var thead = '<tr>';
			for( var c = 0; c < header_size; c++ ){
				
				var col_header = "";
				var index = c;
				
				if( index === 0 ){
					col_header= "A";
				}else{
					while( index >= 0 ){
						var digit = index % 26;
						index = Math.floor( index/26 ) -1;
						col_header = String.fromCharCode( 65 + digit ) + col_header;
					}
				}
				
				thead += '<th>' + col_header + '</th>';
			}
			thead += '</tr>';
			
			
			//Generate table body
			var tbody = '';
			for( var r = 0; r < rows.length; r++ ){
				tbody += '<tr class="eo-csv-row-'+r+'">';
				
				for( c = 0; c < header_size; c++ ){
					var value = rows[r][c];
					tbody += '<td><div class="eo-csv-cell-content">'+value+'</div></td>';
				}
				
				tbody += '</tr>';
			}
			
			
			//Generate table footer
			var tfoot = '<tr class="eo-csv-import-column-selection">';
			for( c = 0; c < header_size; c++ ){
				tfoot += '<td>' + 
						'<select class="eo-csv-col-map" name="column_map['+c+'][col]" style="width: 100%;" data-eo-csv-col="1">' +
							'<option value="0"> Please select </option>';
						
				   			for ( var key in eo_csv.columns ) {
				   				if( eo_csv.columns.hasOwnProperty( key ) ){
				   					tfoot += '<option value="' + key + '">' + eo_csv.columns[key] + '</option>';
				   				}
				   			}
				   	
				   		tfoot += '</select>' +
				   			'<input type="text" name="column_map['+c+'][other]" style="display:none" value="" class="eo-csv-col-map-meta">' + 
				   			'</td>';
			}
			tfoot += '</tr>';
			
			//Insert table
			var $table = $('.eo-csv-table-wrap table');
			
			$table.find('thead').html( tfoot +thead );
			$table.find('tbody').html( tbody );
			//$table.find('tfoot').html( tfoot );
			
		});//.eq(0).click();
		
		
		if( !eo_csv_data.hasOwnProperty( 'input' ) || !eo_csv_data.input ){
			eventorganisercsv.errorMessage( "Cannot read file content. Please check that the uploaded file is CSV encoded." );
		
		}else{
		
			//Try all delimiters and pick the first one without an error
			var delimiters = [ " ", "\t", ",", ";" ];
			for( var i = 0; i < delimiters.length; i++ ){
				
				var delimiter = delimiters[i];
				
				try{
					$.csv.toArrays( eo_csv_data.input, {
						delimiter:"\"", // sets a custom value delimiter character
						separator:delimiter // sets a custom field separator character
					});
					$('input[name="delimiter"]').eq(0).click();
					break;
					
				}catch( exception ){
					continue;
				}
				
			}
		}	
		
	});
})(jQuery);