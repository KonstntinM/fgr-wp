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
    /*
     * Disable to prevent accidental usage
     * if (isset($_POST['import'])) {
        import_from_berlinale_string();
    }

    if (isset($_POST['adjust_legacy'])) {
        adjust_legacy_posts();
    }*/



    // Display the form
    echo '<h1>Berlinale Film Import</h1>';
    echo '<p>Nutze das Formular, um Filme im JSON Format zu importieren.</p>';
    echo '<form method="post">';
    //echo '<textarea name="text_input" rows="10" cols="30"></textarea><br>';
    echo '<textarea name="text_input" rows="10" cols="30" value="Dummy" style="display: none;"></textarea><br>';
    echo '<input type="submit" name="import" value="Import 2025">';
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
            "title": "Akababuru: Expresión de asombro",
            "description": "Kari, ein junges Embera-Mädchen, hat Angst zu lachen. Eines Tages begegnet sie der jungen Mestizin Kera, die ihr die Legende von Kiraparamia erzählt: eine Frau, die in der Erzählung der Ältesten von den Göttern bestraft wurde, weil sie ihren Mann ausgelacht hatte. Doch Kera interpretiert die Legende neu. Sie ist überzeugt, Kiraparamias Lachen habe sie in Wirklichkeit befreit. Diese Geschichte inspiriert Kari, und sie beschließt, sich den Kindern entgegenzustellen, die sie und ihre Freund*innen immerzu bedrängen.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202516209_1_RWD_1920.jpg",
            "section": "Generation Kplus",
            "director": "Irati Dojura Landa Yagarí",
            "link": "https://www.berlinale.de/de/2025/programm/202516209.html"
          },
          {
            "title": "Anngeerdardardor",
            "description": "Kaali stellt fest, dass sein geliebter Schlittenhund verschwunden ist. Sofort macht er sich in der kleinen Stadt Tasiilaq in Ostgrönland auf die Suche nach ihm. Er vermutet, dass der Hund gestohlen wurde. In Begleitung seines einzigen Freundes, Bartilaa, trifft er auf verschiedene Ortsbewohner*innen und muss sich den Herausforderungen stellen, die das Anderssein in dieser eng verbundenen Gemeinschaft mit sich bringt. Auf der Flucht vor einer Gruppe Jugendlicher entdeckt Kaali seinen Hund und stiehlt ihn zurück. Doch er wird auf frischer Tat ertappt, flieht und lässt Bartilaa allein zurück. Als er erfährt, dass der wahre Dieb ein anderer ist als gedacht, muss er schnell handeln, um seinen einzigen Freund nicht zu verlieren.\nAnngeerdardardor ist der erste Kurzfilm, der in Ostgrönland entstanden ist. Er basiert auf einer wahren Begebenheit und wurde in Zusammenarbeit mit den Jugendlichen von Tasiilaq gedreht.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202510745_1_RWD_1920.jpg",
            "section": "Generation Kplus",
            "director": "Christoffer Rizvanovic Stenbakken",
            "link": "https://www.berlinale.de/de/2025/programm/202510745.html"
          },
          {
            "title": "Arame farpado",
            "description": "Im Umland von São Paulo spannen die zwölfjährige Angelina und ihr jüngerer Bruder Santiago einen Stacheldraht über eine unbefestigte Straße, um damit den vorbeifahrenden Lastwagen ihres Stiefvaters zu zerkratzen. Doch der Plan geht furchtbar schief: Eine Unbeteiligte wird verletzt. Die ältere Schwester Evita versucht, die Situation zu retten. Gemeinsam verbringen sie eine schlaflose Nacht in einer überfüllten Notaufnahme, wo die verborgenen Spannungen der Familie an die Oberfläche kommen. Mit Menschen aus den unterschiedlichsten Gesellschaftsschichten konfrontiert, müssen Angelina, Evita und ihr Stiefvater Zé Luis sich den Konsequenzen ihres Handelns stellen und darum ringen, einander als Familie zu verstehen.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202512078_1_RWD_1920.jpg",
            "section": "Generation 14plus",
            "director": "Gustavo de Carvalho",
            "link": "https://www.berlinale.de/de/2025/programm/202512078.html"
          },
          {
            "title": "Atardecer en América",
            "description": "Die 13-jährige Bárbara lässt die Krise in Venezuela hinter sich und durchquert Südamerika. In Chile warten bessere Lebensbedingungen auf sie. Sie erinnert sich an die Nacht, in der sie die Hochebene Altiplano in den Anden überquerte. In 4000 Metern Höhe kämpfte sie bei klirrender Kälte gegen ihre Erschöpfung an, hatte aber gleichzeitig das Gefühl, von einer spirituellen Präsenz begleitet zu werden, die umherstreift wie der Wind. Durch diese Landschaft zwischen Bolivien und Chile, mit ihren schneebedeckten Bergen, wild lebenden Alpakas, kristallklaren Bächen und kargen Wüstenabschnitten, führt eine der gefährlichsten Migrationsrouten Lateinamerikas. Für Bárbara bleibt klar: Sie wird niemals aufgeben, egal was passiert.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202516133_1_RWD_1920.jpg",
            "section": "Generation 14plus",
            "director": "Matías Rojas Valencia",
            "link": "https://www.berlinale.de/de/2025/programm/202516133.html"
          },
          {
            "title": "Autokar",
            "description": "In den 1990er-Jahren verlässt die achtjährige Agata ihre polnische Heimat in Richtung Belgien – eine Reise, die ihr Angst macht. Im Bus beginnt sie, einen Brief an ihren Vater zu schreiben, der in Polen geblieben ist. Als ihr Bleistift herunterfällt und wegrollt, ist Agata gezwungen, ihre Schüchternheit zu überwinden. Auf der Suche nach dem Stift schlängelt sie sich zwischen den Sitzreihen hindurch und taucht dabei in eine fantastische Welt ein, die von seltsamen Fahrgästen – halb Mensch, halb Tier – bewohnt wird. Agatas Wahrnehmung verwandelt migrantische Realität in ein Initiationserlebnis.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202513207_1_RWD_1920.jpg",
            "section": "Generation Kplus",
            "director": "Sylwia Szkiłądź",
            "link": "https://www.berlinale.de/de/2025/programm/202513207.html"
          },
          {
            "title": "Beneath Which Rivers Flow",
            "description": "In den Sümpfen des Südiraks, abgeschieden vom Rest der Welt, leben Ibrahim und seine Familie eng verwoben mit dem Marschland, dem Fluss, dem Schilfrohr und den Tieren, die sie versorgen. In seinem Büffel hat der stille und verschlossene Ibrahim einen treuen Begleiter. Als sich eines Morgens dichter Nebel über das Land legt, nimmt Ibrahim eine unheilvolle Veränderung wahr. Die Flüsse beginnen auszutrocknen, die Erde bekommt Risse, und die einst blühende Landschaft verwandelt sich in eine triste Einöde. Ibrahims Welt bricht zusammen. Er sieht sich unkontrollierbaren Kräften ausgesetzt, die nicht nur seine Lebensweise bedrohen, sondern auch das einzige Lebewesen, das er wirklich versteht.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202506829_1_RWD_1920.jpg",
            "section": "Generation 14plus",
            "director": "Ali Yahya",
            "link": "https://www.berlinale.de/de/2025/programm/202506829.html"
          },
          {
            "title": "Christy",
            "description": "Der 17-jährige Christy steht an einem Scheideweg. Er ist gerade aus seiner Pflegefamilie in der ruhigen Vorstadt geworfen worden und bei seinem älteren Bruder Shane eingezogen, der im Arbeiter*innenviertel im Norden der irischen Stadt Cork wohnt. Für Shane ist das nur eine vorübergehende Lösung, aber Christy beginnt, sich bei ihm zu Hause zu fühlen, lässt sich auf die Menschen vor Ort ein und findet Freund*innen. Über die erweiterte Familie – so schlecht ihr Ruf auch ist – kann er sogar wieder an seine Vergangenheit anknüpfen. Shane versucht jedoch, ihn von der Verwandtschaft fernzuhalten. Er will um jeden Preis ein besseres Leben für Christy, selbst wenn das bedeutet, dass er ihn von sich stoßen muss. Nach vielen Jahren der Trennung stehen die Brüder vor der Aufgabe, sich mit ihren turbulenten Vergangenheiten zu versöhnen und zu entscheiden, wie die Zukunft aussehen soll.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202503982_1_RWD_1920.jpg",
            "section": "Generation 14plus",
            "director": "Brendan Canty",
            "link": "https://www.berlinale.de/de/2025/programm/202503982.html"
          },
          {
            "title": "Daye: Seret Ahl El Daye",
            "description": "Daye ist ein 14-jähriger nubischer Albino mit einer goldenen Stimme. Obwohl er wegen seines Aussehens gemobbt wird und sein Vater sich von ihm abgewendet hat, lässt er sich nicht unterkriegen. Er träumt davon, Sänger zu werden wie sein Idol Mohamed Mounir. Als er die Chance bekommt, bei „The Voice“ vorzusingen, machen sich Daye und seine Familie auf die Reise von Assuan nach Kairo. Auf der Reise geht es turbulent zu: Ein Auto wird gestohlen, Ersparnisse gehen verloren, es muss Flucht vor der Polizei ergriffen und Diskriminierung entgegengetreten werden. Aber ihre Liebe zueinander und Dayes außergewöhnliches Talent helfen ihnen über alle Hindernisse hinweg, und sie erhalten unerwarteten Beistand, unter anderem von einem freundlichen Feuerwehrmann – und von Mounir höchstpersönlich. Daye ist eine Geschichte über Resilienz, Hoffnung und die Kraft der Musik. Sie erforscht Identität, Ehrgeiz und Familienzusammenhalt und zeigt, wie man mit Entschlossenheit und Liebe alle Widrigkeiten überwinden kann.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202501754_1_RWD_1920.jpg",
            "section": "Generation 14plus",
            "director": "Karim El Shenawy",
            "link": "https://www.berlinale.de/de/2025/programm/202501754.html"
          },
          {
            "title": "De menor",
            "description": "Eine fiktionale Fernsehserie über das brasilianische Rechtssystem: Während sich einige Gerichtsverhandlungen in einem schicken Esszimmer abspielen, können andere als Dialoge im Podcast-Format verfolgt werden. In der letzten Episode kommen ein Pflichtverteidiger und zwei Jugendliche zusammen, um das Filmmaterial zu kommentieren, wie es im Internetgenre der Reaktionsvideos üblich ist. Bei Generation 14plus werden zwei Episoden gezeigt: In der einen wird ein Jugendlicher als mutmaßlicher Dealer verurteilt und gerät in einen musikalischen Konflikt mit dem strengen Richter, der sich fragt, welches Urteil am gerechtesten ist. In der anderen Episode wird der Fall einer Jugendlichen, die ein Lebensmittelgeschäft ausraubt, um ihren Schwarm zu beeindrucken, als Sensationsgeschichte in einer Talkshow besprochen.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202515972_1_RWD_1920.jpg",
            "section": "Generation 14plus Sondervorführung",
            "director": "Caru Alves de Souza",
            "link": "https://www.berlinale.de/de/2025/programm/202515972.html"
          },
          {
            "title": "Down in the Dumps",
            "description": "Cinelli, eine neurotische und perfektionistische Marienkäferin, bereitet sich akribisch auf ihre bevorstehende Geburtstagsparty vor. Alles muss makellos sein – das ist ja wohl das Mindeste! Ihr unbeliebter Nachbar Peri, eine tollpatschige Kakerlake, möchte mitfeiern. Aber aus Angst, er könnte die Party ruinieren, lädt Cinelli ihn nicht ein. Peri ist tief verletzt und beschließt, Cinellis rote Deckflügel zu stehlen, die wie nichts anderes ihre Vollkommenheit symbolisieren. Als Cinelli am nächsten Tag bemerkt, dass die Flügel weg sind, verfällt sie in eine Depression. Was hat das Leben noch für einen Sinn ohne ihr schillerndes Äußeres? Während sie in einem dunklen Tunnel verschwindet, erkennt Peri nach und nach, dass in Cinellis Leben nicht alles so perfekt ist, wie es scheint. Als er versteht, was das für sie bedeutet, beschließt er, seine Nachbarin zu suchen …",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202506788_1_RWD_1920.jpg",
            "section": "Generation Kplus",
            "director": "Vera van Wolferen",
            "link": "https://www.berlinale.de/de/2025/programm/202506788.html"
          },
          {
            "title": "Fantas",
            "description": "Tania beschließt, ihr Pferd Fantas mit in die Stadt zu nehmen und in dem Arbeiter*innenviertel, in dem sie mit ihrer Familie lebt, ihren Freund*innen vorzustellen. Die Entscheidung, ihre Leidenschaft mit ihnen zu teilen, ist auch ein Versuch, zwei Teile von sich selbst zusammenzubringen. Eine urbane Geschichte mit magischen Untertönen, in der zwei Welten, die sich nie zuvor vermischt haben, aufeinanderprallen.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202506827_1_RWD_1920.jpg",
            "section": "Generation 14plus",
            "director": "Halima Elkhatabi",
            "link": "https://www.berlinale.de/de/2025/programm/202506827.html"
          },
          {
            "title": "Hora do recreio",
            "description": "Hora do recreio befasst sich mit dem Thema Bildung in Brasilien und verfolgt dabei sowohl einen dokumentarischen als auch einen fiktionalen Ansatz. Schüler*innen sprechen über Probleme wie Gewalt, Rassismus und Femizide und spielen Situationen nach, die sie bei Polizeieinsätzen in den Slums erlebt haben. An einer anderen Schule erarbeiten die Schüler*innen eine dramatische Fassung des Romans „Clara dos Anjos“ von Lima Barreto, der zu Beginn des 20. Jahrhunderts geschrieben wurde und die Misshandlung eines mittellosen Schwarzen Mädchens schildert. Anhand ihres Textes gleichen die Schüler*innen diese Geschichte mit eigenen Erfahrungen und den Problemen der heutigen Gesellschaft ab. Ausgangspunkt des Films war eine Umfrage unter Lehrer*innen an öffentlichen Einrichtungen; gedreht wurde an Schulen, die für unterschiedliche Stadtteile und Communities in Rio de Janeiro repräsentativ sind. Der dokumentarische Teil wurde aus den Diskussionen in den Klassenzimmern entwickelt, aber auch Ereignisse während der Dreharbeiten, darunter ein Polizeieinsatz, flossen mit ein.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202505113_1_ORG.jpg",
            "section": "Generation 14plus",
            "director": "Lucia Murat",
            "link": "https://www.berlinale.de/de/2025/programm/202505113.html"
          },
          {
            "title": "Howl",
            "description": "Daisy und Lila sind beste Freundinnen. Die beiden 16-Jährigen haben immer alles miteinander geteilt, nun zeigen sich Risse. Daisy hat ihre erste Periode bekommen, und Lila, die immer noch auf ihre wartet, fühlt sich abgehängt. Auf einer sommerlichen Hausparty in der Vorstadt bricht Lila das Versprechen, stets Seite an Seite zu bleiben, und lässt Daisy für einen Jungen sitzen. Daisy zieht sich an den Pool zurück, wo sie Drew begegnet. Selbstbewusst und unwiderstehlich, ist Drew all das, was sie selbst gerne wäre. Als Drew sich für einen Kuss zu ihr beugt, spürt sie, wie sich in ihrem Inneren etwas verschiebt. Verzaubert von diesem Moment, ignoriert sie Lilas Rufe nach Hilfe. Später, als sie Lila wiedertrifft, bekommt sie Zweifel, ob das richtig war. Eine Geschichte über Freundschaft, Identität und die Momente, in denen sich zeigt, was wir einander bedeuten.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202515181_1_ORG.jpg",
            "section": "Generation 14plus",
            "director": "Domini Marshall",
            "link": "https://www.berlinale.de/de/2025/programm/202515181.html"
          },
          {
            "title": "I agries meres mas",
            "description": "Als Chloe obdachlos wird, beschließt sie, Athen zu verlassen. Unterwegs zu ihrer älteren Schwester trifft sie zufällig auf eine Gruppe junger Leute, die mit einem Wohnmobil durch Griechenland reisen und Menschen helfen, die am Rande der Armut leben. Chloe ist sofort von den Idealen und dem unkonventionellen Lebensstil der Gruppe fasziniert und schließt sich ihr an. So entdeckt sie Seiten ihres Landes, aber auch an sich selbst, die ihr bislang unbekannt waren. Sie erfährt, wie es ist, sich um andere zu kümmern und umsorgt zu werden. Wie es ist, eine Familie zu haben, die man sich selbst ausgesucht hat, und wie viel man eventuell opfern muss, um sie nicht zu verlieren. Sie erlernt die Rituale ihres neuen Stammes, findet Liebe, wird enttäuscht, erlebt Angst, Hunger, Gefahr, Freiheit. Die Straßen der Rebellion, denen Chloe auf ihrer fieberhaften Reise folgt, führen sie über die Schwelle zum Erwachsensein und bringen die Erkenntnis, dass es weniger wehtut, wenn man nicht allein ist.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202503099_1_RWD_1920.jpg",
            "section": "Generation 14plus",
            "director": "Vasilis Kekatos",
            "link": "https://www.berlinale.de/de/2025/programm/202503099.html"
          },
          {
            "title": "Juanita",
            "description": "Die zwölfjährige Juanita wurde in der Dominikanischen Republik geboren und lebt jetzt in einer Kleinstadt in der Nähe von Barcelona. Wie viele Mädchen aus Lateinamerika hat sie immer von all den Freiheiten geträumt, die Frauen in Europa genießen. Doch selbst in ihrem scheinbar so aufgeschlossenen Umfeld hat sie das Gefühl, dass ihr Körper nicht den Schönheitsnormen entspricht, die für Frauen gelten. Als sie zu einer Poolparty eingeladen wird, spürt Juanita, dass auch sie sich dem gesellschaftlichen Druck nicht entziehen kann. Mit allen Mitteln versucht sie, loszuwerden, was sie am meisten verunsichert: die Haare an ihren Beinen. Ihre Freund*innen, ihr Schwarm, ihre Mutter – alle haben dazu etwas zu sagen. Doch für Juanita, die auch im Sommer stets nur lange Hosen trägt, ist es undenkbar, sich vor der Party nicht zu rasieren. Nicht, weil sie es sich so genau überlegt hat, sondern weil sie glaubt, keine Wahl zu haben.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202513675_1_ORG.jpg",
            "section": "Generation Kplus",
            "director": "Karen Joaquín,",
            "link": "https://www.berlinale.de/de/2025/programm/202513675.html"
          },
          {
            "title": "Julian and the Wind",
            "description": "Arthur und Julian sind Zimmergenossen in einem Jungeninternat. Arthur ist in Julian verliebt. Doch Julian lässt ihn links liegen. Als Julian zu schlafwandeln beginnt, entwickelt sich eine sonderbare Form der Intimität. Schlafend verlässt Julian das gemeinsame Zimmer und das Wohngebäude und geht auf den Wiesen des Internatsgeländes umher. Arthur folgt ihm nach draußen. Gleichzeitig kommt ein mysteriöser Wind auf, der mit jeder weiteren Nacht stärker wird …",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202504111_1_RWD_1920.jpg",
            "section": "Generation 14plus",
            "director": "Connor Jessup",
            "link": "https://www.berlinale.de/de/2025/programm/202504111.html"
          },
          {
            "title": "Little Rebels Cinema Club",
            "description": "2008 in Indonesien: Der 14-jährige Doddy erzählt seinen drei besten Freund*innen von seinen Kinobesuchen in der Hauptstadt Jakarta. In Parepare, wo die vier wohnen, gibt es kein Kino. Wortgewandt gibt Doddy die Handlung eines Zombiefilms wieder, weigert sich aber, das Ende zu verraten. Er hat eine bessere Idee: In wenigen Wochen wird er mit seiner Familie nach Jakarta umziehen, als Abschiedsgeschenk möchte er mit seinen Freund*innen die Schlussszene des Films nachstellen und mit der Handycam aufnehmen. Das Ergebnis sollen sie sich dann bei ihm zu Hause ansehen, wo Doddy zusammen mit seiner Mutter ein Kino einrichten will. Doch zuerst muss er mal an die Handycam kommen, denn die gehört Anji, seinem von Trauer, Herzschmerz und Wut geplagten Emo-Bruder.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202515182_1_RWD_1920.jpg",
            "section": "Generation Kplus",
            "director": "Khozy Rizal",
            "link": "https://www.berlinale.de/de/2025/programm/202515182.html"
          },
          {
            "title": "Maya, donne-moi un titre",
            "description": "Maya und ihr Vater, Michel Gondry, leben in zwei unterschiedlichen Ländern. Jeden Abend bittet er sie: „Maya, schenke mir einen Titel.“ Ihre Antwort dient ihm als Grundlage für viele kurze Stop-Motion-Animationen, in denen Maya die Heldin ist. So entsteht eine poetische und amüsante Reise, die zum Träumen und Lachen einlädt.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202511668_1_ORG.jpg",
            "section": "Generation Kplus",
            "director": "Michel Gondry",
            "link": "https://www.berlinale.de/de/2025/programm/202511668.html"
          },
          {
            "title": "Naerata ometi",
            "description": "Mari lebt im Erziehungsheim. Die Mutter der 16-Jährigen ist tot, der Vater ein Trinker. Darum wurde Mari ins Heim abgeschoben, so wie die anderen Kinder und Jugendlichen, die hier leben. Ihre Umgangsformen sind rau, ihre Spiele brutal, und Mari kommt damit nicht zurande. Sie unternimmt einen Fluchtversuch, doch der misslingt. Daraufhin bietet ihr Tauri, der Sohn eines einflussreichen Vaters, seinen Schutz an. Mari aber fühlt sich zu dem rüden Robi hingezogen und bringt damit eine Rivalin gegen sich auf … Der 1987 bei der Berlinale mit dem UNICEF-Preis ausgezeichnete Spielfilm der Estin Leida Laius (1923–1996) führt in eine jugendliche Parallelwelt abseits staatlicher Ordnungsvorstellungen. Inszeniert wurde er an authentischen Schauplätzen und vorwiegend mit Laiendarsteller*innen, was dem Film einen dokumentarischen Touch verleiht. Co-Regisseur Arvo Iho: „Wir verwendeten Musik von Bob Dylan und Janis Joplin, um das richtige Tempo und die richtige Stimmung am Set zu erzeugen. Sie war so ehrlich und leidenschaftlich wie der Film, den wir gemacht haben.“\nArvo Iho überwachte auch die Restaurierung des von ihm mit moderner Handkamera und Originalton gedrehten Films.",
            "image": "https://www.berlinale.de/media/filmstills/2025/berlinale-classics-2025/202503014_1_RWD_1920.jpg",
            "section": "Berlinale Classics",
            "director": "Leida Laius,",
            "link": "https://www.berlinale.de/de/2025/programm/202503014.html"
          },
          {
            "title": "A natureza das coisas invisíveis",
            "description": "Es ist Sommer, und die zehnjährige Gloria begleitet ihre Mutter Antônia, eine Krankenschwester, auf die Arbeit. Gloria kennt sich im Krankenhaus gut aus, sie erkundet es oft alleine. Eines Tages trifft sie die ebenfalls zehnjährige Sofia, die wegen ihrer Großmutter hier ist: Bisa Francisca, eine an Alzheimer erkrankte Seelenheilerin, wurde nach einem häuslichen Unfall hier eingeliefert. Sofia ist mit ihrer Mutter Simone uneins darüber, wie es mit Bisa weitergehen soll. Simone besteht darauf, dass sie vorerst im Krankenhaus bleibt; Sofia wünscht sich sehnlich, sie in das Haus der Familie auf dem Land zurückzubringen. Während Gloria und Sofia davon träumen, das Krankenhaus zu verlassen, freunden sich ihre Mütter – beide sind alleinerziehend – an und beginnen, einander zu unterstützen. Der Kreislauf von Tod und Wiedergeburt hat für Gloria, Sofia, Antônia und Simone eine tiefe Bedeutung. Als Bisas Abreise ansteht, übernimmt Antônia die palliative Pflege. Auf dem Land finden sie eine Gemeinschaft, die auf Bisa wartet und darauf vorbereitet ist, für die wandernden Seelen zu beten, damit sie weiterziehen können.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202504315_1_RWD_1920.jpg",
            "section": "Generation Kplus",
            "director": "Rafaela Camelo",
            "link": "https://www.berlinale.de/de/2025/programm/202504315.html"
          },
          {
            "title": "Ne réveillez pas l’enfant qui dort",
            "description": "Diamant, ein 15-jähriges Mädchen aus Dakar, träumt davon, Filme zu machen. Doch ihre Familie hat andere Pläne für ihre Zukunft. Ohne Diamants Zustimmung werden Entscheidungen über ihr Leben getroffen. Diamant vertraut sich ihrer Schwester an; sie sehnt sich nach Ruhe und sucht nach einer Fluchtmöglichkeit. Am nächsten Morgen ist Diamant in einen unerklärlichen, tiefen Schlaf gefallen – Ausdruck des Widerstands gegen die ihr auferlegten Verpflichtungen. Ihr mysteriöser Schlummer wird zu einem stillen Akt des Trotzes, der Spannungen und Ängste schürt, bis er nicht nur Diamant selbst, sondern auch ihre Familie und ihr Zuhause bedroht.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202509154_1_RWD_1920.jpg",
            "section": "Generation 14plus",
            "director": "Kevin Aubert",
            "link": "https://www.berlinale.de/de/2025/programm/202509154.html"
          },
          {
            "title": "On a Sunday at Eleven",
            "description": "Die siebenjährige Angel vollführt ihre sonntäglichen Rituale. Sie geht in einen Kosmetikladen, in dem überall weiße Schönheitsideale angepriesen werden, und besucht den Ballettunterricht, wo sie als einziges Schwarzes Mädchen heraussticht. Sie fühlt sich ausgeschlossen und flieht in eine Traumwelt, in der die Schwarzen Frauen, die ihr Leben begleiten, als Ballerinen mit aufwendigen Afro-Frisuren um sie herumtanzen. In ihrer Kirchengemeinde wird Angel dann von den Ältesten in die Mitte genommen. Hier fühlt sie sich akzeptiert und geliebt. Der Film zelebriert Schwarze Hairstyles und die Kraft, die für Schwarze Frauen in der spirituellen Verbindung zu den Vorfahr*innen steckt.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202503328_1_RWD_1920.jpg",
            "section": "Generation Kplus",
            "director": "Alicia K. Harris",
            "link": "https://www.berlinale.de/de/2025/programm/202503328.html"
          },
          {
            "title": "Only on Earth",
            "description": "Der Süden Galiciens ist eines der am stärksten durch Waldbrände bedrohten Gebiete Europas. Seit Jahrhunderten leben Wildpferde hier in den Bergen. Sie spielen eine entscheidende Rolle bei der Brandverhütung, da sie das Unterholz niedrig halten. Doch ihre Zahl schwindet. Auf eindringliche und visuell beeindruckende Weise nimmt der Film die Zuschauer*innen mit in den heißesten Sommer seit Beginn der Wetteraufzeichnungen, in dem tagelang unlöschbare Waldbrände wüten. Der Feuerwehrmann San beschäftigt sich mit der Brandanalyse – ein Job, der ihn an die Frontlinien des Geschehens führt. Die warmherzige Tierärztin Eva arbeitet mit wilden und zahmen Pferden. Und der zehnjährige Pedro ist bereits ein angehender Cowboy. Der Film nimmt auch die Perspektive der Tiere ein, die den menschlichen Blick stets erwidern. Ein Film über das fragile Gleichgewicht in der Natur und über die Beziehung zwischen Mensch und Tier.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202503252_1_RWD_1920.jpg",
            "section": "Generation Kplus",
            "director": "Robin Petré",
            "link": "https://www.berlinale.de/de/2025/programm/202503252.html"
          },
          {
            "title": "Ornmol",
            "description": "In der Region Kimberley in Westaustralien beschließt eine kleine Gemeinschaftsschule jeden Tag mit dem Joonba-Ritual, bei dem alle Kinder tanzen. Sie spüren darin eine Verbundenheit mit ihrer Kultur und dem Land, auf dem sie leben, die ihnen ein tiefes Vertrauen in sich selbst und ihre Umgebung schenkt. Ornmol begleitet die Kinder von Kupungarri beim Ockerfarbesammeln und bis zum Tanz auf einem der größten Ereignisse im Jahreslauf, dem Mowanjum-Fest. Die Vorfreude wächst von Tag zu Tag.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202503255_1_RWD_1920.jpg",
            "section": "Generation Kplus",
            "director": "Marlikka Perdrisat",
            "link": "https://www.berlinale.de/de/2025/programm/202503255.html"
          },
          {
            "title": "El paso",
            "description": "In einem abgelegenen kubanischen Dorf spielen, angeln und lachen der elfjährige Fabián und der neunjährige Christian miteinander, ohne etwas von dem Drama zu ahnen, das die Erwachsenen in mondlosen Nächten erleben. Erst als sie, in der Dunkelheit versteckt, den Gesprächen der Erwachsenen lauschen, erfahren sie von den grauenhaften Vorfällen: Das Vieh verschwindet, und einige Bauern haben im Kampf gegen das, was auch immer da sein Unwesen treibt, sogar ihr Leben verloren. Wieder in ihre Spiele vertieft, werden die Kinder von einem Unbekannten überrascht, der sie einlädt, ihn auf einer nächtlichen Suche zu begleiten. Mit Laternen und Holzschwertern ausgestattet, werden sie auf die andere Seite der Lagune gebracht, an der sie leben. Dort beginnt ihre große Reise. Der anfänglichen Verspieltheit und Fantasie der Jungen tritt eine andere Realität gegenüber: Knochen, Feuer und völlige Dunkelheit verschmelzen in einem Ritual der Verwandlung, in dem die Grenzen zwischen Leben und Tod, Unschuld und Reife verschwimmen.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202513175_1_RWD_1920.jpg",
            "section": "Generation Kplus",
            "director": "Roberto Tarazona",
            "link": "https://www.berlinale.de/de/2025/programm/202513175.html"
          },
          {
            "title": "Paternal Leave",
            "description": "Die 15-jährige Leo ist in Deutschland ohne Vater aufgewachsen. Als sie von seiner Identität erfährt, macht sie sich sofort auf die Suche nach ihm. Sie findet Paolo in einer verrammelten Strandbar an der winterlichen Küste Norditaliens. Ihn überwältigt und überfordert das Wiedersehen. Nach Leos plötzlichem Auftauchen hat er Mühe, seine Balance zwischen ihr und seiner neuen Familie zu finden. Zunächst will Leo nur Antworten, doch schon bald sehnt sie sich nach einem Platz in Paolos Leben. Da sie weder Geld noch einen Plan hat, bleibt sie erst mal in dem kleinen Ort. Je mehr Zeit sie miteinander verbringen, desto mehr Gemeinsamkeiten entdecken Leo und Paolo. Doch die Realität holt die beiden unweigerlich ein. Als Paolo sich wieder vermehrt seiner jüngeren Tochter zuwendet, reagiert Leo verletzt und wütend. Ein Streit bringt den Schmerz auf beiden Seiten ans Licht, ihre zarte Verbindung scheint zerstört. Mitten im Gefühlschaos beginnen Vater und Tochter, ihre jeweiligen Wahrheiten anzuerkennen, und machen einen kleinen, aber bedeutsamen Schritt in Richtung Akzeptanz.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202501646_1_RWD_1920.jpg",
            "section": "Generation 14plus",
            "director": "Alissa Jung",
            "link": "https://www.berlinale.de/de/2025/programm/202501646.html"
          },
          {
            "title": "Quaker",
            "description": "In Brooklyn kommt eine Gruppe von Schüler*innen, die der Gemeinschaft der Quäker angehören, zu einem letzten Treffen vor ihrem Highschool-Abschluss zusammen. Reihum geben sie einander eine letzte Botschaft mit, die von den anderen schweigend entgegengenommen wird. Die Aussagen kommen von Herzen, handeln von Liebe, Freude und Hoffnung. Doch als eine Schülerin gesteht, dass sie sich nie mit ihren Klassenkamerad*innen verbunden gefühlt hat, nimmt das Treffen eine abrupte Wendung.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202500571_1_RWD_1920.jpg",
            "section": "Generation 14plus",
            "director": "Giovanna Molina",
            "link": "https://www.berlinale.de/de/2025/programm/202500571.html"
          },
          {
            "title": "Ran Bi Wa",
            "description": "Dieser Film basiert auf einer alten Legende der Qiang, einer ethnischen Minderheit, die in den Bergen im Südwesten Chinas lebt. Er erzählt die Geschichte eines Affen, der unter Menschen aufwächst und den Spuren seiner Mutter Awubaji zum Heiligen Berg folgt, um das Geheimnis der Wärme aufzudecken. Nach vielen Entbehrungen und Gefahren bekommt er einen Feuerstein in die Hände. Dabei verbrennen seine Haare und er verwandelt sich schließlich in einen Menschen. Der Film ist im Stil der traditionellen chinesischen Tuschemalerei gehalten und zeigt auf poetische Weise das Leben in einer urgeschichtlichen Gesellschaft. Mit der Geschichte des Feuerdiebs greift er zeitlose Themen wie Wachstum und Kameradschaft auf, die auch in der heutigen Zeit noch bedeutend sind. Mit Ran Bi Wa feiert das legendäre Shanghai Animation Film Studio, die Wiege vieler chinesischer Kult-Animationsfilme, seine Rückkehr.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202507662_1_RWD_1920.jpg",
            "section": "Generation Kplus",
            "director": "Li Wenyu",
            "link": "https://www.berlinale.de/de/2025/programm/202507662.html"
          },
          {
            "title": "Ruse",
            "description": "An einem regnerischen Nachmittag vertreiben sich Kavya und Tara die Zeit zu Hause bei ihrer Freundin Revati. Revatis Mutter ist nicht da, und der Nachmittag zieht sich in die Länge. Die drei Mädchen spielen Verstecken und durchstöbern den Schminktisch der Mutter, sie finden Lippenstifte und Schmuck und probieren sich damit aus. Schließlich beginnen sie, für einen Tanz zu proben, den sie sich gemeinsam ausgedacht haben. Dabei löst die körperliche Nähe ein Gefühl des Begehrens aus, für das sie noch keine Worte haben. Sie gehen mit dem Wissen auseinander, dass diese Erfahrung ein Geheimnis bleiben wird. Zurück bleibt ein Gefühl von Verwirrung über den diffusen Moment, in dem sie über ihre sexuelle Neugier gestolpert sind.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202515800_1_RWD_1920.jpg",
            "section": "Generation Kplus",
            "director": "Rhea Shukla",
            "link": "https://www.berlinale.de/de/2025/programm/202515800.html"
          },
          {
            "title": "Sous ma fenêtre, la boue",
            "description": "Die 14-jährige Emma lebt bei ihrer Mutter Hélène. Ihre andere Mutter wohnt weit weg. Emma sieht sie nur selten, projiziert jedoch das Bild der perfekten Mutter auf sie, während sie Hélène mit Ablehnung und Feindseligkeit begegnet. Diese Rollenverteilung bietet ihr ein Gefühl von Orientierung. Ein Streit mit Hélène zwingt Emma dazu, solche Zuschreibungen infrage zu stellen. Hélène ihrerseits wird sich eigener widersprüchlicher Überzeugungen bewusst. Eine derartig emotionale Auseinandersetzung sind beide nicht (mehr) gewohnt. Unbeholfen beginnen sie, gemeinsam einen Weg aus dem Zwist zu suchen.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202509132_1_ORG.jpg",
            "section": "Generation 14plus",
            "director": "Violette Delvoye",
            "link": "https://www.berlinale.de/de/2025/programm/202509132.html"
          },
          {
            "title": "Space Cadet",
            "description": "Robots einziges Ziel war es stets, Celeste zu der brillanten Wissenschaftlerin großzuziehen, die sie heute ist. Als die junge Astronautin zu ihrer ersten interstellaren Mission aufbricht, hat Robot niemanden mehr, um den er sich kümmern könnte. Er freut sich, dass Celeste endlich ihren Traum verwirklicht, und er weiß auch, dass sie eines Tages zurückkehren wird. Doch die Einsamkeit tut seinen alternden technischen Systemen nicht gut. Währenddessen sieht sich Celeste in den Weiten des Weltraums unerwarteten Gefahren ausgesetzt. Die Erinnerungen an ihre Kindheit weisen ihr jedoch einen Weg durch die Dunkelheit … Basierend auf der Graphic Novel von Kid Koala, ist Space Cadet eine musikalische Erzählung über Erinnerungen und die Bande, die uns zusammenhalten.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202511284_1_RWD_1920.jpg",
            "section": "Generation Kplus",
            "director": "Eric San (aka Kid Koala)",
            "link": "https://www.berlinale.de/de/2025/programm/202511284.html"
          },
          {
            "title": "Sunshine",
            "description": "Die talentierte Turnerin Sunshine gilt als sichere Kandidatin für die Nationalmannschaft. Doch in der Woche der Probetrainings erfährt sie, dass sie schwanger ist. Damit stehen ihr Lebenstraum und ihr College-Stipendium auf dem Spiel. Sunshine zieht einen Schwangerschaftsabbruch in Betracht. Auf dem Weg zu einer Verkäuferin illegaler Abtreibungspillen begegnet sie einem mysteriösen Mädchen, das auf unheimliche Weise so spricht und denkt wie sie. Die Begegnung erschüttert Sunshine bis ins Mark, denn das Mädchen hinterfragt ihre Überzeugungen und zwingt sie, sich ihren Ängsten, Träumen und dem Ausmaß ihrer Entscheidungen zu stellen.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202501568_1_RWD_1920.jpg",
            "section": "Generation 14plus",
            "director": "Antoinette Jadaone",
            "link": "https://www.berlinale.de/de/2025/programm/202501568.html"
          },
          {
            "title": "Tales from the Magic Garden",
            "description": "Die Geschwister Tom, Suzanne und Derek übernachten zum ersten Mal allein bei ihrem Großvater, der sich seit dem Tod ihrer Großmutter ein wenig zurückgezogen hat. Es ist für sie alle eine schwierige Zeit. Doch als Suzanne in die Rolle der Geschichtenerzählerin schlüpft, genau wie ihre Großmutter es immer getan hat, füllt sich das Haus mit magischen Abenteuern, Humor und Fantasie. Es ist eine lieb gewonnene Familientradition, dass jede*r ein eigenes Element zu den Erzählungen beisteuert, und so entdecken alle die heilende Kraft des Geschichtenerzählens wieder. Freude und Lachen kehren ins Haus zurück, die Kinder und ihr Großvater rücken näher zusammen und schöpfen Trost aus ihren gemeinsamen Erinnerungen. Eine Stop-Motion-Animation darüber, wie wir mit Liebe und Fantasie die Herausforderungen des Lebens meistern können.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202511852_1_RWD_1920.jpg",
            "section": "Generation Kplus",
            "director": "David Súkup,",
            "link": "https://www.berlinale.de/de/2025/programm/202511852.html"
          },
          {
            "title": "Têtes brûlées",
            "description": "Die zwölfjährige Eya wächst in einer tunesisch-muslimischen Familie in Brüssel auf. Zu ihrem 25-jährigen Bruder Younès hat sie eine enge Beziehung. Eya verbringt ihre Tage an seiner Seite und im Kreise einer Gruppe von Younès’ Freund*innen, die sie als eine der Ihren betrachten. Als Younès plötzlich stirbt, bricht für Eya eine Welt zusammen. In dem streng ritualisierten Trauerprozess nimmt sie sowohl die Solidarität der Gemeinschaft wahr, die sich um ihre Familie schart, als auch das Schweigen, das die einzelnen Beteiligten in ihrem Kummer isoliert. Eya muss einen eigenen Weg suchen, um ihre Trauer zu bewältigen. Ihre Kreativität, die Verbindung zu den Freund*innen ihres Bruders und vor allem ihr Glaube helfen ihr dabei. Ein zärtliches und nuanciertes Porträt einer multikulturellen und solidarischen Jugend.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202517000_1_ORG.jpg",
            "section": "Generation 14plus",
            "director": "Maja-Ajmia Yde Zellama",
            "link": "https://www.berlinale.de/de/2025/programm/202517000.html"
          },
          {
            "title": "Uiksaringitara",
            "description": "Vor 4000 Jahren in der kanadischen Arktis: Kaujak und Sapa sind einander bei ihrer Geburt versprochen worden und haben seit jeher eine aufrichtige, liebevolle Beziehung zueinander. Nach dem plötzlichen Tod von Kaujaks Vater wird ihre Verbindung jedoch gelöst. Sapa ist auf der Jagd, als Kaujaks Mutter einen neuen Mann heiratet und mit Kaujak in dessen Familienlager umzieht. Dort wird das Leben bald zum Albtraum. Aggressive Freier, die von einem dämonischen Schamanen unterstützt werden, werben um Kaujaks Hand. Sie lehnt alle ab, obwohl ihre Hoffnung, dass Sapa zurückkehrt und die Dinge wieder in Ordnung bringt, zu schwinden beginnt. Doch auch Sapa hat einen Schamanen an seiner Seite: Ulluriaq kanalisiert geistige Helfer*innen, die Sapa auf seinem Weg zu Kaujak leiten und beschützen. Ein bildgewaltiges arktisches Märchen von Zacharias Kunuk, einem Pionier des indigenen Kinos.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202507429_1_ORG.jpg",
            "section": "Generation 14plus",
            "director": "Zacharias Kunuk",
            "link": "https://www.berlinale.de/de/2025/programm/202507429.html"
          },
          {
            "title": "Umibe é Iku Michi",
            "description": "Ein Küstenstädtchen mit langjährigen Bewohner*innen aller Altersgruppen und Geschlechter lockt neuerdings Künstler*innen an, die sich für ein Projekt in der Gegend interessieren. Seltsame Vorfälle ereignen sich, naive Erwachsene treffen auf schlaue Kinder, das Meer und der Himmel schimmern im Licht. Mittelstufenschüler Sosuke fällt das Leben leicht, er wird angetrieben von der Freude am Schaffen. Im hellen Sonnenschein verändert sich zusammen mit Sosuke auch die zweite Hauptfigur: die Stadt. Wie ein unruhiges Kind bleibt sie ständig in Bewegung. Niemand ist perfekt. Aber alle haben die Fähigkeit, andere zu lieben. Während die Kinder unermüdlich versuchen, die vor ihnen liegenden Aufgaben zu lösen, wollen die Erwachsenen vor allem ihre Existenz ergründen. Eine Sammlung von Vignetten aus einem unvergesslichen Sommer, in denen jede Figur liebenswert und von einer Zärtlichkeit erfüllt ist, die zu Herzen geht.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202506669_1_RWD_1920.jpg",
            "section": "Generation Kplus",
            "director": "Satoko Yokohama",
            "link": "https://www.berlinale.de/de/2025/programm/202506669.html"
          },
          {
            "title": "Village Rockstars 2",
            "description": "Dhunu lebt in einem kleinen Dorf in Assam, Indien, und träumt davon, Musikerin zu werden. Ihre Mutter teilt ihre Leidenschaft und ermutigt sie, ihren Traum zu verfolgen. Dhunus langjährige Freund*innen haben die Musik aufgegeben und beschäftigen sich inzwischen mit anderen Dingen, doch Dhunu schließt sich mit anderen angehenden Musiker*innen in ihrem Alter zusammen und tritt mit einer lokalen Band auf. Eine unerwartete Wendung der Ereignisse zwingt sie, plötzlich erwachsen zu werden und sich den harten Realitäten des Lebens zu stellen. Sie übernimmt Verantwortung für ihre Familie und ihr eigenes Leben, verliert aber die Musik nicht aus den Augen. Während sie neue, zum Teil unsichere Wege beschreitet, findet Dhunu Trost bei ihrer Mutter, in der Natur und in der Musik. In ihrem neuen Film nimmt Rima Das nach sieben Jahren die Fährte ihrer Charaktere aus Village Rockstars wieder auf und folgt Dhunu auf der Suche nach ihrer eigenen Melodie und einem neuen Sinn im Leben – eine Reise voller Wandlungen.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202506997_1_ORG.jpg",
            "section": "Generation 14plus",
            "director": "Rima Das",
            "link": "https://www.berlinale.de/de/2025/programm/202506997.html"
          },
          {
            "title": "Wish You Were Ear",
            "description": "In einer Welt, in der Ex-Partner*innen nach der Trennung einen ausgewählten Körperteil miteinander tauschen müssen, hinterlässt jede beendete Beziehung sichtbare Spuren: Man verliert nicht nur ein Stück von sich selbst, sondern trägt fortan auch ein Fragment der vergangenen Liebe mit sich. Der*ie Protagonist*in fühlt sich unvollständig, ja entstellt, und sehnt sich nach ihrer*seiner ursprünglichen Gestalt. Die Begegnung mit einer Person, die ihr*sein ehemaliges Ohr trägt, eröffnet einen Weg, sich zu akzeptieren. Eine Reflexion darüber, wie Beziehungen uns verändern, unsere Identität und unser Selbstbild prägen – ob wir das wollen oder nicht.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202513432_1_RWD_1920.jpg",
            "section": "Generation 14plus",
            "director": "Mirjana Balogh",
            "link": "https://www.berlinale.de/de/2025/programm/202513432.html"
          },
          {
            "title": "Zečji nasip",
            "description": "Marko lebt mit seinen Eltern und seinem jüngeren Bruder Fićo, dem er sehr zugetan ist, in einem kroatischen Dorf. Er ist ein begabter Sportler, hat aber vor, nach seinem Schulabschluss den Plänen seines Vaters zu folgen und Automechaniker zu werden. Zwei Ereignisse bringen sein scheinbar stabiles Leben aus dem Gleichgewicht: Das Dorf wird von einer Überschwemmung bedroht, und seine heimliche erste Liebe, Slaven, kommt zurück in die Heimat, um seinen Vater zu beerdigen. Marko bemüht sich, seine ganze Aufmerksamkeit auf seine Freundin Petra und ein anstehendes Turnier zu richten. Doch je näher die Fluten rücken, desto größer werden auch seine emotionalen Turbulenzen. Während die Dorfbewohner*innen unermüdlich daran arbeiten, Mauern aus Sandsäcken um ihre Häuser zu errichten, baut Marko seine eigene Mauer – eine, die seine Gefühle in Schach halten soll. Doch wie Wasser haben auch Gefühle einen Weg, alle Dämme zu durchbrechen ...",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202503115_1_RWD_1920.jpg",
            "section": "Generation 14plus",
            "director": "Čejen Černić Čanak",
            "link": "https://www.berlinale.de/de/2025/programm/202503115.html"
          },
          {
            "title": "Zhi Wu Xue Jia",
            "description": "In einem abgelegenen Dorf in einem Tal an der nördlichen Grenze von Xinjiang, China, hängt der einsame kasachische Junge Arsin Erinnerungen an seine Familie nach. Trost findet er in der Beobachtung der Natur. Die Ankunft von Meiyu, einem Han-chinesischen Mädchen, wirkt auf ihn wie die Entdeckung einer Pflanze, die er noch nie zuvor gesehen hat – neben etwas Tröstlichem ist da ein seltsames Gefühl der Verwunderung. Gemeinsam wachsen die Kinder heran wie zwei verschiedene Gewächse, die Wurzeln in demselben Fleckchen Erde geschlagen haben. Das Tal stellen sie sich als ein endloses Meer vor. Eines Tages erfährt Arsin, dass Meiyu in das 4792 Kilometer entfernte Shanghai umziehen wird – eine Entfernung, die sich kaum begreifen lässt. Sie geht in eine Stadt, die tatsächlich am Meer liegt. Nun ist es ihm allein überlassen, die leisen Veränderungen in ihrer kleinen, zerbrechlichen Welt zu beobachten.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202501604_1_RWD_1920.jpg",
            "section": "Generation Kplus",
            "director": "Jing Yi",
            "link": "https://www.berlinale.de/de/2025/programm/202501604.html"
          },
          {
            "title": "Zirkuskind",
            "description": "Santino ist ein Zirkuskind. Mit seiner Familie und den Tieren zieht er im Wohnwagen durchs Land – heute hier, morgen dort. Zuhause ist für ihn kein fester Ort, sondern das sind seine Eltern Angie und Gitano, sein Bruder Giordano, unzählige Onkel und Tanten, Cousinen und Cousins und ganz besonders sein Uropa Ehe, einer der letzten großen Zirkusdirektoren Deutschlands. Ehe erzählt Santino wilde Geschichten aus seinem langen Leben: vom prachtvollen Elefantenbullen Sahib, seinen eigenen ersten Schritten als Clown und dem Freiheitsgefühl, für das es sich lohnt, alle Strapazen in Kauf zu nehmen. An Santinos elftem Geburtstag stellt Ehe die Frage, was Santino denn einmal in der Manege zeigen möchte, schließlich müsse auch er etwas zu ihrer Gemeinschaft beitragen. Doch wie findet man das nur heraus? Zirkuskind erzählt aus dem Leben der letzten Nomad*innen in Deutschland. Vom Aufwachsen in der Großfamilie, mit den Tieren, und einem Leben ohne Netz und doppelten Boden. Ein dokumentarisches, mit Animationen versehenes Roadmovie über die Kraft von Zugehörigkeit und Gemeinschaft.",
            "image": "https://www.berlinale.de/media/filmstills/2025/generation-2025/202504318_1_RWD_1920.jpg",
            "section": "Generation Kplus",
            "director": "Julia Lemke,",
            "link": "https://www.berlinale.de/de/2025/programm/202504318.html"
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
