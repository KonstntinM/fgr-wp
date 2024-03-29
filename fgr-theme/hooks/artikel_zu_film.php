<?php

function artikel_zu_film()
{
    // Query all the posts that are related to the films
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 5,
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_query' => array(array(
            'key' => 'film_meta_box_select',
            'value' => get_the_ID(),
            'compare' => 'LIKE'
        ))
    );

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        echo '<h3>Bisher haben wir nicht zu diesem Film veröffentlicht.</h3>';
        return;
    }
?>


    <nav class="navigation post-navigation" role="navigation" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
        <h3 style="display: block;">
            Unsere Beiträge zu 
            <strong>
                <?php
                    echo get_the_title();
                ?>
            </strong>
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

add_action('fgr_artikel_zu_film', 'artikel_zu_film');
?>