<?php
/**
 *
 * The template part for displaying the questions listings.
 *
 * @package   Listingo
 * @author    Themographics
 * @link      http://themographics.com/
 * @since 1.0
 */
global $current_user, $wp_query;
//Get User Queried Object Data
$queried_object = $wp_query->get_queried_object();

$posts_per_page	= get_option('posts_per_page');
$q_args = array(
    'post_type' => 'sp_questions',
    'post_status' => 'publish',
    'posts_per_page' => $posts_per_page,
    'order' => 'DESC',
);

$meta_query_args	= array();
$meta_query_args[] = array(
	'key' 		=> 'question_to',
	'value' 	=> $queried_object->ID,
	'compare' 	=> '=',
);

$query_relation = array('relation' => 'AND',);
$meta_query_args = array_merge($query_relation, $meta_query_args);
$q_args['meta_query'] = $meta_query_args;

$username = listingo_get_username($current_user->ID);
$q_query = new WP_Query($q_args);

/**
 * Render Add Question Model Box
 * @return html
 */
do_action('render_add_questions_view');
?>
<div class="tg-questions">
	<div class="tg-companyfeaturetitle">
		<h3><?php esc_html_e('Questions', 'listingo'); ?></h3>
	</div>
</div>
<div class="question-panel-wrap tg-haslayout">
	<?php if ($q_query->have_posts()) {?>
    <div class="tg-widgetrelatedposts sp-provider-articles">
        <div class="questions-area tg-haslayout">
		   <?php
			while ($q_query->have_posts()) : $q_query->the_post();
				global $post;
				$question_by = get_post_meta($post->ID, 'question_by', true);
				$question_to = get_post_meta($post->ID, 'question_to', true);
				$category 	 = get_user_meta($question_to, 'category', true);
	
				$category_icon = '';
				$category_color = '';
				if (function_exists('fw_get_db_post_option') && !empty( $category )) {
					$categoy_bg_img = fw_get_db_post_option($category, 'category_image', true);
					$category_icon  = fw_get_db_post_option($category, 'category_icon', true);
					$category_color = fw_get_db_post_option($category, 'category_color', true);
				}
				?>
				<div class="tg-question">
					<div class="tg-questioncontent">
						<div class="tg-answerholder spq-v2">
							<?php
								if (isset($category_icon['type']) && $category_icon['type'] === 'icon-font') {
									do_action('enqueue_unyson_icon_css');
									if (!empty($category_icon['icon-class'])) {
										?>
										<figure class="tg-docimg"><span class="<?php echo esc_attr($category_icon['icon-class']); ?> tg-categoryicon" style="background: <?php echo esc_attr($category_color); ?>;"></span></figure>
										<?php
									}
								} else if (isset($category_icon['type']) && $category_icon['type'] === 'custom-upload') {
									if (!empty($category_icon['url'])) {
										?>
										<figure class="tg-docimg"><em><img src="<?php echo esc_url($category_icon['url']); ?>" alt="<?php esc_html_e('category', 'listingo'); ?>"></em></figure>
										<?php
									}
								}
							?>

							<h4><a href="<?php echo esc_url(get_permalink()); ?>"> <?php echo esc_attr(get_the_title()); ?> </a></h4>
							<div class="tg-description">
								<p><?php listingo_prepare_excerpt('255','false');?></p>
							</div>
							<div class="tg-questionbottom">
								<a class="tg-btn" href="<?php echo esc_url(get_permalink()); ?>">  <?php esc_html_e('Add/View Answers', 'listingo'); ?> </a>
								<?php fw_ext_get_total_votes_and_answers_html($post->ID);?>
							</div>
						</div>
					</div>
					<div class="tg-matadatahelpfull">
						<?php fw_ext_get_views_and_time_html($post->ID);?>
						<?php fw_ext_get_votes_html($post->ID,esc_html__('Is this helpful?', 'listingo'));?>
					</div>
				</div>
			<?php
			endwhile;
			wp_reset_postdata();
           ?>
        </div>
    </div>
	<?php } else{?>
		<p><?php esc_html_e('No query answered yet.', 'listingo'); ?></p>
	<?php }?>
</div>
