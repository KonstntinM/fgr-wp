<?php
function fgr_film_import_menu() {
    add_menu_page(
        'Berlinale Film Import', // page title
        'Berlinale Film Import', // menu title
        'manage_options', // capability
        'berlinale-film-import', // menu slug
        'fgr_film_import_tool' // function
    );
}

add_action('admin_menu', 'fgr_film_import_menu');

function fgr_film_import_tool() {
var_dump("hey");
    /* Disable to prevent accidental usage
     * if (isset($_POST['import'])) {
       import_from_berlinale_string();
   }

       if (isset($_POST['adjust_legacy'])) {

       }*/



    // Display the form
    echo '<h1>Berlinale Film Import</h1>';
    echo '<p>Nutze das Formular, um Filme im JSON Format zu importieren.</p>';
    echo '<form method="post">';
    //echo '<textarea name="text_input" rows="10" cols="30"></textarea><br>';
    echo '<textarea name="text_input" rows="10" cols="30" value="Dummy" style="display: none;"></textarea><br>';
    echo '<input type="submit" name="import" value="Import 2026">';
    echo '</form>';
    echo '<form method="post">';
    //echo '<textarea name="text_input" rows="10" cols="30"></textarea><br>';
    echo '<textarea name="text_input" rows="10" cols="30" value="Dummy" style="display: none;"></textarea><br>';
    echo '<input type="submit" name="adjust_legacy" value="Importierte Posts Anpassen">';
    echo '</form>';
}

function adjust_legacy_posts () {
    // Loop over all posts
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => -1
    );
    $query = new WP_Query($args);
    while ($query->have_posts()) {
        $query->the_post();
        
        try {
            // Get the first referenced image in the post content
            $content = get_the_content();
            $pattern = '/<img.*?src=["\'](.*?)["\'].*?>/i';
            preg_match($pattern, $content, $matches);
            $first_image = $matches[1];

            // Set the image (that comes from an external source) as the featured image
            if ($first_image) {
                $upload_dir = wp_upload_dir();
                $image_data = file_get_contents($first_image);
                $filename = basename($first_image);
                if (wp_mkdir_p($upload_dir['path'])) {
                    $file = $upload_dir['path'] . '/' . $filename;
                } else {
                    $file = $upload_dir['basedir'] . '/' . $filename;
                }
                file_put_contents($file, $image_data);
                $wp_filetype = wp_check_filetype($filename, null);
                $attachment = array(
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => sanitize_file_name($filename),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );
                $attach_id = wp_insert_attachment($attachment, $file, get_the_ID());
                set_post_thumbnail(get_the_ID(), $attach_id);
            }
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }

        // Remove all categories from the post
        $categories = get_the_category(get_the_ID());
        foreach ($categories as $category) {
            wp_remove_object_terms(get_the_ID(), $category->term_id, 'category');
        }
    }
}

function import_from_berlinale_string() {
    var_dump("from berlinale strings");
    // 1. DATA PREPARATION
    // $text_input = sanitize_textarea_field($_POST['text_input']);
    $text_input = '
    [
  {
    "link": "https://www.berlinale.de/de/2026/programm/202612319.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Als sein bester Freund sich auf einer Schulbusfahrt entscheidet, woanders zu sitzen, bricht in Agastya ein stiller Sturm aus unterdrückten Emotionen, Identitätsfragen und Schuldgefühlen los.",
    "description_en": "On a school bus ride that feels both endless and fleeting, Agastya wrestles with identity, guilt and unspoken emotions when his best friend chooses to sit elsewhere.",
    "director": "Amay Mehrishi | mit Advay Pradhan, Arsh Victor Suri, Anvishaa Tyagi, Ira Chitalia, Supriya Sawant",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202612319_1_RWD_1380.jpg",
    "title_original": "Abracadabra",
    "title_de_en": "",
    "link_en": "https://www.berlinale.de/en/2026/programme/202612319.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202609628.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Im dürren Hinterland Brasiliens spielen Mädchen miteinander. Sie wissen um die schwierige Vergangenheit ihrer Mütter und hegen kühne Zukunftsträume. Wo Männer gegenüber Frauen noch als Riesen gelten, überschreiten sie die Schwelle von Kindheit zu Jugend.",
    "description_en": "In the arid Brazilian hinterland, girls play poised between their mothers’ difficult pasts and fantastic dreams for the future. In a place where men are still seen as giants compared to women, the girls cross the threshold from childhood into adolescence.",
    "director": "Eliza Capai",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202609628_1_RWD_1380.jpg",
    "title_original": "A Fabulosa Máquina do Tempo",
    "title_de_en": "The Fabulous Time Machine | Die fabelhafte Zeitmaschine",
    "link_en": "https://www.berlinale.de/en/2026/programme/202609628.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202616575.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "Zwischen den Fronten der Scheidung ihrer Eltern werden Nina und Eli aufgerieben zwischen Loyalität, Wut und dem Wunsch, gesehen zu werden. In sich spiegelnden Perspektiven zeigt ihre Geschichte, wie Liebe zerbrechen und dennoch ihren Weg finden kann.",
    "description_en": "Caught in the crossfire of their parents’ divorce, Nina and Eli are torn between loyalty, anger and a longing to be seen. Through their mirrored perspectives the same story unfolds, revealing how love can fracture and still find its way home.",
    "director": "Mees Peijnenburg | mit Finn Vogels, Celeste Holsheimer, Carice van Houten, Pieter Embrechts",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202616575_1_RWD_1380.jpg",
    "title_original": "A Family",
    "title_de_en": "",
    "link_en": "https://www.berlinale.de/en/2026/programme/202616575.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202607400.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "Am Stadtrand von Lima kümmert sich der elfjährige Chito um Brieftauben, die sein Bruder für Drogengeschäfte nutzt. Chitos Kindheit zerbricht an Gewalt, doch Trauer und eine zarte Geste der Menschlichkeit öffnen ihm den Weg in ein anderes Leben.",
    "description_en": "In the outskirts of Lima, eleven-year-old Chito cares for the carrier pigeons used in his brother’s drug trade. After violence shatters his childhood, grief and a tender gesture of humanity open the possibility of another destiny.",
    "director": "Roddy Dextre | mit Ransés Naranjo Franco, Gabriel Merino Dorival, Kenyi Farfán Baique, Santiago Solórzano Zevallos",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202607400_1_RWD_1380.jpg",
    "title_original": "Allá en el cielo",
    "title_de_en": "Nobody Knows the World",
    "link_en": "https://www.berlinale.de/en/2026/programme/202607400.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202607554.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Nachdem er versehentlich zwei rechte Schuhe gekauft hat, macht sich der zehnjährige Filip auf den Weg übers Land, um den fehlenden linken Schuh zu finden. Eine überraschende Reise voller Mut, Freundschaft und Selbstentdeckung.",
    "description_en": "After mistakenly buying two right-footed shoes, ten-year-old Filip sets off across the countryside to find the missing left one. An unexpected journey of courage, friendship and self-discovery.",
    "director": "Paul Negoescu | mit Matei Donciu, Johanna Mild, Călin Petru, Sofia Marinescu, Marin Grigore",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202607554_1_RWD_1380.jpg",
    "title_original": "Atlasul universului",
    "title_de_en": "Atlas of the Universe | Atlas des Universums",
    "link_en": "https://www.berlinale.de/en/2026/programme/202607554.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202616170.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Als an einer Landstraße im Dschungel eine Laterne aufleuchtet, dreht eine Gruppe von Insekten völlig durch. Das kommt den hungrigen Fledermäusen aus einer Höhle in der Nähe sehr gelegen.",
    "description_en": "When a streetlamp lights up on a country road in the jungle, a group of insects goes crazy – which proves to be handy for the hungry bats in a nearby cave.",
    "director": "Lena von Döhren",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202616170_1_RWD_1380.jpg",
    "title_original": "Bats & Bugs",
    "title_de_en": "Nachtschwärmer",
    "link_en": "https://www.berlinale.de/en/2026/programme/202616170.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202606248.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "Die nerdige Luthando erwartet ein gewöhnliches Schuljahr an einem renommierten Internat, das sie dank eines Stipendiums besucht. Bis eine neue Mitschülerin unterdrückte Sehnsüchte weckt, die Luthandos Selbstbild und Beziehungen auf die Probe stellen.",
    "description_en": "Adorkable Luthando is on track for an ordinary year at the prestigious boarding school she attends on scholarship – until the arrival of a new girl in her class ignites Luthando’s suppressed desires and threatens her self-image and relationships.",
    "director": "Sandulela Asanda | mit Esihle Ndleleni, Muadi Ilung",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202606248_1_RWD_1380.jpg",
    "title_original": "Black Burns Fast",
    "title_de_en": "",
    "link_en": "https://www.berlinale.de/en/2026/programme/202606248.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202612404.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "Als Camille, eine Jugendliche mit Down-Syndrom, an einem Vortanzen teilnimmt, spürt ihre Schwester Agathe die stille Grausamkeit von Vorurteilen – und die bedingungslose Liebe, die dagegen aufbegehrt.",
    "description_en": "When Camille, a teenager with Down’s syndrome, auditions for a dance troupe, her sister Agathe discovers the quiet cruelty of prejudice – and the fierce love that fights back.",
    "director": "Zoé Pelchat | mit Florence Saint-Yves, Anne Florence, Stéphane Jacques, Pascale Desrochers, Alexandre Bergeron",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202612404_1_RWD_1380.jpg",
    "title_original": "C’est ma sœur",
    "title_de_en": "That’s My Sister",
    "link_en": "https://www.berlinale.de/en/2026/programme/202612404.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202604753.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "La Maestra und Paula sind unzertrennliche Freundinnen und die besten Schwimmerinnen ihres Teams. Ein Vorfall auf einer Party zwingt sie, sich zwischen Schweigen und Sprechen zu entscheiden – eine Belastungsprobe für ihre Freundschaft.",
    "description_en": "La Maestra and Paula are inseparable friends and the strongest swimmers on their team, until an incident at a party forces them to choose between silence and speaking out, testing the limits of their friendship.",
    "director": "Fernanda Tovar | mit Rocio Guzmán, Darana Álvarez, Tatsumi Milori, Tomás García Agraz, Mónica del Carmen",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202604753_1_RWD_1380.jpg",
    "title_original": "Chicas Tristes",
    "title_de_en": "Sad Girlz",
    "link_en": "https://www.berlinale.de/en/2026/programme/202604753.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202612846.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "Paco, ein Teenager aus Guadalajara, erkundet seine Identität durch die Freundschaft mit Andrea und seine Anziehung zu Mario. Während der Vorbereitungen auf die Feierlichkeiten zum Patronatsfest stellt dies die Beziehung zu seiner Großmutter auf die Probe.",
    "description_en": "Paco, a teenager from Guadalajara, explores his identity through his friendship with Andrea and his attraction to Mario. This tests his bond with his grandmother as he prepares for the feast of the patron saint.",
    "director": "Edgar Adrián | mit Ricardo Martínez, María Rojo, Alise Cortéz, Zahid Estrada, Antonio Venegas",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202612846_1_RWD_1380.jpg",
    "title_original": "Cuando llegue a casa",
    "title_de_en": "When I Get Home",
    "link_en": "https://www.berlinale.de/en/2026/programme/202612846.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202607204.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "Nach dem Verschwinden ihres Lehrers steht die schwangere Schülerin Yun-ji allein da. Um eine Abtreibung zu finanzieren, stiehlt sie die Ersparnisse ihrer Mitbewohnerin Kyung-sun. Ein Film über die Rechte am eigenen Körper, über Gesellschaft und Gemeinschaft.",
    "description_en": "Following the disappearance of her teacher, the pregnant pupil Yun-ji is left all alone. To pay for an abortion, she steals the savings of her roommate, Kyung-sun. A film about the right over one’s own body, about society and community.",
    "director": "Yoo Jaein | mit Subin Sim, Jiwon Lee, Sun Jang",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202607204_1_RWD_1380.jpg",
    "title_original": "En Route To",
    "title_de_en": "",
    "link_en": "https://www.berlinale.de/en/2026/programme/202607204.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202613220.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Yios, ein einsamer mythischer Junge, dessen Kopf wie die Sonne strahlt, hat Schwierigkeiten, hoch oben in den Wolken Freund*innen zu finden. Als ein Streich ihn auf die Erde befördert, stellt eine unerwartete Begegnung seine Selbstwahrnehmung infrage.",
    "description_en": "Yios, a lonely mythical boy whose head shines like the sun, struggles to make friends in the clouds. After an unfortunate prank sends him down to Earth, an unexpected encounter challenges the way he sees himself.",
    "director": "Andrea Szelesová",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202613220_1_RWD_1380.jpg",
    "title_original": "En, ten, týky!",
    "title_de_en": "Eeny, Meeny, Miny, Moe! | Ene, mene, muh!",
    "link_en": "https://www.berlinale.de/en/2026/programme/202613220.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202613780.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Nach dem Verlust seines besten Freundes Poupelle gerät der junge Lubicchi in ein geheimnisvolles Reich, wo eine Turmuhr auf 11:59 Uhr stehen geblieben ist. Um nach Hause zurückzukehren, muss er die Uhr wieder in Gang setzen – und neuen Mut fassen.",
    "description_en": "After losing his friend Poupelle, young Lubicchi wanders into a mysterious realm where a tower clock is frozen at 11:59. To return home, he must restart it – and find the courage to believe again.",
    "director": "Hirota Yusuke | mit Nagase Yuzuna, Megumi, Kubota Masataka",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202613780_1_RWD_1380.jpg",
    "title_original": "Entotsumachi no Poupelle – Yakusoku no Tokeidai",
    "title_de_en": "Chimney Town: Frozen in Time | Stadt der Schornsteine: In der Zeit gefangen",
    "link_en": "https://www.berlinale.de/en/2026/programme/202613780.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202609138.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Gugu, ein fast zwölfjähriger Junge mit großen Fußballträumen, wächst in der liebevollen Obhut seiner Großmutter Dilma auf. Als sie zunehmend gebrechlich wird, kämpft er darum, den Ort zu bewahren, an dem er frei und ganz er selbst sein kann.",
    "description_en": "Gugu, an almost-twelve-year-old boy with football dreams, is raised in the accepting care of his grandmother, Dilma. As she becomes increasingly frail and their world shifts, he struggles to protect the place where he is free to be who he is.",
    "director": "Allan Deberton | mit Yuri Gomes, Teca Pereira, Lázaro Ramos, Carlos Francisco, Georgina Castro",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202609138_1_RWD_1380.jpg",
    "title_original": "Feito Pipa",
    "title_de_en": "Gugu’s World | Gugus Welt",
    "link_en": "https://www.berlinale.de/en/2026/programme/202609138.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202603120.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Die zehnjährige Rabia will wissen, warum ihre Schule plötzlich schließt. Im Dorf wird wild gemunkelt, und die Behörden helfen nicht weiter. Mutig macht sich Rabia auf die Suche nach der Wahrheit und stößt auf Aberglaube, Machtmissbrauch und Schweigen.",
    "description_en": "Ten-year-old Rabia wants to know why her school has suddenly closed. Rumours are rife in the village and the authorities are no help. Courageously setting out to find the truth, Rabia navigates rural superstitions, local corruption and a wall of silence.",
    "director": "Seemab Gul | mit Nazualiya Arsalan, Samina Seher, Adnan Shah Tipu, Vajdaan Shah, Muhammad Zaman",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202603120_1_RWD_1380.jpg",
    "title_original": "Ghost School",
    "title_de_en": "Geisterschule",
    "link_en": "https://www.berlinale.de/en/2026/programme/202603120.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202604241.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "Ein Wellensittich findet sich in einem luxuriösen Wellnesshotel wieder, das gestressten Vögeln Ruhe und Entspannung verspricht. Doch zwischen plätschernden Brunnen und wohltuenden Massagen sehnt er sich nur zurück in die Sicherheit seines Käfigs.",
    "description_en": "A budgie finds itself in a luxurious wellness hotel that promises stressed birds peace and relaxation. But between the lulling sound of the fountains and soothing massages, it longs only to return to the safety of its cage.",
    "director": "Merlin Flügel",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202604241_1_RWD_1380.jpg",
    "title_original": "Hotel Oblique",
    "title_de_en": "",
    "link_en": "https://www.berlinale.de/en/2026/programme/202604241.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202612227.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Mirna nimmt mit ihrem Vater den Bus von ihrem Dorf in die Stadt Niš, um dort am nationalen Mathewettbewerb teilzunehmen. Es ist ein wichtiger Tag: Ein Preis könnte ihr einen Platz an einer renommierten Schule verschaffen und eine bessere Zukunft eröffnen.",
    "description_en": "Mirna and her father take the bus from their village to the city of Niš for her to participate in the national mathematics competition. It is a big day: winning an award could gain her a place at a prestigious school and pave the way to a better future.",
    "director": "Jelica Jerinić | mit Goran Bogdan, Maša Radusin, Milica Janevski, Milica Trifunović, Aleksandar Milojević",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202612227_1_RWD_1380.jpg",
    "title_original": "Imaginarni brojevi",
    "title_de_en": "Imaginary Numbers | Imaginäre Zahlen",
    "link_en": "https://www.berlinale.de/en/2026/programme/202612227.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202602956.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "Im Herzen der kolumbianischen Wüstenregion La Alta Guajira hört die 15-jährige Weinshi, ein Wayuu-Mädchen, deren Name „Zeit“ bedeutet, in ihren Träumen einen Ruf ihrer Vorfahren: Die Erde muss durch den heiligen Tanz ihres Volkes, den Yonna, geheilt werden.",
    "description_en": "In the heart of the Alta Guajira, 15-year-old Weinshi, a girl from the Wayuu people whose name means “Time”, begins to hear an ancestral calling in her dreams: the earth must be healed through her people’s sacred dance, the Yonna.",
    "director": "Luzbeidy Monterrosa Atencio | mit Luznery Epieyu, Margarita Barrios, Jose Vicente",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202602956_1_RWD_1380.jpg",
    "title_original": "Jülapüin Yonna",
    "title_de_en": "The Dream of Dance",
    "link_en": "https://www.berlinale.de/en/2026/programme/202602956.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202612014.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Ein kleiner Junge sucht nach etwas Aufregenderem, als ein Bett aufzubauen. Ein schelmisches Feuerwesen lockt ihn in eine magische Pilzwelt, in der alles möglich scheint und allerlei Abenteuer auf ihn warten – fernab der Kontrolle seines Vaters.",
    "description_en": "A little boy, bored with building a bed, seeks something more exciting. A mischievous fire creature lures him into a magical mushroom world, where anything is possible and adventure awaits – beyond his father’s reach.",
    "director": "Janka Feiner",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202612014_1_RWD_1380.jpg",
    "title_original": "Lángbogár a zsebemben",
    "title_de_en": "Fire in My Pocket | Feuer in der Hosentasche",
    "link_en": "https://www.berlinale.de/en/2026/programme/202612014.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202607900.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "An einem ruhigen Spätsommertag machen sich Alessandro und Flavio auf die Suche nach den letzten Geheimnissen ihrer Stadt. Auf einem großen Kirschbaum warten sie auf die Nacht. Ein Bier mit Freund*innen, Mopeds auf dem Weg nach Hause und ein Abschied.",
    "description_en": "On a quiet late-summer day, Alessandro and Flavio set out in search of the last secrets of their city. Sitting on the branch of a large cherry tree, they wait for the night. A beer with friends, mopeds heading home, and a farewell.",
    "director": "Emanuele Tresca | mit Alessandro Nicola Bernardo, Flavio Condemi",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202607900_1_RWD_1380.jpg",
    "title_original": "Mambo Kids",
    "title_de_en": "",
    "link_en": "https://www.berlinale.de/en/2026/programme/202607900.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202609924.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "Ricardo ist ein junger Punk aus der Vorstadt. Plötzlich im Besitz alkoholbefeuerter Superkräfte, macht er sich daran, die Welt zu verändern. Doch ein brutaler Fehler entfacht einen internationalen Konflikt – und er steht im Zentrum des Sturms.",
    "description_en": "After gaining alcohol-fuelled superpowers, Ricardo, a young punk from the outskirts, sets out to change society. But one brutal mistake ignites an international conflict, with him at the centre of the storm.",
    "director": "Diego (Mapache) Fuentes | mit Ramón Gávez, Diego Bravo, Antonia McCarthy, Rosa Peñaloza, Rodrigo Lisboa",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202609924_1_RWD_1380.jpg",
    "title_original": "Matapanki",
    "title_de_en": "",
    "link_en": "https://www.berlinale.de/en/2026/programme/202609924.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202601748.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "Nach staatlichen Angriffen auf die Proteste im Iran beginnen Zivilist*innen, diese vom Fenster aus zu dokumentieren. Als eine Frau beim Filmen erschossen wird, stellt sich eine Filmstudentin die Frage, ob eine Revolution hinter Fenstern beginnen kann.",
    "description_en": "Following crackdowns on protests in Iran, civilians begin documenting the unrest from behind windows. When a woman is shot while recording, a film student writes her a letter raising the question: Can a revolution emerge from behind windows?",
    "director": "Mehraneh Salimian, Amin Pakparvar",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202601748_1_RWD_1380.jpg",
    "title_original": "Memories of a Window",
    "title_de_en": "",
    "link_en": "https://www.berlinale.de/en/2026/programme/202601748.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202612564.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Rong Chen möchte gerne ihren Platz in der Blockflötengruppe behalten, Shao Yu will das Spielen aufgeben. Sie fragt ihn nach Tipps, und durch das gemeinsame Üben verbessern beide nicht nur ihr Können, sie lernen auch noch viel Wichtigeres voneinander.",
    "description_en": "Rong Chen would like to keep her place in the recorder group, Shao Yu wants to give up playing. She asks him for tips, and by practicing together, they not only improve their skills, but also learn something much more important from each other.",
    "director": "Zhuang Rong Zuo | mit Liu Rong Chen, Wu Shao Yu",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202612564_1_RWD_1380.jpg",
    "title_original": "Ni chui do wo chui si",
    "title_de_en": "Tutti",
    "link_en": "https://www.berlinale.de/en/2026/programme/202612564.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202612224.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "Die 17-jährige Ni’er fühlt sich zwischen ihrem monotonen Job an einer Tankstelle und den Erwartungen ihrer Familie gefangen. Eine flüchtige Begegnung mit einer Truckerin, die Freiheit und Selbstbestimmung ausstrahlt, löst eine stille Revolution in ihr aus.",
    "description_en": "Seventeen-year-old Ni’er feels trapped between her monotonous daily grind at a petrol station and the weight of family expectations. A fleeting encounter with a female truck driver radiating freedom and self-determination sparks a quiet revolution within her.",
    "director": "Tan Yucheng | mit Wang Zhiyun, Hou Ruining",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202612224_1_RWD_1380.jpg",
    "title_original": "Ni’er",
    "title_de_en": "The Girl",
    "link_en": "https://www.berlinale.de/en/2026/programme/202612224.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202611243.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "Nach dem Tod ihrer besten Freundin verbirgt Liz ihr wahres Ich, bis ein Wochenendtrip verbotenes Begehren weckt. Die Folgen sind Paranoia und Gewalt – Liz’ Geheimnis entfaltet tödliche Kraft.",
    "description_en": "After her girlfriend’s death, Liz hides her true self until a weekend trip with friends awakens forbidden desires. The result is paranoia and violence that consumes the group as Liz’s secret unleashes a deadly force.",
    "director": "Victoria Linares Villegas | mit Cecile van Welie, Gabriela Cortés, Camila Issa, Camila Santana, Mariela Guerrero",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202611243_1_RWD_1380.jpg",
    "title_original": "No Salgas",
    "title_de_en": "Don’t Come Out",
    "link_en": "https://www.berlinale.de/en/2026/programme/202611243.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202610128.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Aus dem komfortablen Stadtleben ins Heimatdorf seiner Familie verbannt, trifft Mivan auf seine verbitterte Tante, freundet sich mit einem Pferd an, erlebt wilde Abenteuer mit den Nachbarskindern, entdeckt die Schönheit des Lebens und findet ungeahnten Mut.",
    "description_en": "Exiled from city comforts to his ancestral village, Mivan meets his embittered aunt, befriends a horse and joins the local kids on wild adventures, discovering the beauty of life and a courage he never knew he had.",
    "director": "Rima Das | mit Bhuman Bhargav Das, Sukanya Boruah, Mrinmoy Das",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202610128_1_RWD_1380.jpg",
    "title_original": "Not a Hero",
    "title_de_en": "Kein Held",
    "link_en": "https://www.berlinale.de/en/2026/programme/202610128.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202613162.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Als er abends nicht einschlafen kann, beginnt ein Junge, über seinen Platz in der Welt nachzudenken. Wenn die Erde bloß eine kleine, durchs All fliegende Murmel ist und er nur ein Kind unter vielen, welche Bedeutung hat dann seine Existenz?",
    "description_en": "When a boy has trouble falling asleep at night, he begins to ponder his place in the world. If the Earth is just a small marble flying through space, and he is only one child among many, what is the meaning of his existence?",
    "director": "Jonas Taul | mit Harriet Toompere",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202613162_1_RWD_1380.jpg",
    "title_original": "Öömõtted",
    "title_de_en": "A Serious Thought | Nachtgedanken",
    "link_en": "https://www.berlinale.de/en/2026/programme/202613162.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202606578.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Papaya, ein winziger, flugbegeisterter Samen im Amazonas-Regenwald, möchte am liebsten immer in Bewegung bleiben, um ja nicht zu keimen. Doch schließlich entdeckt Papaya die Kraft ihrer Wurzeln und löst damit eine Revolution aus, die ihre Welt verändert.",
    "description_en": "Papaya, a tiny seed in the Amazon rainforest who is passionate about flying, must keep moving to avoid taking root. But when she discovers the power of her roots, it triggers a revolution that transforms her world and fulfils her dreams in an unexpected way.",
    "director": "Priscilla Kellen",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202606578_1_RWD_1380.jpg",
    "title_original": "Papaya",
    "title_de_en": "",
    "link_en": "https://www.berlinale.de/en/2026/programme/202606578.html"
  },
  {
    "link": "https://www.berlinale.de/de/2026/programm/202607049.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "Vier versklavte Mädchen träumen von der Freiheit. Durch unerwartete Ereignisse in Lebensgefahr gebracht, beschließen sie zu fliehen. Ihre Gebieterinnen entdecken den Plan – und bestehen überraschenderweise darauf, sie zu begleiten.",
    "description_en": "Four enslaved girls dream of freedom. When a turn of events puts their lives at risk, they decide to run away. Their mistresses discover the plan – and to the girls surprise insist on going with them.",
    "director": "Karen Suzane | mit Ágatha Marinho, Alana Cabral, Dhara Lopes",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202607049_1_RWD_1380.jpg",
    "title_original": "Quatro Meninas",
    "title_de_en": "Four Girls",
    "link_en": "https://www.berlinale.de/en/2026/programme/202607049.html"
  },
{
    "link": "https://www.berlinale.de/de/2026/programm/202602593.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Der pferdebegeisterte Jaleal wächst in den West Yorkshire Moors auf und betreibt dort den uralten, aus Südasien stammenden Reitsport Neza Bazi. Über drei Sommer gedreht, entstaubt der Film traditionelle Erzählungen über das Empire, Migration und Zugehörigkeit.",
    "description_en": "Horse-obsessed Jaleal comes of age on the West Yorkshire moors while carrying forward the ancient cavalry sport of Neza Bazi. Filmed over three summers, this joyful portrait blows the dust off traditional tales of Empire, migration and belonging.",
    "director": "Roopa Gogineni, Farhaan Mumtaz",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202602593_1_RWD_1380.jpg",
    "title_original": "Riding Time",
    "title_de_en": "Reitend",
    "link_en": "https://www.berlinale.de/en/2026/programme/202602593.html"
  },
{
    "link": "https://www.berlinale.de/de/2026/programm/202602605.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "Li Yans Leben ist so streng geregelt wie die Produktionslinie in der örtlichen Geflügelfabrik. Ihre Oma will ihr dort einen Job besorgen, doch Li Yan lehnt ab: Besessen von den Mysterien von Schöpfung und Leben versucht sie lieber, selbst ein Ei auszubrüten.",
    "description_en": "Li Yan’s life is as regimented as the production lines in the local poultry factory. Her grandmother is determined to get her a job there, but Li Yan refuses. Obsessed with the mystery of life and creation, she secretly attempts to hatch a stolen egg.",
    "director": "Wang Beidi | mit Lv Jiaxin, Li Li, Xu Qinghe",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202602605_1_RWD_1380.jpg",
    "title_original": "Scorching",
    "title_de_en": "",
    "link_en": "https://www.berlinale.de/en/2026/programme/202602605.html"
  },
{
    "link": "https://www.berlinale.de/de/2026/programm/202614883.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Seoul, 1989: Jung-min möchte ein Schnelllese-Wunderkind werden, genau wie Dong-hyun – der coolste Typ der Stadt, der ein ganzes Buch in nur 60 Sekunden lesen kann. Aber die Dinge laufen nicht ganz so, wie sie es sich vorgestellt hat.",
    "description_en": "Seoul, 1989. Jung-min wants to become a speed-reading prodigy, just like Dong-hyun – the coolest guy in town who can finish an entire book in just 60 seconds! But things do not go exactly as she expected.",
    "director": "Oh Jiin | mit Kim Gyuna, Lee Kyoung Hoon, Hong Sungchoon, Leem Seung-Min, Ban Hae Young",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202614883_1_RWD_1380.jpg",
    "title_original": "Speedy!",
    "title_de_en": "",
    "link_en": "https://www.berlinale.de/en/2026/programme/202614883.html"
  },
{
    "link": "https://www.berlinale.de/de/2026/programm/202601583.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "In einer kleinen Dorfschule verwandelt ein Lehrer die Tafel des Klassenzimmers manchmal mithilfe eines weißen Tuches. Als es für eine Bestattungszeremonie benötigt wird, erheben die Kinder Einspruch – und fordern das ein, was ihnen versprochen wurde.",
    "description_en": "In a small village classroom, a teacher often transforms the blackboard with a simple white sheet. When it is taken for a burial rite, the children refuse to let their moment slip away – and set out to reclaim what they were promised.",
    "director": "Navroz Shaban | mit Rewan Nizar",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202601583_1_RWD_1380.jpg",
    "title_original": "Spî",
    "title_de_en": "White | Weiß",
    "link_en": "https://www.berlinale.de/en/2026/programme/202601583.html"
  },
{
    "link": "https://www.berlinale.de/de/2026/programm/202609010.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "Als wäre der Kampf gegen Krebs nicht schon hart genug, melden die Eltern der 17-jährigen Ivy sie für ein Feriencamp an, das Ivy nur „Chemo-Camp“ nennt. Entgegen ihrer Erwartungen findet sie dort Freund*innen und erlebt einen Sommer, den sie nie vergessen wird.",
    "description_en": "As if conquering cancer were not hard enough, 17-year-old Ivy’s parents sign her up to spend the summer at what she calls “chemo camp”. Once there, she unexpectedly manages to find friends in a group of misfits and has a summer she will never forget.",
    "director": "George Jaques | mit Bella Ramsey, Neil Patrick Harris",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202609010_1_RWD_1380.jpg",
    "title_original": "Sunny Dancer",
    "title_de_en": "",
    "link_en": "https://www.berlinale.de/en/2026/programme/202609010.html"
  },
{
    "link": "https://www.berlinale.de/de/2026/programm/202616239.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Als sich Bianca an einem heißen Sommertag von ihren Eltern übersehen fühlt, hilft ihr die unerwartete Begegnung mit ihrer Lieblingsschauspielerin Billie King, zu sich zu finden. Eine zarte, fantasievolle Geschichte über Träume und einen eigenen Platz im Leben.",
    "description_en": "On a sweltering summer day, Bianca feels unseen by her parents. An unexpected encounter with her favourite female actor, Billie King, helps her to find herself. A tender, imaginative tale about dreams and carving out a space for yourself in life.",
    "director": "Frederike Migom | mit Lisa Vanhemelrijck, Laurence Roothooft, Sachli Gholamalizad, Lewis Hannes, Lewis Gérard",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202616239_1_RWD_1380.jpg",
    "title_original": "Tegenwoordig heet iedereen Sorry",
    "title_de_en": "Everyone’s Sorry Nowadays | Heute heißen alle Sorry",
    "link_en": "https://www.berlinale.de/en/2026/programme/202616239.html"
  },
{
    "link": "https://www.berlinale.de/de/2026/programm/202602524.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "Am Stadtrand von Berlin driftet der 16-jährige Ilay durch den Sommer, während eine Palliativpflegerin sich um seine schwer kranke Mutter kümmert.",
    "description_en": "Sixteen-year-old Ilay drifts through the summer on the outskirts of Berlin while a palliative nurse accompanies his ailing mother during her last days.",
    "director": "Saša Vajda | mit Mohammed Yassin Ben Majdouba, Flor Prieto Catemaxca, Mahira Hakberdieva, Safet Bajraj, Shanthi Philipp",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202602524_1_RWD_1380.jpg",
    "title_original": "The Lights, They Fall",
    "title_de_en": "",
    "link_en": "https://www.berlinale.de/en/2026/programme/202602524.html"
  },
{
    "link": "https://www.berlinale.de/de/2026/programm/202610894.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "Grace wächst mit zwei Kulturen auf. Sie hat eine weiße britische Mutter und einen Schwarzen jamaikanischen Vater. Mit zunehmendem Alter entwickelt sie ein Bewusstsein für die eigene Identität und lernt, als junge Frau zu leben, ganz Schwarz, ganz weiß.",
    "description_en": "Grace is born of dual heritage: she has a white British mother and a Black Jamaican father. As she reaches her early teens, Grace takes her identity into her own hands and begins to navigate how to exist as a young woman, fully Black, fully white.",
    "director": "Fenn O Meally | mit Anaya Thorley, Tyrelle Boyce, David Gyasi, Lucy Phelps, Thea Butler",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202610894_1_RWD_1380.jpg",
    "title_original": "The Thread",
    "title_de_en": "",
    "link_en": "https://www.berlinale.de/en/2026/programme/202610894.html"
  },
{
    "link": "https://www.berlinale.de/de/2026/programm/202613958.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Als ihr Goldfisch stirbt, macht sich die sechsjährige Feifei in einem kleinen walisischen Küstenort auf die Suche nach Antworten – und deutet ein Volksmärchen aus der Heimat ihrer Mutter auf ganz eigene Weise um.",
    "description_en": "When her goldfish dies, six-year-old Feifei sets off through a small Welsh seaside town in search of answers. Her journey leads her to reimagine a folktale from her mother’s homeland – on her own terms.",
    "director": "Jian Luo | mit Wang Kexin, Jessica Dong",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202613958_1_RWD_1380.jpg",
    "title_original": "Under the Wave off Little Dragon",
    "title_de_en": "Unter der Welle vor Little Dragon",
    "link_en": "https://www.berlinale.de/en/2026/programme/202613958.html"
  },
{
    "link": "https://www.berlinale.de/de/2026/programm/202611958.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Ein stiller Junge und ein leise trauernder Witwer, der ehrenamtlich an dessen Schule arbeitet, entdecken einen magischen Stift und ein Tagebuch. Diese ermöglichen es ihnen, einander mit hörendem Herzen zu verstehen.",
    "description_en": "A quiet boy and a silently grieving widower who volunteers at the boy’s school discover a magical pen and journal that enable them to hear each other with a listening heart.",
    "director": "Daniel Neiden | mit Bruce Vilanch, Parker Allana Hughes",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202611958_1_RWD_1380.jpg",
    "title_original": "Whale 52 – Suite for Man, Boy, and Whale",
    "title_de_en": "Wal 52 – Suite für Mann, Junge und Wal",
    "link_en": "https://www.berlinale.de/en/2026/programme/202611958.html"
  },
{
    "link": "https://www.berlinale.de/de/2026/programm/202603286.html",
    "title": "",
    "title_de": "",
    "section": "Generation 14plus",
    "description": "Die Co-Regisseur*innen Lexie und Logan verweben die Geschichten zweier trans Jungen, die durch Suizid gestorben sind, mit denen ihrer transmännlichen+ Community. Dabei vermitteln sie Ressourcen und Vorstellungskraft für einen Weg nach vorn.",
    "description_en": "Co-directors Lexie and Logan weave together the stories of two trans boys who died by suicide and their trans masculine+ community, offering resources and imagination for a way forward.",
    "director": "Lexie Bean, Logan Rozos",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202603286_1_RWD_1380.jpg",
    "title_original": "What Will I Become?",
    "title_de_en": "",
    "link_en": "https://www.berlinale.de/en/2026/programme/202603286.html"
  },
{
    "link": "https://www.berlinale.de/de/2026/programm/202613625.html",
    "title": "",
    "title_de": "",
    "section": "Generation Kplus",
    "description": "Mit ihrem besten Freund Umut spielt Deniz ganz unbeschwert in ihrem Geheimversteck. Aber in der Schule schließen die Jungs sie aus, und zu den Mädchen passt sie auch nicht. Doch dann wird ein Kindheitstraum wahr und bekommt eine neue Bedeutung.",
    "description_en": "Deniz plays happily with her best friend Umut at their secret hideout. But at school, she is excluded by the boys and also fails to fit in with the girls. Then, a childhood dream becomes true and takes on a new meaning.",
    "director": "Dalya Keleş | mit Sudem Berin-Dinç, Mustafa Konak, İpek Çattım",
    "image": "https://www.berlinale.de/media/filmstills/2026/generation-2026/202613625_1_RWD_1380.jpg",
    "title_original": "Yerçekimi",
    "title_de_en": "Gravity | Schwerkraft",
    "link_en": "https://www.berlinale.de/en/2026/programme/202613625.html"
  }
]';

    $films = json_decode($text_input, true);
    if (!$films) return;

    var_dump("from berlinale str22ings");

// 2. DEPENDENCY CHECK (Corrected)
    // Check if the main WPML constant is defined
    if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>Import aborted: WPML is not active.</p></div>';
        });
        return;
    }

    var_dump("from berlinale 333strings");

    $imported = 0;

    foreach ($films as $film) {

        // --- Step A: Parse Data ---
        $titles     = get_film_titles($film);
        $tax_slug   = ($film['section'] === 'Generation Kplus') ? 'k' : '14';
        $image_url  = $film['image'];

        // --- Step B: Create English Post (Source) ---
        $id_en = insert_film_post([
            'title'   => $titles['en'],
            'content' => $film['description_en'],
            'meta'    => [
                'director'          => $film['director'],
                'berlinale_link'    => $film['link_en'],
                'title_original_raw'=> $film['title_original'],
            ]
        ], 'en');

        if ( ! $id_en ) continue; // Skip if failed

        // --- Step C: Create German Post (Translation) ---
        // Get the "Translation Group ID" (trid) from the English post we just made
        $trid = apply_filters( 'wpml_element_trid', NULL, $id_en, 'post_film' );

        $id_de = insert_film_post([
            'title'   => $titles['de'],
            'content' => $film['description'],
            'meta'    => [
                'director'          => $film['director'],
                'berlinale_link'    => $film['link'],
                'title_original_raw'=> $film['title_original'],
            ]
        ], 'de', $trid); // <--- Pass TRID to link them!

        // --- Step D: Assign Shared Assets (Taxonomies & Images) ---
        if ( $id_de ) {
            // Assign Categories (Handles language mapping internally)
            assign_film_category( $id_en, $tax_slug, 'en' );
            assign_film_category( $id_de, $tax_slug, 'de' );

            // Handle Image (Downloads once, attaches to both)
            attach_film_image( $image_url, $id_en, $id_de );

            $imported++;
        }
    }

    // Feedback
    add_action('admin_notices', function() use ($imported) {
        echo '<div class="notice notice-success is-dismissible"><p>Imported ' . $imported . ' films.</p></div>';
    });
}

/**
 * Helper: Insert Post & Force Language
 * Removes the need to switch global context repeatedly.
 */
function insert_film_post( $data, $lang_code, $trid = null ) {

    // 1. Insert standard WP Post
    $post_id = wp_insert_post([
        'post_title'   => $data['title'],
        'post_content' => $data['content'],
        'post_status'  => 'publish',
        'post_type'    => 'film', // ensure lowercase 'film'
        'meta_input'   => $data['meta']
    ]);

    if ( is_wp_error($post_id) ) return false;

    // 2. Explicitly set WPML Language Info
    // This overwrites whatever language WP assigned by default
    $element_type = apply_filters( 'wpml_element_type', 'film' );

    do_action( 'wpml_set_element_language_details', [
        'element_id'           => $post_id,
        'element_type'         => $element_type,
        'trid'                 => $trid, // Null for English, Valid ID for German
        'language_code'        => $lang_code,
        'source_language_code' => $trid ? 'en' : null // Source is EN if we have a TRID
    ]);

    return $post_id;
}

/**
 * Helper: Parse Titles based on logic
 */
function get_film_titles( $film ) {
    $en = $film['title_original'];
    $de = $film['title_original']; // Default

    if ( ! empty( $film['title_de_en'] ) ) {
        if ( strpos( $film['title_de_en'], '|' ) !== false ) {
            $parts = explode( '|', $film['title_de_en'] );
            $de = trim( $parts[1] );
        } else {
            $de = $film['title_de_en'];
        }
    }
    return ['en' => $en, 'de' => $de];
}

/**
 * Helper: Assign Category safely by Language
 * Clean version: Uses 'suppress_filters' to find the term without switching languages.
 */
function assign_film_category( $post_id, $slug, $lang ) {

    // 1. Find the term by slug, ignoring the current active language
    $terms = get_terms( array(
        'taxonomy'         => 'section',
        'slug'             => $slug,
        'hide_empty'       => false,
        'suppress_filters' => true, // <--- The magic key: ignores WPML context
        'number'           => 1
    ) );

    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        // We found a term (it could be the English or German one, doesn't matter yet)
        $found_term_id = $terms[0]->term_id;

        // 2. Ask WPML for the ID in the target language ($lang)
        // This filter is smart: It takes *any* ID from the translation group
        // and returns the correct ID for the requested language code.
        $final_term_id = apply_filters( 'wpml_object_id', $found_term_id, 'section', false, $lang );

        // 3. Assign it
        if ( $final_term_id ) {
            wp_set_object_terms( $post_id, (int)$final_term_id, 'section' );
        }
    }
}

/**
 * Helper: Handle Image Upload/Check
 */
function attach_film_image( $url, $id_en, $id_de ) {
    if ( empty($url) ) return;

    // 1. Check if image exists in DB (Custom SQL for speed)
    global $wpdb;
    $file_name = basename($url);
    $att_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND guid LIKE %s LIMIT 1",
        '%' . $wpdb->esc_like($file_name)
    ));

    // 2. If not, download it
    if ( ! $att_id ) {
        if ( ! function_exists( 'media_sideload_image' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/media.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
        }
        $new_id = media_sideload_image( $url, $id_en, null, 'id' );
        if ( ! is_wp_error($new_id) ) $att_id = $new_id;
    }

    // 3. Attach to both posts
    if ( $att_id ) {
        set_post_thumbnail( $id_en, $att_id );
        set_post_thumbnail( $id_de, $att_id );
    }
}

