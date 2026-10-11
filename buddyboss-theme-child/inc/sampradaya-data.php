<?php
/**
 * Reference data for Sampradaya profiles (kind = sampradaya).
 *
 * Same rules as dharma-data.php: well-documented facts only, traditional
 * accounts labelled as tradition, sources listed for every entry.
 * Merged into sp_dharma_data_all(). Reviewed: October 2026.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function sp_sampradaya_wiki( $page ) {
    return 'https://en.wikipedia.org/wiki/' . $page;
}

function sp_sampradaya_data_all() {
    $w = 'sp_sampradaya_wiki';
    return array(

        /* ------------------------------------------------------ Smarta */

        'smarta-dashanami-sampradaya' => array(
            'kind'     => 'sampradaya',
            'order'    => 100,
            'family'   => 'Smarta',
            'title'    => 'Smarta and Dashanami Sampradaya',
            'name_hi'  => 'स्मार्त एवं दशनामी सम्प्रदाय',
            'summary'  => 'The Smarta tradition and the Dashanami monastic order associated with Adi Shankaracharya, rooted in Advaita Vedanta and organised around the Shankaracharya Peethas.',
            'infobox'  => array(
                'Tradition'   => 'Smarta',
                'Founder'     => 'Adi Shankaracharya (per tradition)',
                'Philosophy'  => 'Advaita Vedanta',
                'Worship'     => 'Panchayatana puja (Shiva, Vishnu, Devi, Surya, Ganesha)',
                'Monastic order' => 'Dashanami — ten names such as Saraswati, Bharati, Tirtha, Giri, Puri',
                'Key centres' => 'Sringeri, Dwarka, Puri, Jyotirmath; Kanchi per its own tradition',
            ),
            'sections' => array(
                'Overview' => '<p>Smartas follow the smriti tradition alongside the Vedas and worship the five deities of the Panchayatana as forms of one Brahman. The Dashanami order of sannyasis, traditionally organised by Adi Shankaracharya, takes its name from ten monastic surnames.</p>',
                'History'  => '<p>Tradition credits Adi Shankaracharya with establishing the Amnaya Peethas and grouping the ten names under them. The Peethas continue to be headed by Shankaracharyas.</p>',
                'Practice' => '<p>Study of the Prasthanatrayi (Upanishads, Bhagavad Gita, Brahma Sutras) with Shankara\'s commentaries, Panchayatana worship and sannyasa within the Dashanami order.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Smarta tradition' => $w( 'Smarta_tradition' ),
                'Wikipedia — Dashanami Sampradaya' => $w( 'Dashanami_Sampradaya' ),
            ),
        ),

        /* --------------------------------------------------- Vaishnava */

        'sri-vaishnava-sampradaya' => array(
            'kind'     => 'sampradaya',
            'order'    => 110,
            'family'   => 'Vaishnava',
            'title'    => 'Sri Vaishnava Sampradaya',
            'name_hi'  => 'श्री वैष्णव सम्प्रदाय',
            'summary'  => 'A Vaishnava tradition of South India, systematised by Ramanujacharya, devoted to Vishnu with Sri (Lakshmi) and grounded in Vishishtadvaita Vedanta.',
            'infobox'  => array(
                'Tradition'   => 'Vaishnava (one of the four classical sampradayas)',
                'Principal Acharya' => 'Ramanujacharya',
                'Philosophy'  => 'Vishishtadvaita (qualified non-dualism)',
                'Deity'       => 'Vishnu–Narayana with Sri Lakshmi',
                'Key texts'   => 'Sri Bhashya; Nalayira Divya Prabandham of the Alvars',
                'Key centres' => 'Srirangam, Tirupati, Kanchipuram, Melkote',
            ),
            'sections' => array(
                'Overview' => '<p>Sri Vaishnavism unites the Sanskrit Vedanta with the Tamil hymns of the twelve Alvars. It teaches that the soul and the world are real and form the body of Brahman, who is Narayana.</p>',
                'History'  => '<p>The tradition traces its teachers through Nathamuni and Yamunacharya to Ramanujacharya, who wrote the Sri Bhashya on the Brahma Sutras. It later developed two schools, Vadakalai and Tenkalai.</p>',
                'Practice' => '<p>Temple worship in the 108 Divya Desams, recitation of the Divya Prabandham, and surrender (prapatti) to Narayana.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Sri Vaishnavism' => $w( 'Sri_Vaishnavism' ),
            ),
        ),

        'madhva-sampradaya' => array(
            'kind'     => 'sampradaya',
            'order'    => 120,
            'family'   => 'Vaishnava',
            'title'    => 'Madhva (Brahma) Sampradaya',
            'name_hi'  => 'माध्व (ब्रह्म) सम्प्रदाय',
            'summary'  => 'The Vaishnava tradition founded by Madhvacharya in the 13th century, teaching Dvaita (dualist) Vedanta, with its principal centre at Udupi.',
            'infobox'  => array(
                'Tradition'   => 'Vaishnava (one of the four classical sampradayas)',
                'Founder'     => 'Madhvacharya (13th century)',
                'Philosophy'  => 'Dvaita (Tattvavada)',
                'Deity'       => 'Vishnu; Krishna at Udupi',
                'Key centres' => 'Udupi Sri Krishna Matha and the Ashta Mathas',
            ),
            'sections' => array(
                'Overview' => '<p>Dvaita holds that God (Vishnu), individual souls and matter are eternally distinct, and that liberation comes through devotion and the grace of Vishnu.</p>',
                'History'  => '<p>Madhvacharya founded the Krishna temple at Udupi and the eight mathas (Ashta Mathas) whose heads take turns, in the Paryaya system, to administer the temple.</p>',
                'Practice' => '<p>Study of Madhva\'s commentaries, worship of Krishna, and the Haridasa devotional song tradition of Karnataka.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Dvaita Vedanta' => $w( 'Dvaita_Vedanta' ),
                'Wikipedia — Madhvacharya' => $w( 'Madhvacharya' ),
            ),
        ),

        'nimbarka-sampradaya' => array(
            'kind'     => 'sampradaya',
            'order'    => 130,
            'family'   => 'Vaishnava',
            'title'    => 'Nimbarka (Kumara) Sampradaya',
            'name_hi'  => 'निम्बार्क (कुमार) सम्प्रदाय',
            'summary'  => 'A Vaishnava tradition founded by Nimbarkacharya, also called the Kumara or Sanakadi Sampradaya, teaching Dvaitadvaita and devotion to Radha–Krishna.',
            'infobox'  => array(
                'Tradition'   => 'Vaishnava (one of the four classical sampradayas)',
                'Founder'     => 'Nimbarkacharya',
                'Philosophy'  => 'Dvaitadvaita (difference and non-difference)',
                'Deity'       => 'Radha–Krishna',
                'Key centres' => 'Braj region; Nimbarka Peeth, Salemabad, Rajasthan',
            ),
            'sections' => array(
                'Overview' => '<p>Dvaitadvaita teaches that souls are both different and not different from God. Tradition traces the line to the four Kumaras and the sage Narada.</p>',
                'History'  => '<p>Nimbarka is regarded by followers as among the earliest Vaishnava acharyas; scholarly dates for him vary. The tradition has been strong in Braj and Rajasthan.</p>',
                'Practice' => '<p>Worship of Radha and Krishna together, recitation of the Gopala mantra, and devotional poetry.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Nimbarka Sampradaya' => $w( 'Nimbarka_Sampradaya' ),
            ),
        ),

        'pushtimarg-vallabha-sampradaya' => array(
            'kind'     => 'sampradaya',
            'order'    => 140,
            'family'   => 'Vaishnava',
            'title'    => 'Pushtimarg (Vallabha Sampradaya)',
            'name_hi'  => 'पुष्टिमार्ग (वल्लभ सम्प्रदाय)',
            'summary'  => 'The Vaishnava "path of grace" founded by Vallabhacharya around 1500, teaching Shuddhadvaita and loving service of Shrinathji (Krishna).',
            'infobox'  => array(
                'Tradition'   => 'Vaishnava (linked with the Rudra Sampradaya)',
                'Founder'     => 'Vallabhacharya (15th–16th century)',
                'Philosophy'  => 'Shuddhadvaita (pure non-dualism)',
                'Deity'       => 'Krishna as Shrinathji',
                'Key centres' => 'Nathdwara (Rajasthan); havelis across Gujarat and Braj',
            ),
            'sections' => array(
                'Overview' => '<p>Pushti means nourishment by divine grace. Devotees serve Krishna in a household mode called seva, rather than through renunciation.</p>',
                'History'  => '<p>After Vallabhacharya the tradition was led by his son Vitthalnath (Gusainji), whose descendants head the havelis. The Ashtachhap poets, including Surdas, composed its devotional songs.</p>',
                'Practice' => '<p>Eight daily darshans with seasonal offerings, music (haveli sangeet) and festivals in the haveli temples.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Pushtimarg' => $w( 'Pushtimarg' ),
            ),
        ),

        'gaudiya-vaishnava-sampradaya' => array(
            'kind'     => 'sampradaya',
            'order'    => 150,
            'family'   => 'Vaishnava',
            'title'    => 'Gaudiya Vaishnava Sampradaya',
            'name_hi'  => 'गौडीय वैष्णव सम्प्रदाय',
            'summary'  => 'The Krishna-bhakti tradition inspired by Sri Chaitanya Mahaprabhu (1486–1534) of Bengal, centred on chanting the holy names and devotion to Radha–Krishna.',
            'infobox'  => array(
                'Tradition'   => 'Vaishnava',
                'Founder'     => 'Sri Chaitanya Mahaprabhu',
                'Philosophy'  => 'Achintya Bheda-Abheda (inconceivable difference and non-difference)',
                'Deity'       => 'Radha–Krishna',
                'Key centres' => 'Navadvip and Mayapur (West Bengal), Vrindavan, Puri',
            ),
            'sections' => array(
                'Overview' => '<p>Gaudiya Vaishnavism places sankirtana, the congregational chanting of the names of Krishna, at the centre of practice.</p>',
                'History'  => '<p>The Six Goswamis of Vrindavan wrote its theology after Chaitanya. In the twentieth century the Gaudiya Math and the International Society for Krishna Consciousness spread it widely.</p>',
                'Practice' => '<p>Chanting of the Hare Krishna maha-mantra, study of the Bhagavata Purana, and kirtan.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Gaudiya Vaishnavism' => $w( 'Gaudiya_Vaishnavism' ),
            ),
        ),

        'ramanandi-sampradaya' => array(
            'kind'     => 'sampradaya',
            'order'    => 160,
            'family'   => 'Vaishnava',
            'title'    => 'Ramanandi Sampradaya',
            'name_hi'  => 'रामानन्दी सम्प्रदाय',
            'summary'  => 'One of the largest Vaishnava monastic orders, traced to Swami Ramananda, devoted to Sita–Rama and Hanuman.',
            'infobox'  => array(
                'Tradition'   => 'Vaishnava (Ramavat)',
                'Founder'     => 'Swami Ramananda (per tradition)',
                'Philosophy'  => 'Vishishtadvaita',
                'Deity'       => 'Sita–Rama, Hanuman',
                'Key centres' => 'Ayodhya, Varanasi, Chitrakoot, Galta (Jaipur)',
            ),
            'sections' => array(
                'Overview' => '<p>Ramanandis, also called Ramavats, regard Rama and Sita as the Supreme. The order includes renunciant sadhus (vairagis) and householder followers.</p>',
                'History'  => '<p>Tradition holds that Ramananda opened bhakti to people of all castes; poets such as Kabir and Ravidas are counted among his disciples in tradition. The order has a strong presence at the Kumbh Mela.</p>',
                'Practice' => '<p>Recitation of the Rama mantra, the Ramcharitmanas of Tulsidas, and temple and akhara life.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Ramanandi Sampradaya' => $w( 'Ramanandi_Sampradaya' ),
            ),
        ),

        'warkari-sampradaya' => array(
            'kind'     => 'sampradaya',
            'order'    => 170,
            'family'   => 'Vaishnava',
            'title'    => 'Warkari Sampradaya',
            'name_hi'  => 'वारकरी सम्प्रदाय',
            'summary'  => 'The bhakti tradition of Maharashtra devoted to Vitthala (Vithoba) of Pandharpur, known for the annual Wari pilgrimage on foot.',
            'infobox'  => array(
                'Tradition'   => 'Vaishnava bhakti',
                'Saints'      => 'Sant Dnyaneshwar, Namdev, Eknath, Tukaram',
                'Deity'       => 'Vitthala (Vithoba) and Rakhumai',
                'Key texts'   => 'Dnyaneshwari; abhangas of the saints',
                'Key centre'  => 'Pandharpur, Maharashtra',
            ),
            'sections' => array(
                'Overview' => '<p>Warkari means "one who makes the pilgrimage". Followers walk to Pandharpur each year carrying the palkhis (palanquins) of the saints.</p>',
                'History'  => '<p>The tradition took shape with Sant Dnyaneshwar in the 13th century and grew through the poet-saints Namdev, Eknath and Tukaram.</p>',
                'Practice' => '<p>Singing abhangas in bhajan and kirtan, the Ashadhi and Kartiki Ekadashi pilgrimages, and wearing the tulsi-bead mala.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Warkari' => $w( 'Warkari' ),
            ),
        ),

        'swaminarayan-sampradaya' => array(
            'kind'     => 'sampradaya',
            'order'    => 180,
            'family'   => 'Vaishnava',
            'title'    => 'Swaminarayan Sampradaya',
            'name_hi'  => 'स्वामिनारायण सम्प्रदाय',
            'summary'  => 'A Vaishnava tradition founded by Bhagwan Swaminarayan (Sahajanand Swami, 1781–1830) in Gujarat.',
            'infobox'  => array(
                'Tradition'   => 'Vaishnava',
                'Founder'     => 'Swaminarayan (Sahajanand Swami)',
                'Philosophy'  => 'Akshar-Purushottam Darshan / Vishishtadvaita (varies by branch)',
                'Key texts'   => 'Vachanamrut, Shikshapatri',
                'Key centres' => 'Ahmedabad and Vadtal (Gujarat)',
            ),
            'sections' => array(
                'Overview' => '<p>Followers worship Swaminarayan and observe a disciplined moral code set out in the Shikshapatri.</p>',
                'History'  => '<p>Swaminarayan established the Ahmedabad and Vadtal dioceses. Later organisations, including BAPS, emerged from the tradition.</p>',
                'Practice' => '<p>Daily puja, temple worship, observance of the Shikshapatri and community service.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Swaminarayan Sampradaya' => $w( 'Swaminarayan_Sampradaya' ),
            ),
        ),

        'ekasarana-dharma' => array(
            'kind'     => 'sampradaya',
            'order'    => 190,
            'family'   => 'Vaishnava',
            'title'    => 'Ekasarana Dharma',
            'name_hi'  => 'एकशरण धर्म',
            'summary'  => 'The Vaishnava tradition of Assam propagated by Srimanta Sankardeva in the 15th–16th century, centred on devotion to Krishna through shravan and kirtan.',
            'infobox'  => array(
                'Tradition'   => 'Vaishnava',
                'Founder'     => 'Srimanta Sankardeva',
                'Deity'       => 'Krishna',
                'Institutions' => 'Satras (monasteries) and Namghars (prayer halls)',
                'Key centre'  => 'Majuli and the Satras of Assam',
            ),
            'sections' => array(
                'Overview' => '<p>Ekasarana means "shelter in one": exclusive devotion to Krishna, with less emphasis on Vedic ritual.</p>',
                'History'  => '<p>Sankardeva and his disciple Madhavdeva founded the Satra system, which shaped Assamese music, drama (Ankiya Naat) and dance (Sattriya).</p>',
                'Practice' => '<p>Congregational listening to and singing of Krishna\'s names and deeds in the Namghar.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Ekasarana Dharma' => $w( 'Ekasarana_Dharma' ),
            ),
        ),

        'mahanubhava-sampradaya' => array(
            'kind'     => 'sampradaya',
            'order'    => 200,
            'family'   => 'Vaishnava',
            'title'    => 'Mahanubhava Sampradaya',
            'name_hi'  => 'महानुभाव सम्प्रदाय',
            'summary'  => 'A Krishna-devotional tradition of Maharashtra, generally described as founded by Chakradhara Swami and formed in the Varhad region in 1267 CE.',
            'infobox'  => array(
                'Tradition'   => 'Krishnaite',
                'Founder'     => 'Chakradhara Swami (per most accounts)',
                'Deity'       => 'Krishna; the five Krishnas of the tradition',
                'Region'      => 'Maharashtra (Vidarbha / Varhad)',
            ),
            'sections' => array(
                'Overview' => '<p>The Mahanubhava Pantha, also called Jai Krishni Pantha, worships Krishna and is known for early Marathi literature.</p>',
                'History'  => '<p>Some sources name Chakrapani and Govinda Prabhu as founders with Chakradhara Swami as the first propagator. The Lilacharitra, a Marathi biography of Chakradhara, is a key early text.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Mahanubhava' => $w( 'Mahanubhava' ),
            ),
        ),

        /* ------------------------------------------------------ Shaiva */

        'shaiva-siddhanta' => array(
            'kind'     => 'sampradaya',
            'order'    => 300,
            'family'   => 'Shaiva',
            'title'    => 'Shaiva Siddhanta',
            'name_hi'  => 'शैव सिद्धान्त',
            'summary'  => 'A major Shaiva tradition, especially strong in Tamil Nadu, based on the Shaiva Agamas and the Tamil Tirumurai hymns.',
            'infobox'  => array(
                'Tradition'   => 'Shaiva',
                'Key teachers' => 'The Nayanars; Meykandar (Shivajnanabodham)',
                'Deity'       => 'Shiva',
                'Key texts'   => 'Shaiva Agamas, Tirumurai, Shivajnanabodham',
                'Key centres' => 'Chidambaram; Adheenams such as Dharmapuram and Thiruvavaduthurai',
            ),
            'sections' => array(
                'Overview' => '<p>Shaiva Siddhanta describes three realities — Pati (God), pashu (soul) and pasha (bonds) — and teaches liberation through Shiva\'s grace.</p>',
                'History'  => '<p>The devotional hymns of the 63 Nayanars were collected in the Tirumurai. Meykandar\'s Shivajnanabodham (13th century) became the basis of its Tamil theology.</p>',
                'Practice' => '<p>Agamic temple worship, recitation of the Tirumurai and guidance from the Adheenams (monasteries).</p>',
            ),
            'sources'  => array(
                'Wikipedia — Shaiva Siddhanta' => $w( 'Shaiva_Siddhanta' ),
            ),
        ),

        'kashmir-shaivism' => array(
            'kind'     => 'sampradaya',
            'order'    => 310,
            'family'   => 'Shaiva',
            'title'    => 'Kashmir Shaivism (Trika)',
            'name_hi'  => 'काश्मीर शैव दर्शन (त्रिक)',
            'summary'  => 'The non-dual Shaiva tradition of Kashmir, associated with the Shiva Sutras of Vasugupta and the works of Abhinavagupta.',
            'infobox'  => array(
                'Tradition'   => 'Shaiva (non-dual)',
                'Key teachers' => 'Vasugupta, Somananda, Utpaladeva, Abhinavagupta, Kshemaraja',
                'Philosophy'  => 'Pratyabhijna (recognition), Trika',
                'Key texts'   => 'Shiva Sutras, Tantraloka',
                'Region'      => 'Kashmir',
            ),
            'sections' => array(
                'Overview' => '<p>Kashmir Shaivism teaches that the world is the free self-expression of Shiva as pure consciousness, and that liberation is recognition of one\'s identity with Shiva.</p>',
                'History'  => '<p>Its classical period runs from Vasugupta (9th century) to Abhinavagupta and Kshemaraja (10th–11th centuries). Swami Lakshmanjoo was a well-known 20th-century teacher.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Kashmir Shaivism' => $w( 'Kashmir_Shaivism' ),
            ),
        ),

        'lingayat-veerashaiva' => array(
            'kind'     => 'sampradaya',
            'order'    => 320,
            'family'   => 'Shaiva',
            'title'    => 'Lingayat / Veerashaiva Tradition',
            'name_hi'  => 'लिंगायत / वीरशैव परम्परा',
            'summary'  => 'A Shaiva tradition of Karnataka shaped by Basavanna and the Sharanas in the 12th century, known for the Vachana literature and worship of the Ishtalinga.',
            'infobox'  => array(
                'Tradition'   => 'Shaiva',
                'Key figures' => 'Basavanna, Allama Prabhu, Akka Mahadevi',
                'Deity'       => 'Shiva as Ishtalinga',
                'Key texts'   => 'Vachanas (Kannada)',
                'Region'      => 'Karnataka and neighbouring states',
            ),
            'sections' => array(
                'Overview' => '<p>Followers wear an Ishtalinga on the body and value devotion, work (kayaka) and equality, as expressed in the Vachanas.</p>',
                'History'  => '<p>Basavanna and the Sharanas gathered at the Anubhava Mantapa at Kalyana in the 12th century. The relationship between the terms Lingayat and Veerashaiva is debated; both are recorded here.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Lingayatism' => $w( 'Lingayatism' ),
            ),
        ),

        'nath-sampradaya' => array(
            'kind'     => 'sampradaya',
            'order'    => 330,
            'family'   => 'Shaiva',
            'title'    => 'Nath Sampradaya',
            'name_hi'  => 'नाथ सम्प्रदाय',
            'summary'  => 'A Shaiva yogic tradition traced to Matsyendranath and Gorakhnath, closely linked with the development of Hatha Yoga.',
            'infobox'  => array(
                'Tradition'   => 'Shaiva (yogic)',
                'Key teachers' => 'Adinath (Shiva), Matsyendranath, Gorakhnath',
                'Practice'    => 'Hatha Yoga',
                'Key centre'  => 'Gorakhnath Math, Gorakhpur',
            ),
            'sections' => array(
                'Overview' => '<p>Nath yogis, also called Kanphata ("split-ear") yogis, worship Shiva as Adinath and follow the yogic discipline taught by Gorakhnath.</p>',
                'History'  => '<p>The tradition spread across North India and Nepal between roughly the 11th and 13th centuries. Several Hatha Yoga texts are attributed to Nath teachers.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Nath' => $w( 'Nath' ),
            ),
        ),

        'pashupata-shaivism' => array(
            'kind'     => 'sampradaya',
            'order'    => 340,
            'family'   => 'Shaiva',
            'title'    => 'Pashupata Shaivism',
            'name_hi'  => 'पाशुपत शैव',
            'summary'  => 'Among the oldest named Shaiva traditions, associated with Lakulisha; recorded here as a historical tradition.',
            'infobox'  => array(
                'Tradition'   => 'Shaiva (ascetic)',
                'Founder'     => 'Lakulisha (per tradition)',
                'Deity'       => 'Shiva as Pashupati',
                'Status'      => 'Historical',
            ),
            'sections' => array(
                'Overview' => '<p>Pashupatas worshipped Shiva as Pashupati, the Lord of beings, and followed a demanding ascetic discipline.</p>',
                'History'  => '<p>The tradition is attested from the early centuries CE and influenced later Shaiva schools. It is no longer widely practised as a separate order.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Pashupata Shaivism' => $w( 'Pashupata_Shaivism' ),
            ),
        ),

        /* ------------------------------------------------------ Shakta */

        'shakta-sampradaya' => array(
            'kind'     => 'sampradaya',
            'order'    => 400,
            'family'   => 'Shakta',
            'title'    => 'Shakta Sampradaya (incl. Sri Vidya)',
            'name_hi'  => 'शाक्त सम्प्रदाय (श्रीविद्या सहित)',
            'summary'  => 'Traditions that worship the Divine Mother (Devi / Shakti) as the supreme reality, including the Sri Vidya and Kalikula lineages.',
            'infobox'  => array(
                'Tradition'   => 'Shakta',
                'Deity'       => 'Devi in forms such as Durga, Kali, Lalita Tripurasundari',
                'Key texts'   => 'Devi Mahatmya, Lalita Sahasranama, Shakta Tantras',
                'Lineages'    => 'Srikula (Sri Vidya), Kalikula',
                'Key centres' => 'Shakti Peethas such as Kamakhya, Kalighat',
            ),
            'sections' => array(
                'Overview' => '<p>Shaktas see the Goddess as the power behind creation and as Brahman itself.</p>',
                'Sri Vidya' => '<p>Sri Vidya worships Lalita Tripurasundari through the Sri Chakra (Sri Yantra) and the Panchadashi mantra. It is also practised within the Smarta tradition.</p>',
                'Practice' => '<p>Navaratri, recitation of the Devi Mahatmya, and pilgrimage to the Shakti Peethas.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Shaktism' => $w( 'Shaktism' ),
                'Wikipedia — Shri Vidya' => $w( 'Shri_Vidya' ),
            ),
        ),

        /* -------------------------------------------------------- Other */

        'ganapatya-sampradaya' => array(
            'kind'     => 'sampradaya',
            'order'    => 500,
            'family'   => 'Ganapatya',
            'title'    => 'Ganapatya Sampradaya',
            'name_hi'  => 'गाणपत्य सम्प्रदाय',
            'summary'  => 'The tradition that worships Ganesha as the supreme deity, strongest in Maharashtra.',
            'infobox'  => array(
                'Tradition'   => 'Ganapatya',
                'Deity'       => 'Ganesha',
                'Key texts'   => 'Ganesha Purana, Mudgala Purana, Ganapati Atharvashirsha',
                'Key centres' => 'Morgaon and the Ashtavinayak temples, Maharashtra',
            ),
            'sections' => array(
                'Overview' => '<p>Ganapatyas regard Ganesha as Brahman, the source of all.</p>',
                'History'  => '<p>The saint Morya Gosavi of Chinchwad was an important figure in its Maharashtrian form.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Ganapatya' => $w( 'Ganapatya' ),
            ),
        ),

        'kabir-panth' => array(
            'kind'     => 'sampradaya',
            'order'    => 510,
            'family'   => 'Sant',
            'title'    => 'Kabir Panth',
            'name_hi'  => 'कबीर पंथ',
            'summary'  => 'A Sant tradition based on the teachings of the 15th-century poet-saint Kabir, emphasising devotion to a formless God and equality.',
            'infobox'  => array(
                'Tradition'   => 'Sant (nirguna bhakti)',
                'Inspiration' => 'Sant Kabir',
                'Key texts'   => 'Bijak and other collections of Kabir\'s verses',
                'Key centre'  => 'Kabir Chaura, Varanasi',
            ),
            'sections' => array(
                'Overview' => '<p>Kabir Panth emphasises devotion to a formless God and rejects ritualism and social divisions. Its followers are spread across India and abroad.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Kabir Panth' => $w( 'Kabir_panth' ),
            ),
        ),

        'udasi-sampradaya' => array(
            'kind'     => 'sampradaya',
            'order'    => 520,
            'family'   => 'Sant',
            'title'    => 'Udasi Sampradaya',
            'name_hi'  => 'उदासीन सम्प्रदाय',
            'summary'  => 'An ascetic order of sadhus in northern India, traced to Sri Chand, son of Guru Nanak, also known as Udasins or Nanak Putras.',
            'infobox'  => array(
                'Tradition'   => 'Udasipanth (ascetic)',
                'Founder'     => 'Sri Chand (per tradition)',
                'Region'      => 'Northern India',
            ),
            'sections' => array(
                'Overview' => '<p>Udasis are renunciant sadhus who were historically among the first missionary traditions linked to Guru Nanak. They have akharas that take part in the Kumbh Mela.</p>',
            ),
            'sources'  => array(
                'Wikipedia — Udasi' => $w( 'Udasi' ),
            ),
        ),
    );
}

/**
 * Display label for a sampradaya family.
 */
function sp_sampradaya_family_label( $family ) {
    $labels = array(
        'Smarta'    => __( 'Smarta', 'sampreshan-child' ),
        'Vaishnava' => __( 'Vaishnava', 'sampreshan-child' ),
        'Shaiva'    => __( 'Shaiva', 'sampreshan-child' ),
        'Shakta'    => __( 'Shakta', 'sampreshan-child' ),
        'Ganapatya' => __( 'Ganapatya', 'sampreshan-child' ),
        'Sant'      => __( 'Sant tradition', 'sampreshan-child' ),
    );
    return isset( $labels[ $family ] ) ? $labels[ $family ] : $family;
}
