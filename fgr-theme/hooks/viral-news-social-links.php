<?php

/**
 * Make the default social links in the header sr friendly
 */

function viral_news_social_links() {
        echo '<div class="vn-header-social-icons">';
        $facebook = get_theme_mod('viral_news_social_facebook', '#');
        $twitter = get_theme_mod('viral_news_social_twitter', '#');
        $youtube = get_theme_mod('viral_news_social_youtube', '#');
        $instagram = get_theme_mod('viral_news_social_instagram', '#');

        if ($facebook)
            echo '<a class="vn-facebook" aria-label="Our Facebook Page" href="' . esc_url($facebook) . '" target="_blank"><i class="mdi-facebook"></i></a>';

        if ($twitter)
            echo '<a class="vn-twitter" aria-label="Our Twitter" href="' . esc_url($twitter) . '" target="_blank"><i class="ti-x-twitter"></i></a>';

        if ($youtube)
            echo '<a class="vn-youtube" aria-label="Our YouTube Channel" href="' . esc_url($youtube) . '" target="_blank"><i class="mdi-youtube"></i></a>';

        if ($instagram)
            echo '<a class="vn-instagram" aria-label="Our Instagram" href="' . esc_url($instagram) . '" target="_blank"><i class="mdi-instagram"></i></a>';
        echo '</div>';
}

add_action('viral_news_social_links', 'viral_news_social_links', 30);

?>
