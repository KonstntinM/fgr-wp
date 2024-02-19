<?php
/*
Plugin Name: Freie Generationen Plugin
Description: WordPress Plugin für die Freie Generationen Reports Website.
Version: 1.0
Author: Konstantin Marx
*/

include( plugin_dir_path( __FILE__ ) . 'movie-import-tool.php');


// Create Film Obkject
// // Register Custom Post Type
function create_film_cpt() {

    // Labels in German language
    $labels = array(
        'name' => _x( 'Filme', 'Post Type General Name', 'textdomain' ),
        'singular_name' => _x( 'Film', 'Post Type Singular Name', 'textdomain' ),
        'menu_name' => _x( 'Filme', 'Admin Menu text', 'textdomain' ),
        'name_admin_bar' => _x( 'Film', 'Add New on Toolbar', 'textdomain' ),
        'archives' => __( 'Film Archive', 'textdomain' ),
        'attributes' => __( 'Film Eigenschaften', 'textdomain' ),
        'parent_item_colon' => __( 'Übergeordneter Film:', 'textdomain' ),
        'all_items' => __( 'Alle Filme', 'textdomain' ),
        'add_new_item' => __( 'Neuen Film erstellen', 'textdomain' ),
        'add_new' => __( 'Film hinzufügen', 'textdomain' ),
        'new_item' => __( 'Neuer Film', 'textdomain' ),
        'edit_item' => __( 'Film bearbeiten', 'textdomain' ),
        'update_item' => __( 'Film aktualisieren', 'textdomain' ),
        'view_item' => __( 'Film ansehen', 'textdomain' ),
        'view_items' => __( 'Filme ansehen', 'textdomain' ),
        'search_items' => __( 'Film suchen', 'textdomain' ),
        'not_found' => __( 'Nicht gefunden', 'textdomain' ),
        'not_found_in_trash' => __( 'Nicht im Papierkorb gefunden', 'textdomain' ),
        'featured_image' => __( 'Beitragsbild', 'textdomain' ),
        'set_featured_image' => __( 'Beitragsbild festlegen', 'textdomain' ),
        'remove_featured_image' => __( 'Beitragsbild entfernen', 'textdomain' ),
        'use_featured_image' => __( 'Beitragsbild verwenden', 'textdomain' ),
        'insert_into_item' => __( 'In Film einfügen', 'textdomain' ),
    );
    $args = array(
        'label' => __( 'Film', 'textdomain' ),
        'description' => __( 'Film information pages', 'textdomain' ),
        'public' => true,
        'labels' => $labels,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes', 'post-formats', 'comments'),
        'taxonomies' => array('section'),
        'menu_icon' => 'dashicons-format-video',
    );
    register_post_type( 'film', $args );

}
// // Hook into the 'init' action
add_action( 'init', 'create_film_cpt', 0 );

// UI zur Verknüpfung von Film und Post einfügen
add_action('add_meta_boxes', 'film_meta_box_add');
function film_meta_box_add()
{
    add_meta_box('film-meta-box-id', 'Film', 'film_meta_box_cb', 'post', 'side', 'high');
}

function film_meta_box_cb()
{
    global $post;
    $values = get_post_custom($post->ID);
    $selected = isset($values['film_meta_box_select']) ? unserialize($values['film_meta_box_select'][0]) : array();
    
    wp_nonce_field('film_meta_box_nonce', 'meta_box_nonce');
    
    $args = array(
        'post_type' => 'film',
        'posts_per_page' => -1
    );
    $films = get_posts($args);
    
    echo '<div>';
    echo '<input type="text" id="film_filter" placeholder="Filme filtern ..." style="margin-bottom: 5px;">';
    echo '<div id="film_dropdown">';
    foreach($films as $film)
    {
        $checked = (in_array($film->ID, $selected, false)) ? 'checked' : 'not-checked';
        $section = get_the_terms($film->ID, 'section')? get_the_terms($film->ID, 'section')[0]->name : 'Keine Sektion';
        $year = get_the_date('Y', $film->ID);
        echo '<label><input type="checkbox" class="film_meta_box_nonce" name="film_meta_box_select[]" value="' . $film->ID . '" ' . $checked . '> ' . $film->post_title . ', ' . $section . ', ' . $year . '</label><br>';
    }
    echo '</div>';
    echo '</div>';
}

    // Enqueue JavaScript for film dropdown filtering
    add_action('admin_enqueue_scripts', 'enqueue_film_dropdown_script');
    function enqueue_film_dropdown_script()
    {
        wp_enqueue_script('film-dropdown-script', plugin_dir_url(__FILE__) . 'js/film-dropdown.js', array('jquery'), '1.0', true);
    }

add_action('save_post', 'film_meta_box_save');
function film_meta_box_save($post_id)
{
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if(!isset($_POST['meta_box_nonce']) || !wp_verify_nonce($_POST['meta_box_nonce'], 'film_meta_box_nonce')) return;
    if(!current_user_can('edit_post', $post_id)) return;
    
    if(isset($_POST['film_meta_box_select']))
    {
        $new = $_POST['film_meta_box_select'];
        update_post_meta($post_id, 'film_meta_box_select', $new);
    }
}

// Display the connected film in the WP-Admin post list
add_filter('manage_post_posts_columns', 'film_columns_head');
function film_columns_head($defaults) {
    $defaults['connected_film'] = 'Verknüpfte Filme';
    $defaults['section'] = 'Sektion';
    return $defaults;
}

add_action('manage_post_posts_custom_column', 'film_columns_content', 10, 2);
function film_columns_content($column_name, $post_ID) {
    if ($column_name == 'connected_film') {
        $connected_film = get_post_meta($post_ID, 'film_meta_box_select', true);
        if ($connected_film) {
            foreach ($connected_film as $film) {
                echo get_the_title($film) . '<br>';
            }
        }
    }
    if ($column_name == 'section') {
        $connected_film = get_post_meta($post_ID, 'film_meta_box_select', true);
        if ($connected_film) {
            $section = get_the_terms($connected_film[0], 'section');
            if ($section) {
                echo $section[0]->name;
            }
        }
    }

}

// Register the Section "Category"
// // Hook into the 'init' action
add_action( 'init', 'create_film_section_taxonomy', 0 );

// // Create a custom taxonomy named 'section'
function create_film_section_taxonomy() {

  $labels = array(
    'name' => _x( 'Sektionen', 'taxonomy general name' ),
    'singular_name' => _x( 'Sektion', 'taxonomy singular name' ),
    'search_items' =>  __( 'Sektionen Durchsuchen' ),
    'all_items' => __( 'Alle Sektionen' ),
    'edit_item' => __( 'Sektion Bearbeiten' ), 
    'update_item' => __( 'Update Sektion' ),
    'add_new_item' => __( 'Add New Sektion' ),
    'new_item_name' => __( 'New Sektion Name' ),
    'menu_name' => __( 'Sektionen' ),
  );    

  register_taxonomy('section', array('film'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'section' ),
  ));

  // Create K+ and 14+ Sections
  wp_insert_term( 'K+', 'section' );
  wp_insert_term( '14+', 'section' );
}


