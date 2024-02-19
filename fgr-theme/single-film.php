<?php

/**
 * @package Viral News
 */
get_header();
?>

<div class="vn-container fgr_film">
    <?php
    while (have_posts()) : the_post();

        $viral_news_hide_title = get_post_meta($post->ID, 'viral_news_hide_title', true);


        if (!$viral_news_hide_title) {
    ?>
            <header class="film-header-container">
                <?php the_post_thumbnail('film-thumbnail'); ?>
                <div class="film-header-gradient"> </div>
                <div class="film-info">
                    <div class="film-labels">
                        <?php 
                            // Get Film Section
                            $film_section = get_the_terms($post->ID, 'section');
                            if ($film_section) {
                                foreach ($film_section as $section) {
                                    echo '<a href="' . get_term_link($section) . '" class="film-section">' . $section->name . '</a>';
                                }
                            }

                            // Display the year of post publication
                            echo '<a class="film-section">' . get_the_date('Y') . '</a>';
                        ?>
                    </div>
                    <?php the_title('<h1 class="film-title">', '</h1>'); ?>
                    <div>
                        <?php
                            $film_director = get_post_meta($post->ID, 'director', true);
                            if ($film_director) {
                                echo '<p class="film-director">' . 'By ' . $film_director . '</p>';
                            }
                        ?>
                    </div>
                </div>
            </header><!-- .entry-header -->
        <?php } ?>

        <div class="vn-content-wrap vn-clearfix">
            <div id="primary" class="content-area">

                <?php get_template_part('template-parts/content', 'single'); ?>

                <p style="margin-top: -3rem; margin-bottom: 4rem; font-style: italic; text-decoration: underline;">
                    <?php
                        $link = get_post_meta($post->ID, 'berlinale_link', true);
                        if ($link) {
                            echo '<a href="' . $link . '" target="_blank">Mehr Informationen auf der Berlinale-Website</a>';
                        }
                    ?>
                </p>

                <?php do_action('fgr_artikel_zu_film'); ?>

                <?php
                // If comments are open or we have at least one comment, load up the comment template.
                if (comments_open() || get_comments_number()) :
                    comments_template();
                endif;
                ?>

            </div><!-- #primary -->

            <?php get_sidebar(); ?>
        </div>
    <?php endwhile; // End of the loop. 
    ?>
</div>
<?php
get_footer();
