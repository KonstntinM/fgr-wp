<?php

/**
 * Override the default post date display to display the author instad.
 * 
 * Using the PublishPress Authors // Has a backup for the default author
 */
function viral_news_post_date() {

    if(!function_exists('get_multiple_authors')) {
        $author_string = '<a href="' . get_the_author_meta("user_url") . '" class="entry-date published updated">' . get_the_author() . '</a>';
        echo '<div class="posted-on"><i class="mdi-account"></i>' . $author_string . '</div>'; // WPCS: XSS OK.
    } else {
        $authors = get_multiple_authors();

        echo '<div class="posted-on"><i class="mdi-account"></i>';

        echo fgr_the_authors();

        echo '</i></div>';
    }
}

add_action('viral_news_post_date', 'viral_news_post_date', 30);
