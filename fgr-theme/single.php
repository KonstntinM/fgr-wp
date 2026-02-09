<?php

/**
 * @package Viral News
 */
get_header();
?>

<div class="vn-container">
    <?php
    while (have_posts()) : the_post();

        $viral_news_hide_title = get_post_meta($post->ID, 'viral_news_hide_title', true);


        if (!$viral_news_hide_title) {
    ?>
            <header class="vn-main-header">
                <?php the_post_thumbnail('viral-news-600x600'); ?>
                <?php the_title('<h1 style="margin-top: 1rem;">', '</h1>'); ?>
                <div style="display: inline-flex; gap: 10px;">
                    <?php viral_news_post_date(); ?>
                    <?php
                        $time_string = '<a class="entry-date published updated">' . get_the_date() . '</a>';

                        echo '<div class="posted-on"><i class="mdi-clock-time-three-outline"></i>' . $time_string  . '</div>'; // WPCS: XSS OK.
                    ?>
                </div>
            </header><!-- .entry-header -->
        <?php } ?>

        <div class="vn-content-wrap vn-clearfix">
            <div id="primary" class="content-area">

                <?php get_template_part('template-parts/content', 'single'); ?>

                <?php do_action('fgr_mehr_zu_film'); ?>

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
