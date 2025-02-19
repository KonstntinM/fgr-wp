<?php

function viral_news_top_section_style2($args) {
    $title = $args['title'];
    $layout = $args['layout'];
    $cat = $args['cat'];
    if ($layout != 'style2')
        return;
    ?>
    <div class="vn-top-block <?php echo esc_attr($layout); ?>">
        <?php if ($title) { ?>
            <h2 class="vn-block-title"><span><?php echo esc_html($title); ?></span></h2>
        <?php } ?>
        <div class="vn-top-block-wrap">
            <?php
            $args = array(
                'cat' => $cat,
                'posts_per_page' => 5,
                'ignore_sticky_posts' => true
            );

            $query = new WP_Query($args);
            while ($query->have_posts()): $query->the_post();
                $index = $query->current_post + 1;
                $last = $query->post_count;
                $title_class = $index == 1 ? 'vn-large-title' : '';

                if ($index == 1) {
                    echo '<div class="col1">';
                } elseif ($index == 2) {
                    echo '<div class="col2">';
                } elseif ($index == 4) {
                    echo '<div class="col3">';
                }
                ?>
                <div class="vn-post-item">
                    <div class="vn-post-thumb">
                        <a href="<?php the_permalink(); ?>">
                            <div class="vn-thumb-container">
                                <?php
                                if (has_post_thumbnail()) {
                                    $image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'viral-news-600x600');
                                    ?>
                                    <img alt="<?php echo the_title_attribute() ?>" src="<?php echo esc_url($image[0]) ?>">
                                <?php }
                                ?>
                            </div>
                        </a>
                        <?php
                        if ($index == 1) {
                            echo get_the_category_list();
                        } else {
                            viral_news_post_primary_category();
                        }
                        ?>
                    </div>

                    <div class="vn-post-content">
                        <h3 class="vn-post-title <?php echo esc_attr($title_class) ?>"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <?php viral_news_post_date(); ?>

                        <?php if ($index == 1) { ?>
                            <div class="vn-excerpt">
                                <?php echo viral_news_excerpt(get_the_content(), 200); ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php
                if ($index == 1 || $index == 3 || $index == $last) {
                    echo '</div>';
                }
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
        <div class="read-more-wrapper">
            <a href="<?php echo get_permalink(get_page_by_path('neues')->ID) ?>" class="read-more-item">
                Alle Artikel
            </a>
        </div>
        <style>
            .read-more-wrapper {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .read-more-item {
                display: block;
                color: #333;
                line-height: 32px;
                letter-spacing: var(--viral-news-menu-letter-spacing, 0);
                font-size: var(--viral-news-menu-size, 15px);
                font-weight: var(--viral-news-menu-weight, 400);
                font-style: var(--viral-news-menu-style, normal);
                text-decoration: var(--viral-news-menu-text-decoration, none);
                text-transform: var(--viral-news-menu-text-transform, uppercase);
                padding: 8px 10px;
                margin: 15px 0px;
                border-style: groove;
            }

            .read-more-item:hover {
                color: white !important;
                background-color: #333333;
            }

            .read-more-item::after{
                content: "";
                position: absolute;
                left: 0;
                width: 0;
                top: 100%;
                margin-top: -15px;
                background: #333;
                height: 1px;
                transition: all 0.2s ease;
                -moz-transition: all 0.2s ease;
                -webkit-transition: all 0.2s ease;
            }
        </style>
    </div>
    <?php
}

add_action('viral_news_top_section_style2', 'viral_news_top_section_style2', 30);
