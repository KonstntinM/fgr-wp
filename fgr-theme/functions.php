<?php

include get_theme_file_path('/hooks/mehr_zu_film.php');
include get_theme_file_path('/hooks/artikel_zu_film.php');

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
}
add_action('wp_enqueue_scripts', 'child_theme_enqueue_styles');

/**
 * Override the default post date display to display the author instad.
 */
function viral_news_post_date() {
    $author_string = '<a href="' . get_the_author_meta("user_url") . '" class="entry-date published updated">' . get_the_author() . '</a>';

    echo '<div class="posted-on"><i class="mdi-account"></i>' . $author_string . '</div>'; // WPCS: XSS OK.
}
