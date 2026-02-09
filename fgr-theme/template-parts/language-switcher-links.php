<?php
/**
 * Template part for rendering language switcher links
 * Expects an array of languages from WPML
 */
$languages = apply_filters('wpml_active_languages', NULL, 'skip_missing=0&orderby=code');

if (!empty($languages)) :
    foreach ($languages as $l) :
        // Skip the current active language
        if ($l['active']) continue;
        ?>

        <a href="<?php echo esc_url($l['url']); ?>" class="tp-language-name">
            <span><?php echo esc_html(strtoupper($l['language_code'])); ?></span>
        </a>

    <?php endforeach;
endif;
