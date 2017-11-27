//"use strict";
jQuery(document).on('ready', function () {
    var loader_html = '<div class="provider-site-wrap"><div class="provider-loader"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>';

    /*************************************
     * Save Question Ajax Request
     ************************************/
    jQuery(document).on('click', '.fw_ext_question_save_btn', function (e) {
        e.preventDefault();
        if (typeof tinyMCE === 'object') {
            tinyMCE.triggerSave();
        }

        var _this = jQuery(this);
        var serialize_data = jQuery('.fw_ext_questions_form').serialize();
        var dataString = serialize_data + '&action=fw_ext_listingo_process_questions';
        jQuery('body').append(loader_html);
        jQuery.ajax({
            type: "POST",
            url: fw_ext_questionsanswers_scripts_vars.ajaxurl,
            data: dataString,
            dataType: "json",
            success: function (response) {
                if (response.type == 'error') {
					jQuery('body').find('.provider-site-wrap').remove();
                    jQuery.sticky(response.message, {classList: 'important', speed: 200, autoclose: 5000});
                } else {
                    jQuery.sticky(response.message, {classList: 'success', speed: 200, autoclose: 5000, position: 'top-right', });
					window.location.reload();
                }
            }
        });
        return false;
    });
    /*************************************
     * Save Question Answer Ajax Request
     ************************************/
    jQuery(document).on('click', '.answer_save_btn', function (e) {
        e.preventDefault();
        if (typeof tinyMCE === 'object') {
            tinyMCE.triggerSave();
        }

        var _this = jQuery(this);
        var serialize_data = jQuery('.listingo_answer_form').serialize();
        var dataString = serialize_data + '&action=fw_ext_listingo_process_answers';
        jQuery('body').append(loader_html);
        jQuery.ajax({
            type: "POST",
            url: fw_ext_questionsanswers_scripts_vars.ajaxurl,
            data: dataString,
            dataType: "json",
            success: function (response) {
                if (response.type == 'error') {
					jQuery('body').find('.provider-site-wrap').remove();
                    jQuery.sticky(response.message, {classList: 'important', speed: 200, autoclose: 5000});
                } else {
                    jQuery.sticky(response.message, {classList: 'success', speed: 200, autoclose: 5000, position: 'top-right', });
					window.location.reload();
                }
            }
        });
        return false;
    });
	
	/*************************************
     * update votes
     ************************************/
    jQuery(document).on('click', '.updatevote', function (e) {
        e.preventDefault();
        var is_loggedin = fw_ext_questionsanswers_scripts_vars.is_loggedin;
		if( is_loggedin === 'false' ){
			jQuery.sticky(fw_ext_questionsanswers_scripts_vars.login_beofer_vote, {classList: 'success', speed: 200, autoclose: 5000});
		}
		
        var _this = jQuery(this);
		var _key	= _this.data('key');
		var _id	= _this.data('id');
        var dataString = 'key='+ _key + '&id='+ _id + '&action=fw_ext_listingo_update_likes';
        jQuery('body').append(loader_html);
        jQuery.ajax({
            type: "POST",
            url: fw_ext_questionsanswers_scripts_vars.ajaxurl,
            data: dataString,
            dataType: "json",
            success: function (response) {
                jQuery('body').find('.provider-site-wrap').remove();
                _this.parents('.tg-question').find('.votes_wrap').html(response.vote);
				jQuery.sticky(response.message, {classList: 'success', speed: 200, autoclose: 5000});
            }
        });
        return false;
    });

});