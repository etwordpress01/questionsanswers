<?php
if (!defined('FW'))
    die('Forbidden');

$manifest = array();
$manifest['name'] = esc_html__('Questions and Answers', 'listingo');
$manifest['uri'] = 'https://themeforest.net/user/themographics/portfolio';
$manifest['description'] = esc_html__('This extension will enable users to post question and answers at provider detail page.', 'listingo');
$manifest['version'] = '2.8';
$manifest['author'] = 'Themographics';
$manifest['display'] = true;
$manifest['standalone'] = true;
$manifest['author_uri'] = 'https://themeforest.net/user/themographics/portfolio';
$manifest['github_repo'] = 'https://github.com/etwordpress01/questionsanswers';
$manifest['github_update'] = 'etwordpress01/questionsanswers';
$manifest['requirements'] = array(
    'wordpress' => array(
        'min_version' => '4.0',
    )
);

$manifest['thumbnail'] = fw_get_template_customizations_directory_uri().'/extensions/questionsanswers/static/img/thumbnails/questions.jpg';
