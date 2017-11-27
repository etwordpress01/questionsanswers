<?php

if (!defined('FW')) {
    die('Forbidden');
}

/**
 * @hook render questions listing view
 * @type echo
 */
if (!function_exists('_filter_fw_ext_get_render_questions_view')) {

    function _filter_fw_ext_get_render_questions_view() {
        echo fw_ext_get_render_questions_view();
    }

    add_action('render_questions_listing_view', '_filter_fw_ext_get_render_questions_view', 10);
}


/**
 * @hook render add questions view
 * @type echo
 */
if (!function_exists('_filter_fw_ext_get_render_add_questions_view')) {

    function _filter_fw_ext_get_render_add_questions_view() {
        echo fw_ext_get_render_add_questions_view();
    }

    add_action('render_add_questions_view', '_filter_fw_ext_get_render_add_questions_view', 10);
}

/**
 * @hook render answers view
 * @type echo
 */
if (!function_exists('_filter_fw_ext_get_render_answers_view')) {

    function _filter_fw_ext_get_render_answers_view() {
        echo fw_ext_get_render_answers_view();
    }

    add_action('render_answers_view', '_filter_fw_ext_get_render_answers_view', 10, 1);
}

/**
 * @hook save questions
 */
if (!function_exists('fw_ext_listingo_process_questions')) {

    function fw_ext_listingo_process_questions() {

        global $current_user, $wp_roles, $userdata;
        $provider_category = listingo_get_provider_category($current_user->ID);
        $json = array();
		
		remove_all_filters("content_save_pre");
		
		if( function_exists('listingo_is_demo_site') ) { 
			listingo_is_demo_site() ;
		}; //if demo site then prevent
		
        $do_check = check_ajax_referer('listingo_question_answers_nounce', 'listingo_question_answers_nounce', false);
        if ($do_check == false) {
            $json['type'] = 'error';
            $json['message'] = esc_html__('No kiddies please!', 'listingo');
            echo json_encode($json);
            die;
        }

        if (empty($_POST['question_title'])) {
            $json['type'] = 'error';
            $json['message'] = esc_html__('Question title field should not be empty.', 'listingo');
            echo json_encode($json);
            die;
        }

        $question_title = !empty($_POST['question_title']) ? esc_attr($_POST['question_title']) : esc_html__('unnamed', 'listingo');
        $question_detail = force_balance_tags($_POST['question_description']);

        $author_id = !empty($_POST['author_id']) ? intval($_POST['author_id']) : '';
        $questions_answers_post = array(
            'post_title' 	=> $question_title,
            'post_status' 	=> 'publish',
            'post_content'  => $question_detail,
            'post_author'   => $current_user->ID,
            'post_type' 	=> 'sp_questions',
            'post_date' 	=> current_time('Y-m-d H:i:s')
        );

        $post_id = wp_insert_post($questions_answers_post);
		
		$category 	 = get_user_meta($author_id, 'category', true);
        update_post_meta($post_id, 'question_to', $author_id);
        update_post_meta($post_id, 'question_by', $current_user->ID);
		update_post_meta($post_id, 'question_cat', $category);
		
        $json['type'] = 'success';
        $json['message'] = esc_html__('Question submit successfully.', 'listingo');
        echo json_encode($json);
        die;
    }

    add_action('wp_ajax_fw_ext_listingo_process_questions', 'fw_ext_listingo_process_questions');
    add_action('wp_ajax_nopriv_fw_ext_listingo_process_questions', 'fw_ext_listingo_process_questions');
}


/**
 * @hook Save Answers
 */
if (!function_exists('fw_ext_listingo_process_answers')) {

    function fw_ext_listingo_process_answers() {
        global $current_user, $wp_roles, $userdata;
        $json = array();
		
		remove_all_filters("content_save_pre");
		
		if( function_exists('listingo_is_demo_site') ) { 
			listingo_is_demo_site() ;
		}; //if demo site then prevent
		
		$offset = get_option('gmt_offset') * intval(60) * intval(60);
		
        $do_check = check_ajax_referer('listingo_answers_nounce', 'listingo_answers_nounce', false);
        if ($do_check == false) {
            $json['type'] = 'error';
            $json['message'] = esc_html__('No kiddies please!', 'listingo');
            echo json_encode($json);
            die;
        }

        if (empty($_POST['answer_description'])) {
            $json['type'] = 'error';
            $json['message'] = esc_html__('Answers description area should not be empty.', 'listingo');
            echo json_encode($json);
            die;
        }

        $answer_detail = force_balance_tags($_POST['answer_description']);

        $question_id = !empty($_POST['question_id']) ? intval($_POST['question_id']) : '';
        $questions_answers_post = array(
            'post_title' 	=> '',
            'post_status' 	=> 'publish',
            'post_content' 	=> $answer_detail,
            'post_author' 	=> $current_user->ID,
            'post_type' 	=> 'sp_answers',
			'post_parent'	=> $question_id,
            'post_date' 	=> current_time('Y-m-d H:i:s')
        );

        $post_id = wp_insert_post($questions_answers_post);

        update_post_meta($post_id, 'answer_question_id', $question_id);
        update_post_meta($post_id, 'answer_user_id', $current_user->ID);
        $json['type'] = 'success';
        $json['message'] = esc_html__('Answer submitted successfully.', 'listingo');
        echo json_encode($json);
        die;
    }

    add_action('wp_ajax_fw_ext_listingo_process_answers', 'fw_ext_listingo_process_answers');
    add_action('wp_ajax_nopriv_fw_ext_listingo_process_answers', 'fw_ext_listingo_process_answers');
}

/**
 * @hook update votes
 */
if (!function_exists('fw_ext_listingo_update_votes')) {

    function fw_ext_listingo_update_votes() {
        global $current_user, $wp_roles, $userdata;
        $json = array();
		$key	= !empty( $_POST['key'] ) ? esc_attr( $_POST['key'] ) : '';
		$id		= !empty( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		
		if(empty( $id ) || empty( $current_user->ID )){ return;}
		
		$db_key	= 'total_votes';
		$count  = get_post_meta($id, $db_key, true);

		if ( empty( $count ) ) {
			if( $key === 'up' ){
				$count = 1;
				do_action('fw_add_user_to_votes',$id);
			} else{
				$count = -1;
				do_action('fw_remove_user_from_votes',$id);
			}
			
			update_post_meta($id, $db_key, $count);
		} else {
			if( $key === 'up' ){
				$count++;
				do_action('fw_add_user_to_votes',$id);
			} else{
				$count--;
				do_action('fw_remove_user_from_votes',$id);
			}
			
			update_post_meta($id, $db_key, $count);
		}
		
        $json['vote'] = $count;
        $json['type'] = 'success';
        $json['message'] = esc_html__('Your vote update.', 'listingo');
        echo json_encode($json);
        die;
    }

    add_action('wp_ajax_fw_ext_listingo_update_votes', 'fw_ext_listingo_update_votes');
    add_action('wp_ajax_nopriv_fw_ext_listingo_update_votes', 'fw_ext_listingo_update_votes');
}

/**
 * @hook update likes
 */
if (!function_exists('fw_ext_listingo_update_likes')) {

    function fw_ext_listingo_update_likes() {
        global $current_user, $wp_roles, $userdata;
        $json = array();
		$key	= !empty( $_POST['key'] ) ? esc_attr( $_POST['key'] ) : '';
		$id		= !empty( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		
		if(empty( $id ) || empty( $current_user->ID )){ return;}
		
		$db_key	= 'total_votes';
		$count  = get_post_meta($id, $db_key, true);
		
		$vote_users = array();
        $vote_users = get_post_meta($id, 'vote_users', true);
		$vote_users = !empty($vote_users) && is_array($vote_users) ? $vote_users : array();
		
		if( in_array( $current_user->ID, $vote_users) ){
			do_action('fw_remove_user_from_votes',$id);
			$count--;
			update_post_meta($id, $db_key, $count);
			$json['message'] = esc_html__('Your vote has removed', 'listingo');
		} else{
			do_action('fw_add_user_to_votes',$id);
			$count++;
			update_post_meta($id, $db_key, $count);
			$json['message'] = esc_html__('Your vote has update', 'listingo');
		}

        $json['vote'] = $count;
        $json['type'] = 'success';
        
        echo json_encode($json);
        die;
    }

    add_action('wp_ajax_fw_ext_listingo_update_likes', 'fw_ext_listingo_update_likes');
    add_action('wp_ajax_nopriv_fw_ext_listingo_update_likes', 'fw_ext_listingo_update_likes');
}

/**
 * @Set Post Views
 * @return {}
 */
if (!function_exists('fw_remove_user_from_votes')) {

    function fw_remove_user_from_votes($id) {
		global $current_user;
		$vote_users = array();
        $vote_users = get_post_meta($id, 'vote_users', true);
        $vote_users = !empty($vote_users) && is_array($vote_users) ? $vote_users : array();
		
		$wl_id[]    = intval($current_user->ID);
		$vote_users = array_diff($vote_users, $wl_id);
        update_post_meta($id, 'vote_users', $vote_users);
    }
    add_action('fw_remove_user_from_votes', 'fw_remove_user_from_votes', 1, 10);
}

/**
 * @Set Post Views
 * @return {}
 */
if (!function_exists('fw_add_user_to_votes')) {

    function fw_add_user_to_votes($id) {
		global $current_user;
		$vote_users = array();
        $vote_users = get_post_meta($id, 'vote_users', true);
        $vote_users = !empty($vote_users) && is_array($vote_users) ? $vote_users : array();
		
		$vote_users[]  = intval($current_user->ID);
		$vote_users 	= array_unique($vote_users);
		update_post_meta($id, 'vote_users', $vote_users);
		
    }
    add_action('fw_add_user_to_votes', 'fw_add_user_to_votes', 1, 10);
}

/**
 * @Set Post Views
 * @return {}
 */
if (!function_exists('listingo_set_question_views')) {

    function listingo_set_question_views($post_id = '', $key = '') {
		
        if (!isset($_COOKIE[$key . $post_id])) {
            setcookie($key . $post_id, 'question_view_count', time() + 3600);
            $count = get_post_meta($post_id, $key, true);
			
            if ($count == '') {
                $count = 0;
                update_post_meta($post_id, $key, $count);
            } else {
                $count++;
                update_post_meta($post_id, $key, $count);
            }
        }
    }
    add_action('sp_set_question_views', 'listingo_set_question_views', 2, 10);
}

/**
 * 
 */
//if (!function_exists('fw_ext_register_listingo_questions_menu')) {
//
//    function fw_ext_register_listingo_questions_menu() {
//        add_menu_page(esc_html__('Listingo Questions & Answers', 'listingo'), esc_html__('Listingo Q&A', 'listingo'), 'manage_options', 'listingo-quesandans', 'listingo_render_menu_page', 'dashicons-format-status', 15);
//    }
//
//    function listingo_render_menu_page() {
//        if (!current_user_can('manage_options')) {
//            wp_die(__('You do not have sufficient permissions to access this page.'));
//        }
//        echo '<div class="wrap">';
//        echo '<p>Here is where the form would go if I actually had options.</p>';
//        echo '</div>';
//    }
//
//    add_action('admin_menu', 'fw_ext_register_listingo_questions_menu');
//}