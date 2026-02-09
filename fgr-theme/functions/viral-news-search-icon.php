<?php

/**
 * This file hooks into the code displaying the Search Icon in the header to add our language menu to display our custom language switcher.
 * 
 * IMPORTANT! Damit dieses Snippet funktioniert muss das Plugin "TranslatePress" installiert sein.
 * Docs: https://translatepress.com/docs/developers/custom-language-switcher/
 * 
 * Dieser Code kontrolliert die Ansicht in der Desktop-Ansicht. Mobil findet sich in header.php
 */

function viral_news_search_icon()
{
?>
    <div class="fgr-right-header">
        <div style="display: inline-flex; gap: 10px; align-items: center;" data-no-translation>

            <?php get_template_part('template-parts/language-switcher-links'); ?>

            <div class="vn-header-search">
                <span <?php echo viral_news_amp_search_toggle(); ?>><i class="mdi-magnify"></i></span>
            </div>
        </div>
    </div>
<?php
}

add_action('viral_news_main_header_content', 'viral_news_search_icon', 30);
?>