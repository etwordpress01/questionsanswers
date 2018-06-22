<?php

if (!defined('FW')) {
    die('Forbidden');
}

/**
 * Enqueue Script on frontend
 * Check if this is not admin
 */
if (!is_admin()) {

    $fw_ext_instance = fw()->extensions->get('questionsanswers');
    wp_register_script(
            'fw_ext_questionsanswers_callback', $fw_ext_instance->get_declared_URI('/static/js/fw_ext_questionsanswers_callbacks.js'), array('jquery'), '1.0', true
    );
	
	if (is_singular()) {
		$_post = get_post();
		if ($_post != null) {
			if ($_post && 
				( 
					preg_match('/sp_post_questions/', $_post->post_content)
					||
					preg_match('/listingo_vc_post_questions/', $_post->post_content)
				)
			) {
				wp_enqueue_script('fw_ext_questionsanswers_callback');
			}
		}
	}
	
	if (is_page_template('directory/dashboard.php') || is_author() || is_singular('sp_questions')) {
		wp_enqueue_script('fw_ext_questionsanswers_callback');
	}
}