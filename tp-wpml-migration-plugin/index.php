<?php
/**
 * Plugin Name: WPML Migration & Translation Tool (last 50 Posts)
 * Description: Detects language ratio, translates Title and Content using TranslatePress, and assigns WPML metadata.
 * Version: 1.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPML_Migration_Tool {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_init', [$this, 'handle_actions']);
    }

    public function add_admin_page() {
        add_menu_page(
                'WPML Migration & Translation Tool',
                'WPML Migration',
                'manage_options',
                'wpml-migration-translation-tool',
                [$this, 'render_admin_page'],
                'dashicons-translation',
                80
        );
    }

    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>WPML Migration & Translation Tool (last 50 Posts)</h1>
            <form method="post">
                <?php wp_nonce_field('wpml_migration_action', 'wpml_migration_nonce'); ?>
                <p>
                    <input type="submit" name="wpml_migrate" class="button button-primary" value="Run Migration & Translation" />
                    <input type="submit" name="wpml_revert" class="button button-secondary" value="Revert Migration & Translation" />
                </p>
            </form>
            <h2>Status Log</h2>
            <textarea id="wpml-migration-log" rows="20" cols="100" style="width:100%;" readonly><?php echo esc_textarea($this->get_log()); ?></textarea>
            <p><button id="wpml-clear-log" class="button">Clear Log</button></p>
            <script>
                document.getElementById('wpml-clear-log').addEventListener('click', function() {
                    if (confirm('Clear the log?')) {
                        fetch(ajaxurl, {
                            method: 'POST',
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                            body: 'action=wpml_clear_log&_wpnonce=<?php echo esc_js(wp_create_nonce('wpml_clear_log')); ?>'
                        }).then(() => { location.reload(); });
                    }
                });
            </script>
        </div>
        <?php
    }

    public function handle_actions() {
        if (isset($_POST['wpml_migrate']) && check_admin_referer('wpml_migration_action', 'wpml_migration_nonce')) {
            $this->run_migration();
        }
        if (isset($_POST['wpml_revert']) && check_admin_referer('wpml_migration_action', 'wpml_migration_nonce')) {
            $this->run_reversion();
        }
        add_action('wp_ajax_wpml_clear_log', [$this, 'clear_log']);
    }

    public function run_migration() {
        global $wpdb;

        // Define word lists (truncated for brevity in this snippet, keep your full lists here)
        $german_words = ['der', 'die', 'das', 'und', 'ist', 'nicht', 'ich', 'zu', 'mit'];
        $english_words = ['the', 'be', 'to', 'of', 'and', 'a', 'in', 'that', 'have'];

        $posts = get_posts(['post_type' => 'post', 'posts_per_page' => 50, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC']);

        foreach ($posts as $post) {
            $this->append_log("--- Processing: {$post->post_title} (ID: {$post->ID}) ---");

            $combined_text = $post->post_title . ' ' . $post->post_content;
            $source_lang = $this->detect_language_ratio($combined_text, $german_words, $english_words);

            if (!$source_lang) {
                $this->append_log("No clear language detected for ID {$post->ID}. Skipping.");
                continue;
            }

            // 1. Assign metadata to original
            $translation_group = sanitize_title($post->post_title);
            update_post_meta($post->ID, '_wpml_import_language_code', $source_lang);
            update_post_meta($post->ID, '_wpml_import_translation_group', $translation_group);

            // 2. Fetch all dictionary entries for this post from TranslatePress
            $translations = $this->get_tp_translations($post->ID, $wpdb);

            if (empty($translations)) {
                $this->append_log("No TranslatePress dictionary entries found for post {$post->ID}.");
                continue;
            }

            // 3. Attempt to translate Content
            $translated_content_data = $this->apply_dictionary($post->post_content, $translations);

            // Check translation ratio (70%)
            $stripped_content = preg_replace('/<[^>]*>/', '', $post->post_content);
            $total_chars = strlen($stripped_content);
            $ratio = ($total_chars > 0) ? ($translated_content_data['chars'] / $total_chars) : 0;

            $minimum_ration = 0.75;
            if ($ratio >= $minimum_ration) {
                // 4. Content passed, now translate Title
                $translated_title_data = $this->apply_dictionary($post->post_title, $translations);

                // Create copy
                $this->create_translated_copy($post, $source_lang, $translation_group, $translated_content_data['text'], $translated_title_data['text']);
                $this->append_log("Success: Translated copy created (Ratio: " . round($ratio, 2) . ").");
            } else {
                $this->append_log("Failed: Translation ratio (" . round($ratio, 2) . ") below " . $minimum_ration );
            }
        }
    }

    private function get_tp_translations($post_id, $wpdb) {
        return $wpdb->get_results($wpdb->prepare(
                "SELECT d.original, d.translated
             FROM {$wpdb->prefix}trp_dictionary_de_de_en_us AS d
             JOIN {$wpdb->prefix}trp_original_meta AS om ON om.original_id = d.original_id
             WHERE om.meta_value = %d AND om.meta_key = 'post_parent_id'",
                $post_id
        ), ARRAY_A);
    }

    private function apply_dictionary($text, $translations) {
        $translated_chars = 0;
        $new_text = $text;

        foreach ($translations as $tr) {
            $original = $tr['original'];
            $translated = $tr['translated'];
            if (empty($original) || empty($translated)) continue;

            // Search and replace (case sensitive to preserve title formatting)
            if (strpos($new_text, $original) !== false) {
                $new_text = str_replace($original, $translated, $new_text);
                $translated_chars += strlen($original);
            }
        }

        return ['text' => $new_text, 'chars' => $translated_chars];
    }

    private function create_translated_copy($original_post, $source_lang, $group, $content, $title) {
        $target_lang = ($source_lang === 'de') ? 'en' : 'de';

        $translated_post_id = wp_insert_post([
                'post_title'   => $title,
                'post_content' => $content,
                'post_status'  => 'publish',
                'post_type'    => 'post',
                'post_author'  => $original_post->post_author,
                'post_date'    => $original_post->post_date,
        ]);

        if ($translated_post_id) {
            update_post_meta($translated_post_id, '_wpml_import_language_code', $target_lang);
            update_post_meta($translated_post_id, '_wpml_import_source_language_code', $source_lang);
            update_post_meta($translated_post_id, '_wpml_import_translation_group', $group);
        }
    }

    public function run_reversion() {
        // Fetch more to ensure we catch the drafts we created
        $posts = get_posts(['post_type' => 'post', 'posts_per_page' => 20, 'post_status' => ['publish', 'draft']]);

        foreach ($posts as $post) {
            $source_lang_meta = get_post_meta($post->ID, '_wpml_import_source_language_code', true);

            // If it has a source language code meta, it is a translated copy created by this tool
            if (!empty($source_lang_meta)) {
                wp_delete_post($post->ID, true);
                $this->append_log("Deleted translated copy: Post ID {$post->ID} ('{$post->post_title}')");
            } else {
                // Otherwise, it's an original: just clean the WPML import metadata
                delete_post_meta($post->ID, '_wpml_import_language_code');
                delete_post_meta($post->ID, '_wpml_import_translation_group');
                $this->append_log("Reverted metadata for original post: ID {$post->ID}");
            }
        }
        $this->append_log("Reversion completed.");
    }

    private function detect_language_ratio($content, $german_words, $english_words) {
        $content = strtolower($content);
        $words = preg_split('/\W+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $german_count = 0; $english_count = 0;

        foreach ($words as $word) {
            if (in_array($word, $german_words)) $german_count++;
            elseif (in_array($word, $english_words)) $english_count++;
        }

        $total = $german_count + $english_count;
        if ($total === 0) return null;

        $g_ratio = $german_count / $total;
        $e_ratio = $english_count / $total;

        if ($g_ratio >= 0.6) return 'de';
        if ($e_ratio >= 0.6) return 'en';
        return null;
    }

    private function append_log($message) {
        $log_file = plugin_dir_path(__FILE__) . 'wpml-migration-log.txt';
        file_put_contents($log_file, date("Y-m-d H:i:s") . " " . $message . "\n", FILE_APPEND);
    }

    private function get_log() {
        $log_file = plugin_dir_path(__FILE__) . 'wpml-migration-log.txt';
        return file_exists($log_file) ? file_get_contents($log_file) : '';
    }

    public function clear_log() {
        check_ajax_referer('wpml_clear_log', '_wpnonce');
        file_put_contents(plugin_dir_path(__FILE__) . 'wpml-migration-log.txt', '');
        wp_send_json_success();
    }
}

new WPML_Migration_Tool();