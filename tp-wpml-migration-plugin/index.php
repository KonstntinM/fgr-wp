<?php
/**
 * Plugin Name: WPML Migration & Translation Tool (last 5 Posts)
 * Description: Detects language ratio for the last 5 published posts, assigns WPML metadata, and creates a translated copy using TranslatePress translations.
 * Version: 1.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WPML_Migration_Tool {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_init', [$this, 'handle_actions']);
    }

    // Add admin page
    public function add_admin_page() {
        add_menu_page(
                'WPML Migration & Translation Tool (last 5 Posts)',
                'WPML Migration & Translation',
                'manage_options',
                'wpml-migration-translation-tool',
                [$this, 'render_admin_page'],
                'dashicons-translation',
                80
        );
    }

    // Render admin page
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>WPML Migration & Translation Tool (last 5 Posts)</h1>

            <h2>Actions</h2>
            <form method="post">
                <?php wp_nonce_field('wpml_migration_action', 'wpml_migration_nonce'); ?>
                <p>
                    <input type="submit" name="wpml_migrate" class="button button-primary" value="Run Migration & Translation (last 5 Posts)" />
                    <input type="submit" name="wpml_revert" class="button button-secondary" value="Revert Migration & Translation (last 5 Posts)" />
                </p>
            </form>

            <h2>Status Log</h2>
            <textarea id="wpml-migration-log" rows="20" cols="100" readonly><?php echo esc_textarea($this->get_log()); ?></textarea>

            <p>
                <button id="wpml-clear-log" class="button">Clear Log</button>
            </p>

            <script>
                document.getElementById('wpml-clear-log').addEventListener('click', function() {
                    if (confirm('Are you sure you want to clear the log?')) {
                        fetch(ajaxurl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=wpml_clear_log&_wpnonce=<?php echo esc_js(wp_create_nonce('wpml_clear_log')); ?>'
                        }).then(response => response.text())
                            .then(data => {
                                document.getElementById('wpml-migration-log').value = '';
                                alert('Log cleared!');
                            });
                    }
                });
            </script>
        </div>
        <?php
    }

    // Handle actions
    public function handle_actions() {
        if (isset($_POST['wpml_migrate']) && check_admin_referer('wpml_migration_action', 'wpml_migration_nonce')) {
            $this->run_migration();
        }

        if (isset($_POST['wpml_revert']) && check_admin_referer('wpml_migration_action', 'wpml_migration_nonce')) {
            $this->run_reversion();
        }

        add_action('wp_ajax_wpml_clear_log', [$this, 'clear_log']);
    }

    // Run migration and translation (last 5 posts)
    public function run_migration() {
        global $wpdb;

        $german_words = [
                'der', 'die', 'das', 'und', 'oder', 'aber', 'den', 'dem', 'des', 'in', 'ist', 'nicht', 'wir', 'er', 'sie', 'es',
                'hallo', 'welt', 'haus', 'auto', 'kind', 'zeit', 'jahr', 'tag', 'nacht', 'wasser', 'essen', 'trinken', 'gehen',
                'kommen', 'sehen', 'machen', 'sagen', 'guten', 'morgen', 'abend', 'name', 'stadt', 'land', 'mensch', 'frau', 'mann',
                'liebe', 'leben', 'arbeit', 'schule', 'hausaufgaben', 'buch', 'lesen', 'schreiben', 'sprechen', 'hören', 'verstehen',
                'deutsch', 'sprache', 'wort', 'satz', 'frage', 'antwort', 'gut', 'schlecht', 'groß', 'klein', 'alt', 'neu', 'schön',
                'hässlich', 'wichtig', 'einfach', 'schwer', 'leicht', 'schnell', 'langsam', 'heute', 'gestern', 'morgen', 'jetzt',
                'später', 'früher', 'immer', 'manchmal', 'oft', 'selten', 'nie', 'hier', 'dort', 'oben', 'unten', 'links', 'rechts',
                'vor', 'nach', 'mit', 'ohne', 'für', 'gegen', 'durch', 'bis', 'seit', 'von', 'zu', 'aus', 'bei', 'über', 'unter',
                'zwischen', 'während', 'trotz', 'wegen', 'statt', 'um', 'als', 'wenn', 'weil', 'dass', 'ob', 'damit', 'dass', 'obwohl',
                'während', 'bevor', 'nachdem', 'indem', 'dadurch', 'wobei', 'sodass', 'sowie', 'sowohl', 'als', 'auch', 'nur', 'schon',
                'noch', 'erst', 'schließlich', 'zwar', 'allerdings', 'trotzdem', 'dennoch', 'sondern', 'außerdem', 'überhaupt',
                'eigentlich', 'vielleicht', 'bestimmt', 'wahrscheinlich', 'möglich', 'unmöglich', 'notwendig', 'wichtig', 'interessant',
                'langweilig', 'spannend', 'lustig', 'traurig', 'fröhlich', 'wütend', 'müde', 'hungrig', 'durstig', 'krank', 'gesund',
                'stark', 'schwach', 'mutig', 'ängstlich', 'glücklich', 'unglücklich', 'stolz', 'schäm', 'dankbar', 'neidisch',
                'eifersüchtig', 'freundlich', 'unfreundlich', 'höflich', 'unhöflich', 'ehrlich', 'unehrlich', 'fair', 'unfair',
                'klug', 'dumm', 'fleißig', 'faul', 'pünktlich', 'unpünktlich', 'sauer', 'süß', 'bitter', 'salzig', 'lecker', 'eklig',
                'teuer', 'billig', 'reich', 'arm', 'voll', 'leer', 'offen', 'geschlossen', 'hell', 'dunkel', 'laut', 'leise', 'warm',
                'kalt', 'heiß', 'kühl', 'nass', 'trocken', 'hart', 'weich', 'glatt', 'rau', 'scharf', 'stumpf', 'rund', 'eckig',
                'gerade', 'krumm', 'lang', 'kurz', 'breit', 'schmal', 'dick', 'dünn', 'hoch', 'niedrig', 'tief', 'flach', 'schwer',
                'leicht', 'stark', 'schwach', 'sicher', 'gefährlich', 'sauber', 'schmutzig', 'ordentlich', 'unordentlich', 'neu',
                'alt', 'modern', 'altmodisch', 'schnell', 'langsam', 'früh', 'spät', 'nah', 'fern', 'einfach', 'kompliziert',
                'möglich', 'unmöglich', 'wahr', 'falsch', 'richtig', 'falsch', 'gut', 'schlecht', 'besser', 'schlechter', 'am besten',
                'am schlechtesten', 'gleich', 'ungleich', 'ähnlich', 'verschieden', 'gleich', 'anders', 'einzig', 'mehrere',
                'viele', 'wenige', 'alle', 'keine', 'manche', 'einige', 'jeder', 'keiner', 'irgendwer', 'irgendwas', 'irgendwo',
                'irgendwann', 'irgendwie', 'irgendwohin', 'irgendwoher', 'jemand', 'niemand', 'etwas', 'nichts', 'überall', 'nirgends',
                'immer', 'nie', 'manchmal', 'oft', 'selten', 'häufig', 'ab und zu', 'gelegentlich', 'regelmäßig', 'ständig',
                'dauernd', 'plötzlich', 'langsam', 'schnell', 'sofort', 'gleich', 'bald', 'später', 'früher', 'jetzt', 'gerade',
                'schon', 'noch', 'erst', 'schließlich', 'endlich', 'immerhin', 'wenigstens', 'zumindest', 'sogar', 'auch', 'nur',
                'bloß', 'lediglich', 'ausschließlich', 'einzig', 'allein', 'zusammen', 'gemeinsam', 'getrennt', 'alleine', 'miteinander',
                'gegenseitig', 'wechselseitig', 'abwechselnd', 'nacheinander', 'gleichzeitig', 'zugleich', 'unterdessen', 'währenddessen',
                'inzwischen', 'zwischenzeitlich', 'mittlerweile', 'vorher', 'nachher', 'danach', 'davor', 'darauf', 'darin', 'damit',
                'dagegen', 'daher', 'dadurch', 'dahin', 'daher', 'dafür', 'davon', 'dazu', 'darüber', 'darunter', 'dabei', 'dabei',
                'dahinter', 'davor', 'daneben', 'dazwischen', 'daraus', 'darum', 'deshalb', 'trotzdem', 'dennoch', 'sonst', 'ansonsten',
                'andernfalls', 'sondern', 'außerdem', 'überhaupt', 'eigentlich', 'vielleicht', 'bestimmt', 'wahrscheinlich', 'möglich',
                'unmöglich', 'notwendig', 'wichtig', 'interessant', 'langweilig', 'spannend', 'lustig', 'traurig', 'fröhlich', 'wütend',
                'müde', 'hungrig', 'durstig', 'krank', 'gesund', 'stark', 'schwach', 'mutig', 'ängstlich', 'glücklich', 'unglücklich',
                'stolz', 'schäm', 'dankbar', 'neidisch', 'eifersüchtig', 'freundlich', 'unfreundlich', 'höflich', 'unhöflich', 'ehrlich',
                'unehrlich', 'fair', 'unfair', 'klug', 'dumm', 'fleißig', 'faul', 'pünktlich', 'unpünktlich', 'sauer', 'süß', 'bitter',
                'salzig', 'lecker', 'eklig', 'teuer', 'billig', 'reich', 'arm', 'voll', 'leer', 'offen', 'geschlossen', 'hell', 'dunkel',
                'laut', 'leise', 'warm', 'kalt', 'heiß', 'kühl', 'nass', 'trocken', 'hart', 'weich', 'glatt', 'rau', 'scharf', 'stumpf',
                'rund', 'eckig', 'gerade', 'krumm', 'lang', 'kurz', 'breit', 'schmal', 'dick', 'dünn', 'hoch', 'niedrig', 'tief', 'flach',
                'schwer', 'leicht', 'stark', 'schwach', 'sicher', 'gefährlich', 'sauber', 'schmutzig', 'ordentlich', 'unordentlich'
        ];

        $english_words = [
                'the', 'be', 'to', 'of', 'and', 'a', 'in', 'that', 'have', 'i', 'it', 'for', 'not', 'on', 'with', 'he', 'she', 'as',
                'you', 'do', 'at', 'this', 'but', 'his', 'by', 'from', 'they', 'we', 'say', 'her', 'she', 'or', 'an', 'will', 'my',
                'one', 'all', 'would', 'there', 'their', 'what', 'so', 'up', 'out', 'if', 'about', 'who', 'get', 'which', 'go', 'me',
                'when', 'make', 'can', 'like', 'time', 'no', 'just', 'him', 'know', 'take', 'people', 'into', 'year', 'your', 'good',
                'some', 'could', 'them', 'see', 'other', 'than', 'then', 'now', 'look', 'only', 'come', 'its', 'over', 'think',
                'also', 'back', 'after', 'use', 'two', 'how', 'our', 'work', 'first', 'well', 'way', 'even', 'new', 'want', 'because',
                'any', 'these', 'give', 'day', 'most', 'us', 'hello', 'world', 'house', 'car', 'child', 'time', 'year', 'day', 'night',
                'water', 'eat', 'drink', 'go', 'come', 'see', 'make', 'say', 'good', 'morning', 'evening', 'name', 'city', 'country',
                'person', 'woman', 'man', 'love', 'life', 'work', 'school', 'homework', 'book', 'read', 'write', 'speak', 'hear',
                'understand', 'english', 'language', 'word', 'sentence', 'question', 'answer', 'good', 'bad', 'big', 'small', 'old',
                'new', 'beautiful', 'ugly', 'important', 'simple', 'difficult', 'easy', 'fast', 'slow', 'today', 'yesterday', 'tomorrow',
                'now', 'later', 'early', 'always', 'sometimes', 'often', 'rarely', 'never', 'here', 'there', 'above', 'below', 'left',
                'right', 'before', 'after', 'with', 'without', 'for', 'against', 'through', 'until', 'since', 'from', 'to', 'out',
                'of', 'at', 'by', 'about', 'as', 'into', 'like', 'through', 'after', 'over', 'between', 'against', 'during', 'without',
                'before', 'under', 'again', 'further', 'then', 'once', 'here', 'there', 'when', 'where', 'why', 'how', 'all', 'any',
                'both', 'each', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor', 'not', 'only', 'own', 'same', 'so', 'too',
                'very', 'just', 'now', 'also', 'than', 'too', 'very', 'can', 'will', 'just', 'should', 'now', 'hello', 'world', 'house',
                'car', 'child', 'time', 'year', 'day', 'night', 'water', 'eat', 'drink', 'go', 'come', 'see', 'make', 'say', 'good',
                'morning', 'evening', 'name', 'city', 'country', 'person', 'woman', 'man', 'love', 'life', 'work', 'school', 'homework',
                'book', 'read', 'write', 'speak', 'hear', 'understand', 'english', 'language', 'word', 'sentence', 'question', 'answer',
                'good', 'bad', 'big', 'small', 'old', 'new', 'beautiful', 'ugly', 'important', 'simple', 'difficult', 'easy', 'fast',
                'slow', 'today', 'yesterday', 'tomorrow', 'now', 'later', 'early', 'always', 'sometimes', 'often', 'rarely', 'never',
                'here', 'there', 'above', 'below', 'left', 'right', 'before', 'after', 'with', 'without', 'for', 'against', 'through',
                'until', 'since', 'from', 'to', 'out', 'of', 'at', 'by', 'about', 'as', 'into', 'like', 'through', 'after', 'over',
                'between', 'against', 'during', 'without', 'before', 'under', 'again', 'further', 'then', 'once', 'here', 'there',
                'when', 'where', 'why', 'how', 'all', 'any', 'both', 'each', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor',
                'not', 'only', 'own', 'same', 'so', 'too', 'very', 'just', 'now', 'also', 'than', 'too', 'very', 'can', 'will', 'just',
                'should', 'now'
        ];

        $posts = get_posts(array(
                'post_type'      => 'post',
                'posts_per_page' => 5,
                'post_status'    => 'publish',
                'orderby'        => 'date',
                'order'          => 'DESC',
        ));

        $log = [];

        foreach ($posts as $post) {
            $this->append_log("
                Processing post {$post->post_title} with id {$post->ID}
            ");

            $content = $post->post_content . ' ' . $post->post_title;
            $language = $this->detect_language_ratio($content, $german_words, $english_words);

            // Always set the translation group to the post title
            $translation_group = sanitize_title($post->post_title);
            update_post_meta($post->ID, '_wpml_import_translation_group', $translation_group);

            if ($language) {
                update_post_meta($post->ID, '_wpml_import_language_code', $language);
                $log[] = "Post ID {$post->ID} ('{$post->post_title}'): Detected language is {$language}.";

                // Create a translated copy with the opposite language
                $translated_content = $this->translate_content($post->ID, $content, $language, $wpdb);
                if (!$translated_content) continue;

                $this->create_translated_copy($post, $language, $translation_group, $translated_content);
            } else {
                delete_post_meta($post->ID, '_wpml_import_language_code');
                $log[] = "Post ID {$post->ID} ('{$post->post_title}'): No clear language detected.";
            }
        }

        $this->append_log(implode("\n", $log) . "\n\nMigration and translation completed for the last 5 posts.\n");
    }

    // Translate content using TranslatePress dictionary
    private function translate_content($post_id, $content, $source_language, $wpdb) {
        $target_language = ($source_language === 'de') ? 'en' : 'de';

        // Query TranslatePress dictionary for translations
        $query = $wpdb->prepare(
                "SELECT d.original, d.translated
             FROM {$wpdb->prefix}trp_dictionary_de_de_en_us AS d
             JOIN {$wpdb->prefix}trp_original_meta AS om ON om.original_id = d.original_id
             WHERE om.meta_value = %d AND om.meta_key = 'post_parent_id'",
                $post_id
        );

        $translations = $wpdb->get_results($query, ARRAY_A);

        if (empty($translations)) {
            return false; // No translations found
        }

        $original_content = $content;
        $translated_chars = 0;
        $stripped = preg_replace('/<[^>]*>/', '', $content);
        $total_chars = strlen($stripped);

        $this->append_log("
            Content is {$content}
        ");

        // Replace original strings with translations
        foreach ($translations as $translation) {
            $original = $translation['original'];
            $translated = $translation['translated'];

            if (empty($original) || empty($translated)) {
                continue;
            }

            $this->append_log("Trying to replace <{$original}> with <{$translated}>");

            // Count characters in the original string
            $original_length = strlen($original);

            if (preg_match('/(^|[^ ])' . preg_quote($original, '/') . '/', $content)) {
                // Replace $original only if it doesn't have a leading space
                $content = preg_replace('/(^|[^ ])' . preg_quote($original, '/') . '/', '$1' . $translated, $content);
            } else if (preg_match('/(^|[^ ])' . preg_quote($translated, '/') . '/', $content)) {
                // Replace $translated only if it doesn't have a leading space
                $content = preg_replace('/(^|[^ ])' . preg_quote($translated, '/') . '/', '$1' . $original, $content);
            } else {
                continue;
            }

            $translated_chars += $original_length;
        }

        $this->append_log("The translated version is {$content}");

        // Check if at least 70% of characters were translated
        $translation_ratio = ($total_chars > 0) ? ($translated_chars / $total_chars) : 0;

        if ($translation_ratio < 0.7) {
            $this->append_log(
                    "Post ID {$post_id}: Translation ratio ({$translation_ratio}) is below 70%. Using original content.\n"
            );
            return false;
        } else {
            $this->append_log(
                    "Successfully translated content of post {$post_id}: Translation ratio ({$translation_ratio}).\n"
            );
        }

        return $content;
    }

    // Create a translated copy of the post
    private function create_translated_copy($original_post, $original_language, $translation_group, $translated_content) {
        $translated_language = ($original_language === 'de') ? 'en' : 'de';

        // Create the translated post (copy of the original)
        $translated_post_id = wp_insert_post([
                'post_title'   => $original_post->post_title . ' (' . strtoupper($translated_language) . ')',
                'post_content' => $translated_content,
                'post_status'  => 'draft', // Set to draft to avoid publishing duplicates
                'post_type'    => 'post',
                'post_author'  => $original_post->post_author,
                'post_date'    => $original_post->post_date,
        ]);

        if ($translated_post_id) {
            // Set WPML metadata for the translated post
            update_post_meta($translated_post_id, '_wpml_import_language_code', $translated_language);
            update_post_meta($translated_post_id, '_wpml_import_source_language_code', $original_language);
            update_post_meta($translated_post_id, '_wpml_import_translation_group', $translation_group);

            $this->append_log(
                    "Created translated copy of Post ID {$original_post->ID} as Post ID {$translated_post_id} " .
                    "(Language: {$translated_language}, Source: {$original_language}, Group: {$translation_group}).\n"
            );
        } else {
            $this->append_log("Failed to create translated copy for Post ID {$original_post->ID}.\n");
        }
    }

    // Run reversion (last 5 posts)
    public function run_reversion() {
        $posts = get_posts(array(
                'post_type'      => 'post',
                'posts_per_page' => 20, // Fetch 20 to include possible translated copies
                'post_status'    => ['publish', 'draft'],
                'orderby'        => 'date',
                'order'          => 'DESC',
        ));

        $log = [];

        foreach ($posts as $post) {
            // Remove metadata for all posts
            delete_post_meta($post->ID, '_wpml_import_language_code');
            delete_post_meta($post->ID, '_wpml_import_source_language_code');
            delete_post_meta($post->ID, '_wpml_import_translation_group');

            // Delete translated copies (posts with " (EN)" or " (DE)" in the title)
            if (preg_match('/ \(EN\)$| \(DE\)$/', $post->post_title)) {
                wp_delete_post($post->ID, true);
                $log[] = "Deleted translated copy: Post ID {$post->ID} ('{$post->post_title}').";
            } else {
                $log[] = "Post ID {$post->ID} ('{$post->post_title}'): Reverted metadata.";
            }
        }

        $this->append_log(implode("\n", $log) . "\n\nReversion completed for the last 5 posts and their translations.\n");
    }

    // Detect language ratio
    private function detect_language_ratio($content, $german_words, $english_words) {
        $content = strtolower($content);
        $words = preg_split('/\W+/', $content, -1, PREG_SPLIT_NO_EMPTY);

        $german_count = 0;
        $english_count = 0;

        foreach ($words as $word) {
            if (in_array($word, $german_words)) {
                $german_count++;
            } elseif (in_array($word, $english_words)) {
                $english_count++;
            }
        }

        $total = $german_count + $english_count;
        if ($total === 0) {
            return null; // No matches
        }

        $german_ratio = $german_count / $total;
        $english_ratio = $english_count / $total;

        if ($german_ratio > $english_ratio && $german_ratio >= 0.6) {
            return 'de';
        } elseif ($english_ratio > $german_ratio && $english_ratio >= 0.6) {
            return 'en';
        } else {
            return null; // No clear majority
        }
    }

    // Append to log
    private function append_log($message) {
        $log_file = plugin_dir_path(__FILE__) . 'wpml-migration-log.txt';
        $message = date("Y-m-d h:s") . " " . $message;
        file_put_contents($log_file, $message, FILE_APPEND);
    }

    // Get log
    private function get_log() {
        $log_file = plugin_dir_path(__FILE__) . 'wpml-migration-log.txt';
        return file_exists($log_file) ? file_get_contents($log_file) : '';
    }

    // Clear log
    public function clear_log() {
        check_ajax_referer('wpml_clear_log', '_wpnonce');
        $log_file = plugin_dir_path(__FILE__) . 'wpml-migration-log.txt';
        file_put_contents($log_file, '');
        wp_send_json_success();
    }
}

new WPML_Migration_Tool();
