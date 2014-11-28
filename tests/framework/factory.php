<?php

class EO_CSV_UnitTest_Factory extends WP_UnitTest_Factory {

	/**
	 * @var EO_UnitTest_Factory_For_Event
	 */
	public $event;

	/**
	 * @var EO_UnitTest_Factory_For_Venue
	 */
	public $venue;

	public function __construct() {
		parent::__construct();

		$this->event = new EO_UnitTest_Factory_For_Event( $this );
		$this->venue = new EO_UnitTest_Factory_For_Venue( $this );
	}

}


class EO_UnitTest_Factory_For_Event extends WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'post_status' => 'publish',
			'post_title' => new WP_UnitTest_Generator_Sequence( 'Post title %s' ),
			'post_content' => new WP_UnitTest_Generator_Sequence( 'Post content %s' ),
			'post_excerpt' => new WP_UnitTest_Generator_Sequence( 'Post excerpt %s' ),
			'schedule' => 'once',
			'frequency' => '1',
		);
	}

	function create_object( $args ) {
		return eo_insert_event( $args );
	}

	function update_object( $post_id, $fields ) {
		$fields['ID'] = $post_id;
		return eo_update_event( $fields );
	}

	function get_object_by_id( $post_id ) {
		return get_post( $post_id );
	}
}


class EO_UnitTest_Factory_For_Venue extends WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );
		$this->default_generation_definitions = array(
				'name' => new WP_UnitTest_Generator_Sequence( 'Venue %s' ),
				'taxonomy' => 'event-venue',
				'description' => new WP_UnitTest_Generator_Sequence( 'Venue description %s' ),
		);
	}

	function create_object( $args ) {

		$term_id_pair = eo_insert_venue( $args['name'], $args );
		if ( is_wp_error( $term_id_pair ) )
			return $term_id_pair;
		return $term_id_pair['term_id'];
	}

	function update_object( $term, $fields ) {
		$term_id_pair = eo_update_venue( $term, $fields );
		return $term_id_pair['term_id'];
	}

	function add_post_terms( $event_id, $terms, $taxonomy, $append = true ) {
		return wp_set_post_terms( $event_id, $terms, 'event-venue', $append );
	}

	function get_object_by_id( $term_id ) {
		return get_term( $term_id, 'event-venue' );
	}
}
