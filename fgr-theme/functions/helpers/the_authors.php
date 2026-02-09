<?php

function fgr_the_authors() {
    // Check if the PublishPress Authors Plugin is installed
    if(function_exists('get_multiple_authors')) {
        $authors = get_multiple_authors();

        $authors_string = '';

        foreach ($authors as $i => $author) {
            $author_string = '<a href="' .  get_author_posts_url($author->ID)  . '" class=""> ' . esc_html($author->display_name) . '</a>';
            $authors_string .= $author_string;

            if ($i < count($authors) - 2) {
                $authors_string .= ', ';
            } elseif ($i == count($authors) - 2) {
                $authors_string .=  ' ' . __('and', 'fgr-theme') . ' ';
            }
        }

        return($authors_string);
    }

    $author_string = '<a href="' . get_the_author_meta("user_url") . '" class="entry-date published updated">' . esc_html(get_the_author()) . '</a>';
    return('<div class="posted-on"><i class="mdi-account"></i>' . $author_string . '</div>');
}

add_action('fgr_the_authors', 'fgr_the_authors', 30);
