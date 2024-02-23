<?php

/**
 * This file hooks into the code displaying the Search Icon in the header to add our language menu.
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
        <div style="display: inline-flex; gap: 10px; align-items: center;">

            <?php

            $sprachen = trp_custom_language_switcher();
            ?>
            <?php // if ( apply_filters( 'trp_allow_tp_to_run', true ) ){ 
            ?>
            <?php foreach ($sprachen as $name => $item) {
                // Check if the current language is the current page language
                global $wp;

                if (!str_starts_with($wp->request, $item['language_code'])) { ?>
                    <a href="<?php echo $item['current_page_url'] ?>" class="tp-language-name">
                        <span><?php echo $item['short_language_name'] ?></span>
                        <span><?php echo $wp->request ?></span>
                    </a>
                <?php }
                // }
                ?>
            <?php } ?>
            <div class="vn-header-search">
                <span <?php echo viral_news_amp_search_toggle(); ?>><i class="mdi-magnify"></i></span>
            </div>
        </div>
    </div>
<?php
}

add_action('viral_news_main_header_content', 'viral_news_search_icon', 30);
?>