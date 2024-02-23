<?php

function mehr_zu_film()
{

    $post_customs = get_post_custom(get_the_ID());
    $films = isset($post_customs['film_meta_box_select']) ? unserialize($post_customs['film_meta_box_select'][0]) : array();

    if (empty($films)) {
        return;
    }

    // Query all the posts that are related to the films
    $meta_query = array();
    foreach ($films as $film_id) {
        $meta_query[] = array(
            'key' => 'film_meta_box_select',
            'value' => $film_id,
            'compare' => 'LIKE'
        );
    }
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 5,
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_query' => $meta_query,
    );

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return;
    }
?>


    <nav class="navigation post-navigation" role="navigation" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
        <h3>
            Mehr über 
            <?php
            if (count($films) == 1) {
                echo '<a href="' . get_permalink($films[0]) . '" style="font-weight: bold; text-decoration: underline;">' . get_the_title($films[0]) . '</a>';
            } elseif (count($films) > 3) {
                echo 'Mehr zu diesen Filmen.';
            } else {
                $index = 0;
                foreach ($films as $film_id) {
                    echo '<a href="' . get_permalink($film_id) . '" style="font-weight: bold;">' . get_the_title($film_id) . '</a>';
                    if ($index < count($films) - 2) {
                        echo ', ';
                    } elseif ($index == count($films) - 2) {
                        echo ' und ';
                    }
                    $index++;
                }
            }

            ?>
        </h3>

        <?php

        echo '<div class="vn-category_block">';
        while ($query->have_posts()) {
            $query->the_post();
        ?>
            <div class="vn-post-item vn-clearfix">
                <div class="vn-post-thumb">
                    <a href="<?php the_permalink(); ?>">
                        <div class="vn-thumb-container">
                            <?php
                            if (has_post_thumbnail()) {
                                $image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'viral-news-150x150');
                            ?>
                                <img alt="<?php echo esc_attr(get_the_title()) ?>" src="<?php echo esc_url($image[0]) ?>">
                            <?php }
                            ?>
                        </div>
                    </a>
                </div>

                <div class="vn-post-content">
                    <h3 style="font-size: 20px;"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <div style="display: flex; gap: 10px;">
                        <div class="small-cat-labels"><?php echo the_category(' '); ?></div>
                        <?php echo viral_news_post_date(); ?>
                    </div>
                </div>
            </div>
        <?php
        }
        echo '</div>';
        wp_reset_postdata();

        ?>
    </nav>
<?php
}

add_action('fgr_mehr_zu_film', 'mehr_zu_film');
?>