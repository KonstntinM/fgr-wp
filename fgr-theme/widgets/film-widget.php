<?php

class Film_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'fgr_film_widget', // Base ID
			'Film Widget', // Name
			array(
                'description' => __( 'Zeigt das Poster und weitere Informationen des besprochenen Films an.', 'text_domain' )
                ) // Args
		);
	}

	public function widget( $args, $instance ) {

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

        // Don't display widget if more than one film
        if (count($films) > 1) {
            return;
        }

        $film = $films[0];

        $film_director = get_post_meta($film, 'director', true);
        if(!$film_director) {
            return;
        }

        echo '<a href="' . get_permalink($film) . '">';
		echo '<div class="film-widget-container">';

        echo get_the_post_thumbnail($film, 'widget-film-thumbnail');
        echo '<div class="film-header-gradient"> </div>
        <div class="widget-film-info">
            <div class="film-labels">';
            $film_section = get_the_terms($film, 'section');
            if ($film_section) {
                foreach ($film_section as $section) {
                    echo '<a href="' . get_term_link($section) . '" class="film-section">' . $section->name . '</a>';
                }
            }
            echo '<a class="film-section">' . get_the_date('Y') . '</a>';
           echo '</div>
            <h1 class="widget-film-title">';
            echo esc_html(get_the_title($film));
        echo '</h1>
            <div>
                <p class="film-director">';
                echo '<span>Von</span> <span>' . $film_director . '</span>';
            echo '</p>
            </div>
        </div>
    
    </div></a>';
	}

	public function form( $instance ) {
		// outputs the options form in the admin
	}

	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
	}
}

function register_film_widget() {
    register_widget( 'Film_Widget' );
}

add_action( 'widgets_init', 'register_film_widget' );

