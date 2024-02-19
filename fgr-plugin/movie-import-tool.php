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
    if (isset($_POST['import'])) {
        import_from_berlinale_string();
    }

    if (isset($_POST['adjust_legacy'])) {
        adjust_legacy_posts();
    }



    // Display the form
    echo '<h1>Berlinale Film Import</h1>';
    echo '<p>Nutze das Formular, um Filme im JSON Format zu importieren.</p>';
    echo '<form method="post">';
    //echo '<textarea name="text_input" rows="10" cols="30"></textarea><br>';
    echo '<textarea name="text_input" rows="10" cols="30" value="Dummy" style="display: none;"></textarea><br>';
    echo '<input type="submit" name="import" value="Import 2024">';
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

function import_from_berlinale_string () {
            // Process the text input
        //$text_input = sanitize_textarea_field($_POST['text_input']);
        // $text_input = '{"films": ' . $text_input . '}'; // Wrap the input in '{"films": ...}

        $imported = 0;
        $errors = 0;

        $text_input = '
        [
            {
              "title": "Obraza",
              "description": "Ukraine, Sowjetunion, 1990. Der 17-jährige Yasha rebelliert, wie viele andere Teenager auch. Doch in seiner Heimat sind einige gegnerische Kräfte gefährlicher als andere. Yasha bekommt Ärger, weil er in der Schule die falsche Musik gespielt hat. Doch die Schulleitung tadelt nicht nur sein vermeintliches Fehlverhalten, sondern lässt ihn auch ihre Feindseligkeit gegenüber seinem Jüdischsein spüren.Obwohl Yasha diese Form von Ressentiments gewohnt ist, wühlt ihn der Vorfall auf. Trost findet er in der Aussicht auf ein besseres Leben in New York. Er geht fest davon aus, dass seine Familie bald dorthin auswandern wird. Aus dem Bedürfnis heraus, alle Brücken hinter sich abzubrechen, trennt er sich von seiner Freundin Lilya. Als er erfährt, dass sein Vater gar nicht in die USA ausreisen will, ist Yasha am Boden zerstört. Er fühlt sich nicht nur in der Schule und in seiner Stadt unwillkommen, sondern auch im Stich gelassen. Zutiefst enttäuscht von seinem Vater, der dem ersehnten Ausbruch im Weg steht, beginnt für Yasha eine Reise, die ihn an den Rand des Abgrunds führt.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202401040_1_RWD_1380.jpg",
              "section": "Generation 14plus",
              "director": "Gleb Osatinski",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202401040"
            },
            {
              "title": "The Girl Who Lived in the Loo",
              "description": "Ein zehnjähriges Mädchen hat seinen Zufluchtsort – das Badezimmer – als ultimative Lösung für alle Probleme entdeckt. Sie isst auf dem Klo, spielt auf dem Klo, und wenn sie dürfte, würde sie dort auch schlafen. Die Reise durch das Leben, von einem Badezimmer zum nächsten, bleibt ruhig und sicher, aber auch etwas einsam. Je größer sie wird, desto enger scheint ihre Welt zu werden. Schließlich kommt sie zu der Einsicht, dass ihr treuer Begleiter, das Klo, nicht all ihre Bedürfnisse erfüllt. So sehr sie ihren Rückzugsraum auch liebt, sie erkennt, dass sie dort nicht mehr lange bleiben kann.  Sie lernt, aus ihrer Komfortzone herauszutreten, über ihre vertrauten Grenzen hinauszugehen, die Hand auszustrecken und um Hilfe zu bitten.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202404350_1_RWD_1380.jpg",
              "section": "Generation 14plus",
              "director": "Subarna Dash",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202404350"
            },
            {
              "title": "Un pájaro voló",
              "description": "Die Erinnerung an einen Freund, der nicht mehr da ist, bestimmt die Gedankenwelt von Boloy. Der wichtigste Spieler des kubanischen Volleyballteams versucht trotz des Schmerzes über den Verlust das morgendliche Training wiederaufzunehmen. Denn er weiß, dass sein Leben weitergehen muss. Der Film spiegelt die Erfahrungen des Regisseurs, der selbst einen geliebten Menschen verloren hat.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202403865_0_RWD_1380.jpg",
              "section": "Generation 14plus",
              "director": "Leinad Pájaro De la Hoz",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202403865"
            },
            {
              "title": "Cura sana",
              "description": "Spanien am Feiertag Noche de San Juan. Die Schwestern Jessica, 14, und Alma, 8, sind, wie so oft, auf dem Weg zur Versorgungsstation der Caritas, um sich Essensmarken abzuholen. Die Gewalt, die sie in all den Jahren zu Hause durch ihren Vater erfahren haben, hat aus Jessica einen wütenden, selbstzerstörerischen Menschen mit einem dicken Schutzpanzer gemacht. Auch ihrer kleinen Schwester gegenüber verhält sie sich hart und feindselig. Doch im Laufe der gemeinsamen Tour erkennt Jessica, dass sie nicht so werden will wie ihr Vater, und sie bemüht sich, Alma mit Liebe statt mit Gewalt zu begegnen.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202412874_1_RWD_1380.jpg",
              "section": "Generation 14plus",
              "director": "Lucía G. Romero",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202412874"
            },
            {
              "title": "Lapso",
              "description": "Bel und Juliano sind Teenager aus den Außenbezirken von Belo Horizonte, Brasilien. Sie lernen sich kennen, während beide wegen Vandalismus eine sozialpädagogische Maßnahme in derselben öffentlichen Bibliothek absolvieren müssen. Bel, die bald 18 Jahre alt wird, ist gehörlos und kommuniziert in Gebärdensprache. Sie fährt Skateboard und zeichnet, was die Aufmerksamkeit von Juliano erregt, der sich selbst für Rap begeistert und seinen Alltag in Audioschnipseln festhält. Bel gegenüber bringt er seine Gefühle, Ängste und Zweifel bezüglich seiner Zukunft zum Ausdruck. Über die geteilte Erfahrung von staatlichen Repressionen kommen sie sich langsam näher und trotzen gemeinsam den schwierigen Umständen angesichts von Ignoranz und Vernachlässigung durch das politische System.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202403698_1_ORG.jpg",
              "section": "Generation 14plus",
              "director": "Caroline Cavalcanti",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202403698"
            },
            {
              "title": "Songs of Love and Hate",
              "description": "Prem, der charismatische Moderator einer beliebten Radiosendung für Ratschläge in Herzensdingen, wird selbst von Liebeskummer geplagt. Trost sucht er in den schroffen Bergen. Während er seine eigenen emotionalen Turbulenzen durchlebt, hallen die verzweifelten Anrufe von Zuhörer*innen, die ihn um Tipps bitten, durch die Wildnis. Sowohl Prem als auch sein Publikum versuchen, sich ihren Weg durch das tückische Terrain der Liebe zu bahnen. Eine packende Geschichte über emotionalen Aufruhr und Selbstfindung.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202403050_1_RWD_1380.jpg",
              "section": "Generation 14plus",
              "director": "Saurav Ghimire",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202403050"
            },
            {
              "title": "Muna",
              "description": "Muna will unbedingt mit auf Klassenfahrt. Sie möchte der Monotonie ihres Zuhauses entkommen, Spaß mit ihren Freund*innen haben und die beste Playlist aller Zeiten erstellen. Doch ihre Eltern zögern noch, ob sie ihre Zustimmung geben sollen. Da erreicht die in Großbritannien lebende Familie die Nachricht vom Tod des Großvaters in Somalia. Muna erlebt mit, wie ihre Familienangehörigen um einen Menschen trauern, den sie selbst nie richtig kennengelernt hat. In dieser für sie verwirrenden Atmosphäre bemüht sie sich weiterhin verzweifelt, ihre Mutter davon zu überzeugen, sie mit auf Klassenfahrt gehen zu lassen. Es kommt zu emotionalen Zusammenstößen. Muna beginnt, über ihr eigenes Leben und das ihres Großvaters nachzudenken, und entdeckt dabei, dass die beiden mehr verbindet, als ihr bisher bewusst war.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202401056_1_RWD_1380.jpg",
              "section": "Generation 14plus",
              "director": "Warda Mohamed",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202401056"
            },
            {
              "title": "Un invincible été",
              "description": "Ein heißer Sommerabend: Allein zu Hause am Pool langweilt sich der 16-jährige Clément beinahe zu Tode. Fest entschlossen, seine Jungfräulichkeit zu verlieren, durchstöbert er die Profile der Männer auf Grindr, lügt, was sein Alter angeht, und verabredet sich mit dem 24-jährigen Naël. Je näher das Date rückt, desto unruhiger wird er. Doch erst durch die Entdeckung eines Körpers, der sich von  Naëls völlig unterscheidet, wird Clément wirklich erwachsen.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202412892_1_RWD_1380.jpg",
              "section": "Generation 14plus",
              "director": "Arnaud Dufeys",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202412892"
            },
            {
              "title": "Comme le feu",
              "description": "Jeff wird von seinem Freund Max eingeladen, in dem tief im Wald versteckt liegenden Domizil des gefeierten Filmregisseurs Blake Cadieux zu übernachten. Jeff hat große Erwartungen: Cadieux ist ein Künstler, den er sehr bewundert – und Max’ ältere Schwester Aliocha, in die er heimlich verliebt ist, kommt auch mit. Der unberührte und unwirtliche Wald und die riesige Blockhütte werden zum Terrain, auf dem die Suche der Jugendlichen nach Idealen und Freiheit auf die verletzten Egos der Erwachsenen trifft.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202403456_1_RWD_1380.jpg",
              "section": "Generation 14plus",
              "director": "Philippe Lesage",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202403456"
            },
            {
              "title": "Disco Afrika : une histoire malgache",
              "description": "In Madagaskar schuftet der 20-jährige Kwame in geheimen Minen, um mit dem Schürfen von Saphiren seinen Lebensunterhalt zu verdienen. Eines Tages führt ihn ein unerwartetes Ereignis zurück in seine Heimatstadt. Hier sieht er nicht nur seine Mutter und alte Freund*innen wieder, sondern wird auch mit der zügellosen Korruption konfrontiert, die das Land beutelt. Er muss sich entscheiden: zwischen leicht verdientem Geld und Loyalität, zwischen eigenem Vorteil und erwachendem politischem Bewusstsein. Der junge Protagonist Kwame scheint die Last der Welt auf seinen Schultern zu tragen und findet doch immer wieder Kraft für die Suche nach Antworten und nach dem Weg in eine bessere Zukunft. Durch den Film hallt das Echo der afrikanischen Bürgerrechtsbewegungen der 1970er-Jahre. Einer Ära, in der sich das Streben nach Unabhängigkeit in einer Blüte von Kunst und Musik fortsetzte. Mit Kwames persönlicher Geschichte verhandelt der Film auch das Erbe des Kolonialismus und die Geschichte des Widerstands und wirft ein Schlaglicht auf die Zustände in der Gegenwart.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202401008_1_RWD_1380.jpg",
              "section": "Generation 14plus",
              "director": "Luck Razanajaona",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202401008"
            },
            {
              "title": "Ellbogen",
              "description": "Hazals sehnlichster Wunsch: ein Leben. Trotz vieler Bewerbungen wird sie zu keinem einzigen Gespräch eingeladen. Stattdessen sitzt sie in einer Bildungsmaßnahme vom Jobcenter fest, die ihr auch keine neuen Möglichkeiten eröffnet. Aber an ihrem 18. Geburtstag fühlt sich Hazal stark. Es ist wie in alten Zeiten, als sie und ihre Freundinnen dachten, sie könnten alles erreichen, solange sie nur zusammenhalten. Erst als sie in der Schlange vor einem hippen Club stehen, wird Hazal klar, dass sie hier nicht hingehören. Und sie behält recht. Der Türsteher lässt sie abblitzen.  Auf dem Heimweg werden sie von einem überheblichen Studenten belästigt, die Situation eskaliert. Die Wut über die nicht endende Ablehnung eruptiert und führt zu einer folgenschweren Tat.  Hazal flieht Hals über Kopf nach Istanbul, eine fremde Stadt in einem ihr unbekannten Land. Dort muss sie allein überleben, koste es, was es wolle. Ellbogen erzählt die Geschichte einer jungen Frau, die aus der Gesellschaft verdrängt wird und die Weichen ihres Lebens neu stellen muss. Man will mit ihr durch die Nacht rennen, man will wissen, wie es mit ihr und mit uns allen weitergeht.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202402994_1_RWD_1380.jpg",
              "section": "Generation 14plus",
              "director": "Aslı Özarslan",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202402994"
            },
            {
              "title": "Fox and Hare Save the Forest",
              "description": "Auf einer großen Waldlichtung weiht Biber, ein kleines Tier mit großem Ego, sein Meisterwerk ein: einen gigantischen Damm. Mithilfe von zwei Ratten blockiert er den Flusslauf, das Wasser steigt, und in kürzester Zeit ist ein riesiger See entstanden. Biber ist sehr stolz auf sich, aber zu seiner Enttäuschung gibt es niemanden, der diese Großtat bewundert. Anderswo im Wald haben Fuchs und Hase eine Party mit ihren Freund*innen gefeiert. Als Eule von dem Fest nach Hause kommt, entdeckt er, dass seltsame Wasserströme an seinem Baum vorbeifließen. Er gerät in Panik und flieht in den Wald. Am nächsten Tag bemerken Fuchs und Hase, dass Eule verschwunden ist. Sie machen sich mit ihren Freund*innen auf den Weg, um ihn zu suchen, und entdecken den Stausee. Wo kommt das ganze Wasser her? Während sie noch nach Eule Ausschau halten, steigt der Pegel, und bald droht eine Überschwemmung. Beim Versuch, den Wald zu retten, wird ihre Freund*innenschaft auf eine harte Probe gestellt.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202407066_1_RWD_1380.jpg",
              "section": "Generation Kplus",
              "director": "Mascha Halberstad",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202407066"
            },
            {
              "title": "Huling Palabas",
              "description": "Mit jedem Film, den er sich ansieht, erfindet der 16-jährige Andoy eine neue Geschichte. Alle drehen sich um die Fragen, die ihn seit jeher umtreiben: Wer ist er, und wer ist sein Vater? Die Wahrheit bleibt im Verborgenen; keine seiner Vorstellungen erfüllt sich. Doch dann tauchen zwei Gestalten in Andoys Heimatstadt auf, die selbst einem Film entsprungen zu sein scheinen: Ariel schneidet Haare und lockt mit viel Charme junge Männer an; der geheimnisvolle, langhaarige Isidro besitzt einen VCD-Player. Andoy fühlt sich zu den beiden hingezogen. Doch je tiefer er sich in ihr abseitiges Leben verstrickt, desto stärker gerät seine Wirklichkeit ins Wanken. Andoy muss entscheiden, ob er die jahrelange Suche nach seinem Vater mit einem großen Finale oder einem sanften Fade-out beenden will.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202410034_1_RWD_1380.jpg",
              "section": "Generation 14plus",
              "director": "Ryan Machado",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202410034"
            },
            {
              "title": "It’s Okay!",
              "description": "Während In-young und ihre Mitschüler*innen von der Seoul International Arts Company in Übersee auftreten, stirbt zu Hause in Korea In-youngs Mutter auf tragische Weise. In-young übersteht tapfer das schwierige erste Trauerjahr. Als ihr wegen überfälliger Mietzahlungen die Zwangsräumung droht, zieht sie heimlich  ins Gebäude ihrer Tanzschule.  Seol-ah, die Chefchoreografin, entdeckt In-youngs Unterschlupf  und nimmt sie widerwillig bei sich zu Hause auf. Die Aufführung zum 60-jährigen Jubiläum der Kompanie steht bevor, und Seol-ah fühlt sich unter Druck gesetzt, eine tadellose Show abzuliefern. Währenddessen wird In-young zur Zielscheibe von Neid und Mobbing durch die Spitzentänzerin des Ensembles. Allmählich lernen In-young und Seol-ah, ihre Erwartungen mit der Realität in Einklang zu bringen, und finden unerwarteten Trost in ihrem Miteinander.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202404801_1_RWD_1380.jpg",
              "section": "Generation Kplus",
              "director": "Kim Hye-young",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202404801"
            },
            {
              "title": "Kai Shi De Qiang",
              "description": "Zhuang ist ein Spätzünder. Bei der ersten Begegnung mit seiner Mitschülerin Meng, einer talentierten Langstreckenläuferin, nimmt er spontan und voreilig die Schuld für den Diebstahl einer Startpistole auf sich, den Meng begangen hat. Doch dann zeigt sie ihm die kalte Schulter, noch bevor sie sich besser kennenlernen können. In seiner Verwirrung beschließt Zhuang, Mengs Leichtathletikteam beizutreten, obwohl ihm damit weniger Zeit fürs Lernen bleibt. Zur Achterbahn der Gefühle gesellt sich schulischer Druck. Mit einem mutigen Zeichen wird Zhuang alle überraschen. Er wartet nur noch auf den richtigen Moment …",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202410405_1_RWD_1380.jpg",
              "section": "Generation 14plus",
              "director": "Qu Youjia",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202410405"
            },
            {
              "title": "Beurk !",
              "description": "Igitt! Paare, die sich auf den Mund küssen, sind eklig. Und das Schlimmste ist, man kann gar nicht über sie hinwegsehen, denn wenn die Menschen kurz davor sind, sich zu küssen, werden ihre Lippen ganz rosa und glänzend. Léo lacht über sie, wie die anderen Kinder auf dem Zeltplatz auch. Doch er hat ein Geheimnis, von dem er seinen Freund*innen nichts erzählt: Auch sein eigener Mund glitzert schon ein bisschen. Und in Wirklichkeit will er das mit dem Küssen unbedingt auch einmal ausprobieren.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202406563_1_RWD_1380.jpg",
              "section": "Generation Kplus",
              "director": "Loïc Espuche",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202406563"
            },
            {
              "title": "Anaar Daana",
              "description": "Guddal, eine temperamentvolle Fünfjährige, isst am liebsten Anaar Daana – saure Bonbons. Sie lebt mit ihrem jüngeren Bruder Laddoo und ihrer älteren Schwester Chinu in einem Haus, das ihre Familie seit Generationen bewohnt. In Abwesenheit ihrer Eltern kümmert sich ein Kindermädchen um sie – und muss oft schimpfen, wenn Guddal und Laddoo wieder einmal Unfug angestellt haben.  Eines Tages sorgt ein trauriges Ereignis für einen plötzlichen Stimmungsumschwung. Guddal ist verwirrt: Fremde und unerwünschte Gäste strömen in das Haus, ihre idyllische Welt wird erschüttert.  Als Coming-of-Age-Erzählung erkundet Anaar Daana das Wesen der Kindheit inmitten der dunkelsten Momente des Lebens.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202412801_1_RWD_1380.jpg",
              "section": "Generation Kplus",
              "director": "Nishi Dugar",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202412801"
            },
            {
              "title": "Goosfand",
              "description": "Rose, ein junges Mädchen, lebt mit ihrer Mutter und ihrem Hund in Teheran. Sie bekommt mit, dass ihre Nachbar*innen Schafe schlachten. Die Mutter erklärt ihr, dass die Tiere für ein traditionelles Ritual geopfert werden, bei dem das Fleisch gekocht und unter den Menschen verteilt wird. Rose findet es unsinnig, im modernen Leben an diesem alten Brauch festzuhalten. Sie beschließt, die verbleibenden Schafe zu retten.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202411929_1_RWD_1380.jpg",
              "section": "Generation Kplus",
              "director": "Hadi Babaeifar",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202411929"
            },
            {
              "title": "Aguacuario",
              "description": "Unter der brennenden Sonne von Veracruz, Mexiko, ist der zehnjährige Vinzent zusammen mit seinem älteren Bruder auf einem alten Dreirad unterwegs. Sie liefern an der einsamen Küste Wasserkrüge aus. Als ein gleichaltriges Mädchen ihren Weg kreuzt, steht Vinzent vor einem Dilemma: Soll er seine Pflicht erfüllen – oder soll er gegen seinen Bruder aufbegehren und sich auf ein kleines dreirädriges Abenteuer einlassen?",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202414163_1_RWD_1380.jpg",
              "section": "Generation Kplus",
              "director": "José Eduardo Castilla Ponce",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202414163"
            },
            {
              "title": "Porzellan",
              "description": "Auf einer abgelegenen deutschen Insel kommen die Bewohner*innen eines Dorfes zusammen, um einen traditionellen Polterabend zu feiern. Seit Wochen schon freut sich die zehnjährige Fina auf dieses Ereignis, doch der Tag nimmt eine unerwartete Wendung.   Während die Gäste sich dem Rausch des  Festes  hingeben, hadert Fina mit traditionellen Frauenbildern und  ihrer Sehnsucht nach einer weiblichen Bezugsperson. Fina und ihrer fürsorglichen  älteren Schwester wird erst allmählich bewusst,  wie sehr die Abwesenheit ihrer Mutter  ihrer beider  Leben beeinflusst. Um  den schwierigen  Übergang ins Erwachsenenalter zu meistern, müssen sie sich gegenseitig unterstützen.Porzellan ist eine zarte und zeitlose Reflexion über das Aufwachsen junger Mädchen in einer von patriarchalen Strukturen geprägten Gesellschaft. Durch die Augen von Fina wirft der Film einen Blick auf weibliche Rollenmuster und auf Erfahrungen und Emotionen, die über Generationen hinweg das Erwachsenwerden begleiten.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202403921_1_RWD_1380.jpg",
              "section": "Generation Kplus",
              "director": "Annika Birgel",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202403921"
            },
            {
              "title": "Sukoun",
              "description": "Hind ist eine junge Karatekämpferin. Als es eines Tages in ihrem Trainingszentrum zu einem Fall von übergriffigem Verhalten kommt, gerät die Welt des hörbehinderten Mädchens aus den Fugen. Entschlossen sucht sie einen Weg, zu ihrer alten Stärke zurückzufinden. Inspiriert von einer wahren Geschichte, erzählt Sukoun einfühlsam davon, was es bedeutet, wenn vermeintlich geschützte Räume gesprengt werden. Mit den Augen und Ohren von Hind bewegt sich der Film zwischen Schmerz und Zärtlichkeit, Stille und Lärm, dem Verborgenen und dem Augenscheinlichen.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202403617_1_RWD_1380.jpg",
              "section": "Generation Kplus",
              "director": "Dina Naser",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202403617"
            },
            {
              "title": "Uli",
              "description": "Während ihre Eltern verreist sind, bleibt die achtjährige Rafaela in der Obhut ihrer 15-jährigen Schwester Laura zu Hause zurück. Als Laura beschließt, einen Jungen zu besuchen, den sie mag, muss Rafaela wohl oder übel mitkommen. Doch ihre Schwester und der Junge schließen sich ein. Rafaela wartet, langweilt sich und beginnt schließlich, das Haus zu erkunden, in dem sie eine einzigartige Begegnung mit Uli, einer queeren jungen Frau, und deren Haustier hat. Ein Film über das Gefühl von Fremdheit und die Möglichkeit, an einem unbekannten Ort Freiheit zu finden.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202411276_1_RWD_1380.jpg",
              "section": "Generation Kplus",
              "director": "Mariana Gil Ríos",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202411276"
            },
            {
              "title": "Papillon",
              "description": "Ein Mann schwimmt im Meer. Während er schwimmt, denkt er zurück. Von seiner frühen Kindheit bis zu seinem Leben als Erwachsener sind all seine Erinnerungen mit Wasser verbunden. Manche sind glücklich, manche ruhmreich, manche traumatisch. Dies wird das letzte Mal sein, dass er schwimmen geht. Die Geschichte führt von der Quelle zum Fluss, vom Wasser in Planschbecken zu dem in Schwimmbädern, von einem nordafrikanischen Land zu den Küsten des Mittelmeers, von olympischen Stadien zu Wasserrückhaltebecken, vom Konzentrationslager zu den Traumstränden von Réunion.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202413056_1_RWD_1380.jpg",
              "section": "Generation Kplus",
              "director": "Florence Miailhe",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202413056"
            },
            {
              "title": "A Summer’s End Poem",
              "description": "In einem Dorf in der Nähe der südchinesischen Stadt Chaozhou hat ein Junge den Sommer bei seinem Großvater verbracht. Als der letzte Ferientag naht, nimmt er sein hart verdientes Geld zusammen, um sich einen Traum zu erfüllen: Schon lange wünscht er sich eine Frisur, wie sie in der Stadt angesagt ist. Doch die Kluft zwischen Erwartung und Realität ist groß. Die Geschichte eines Jungen, der Abschied von seiner Kindheit nimmt und die Reise Richtung Selbstentdeckung beginnt, geht über die Grenzen des kleinen Dorfes hinaus und berührt universelle Themen wie das Erwachsenwerden und die Unberechenbarkeit des Lebens.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202403603_0_RWD_1380.jpg",
              "section": "Generation Kplus",
              "director": "Lam Can-zhao",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202403603"
            },
            {
              "title": "Last Swim",
              "description": "Ziba ist eine intelligente und ehrgeizige junge Londonerin. Sie ist stolz auf ihre iranische Herkunft, ist gewissenhaft und vernünftig, hat aber auch nihilistische Tendenzen. Vielleicht hängt das mit ihrem Faible für Astronomie zusammen, vielleicht sehnt sie sich nach glücklicheren Zeiten. Obwohl sie als einzige unter ihren engen Freund*innen bei den Schulabschlussprüfungen  gute Ergebnisse erzielt hat, fällt es ihr schwer, optimistisch zu bleiben. Zusammen wollen die Jugendlichen einen Tag im sommerlich heißen London verbringen und  ein einmaliges Himmelsereignis beobachten. Insgeheim plant Ziba einen unumkehrbaren Schritt, der ihr – ihrer Meinung nach – die Kontrolle über ihr Leben zurückgeben wird. Gefangen zwischen einer tiefen Leidenschaft  fürs Leben und dem überwältigenden Wunsch, Hoffnungslosigkeit und Ängsten ein Ende zu setzen, muss Ziba lernen, Träume loszulassen und mit der Ungewissheit zurechtzukommen.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202405846_1_RWD_1380.jpg",
              "section": "Generation 14plus",
              "director": "Sasha Nathwani",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202405846"
            },
            {
              "title": "Maydegol",
              "description": "Eine afghanische Jugendliche, deren Eltern nach Iran eingewandert sind, kämpft für ihren Traum, professionelle Muay-Thai-Boxerin zu werden. Weder die konservative Haltung ihrer Familie noch körperliche Misshandlungen oder die Immigrant*innenfeindlichkeit ihres Umfelds können sie davon abhalten. Die Boxkurse finanziert sie ohne das Wissen ihrer Eltern, indem sie Tag und Nacht arbeitet. Mit ihrem Sport will sie nicht nur im Ring Erfolg haben, sondern auch die Schwierigkeiten des Lebens überwinden. Maydegol zeigt die Beharrlichkeit der Generation Z, die ihrem scheinbar düsteren Schicksal entkommen will und ihre Rechte einfordert, wobei insbesondere junge Frauen nach Freiheit suchen – auch auf die Gefahr hin, ihr Leben zu verlieren. Ein Film, der als Spiegel dient, um die eigene Stärke zu erkennen.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202414269_1_RWD_1380.jpg",
              "section": "Generation 14plus",
              "director": "Sarvnaz Alambeigi",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202414269"
            },
            {
              "title": "Quell’estate con Irène",
              "description": "Italien, 1997. Clara und Irène, beide 17 Jahre alt, lernen sich auf einer Ferienfreizeit kennen. Organisiert wird diese von dem Krankenhaus, in dem sie beide in Behandlung sind. Clara ist schüchtern, Irène ungezähmt, sie ähneln einander überhaupt nicht, verstehen sich aber auf Anhieb. Anstatt zu ihren Familien zurückzukehren, beschließen sie, auf eine Insel vor der Küste Siziliens zu fliehen, um dort ihren ersten Sommer als junge Frauen zu verbringen. Sie leben in den Tag hinein, träumen von der Liebe, nehmen ihre gemeinsamen Momente auf VHS auf und stellen sich ihrer Angst vor der Zukunft.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202410707_1_RWD_1380.jpg",
              "section": "Generation 14plus",
              "director": "Carlo Sironi",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202410707"
            },
            {
              "title": "Raíz",
              "description": "Der achtjährige Feliciano verbringt seine Tage mit dem Hüten von Alpakas in einer abgelegenen Andenregion. Seine einzigen Freunde sind Ronaldo, ein junges Alpaka, und Rambo, ein alter Hund. Ihnen erzählt er alles über Fußball und die WM-Qualifikationsspiele der peruanischen Nationalmannschaft. Doch hinter der scheinbar eintönigen, friedlichen Routine verbirgt sich eine bedrohliche Realität: ein verseuchter See und die Sorgen seiner Eltern. Ein Bergbauunternehmen macht den Bewohner*innen der Gemeinde Druck, ihr Land zu verkaufen, und greift zur Einschüchterung zu radikalen Mitteln: Mehrere Alpakas werden mit aufgeschlitzter Kehle vorgefunden. Die Behörden ignorieren alle Hilferufe; Feliciano und das Dorf sind auf sich allein gestellt. Als eines Tages Ronaldo verschwindet, beginnt für Feliciano eine verzweifelte Suche. Währenddessen schließen seine Eltern und Nachbar*innen sich zusammen, um gemeinsam gegen das Unternehmen vorzugehen …",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202406370_1_RWD_1380.jpg",
              "section": "Generation Kplus",
              "director": "Franco García Becerra",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202406370"
            },
            {
              "title": "Reinas",
              "description": "Im Sommer 1992 überschlagen sich in Lima die Ereignisse. Inmitten von sozialen und politischen Unruhen bereiten Lucia, Aurora und ihre Mutter Elena ihre Übersiedlung von Peru in die USA vor. Beklommen blicken sie dem Abschied entgegen – von ihrem Land, von Familie und Freund*innen, aber vor allem von Carlos, Vater und Ex-Ehemann, der beinahe schon aus ihrem Leben verschwunden ist. Angesichts der ungewissen Zukunft werden widersprüchliche Gefühle wach. Alte Reue regt sich, neue Illusionen entstehen. Frustrationen und Ängste mischen sich mit Aufregung und freudiger Erwartung. Gemeinsam muss sich die Familie der schwer verdaulichen Wahrheit stellen, welche Verluste ihre Abreise mit sich bringt. Reinas ist eine intensive, vielstimmige und bewegende Initiationsgeschichte im Geiste der 1990er-Jahre.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202408439_1_RWD_1380.jpg",
              "section": "Generation Kplus",
              "director": "Klaudia Reynicke",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202408439"
            },
            {
              "title": "Sieger sein",
              "description": "Die elfjährige Mona ist mit ihrer siebenköpfigen kurdischen Familie aus Syrien geflüchtet und in Berlin gelandet, genauer gesagt im Bezirk Wedding. Dort kommt sie an eine berüchtigte Grundschule. 90 Prozent „Ausländeranteil“. Hier herrscht Chaos. Die meisten Lehrkräfte sind mit den Nerven am Ende, und bei den Schüler*innen steigt das Frustlevel täglich. Auch bei Mona. Deutsch kann sie kaum, dafür aber Fußball. In ihrer Heimat hat sie oft mit ihren Freund*innen auf der Straße Fußball gespielt. Sie vermisst ihr Zuhause, die Freund*innen und besonders ihre Tante Helin. Sie war Monas Heldin und hat deren Fußballleidenschaft stets unterstützt.  In Deutschland ist alles anders. Herr Che, ein engagierter Lehrer, erkennt Monas außergewöhnliches Talent und nimmt sie in die Mädchenmannschaft auf. Gut gemeint, aber alles andere als einfach. Mona gilt schnell als Außenseiterin, und das Zusammenspiel mit den anderen Mädchen gestaltet sich schwieriger als gedacht. Jede von ihnen kämpft ihre eigenen Kämpfe, doch bald wird klar: Nur wenn sie zusammenspielen, können sie gewinnen.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202403796_1_RWD_1380.jpg",
              "section": "Generation Kplus",
              "director": "Soleen Yusef",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202403796"
            },
            {
              "title": "Los tonos mayores",
              "description": "Die 14-jährige Ana lebt mit ihrem Vater Javier, einem Künstler und Lehrer, in Buenos Aires. Seit einem Unfall hat sie eine Metallplatte im Arm. Während der Winterferien spürt Ana plötzlich rhythmische Impulse in dieser Platte. Ihrem Vater erzählt sie nichts davon, aber zusammen mit ihrer Freundin Lepa komponiert sie, inspiriert von den Signalen, ein Musikstück, den „Heartbeat Song“. Nach einem Streit mit Lepa läuft Ana eines Nachts allein durch die Straßen und begegnet dabei zufällig einem jungen Soldaten, der ihr enthüllt, was wirklich hinter den Impulsen steckt: Es sind Morsezeichen. Ana kommt zu der Erkenntnis, dass ihr Arm als Antenne zur Übermittlung einer verschlüsselten Botschaft dient, und ist fest entschlossen, den Code zu knacken. Während sie immer tiefer in ein Labyrinth von geheimen Nachrichten vordringt, entfernt sie sich zugleich immer mehr von ihrer Freundin und von ihrem Vater. Von der Harmonie der ersten Ferientage ist nicht mehr viel übrig, und Ana fragt sich mit zunehmender Besessenheit, ob die Worte speziell an sie gerichtet sind. Ein fantasievolles Märchen über Musik, geheime Botschaften und kalte Wintertage.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202402016_1_RWD_1380.jpg",
              "section": "Generation Kplus",
              "director": "Ingrid Pokropek",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202402016"
            },
            {
              "title": "Wo Tu",
              "description": "Der zehnjährige Wo Tu träumt davon, eine Wasserpistole zu besitzen wie die anderen Jungen in seinem Dorf. Obwohl sein Vater es versprochen hat, schafft er es nicht, ihm eine aus der Stadt mitzubringen. Doch es gibt Hoffnung für Wo Tu: Sein sterbender Großvater sichert ihm zu, ihm als Geist den Wunsch zu erfüllen.  Nach seinem Tod besucht der alte Mann den Jungen in seinen Träumen und setzt eine Schatzsuche in Gang. Bald verschwimmen die Grenzen zwischen Realität und Traum, Vergangenheit und Gegenwart immer mehr. Drei Generationen einer Familie im dörflichen China umspannend, zeichnet der Film auch das Porträt von deren tiefer Verbundenheit mit ihrer Region.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202409035_1_RWD_1380.jpg",
              "section": "Generation Kplus",
              "director": "Wang Xiaoshuai",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202409035"
            },
            {
              "title": "Xiao Ban Jie",
              "description": "Der 14-jährige Li Xing lebt in einer südchinesischen Stadt in einem Bezirk namens Great Phuket, der von Zerfall und Wiederaufbau gekennzeichnet ist. Sein Vater ist verstorben, mit seiner Mutter kommt er nicht gut zurecht; sie weigert sich, das zum Abriss vorgesehene Haus der Familie zu verlassen. Auch in der Schule hat er nichts als Probleme. Eines Tages entdeckt Li zusammen mit seinem einzigen Freund Song einen Tunnel, der zum Unterschlupf wird. Die Geräusche der Stadt hallen dort wider, und auch Lis Gedanken und Gefühle finden hier einen Raum. Bei einer Verfolgungsjagd mit den Wachleuten einer verlassenen Fabrik wird Song so schwer verletzt, dass er ins Krankenhaus muss. Verzweifelt, voller Schuldgefühle und einsamer denn je kehrt Li in den Tunnel zurück. Doch dort gehen merkwürdige Dinge vor sich. Der Tunnel scheint zum Leben zu erwachen, Lis traurige Erinnerungen werden immer beklemmender. Schritt für Schritt findet er zurück in die Außenwelt. Was von seiner Reise bleibt, sind Lis Gefühle und Gedanken zu seinem Land und den Menschen, die ihn umgeben – auf mysteriöse Weise gespeichert von den Steinen des Tunnels.",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202405933_1_RWD_1380.jpg",
              "section": "Generation 14plus",
              "director": "Liu Yaonan",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202405933"
            },
            {
              "title": "Young Hearts",
              "description": "Mitten im Schuljahr lernt der 14-jährige Elias seinen neuen Nachbarn Alexander kennen, einen Jungen aus Brüssel, der selbstbewusst und eigenwillig wirkt. Die beiden verstehen sich auf Anhieb. Ob er eine Freundin hat, will Alexander von Elias wissen – und verrät ihm ohne zu zögern, dass er selbst auf Jungs steht. Elias genießt die Zeit mit Alexander. Die Gefühle, die in ihm aufkeimen, behält er jedoch lieber für sich. Aus Angst vor den Reaktionen seines Umfelds verstrickt er sich in ein Netz aus Lügen, bis er sich nicht mehr anders zu helfen weiß, als Alexander zurückzustoßen. Er fühlt sich völlig allein. In den Erzählungen seines Großvaters und dessen Liebe zu seiner verstorbenen Frau findet Elias Trost und Bestärkung. Elias erkennt, dass Liebe zu kostbar ist, um sie entgleiten zu lassen. Er muss Alexander zurückgewinnen …",
              "image": "https://www.berlinale.de/media/filmstills/2024/generation-2024/202412978_1_RWD_1380.jpg",
              "section": "Generation Kplus",
              "director": "Anthony Schatteman",
              "link": "https://www.berlinale.de/de/programm/detail.html?film_id=202412978"
            }
          ]
        ';
        
        $films = json_decode($text_input, true); 

        var_dump($films);

        foreach ($films as $film) {

            // Prepare the post data
            $post_data = array(
                'post_title'    => wp_strip_all_tags($film['title']),
                'post_content'  => $film['description'],
                'post_status'   => 'publish',
                'post_type'     => 'Film',
                // Custom meta fields
                'meta_input'    => array(
                    'director' => $film['director'],
                    'berlinale_link' => $film['link']
                )
                // Add more fields as needed
            );

            // Insert the post into the database
            $post_id = wp_insert_post($post_data);

            // Check if the post was created successfully
            if ($post_id && !is_wp_error($post_id) && !is_wp_error($post_id)) {
                // Determine the section based on the film's section
                $section = $film['section'] === 'Generation Kplus' ? 'k' : '14';

                // Set the 'Section' taxonomy for the post
                wp_set_object_terms($post_id, $section, 'section', false);

                // Download and attach the image to the post
                if (!empty($film['image'])) {
                    // Download the image
                    $media = media_sideload_image($film['image'], $post_id);

                    // Check if the image was downloaded successfully
                    if (!is_wp_error($media)) {
                        // Get the id of the attachment
                        $attachments = get_posts(array(
                            'post_type' => 'attachment',
                            'posts_per_page' => 1,
                            'post_status' => 'any',
                            'post_parent' => $post_id
                        ));

                        // Check if the attachment was found
                        if ($attachments) {
                            // Set the post thumbnail (featured image)
                            set_post_thumbnail($post_id, $attachments[0]->ID);
                        }
                    }
                }

                $imported++;
            } else {
                $errors++;
            }
        }

        // Display a success or error notice
        add_action('admin_notices', function() use ($imported, $errors) {
            if ($errors > 0) {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p>' . sprintf('Import completed with %s errors.', $errors) . '</p>';
                echo '</div>';
            } else {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p>' . sprintf('Successfully imported %s films.', $imported) . '</p>';
                echo '</div>';
            }
        });
}
?>
