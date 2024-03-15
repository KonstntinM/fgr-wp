<?php

/**
 * Customize the post date display to display all authors
 *
 * @package Viral News
 */
if (!function_exists('viral_news_posted_on')) :

    /**
     * Prints HTML with meta information for the current post-date/time and author.
     */
    function viral_news_posted_on() {
        $viral_news_is_updated_date = get_theme_mod('viral_news_display_date_option', 'posted') == 'updated' ? true : false;

        $posted_on = sprintf('<span class="vn-day">%1$s</span><span class="vn-month">%2$s</span>', esc_html($viral_news_is_updated_date ? get_the_modified_date('j') : get_the_date('j')), esc_attr($viral_news_is_updated_date ? get_the_modified_date('M') : get_the_date('M')));

        $avatar = get_avatar(get_the_author_meta('ID'), 48);
        $comment_count = get_comments_number(); // get_comments_number returns only a numeric value

        // Lang options
        if (function_exists('get_locale')) {
            $locale = get_locale(); 
            if ($locale == 'de_DE') {
                $author = sprintf(esc_html_x('Von %s', 'post author', 'viral-news'), fgr_the_authors());

                if ($comment_count == 0) {
                    $comments = esc_html__('Keine Kommentare', 'viral-news');
                } elseif ($comment_count > 1) {
                    $comments = $comment_count . ' ' . esc_html__('Comments', 'viral-news');
                } else {
                    $comments = esc_html__('1 Kommentar', 'viral-news');
                }
            } else {
                $author = sprintf(esc_html_x('By %s', 'post author', 'viral-news'), fgr_the_authors());

                if ($comment_count == 0) {
                    $comments = esc_html__('No Comments', 'viral-news');
                } elseif ($comment_count > 1) {
                    $comments = $comment_count . ' ' . esc_html__('Comments', 'viral-news');
                } else {
                    $comments = esc_html__('1 Comment', 'viral-news');
                }
            }
        } else {
            $author = sprintf(esc_html_x('Von %s', 'post author', 'viral-news'), fgr_the_authors());

            if ($comment_count == 0) {
                $comments = esc_html__('Keine Kommentare', 'viral-news');
            } elseif ($comment_count > 1) {
                $comments = $comment_count . ' ' . esc_html__('Comments', 'viral-news');
            } else {
                $comments = esc_html__('1 Kommentar', 'viral-news');
            }
        }

        echo '<span class="entry-date" ' . viral_news_get_schema_attribute('publish_date') . '>' . $posted_on . '</span><span class="entry-author" ' . viral_news_get_schema_attribute('author') . '> ' . $avatar . '<span class="author" ' . viral_news_get_schema_attribute('author_name') . '>' . $author . '</span></span><span class="entry-comment">' . $comments . '</span>'; // WPCS: XSS OK.
    }

endif;

add_action('viral_news_posted_on', 'viral_news_posted_on', 10);
