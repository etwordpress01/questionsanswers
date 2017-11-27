<?php

if (!defined('FW')) {
    die('Forbidden');
}

class FW_Extension_QuestionsAnswers extends FW_Extension {

    /**
     * @internal
     */
    public function _init() {
        $this->register_post_type();
		add_filter( 'manage_edit-sp_answers_columns', array(&$this,'cpt_answer_columns') );
		add_action( 'manage_sp_answers_posts_custom_column', array(&$this,'cpt_answer_row_actions'), 10, 2 );
		
		add_filter( 'manage_edit-sp_questions_columns', array(&$this,'cpt_questions_columns') );
		add_action( 'manage_sp_questions_posts_custom_column', array(&$this,'cpt_questions_row_actions'), 10, 2 );
    }

	/**
	 * Answer CPT columns.
	 *
	 * @param  array $columns Columns.
	 */
	public function cpt_answer_columns( $columns ) {
		$columns = array(
			'cb'                => '<input type="checkbox" />',
			'answer_content'    => esc_html__( 'Content', 'listingo' ),
			'votes'    			=> esc_html__( 'Votes', 'listingo' ),
			'date'              => esc_html__( 'Date', 'listingo' ),
		);

		return $columns;
	}
	
	/**
	 * Questions CPT columns.
	 *
	 * @param  array $columns Columns.
	 */
	public function cpt_questions_columns( $columns ) {
		unset($columns['date']);
		$votes	= array(
				'votes'  => esc_html__( 'Votes', 'listingo' ),
				'date'   => esc_html__( 'Date', 'listingo' ),
		);
		$columns	= array_merge($columns,$votes);

		return $columns;
	}
	
	/**
	 * Questions CPT columns values.
	 *
	 * @param  array $columns Columns.
	 */
	public function cpt_questions_row_actions( $column, $post_id ) {
		if ( 'votes' == $column ) {
			$total_votes = get_post_meta($post_id, 'total_votes', true);
			echo intval($total_votes);
		}
	}
	
	/**
	 * Add action links below question/answer content in wp post list.
	 *
	 * @param  string  $column  Current column name.
	 * @param  integer $post_id Current post id.
	 */
	public function cpt_answer_row_actions( $column, $post_id ) {
		global $post, $mode;
		
		if ( 'votes' == $column ) {
			$total_votes = get_post_meta($post_id, 'total_votes', true);
			echo intval($total_votes);
			return;
		}
		
		if ( 'answer_content' == $column ) {
			$content = $this->cpt_truncate_chars( esc_html( get_the_excerpt() ), 70 );
			echo '<a href="' . esc_url( get_permalink( $post->post_parent ) ) . '" class="row-title">' . $content . '</a>'; // xss okay.
		}
		
		
		
		// First set up some variables.
		$actions          = array();
		$post_type_object = get_post_type_object( $post->post_type ); // override ok.
		$can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $post->ID );

		// Actions to delete/trash.
		if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
			if ( 'trash' === $post->post_status ) {
				$_wpnonce           = wp_create_nonce( 'untrash-post_' . $post_id );
				$url                = admin_url( 'post.php?post=' . $post_id . '&action=untrash&_wpnonce=' . $_wpnonce );
				$actions['untrash'] = "<a title='" . esc_attr( __( 'Restore this item from the Trash', 'listingo' ) ) . "' href='" . $url . "'>" . __( 'Restore', 'listingo' ) . '</a>';

			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this item to the Trash', 'listingo' ) ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash', 'listingo' ) . '</a>';
			}

			if ( 'trash' === $post->post_status || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete this item permanently', 'listingo' ) ) . "' href='" . get_delete_post_link( $post->ID, '', true ) . "'>" . __( 'Delete Permanently', 'listingo' ) . '</a>';
			}
		}

		if ( $can_edit_post ) {
			$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, '', true ) . '" title="' . esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'listingo' ),$post->title ) ) . '" rel="permalink">' . __( 'Edit', 'listingo' ) . '</a>';
		}

		// Echo the 'actions' HTML, let WP_List_Table do the hard work.
		$WP_List_Table = new WP_List_Table(); // @codingStandardsIgnoreLine
		echo ( $WP_List_Table->row_actions( $actions ) );

	}
	
	/**
	 * Trim strings.
	 *
	 * @param string $text String.
	 * @param int    $limit Limit string to.
	 * @param string $ellipsis Ellipsis.
	 * @return string
	 */
	public function cpt_truncate_chars( $text, $limit = 40, $ellipsis = '...' ) {
		$text = str_replace( array( "\r\n", "\r", "\n", "\t" ), ' ', $text );
		if ( strlen( $text ) > $limit ) {
			$endpos = strpos( $text, ' ', (string) $limit );
			if ( false !== $endpos ) {
				$text = trim( substr( $text, 0, $endpos ) ) . $ellipsis;
			}
		}
		return $text;
	}
	
    /**
     * @Render Question Add View
     * @return type
     */
    public function render_add_questions() {
        return $this->render_view('add_question');
    }
    
    /**
     * @Render Question Add View
     * @return type
     */
    public function render_questions_view() {
        return $this->render_view('view_questions');
    }
    
    /**
     * @Render Question Add View
     * @return type
     */
    public function render_questions_add() {
        return $this->render_view('add_question');
    }
    
    /**
     * @Render Question Add View
     * @return type
     */
    public function render_answers_view() {
        return $this->render_view('view_answers');
    }

    /**
     * @access Private
     * @Register Post Type
     */
    private function register_post_type() {

        register_post_type('sp_questions', array(
            'labels' => array(
                'name' => esc_html__('Consult Q&A', 'listingo'),
                'all_items' => esc_html__('Questions', 'listingo'),
                'singular_name' => esc_html__('Question', 'listingo'),
                'add_new' => esc_html__('New Question', 'listingo'),
                'add_new_item' => esc_html__('Add New Question', 'listingo'),
                'edit' => esc_html__('Edit', 'listingo'),
                'edit_item' => esc_html__('Edit Question', 'listingo'),
                'new_item' => esc_html__('New Question', 'listingo'),
                'view' => esc_html__('View Question', 'listingo'),
                'view_item' => esc_html__('View Question', 'listingo'),
                'search_items' => esc_html__('Search Question', 'listingo'),
                'not_found' => esc_html__('No Question found', 'listingo'),
                'not_found_in_trash' => esc_html__('No Question found in trash', 'listingo'),
                'parent' => esc_html__('Parent Question', 'listingo'),
            ),
			'capabilities' => array('create_posts' => false), //Hide add New Button
            'description' => esc_html__('This is where you can add new Questions.', 'listingo'),
            'public' => true,
            'supports' => array('title', 'editor'),
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => true,
            'hierarchical' => true,
            'menu_position' => 10,
            'rewrite' => array('slug' => 'question', 'with_front' => true),
            'query_var' => true,
            'has_archive' => 'false'
        ));
        register_post_type('sp_answers', array(
            'labels' => array(
                'name' => esc_html__('Answers', 'listingo'),
                'all_items' => esc_html__('Answers', 'listingo'),
                'singular_name' => esc_html__('New Answer', 'listingo'),
                'add_new' => esc_html__('Add Answer', 'listingo'),
                'add_new_item' => esc_html__('Add New Answer', 'listingo'),
                'edit' => esc_html__('Edit', 'listingo'),
                'edit_item' => esc_html__('Edit Answer', 'listingo'),
                'new_item' => esc_html__('New Answer', 'listingo'),
                'view' => esc_html__('View Answer', 'listingo'),
                'view_item' => esc_html__('View Answer', 'listingo'),
                'search_items' => esc_html__('Search Answer', 'listingo'),
                'not_found' => esc_html__('No Answer found', 'listingo'),
                'not_found_in_trash' => esc_html__('No Answer found in trash', 'listingo'),
                'parent' => esc_html__('Parent Answer', 'listingo'),
            ),
			'capabilities' => array('create_posts' => false), //Hide add New Button
            'description' => esc_html__('This is where you can add new Answers.', 'listingo'),
            'public' => true,
            'supports' => array('editor'),
			'show_in_menu' => 'edit.php?post_type=sp_questions',
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'publicly_queryable' => false,
            'exclude_from_search' => false,
            'hierarchical' => false,
            'menu_position' => 10,
            'rewrite' => array('slug' => 'answer', 'with_front' => true),
            'query_var' => false,
            'has_archive' => 'false'
        ));
    }

}
