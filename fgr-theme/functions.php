<?php

include get_theme_file_path('/hooks/mehr_zu_film.php');
include get_theme_file_path('/hooks/artikel_zu_film.php');
include get_theme_file_path('/functions/viral-news-search-icon.php');
include get_theme_file_path('/functions/viral-news-social-links.php');
include get_theme_file_path('/functions/viral-news-post-date.php');
include get_theme_file_path('/functions/viral-news-posted-on.php');
include get_theme_file_path('/functions/viral-news-top-section-style-2.php');
include get_theme_file_path('/functions/helpers/the_authors.php');
// include get_theme_file_path('/widgets/film-widget.php');

/**
 * Enqueue scripts and styles of parent theme
 */
function child_theme_enqueue_styles() {
    $parent_style = 'viral-news-style'; // This is 'viral-news-style' for the Viral News theme

    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array($parent_style),
        filemtime(get_stylesheet_directory() . '/style.css') // Version based on file modification time to ensure cache busting
    );
    // Single Film Styles
    wp_enqueue_style('single-film', get_stylesheet_directory_uri() . '/single-film.css');

    wp_enqueue_script(
        'child-menu-fix',
        get_stylesheet_directory_uri() . '/js/menu-fix.js',
        array('jquery', 'viral-news-custom'),
        '1.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'child_theme_enqueue_styles');
