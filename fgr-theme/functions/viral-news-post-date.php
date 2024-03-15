<?php

/**
 * Override the default post date display to display the author instad.
 */
function viral_news_post_date() {
    $author_string = '<a href="' . get_the_author_meta("user_url") . '" class="entry-date published updated">' . get_the_author() . '</a>';

    echo '<div class="posted-on"><i class="mdi-account"></i>' . $author_string . '</div>'; // WPCS: XSS OK.
}

add_action('viral_news_post_date', 'viral_news_post_date', 30);