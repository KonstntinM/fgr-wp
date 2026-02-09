<?php
/*
Plugin Name: Freie Generationen Plugin
Description: WordPress Plugin für die Freie Generationen Reports Website.
Version: 1.1
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
        'show_in_nav_menus' => true, // You have this
        'show_ui' => true,           // Ensure the UI is enabled
        'has_archive' => true,       // Usually helpful for menus
        'publicly_queryable' => true, // This is often the culprit
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

function film_meta_box_cb($post)
{
    // 1. Get the current editing language
    $post_lang = apply_filters('wpml_post_language_details', null, $post->ID);
    $lang_code = isset($post_lang['language_code']) ? $post_lang['language_code'] : 'de';

    // 2. Fetch saved values
    $selected = get_post_meta($post->ID, 'film_meta_box_select', true);
    if (!is_array($selected)) { $selected = array(); }

    wp_nonce_field('film_meta_box_nonce', 'meta_box_nonce');

    // 3. Fetch ALL films regardless of language initially
    // We then filter them in the loop to avoid duplicates but show fallbacks
    $args = array(
        'post_type'        => 'film',
        'posts_per_page'   => -1,
        'suppress_filters' => true, // We will handle language logic manually for "fallback" support
    );
    $all_films = get_posts($args);

// 4. Filter to ensure we only show one version per film (Current Lang > English > Original)
    $processed_trids = array();
    $films_to_display = array();

    foreach ($all_films as $f) {
        // Use the dedicated WPML function to get the Translation Group ID (trid)
        // This is more reliable than the filter you were using
        $trid = apply_filters('wpml_element_trid', NULL, $f->ID, 'post_film');

        // If for some reason there is no trid (e.g. WPML isn't managing this post),
        // fallback to the Post ID so it still shows up.
        $lookup_key = $trid ? $trid : 'no-trid-' . $f->ID;

        if (in_array($lookup_key, $processed_trids)) continue;

        // Try to get the version in the current language
        $best_id = apply_filters('wpml_object_id', $f->ID, 'film', false, $lang_code);

        // Fallback: If no translation exists in current lang, use the original ID
        if (!$best_id) {
            $best_id = $f->ID;
        }

        $films_to_display[] = get_post($best_id);
        $processed_trids[] = $lookup_key;
    }

    // --- START HTML OUTPUT ---
    echo '<div class="film-meta-box-container" style="padding: 10px 0;">';
    echo '<div style="margin-bottom: 12px;">';
    echo '<input type="text" id="film_filter" placeholder="Filme filtern (Titel oder Englisch)..." style="width: 100%; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px;">';
    echo '</div>';

    echo '<div id="film_dropdown" style="max-height: 350px; overflow-y: auto; border: 1px solid #ccd0d4; background: #fff; border-radius: 4px;">';

    echo '<div id="selected_films_area" style="background: #f9f9f9; border-bottom: 2px solid #eee; display:none;">';
    echo '<div style="padding: 5px 10px; font-size: 10px; text-transform: uppercase; color: #2271b1; font-weight: bold; background: #f0f6fb;">Ausgewählt</div>';
    echo '<div class="selected-container"></div>';
    echo '</div>';

    echo '<div id="available_films_area">';
    foreach ($films_to_display as $film) {
        $is_checked = in_array($film->ID, $selected) ? 'checked="checked"' : '';

        // Always try to find the English ID for the " / Title" display
        $en_id = apply_filters('wpml_object_id', $film->ID, 'film', false, 'en');
        $display_title = esc_html($film->post_title);

        if ($en_id && $en_id != $film->ID) {
            $en_title = get_the_title($en_id);
            $display_title .= ' <span style="color: #666; font-weight: normal;"> / ' . esc_html($en_title) . '</span>';
        }

        $terms = get_the_terms($film->ID, 'section');
        $section = ($terms && !is_wp_error($terms)) ? $terms[0]->name : 'Keine Sektion';
        $year = get_the_date('Y', $film->ID);

        echo '<label class="film-item" style="display: block; padding: 8px 10px; border-bottom: 1px solid #f0f0f0; cursor: pointer; margin: 0;">';
        echo '<input type="checkbox" name="film_meta_box_select[]" value="' . esc_attr($film->ID) . '" ' . $is_checked . ' style="margin-right: 10px; vertical-align: middle;">';
        echo '<span style="vertical-align: middle;">';
        echo '<strong>' . $display_title . '</strong>';
        echo ' <span style="color: #888; font-size: 0.85em; margin-left: 5px;">(' . esc_html($section) . ', ' . esc_html($year) . ')</span>';
        echo '</span>';
        echo '</label>';
    }
    echo '</div>';

    echo '<p id="no-results" style="display:none; padding: 15px; color: #d63638; margin: 0; background: #fcf0f1;">Keine Übereinstimmung gefunden.</p>';
    echo '</div>';
    echo '</div>';
}

    // Enqueue JavaScript for film dropdown filtering
    add_action('admin_enqueue_scripts', 'enqueue_film_dropdown_script');
function enqueue_film_dropdown_script()
{
    // Get the file path to check the modification time
    $js_path = plugin_dir_path(__FILE__) . 'js/film-dropdown.js';

    // Get the last modified time, or use a fallback version if the file doesn't exist
    $version = file_exists($js_path) ? filemtime($js_path) : '1.0';

    wp_enqueue_script(
        'film-dropdown-script',
        plugin_dir_url(__FILE__) . 'js/film-dropdown.js',
        array('jquery'),
        $version,
        true
    );
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


// Change Slim Seo Separator
add_filter( 'document_title_separator', function() {
    return '|'; // Replace with your custom separator.
} );