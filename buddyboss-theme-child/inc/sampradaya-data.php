<?php
/**
 * Reference data for Sampradaya profiles (kind = sampradaya).
 *
 * Same rules as dharma-data.php: well-documented facts only, traditional
 * accounts labelled as tradition, written in our own words.
 * Merged into sp_dharma_data_all(). Reviewed: October 2026.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function sp_sampradaya_base_data() {
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
                'Founder'     => 'Adi Shankaracharya',
                'Philosophy'  => 'Advaita Vedanta',
                'Worship'     => 'Panchayatana puja (Shiva, Vishnu, Devi, Surya, Ganesha)',
                'Monastic order' => 'Dashanami — ten names such as Saraswati, Bharati, Tirtha, Giri, Puri',
                'Key centres' => 'Sringeri, Dwarka, Puri, Jyotirmath; also Kanchi',
            ),
            'sections' => array(
                'Overview' => '<p>Smartas follow the smriti tradition alongside the Vedas and worship the five deities of the Panchayatana as forms of one Brahman. The Dashanami order of sannyasis, traditionally organised by Adi Shankaracharya, takes its name from ten monastic surnames.</p>',
                'History'  => '<p>Tradition credits Adi Shankaracharya with establishing the Amnaya Peethas and grouping the ten names under them. The Peethas continue to be headed by Shankaracharyas.</p>',
                'Practice' => '<p>Study of the Prasthanatrayi (Upanishads, Bhagavad Gita, Brahma Sutras) with Shankara\'s commentaries, Panchayatana worship and sannyasa within the Dashanami order.</p>',
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
                'History'  => '<p>Nimbarka is revered by his followers as among the earliest Vaishnava acharyas. The tradition has been strong in Braj and Rajasthan.</p>',
                'Practice' => '<p>Worship of Radha and Krishna together, recitation of the Gopala mantra, and devotional poetry.</p>',
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
                'Founder'     => 'Swami Ramananda',
                'Philosophy'  => 'Vishishtadvaita',
                'Deity'       => 'Sita–Rama, Hanuman',
                'Key centres' => 'Ayodhya, Varanasi, Chitrakoot, Galta (Jaipur)',
            ),
            'sections' => array(
                'Overview' => '<p>Ramanandis, also called Ramavats, regard Rama and Sita as the Supreme. The order includes renunciant sadhus (vairagis) and householder followers.</p>',
                'History'  => '<p>Tradition holds that Ramananda opened bhakti to people of all castes; poets such as Kabir and Ravidas are counted among his disciples in tradition. The order has a strong presence at the Kumbh Mela.</p>',
                'Practice' => '<p>Recitation of the Rama mantra, the Ramcharitmanas of Tulsidas, and temple and akhara life.</p>',
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
                'History'  => '<p>Chakrapani and Govinda Prabhu are revered as founders, with Chakradhara Swami as the first propagator. The Lilacharitra, a Marathi biography of Chakradhara, is a key early text.</p>',
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
                'Founder'     => 'Lakulisha',
                'Deity'       => 'Shiva as Pashupati',
                'Status'      => 'Historical',
            ),
            'sections' => array(
                'Overview' => '<p>Pashupatas worshipped Shiva as Pashupati, the Lord of beings, and followed a demanding ascetic discipline.</p>',
                'History'  => '<p>The tradition dates to the early centuries CE and influenced later Shaiva schools. It is no longer widely practised as a separate order.</p>',
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
                'Founder'     => 'Sri Chand',
                'Region'      => 'Northern India',
            ),
            'sections' => array(
                'Overview' => '<p>Udasis are renunciant sadhus who were historically among the first missionary traditions linked to Guru Nanak. They have akharas that take part in the Kumbh Mela.</p>',
            ),
        ),

        'sikh-panth' => array(
            'kind'     => 'sampradaya',
            'order'    => 530,
            'family'   => 'Sikh',
            'title'    => 'Sikh Panth (Gurmat)',
            'name_hi'  => 'सिख पंथ (गुरमत)',
            'summary'  => 'The path taught by Guru Nanak (1469–1539) and the nine Gurus after him, centred on remembrance of one God, honest work, sharing and service, with the Guru Granth Sahib as the living Guru.',
            'infobox'  => array(
                'Tradition'   => 'Sikh (Gurmat)',
                'Founder'     => 'Guru Nanak (1469–1539)',
                'Gurus'       => 'Ten Gurus, from Guru Nanak to Guru Gobind Singh',
                'Scripture'   => 'Guru Granth Sahib',
                'Region'      => 'Punjab, then across India and the world',
                'Key centre'  => 'Harmandir Sahib and the Akal Takht, Amritsar',
            ),
            'sections' => array(
                'Overview' => '<p>Guru Nanak taught that the highest aim of human life is union with the one Creator, reached through right belief, right worship and right conduct as shown by the Guru. He rejected empty ritual and caste pride and gave his followers new forms of shared worship, such as the community kitchen (langar).</p>',
                'History'  => '<p>Nine Gurus followed Guru Nanak. Guru Arjan Dev compiled the Adi Granth and was executed on the orders of the Mughal emperor Jahangir. His son, Guru Hargobind, built the Akal Takht and gave the community a martial form. Guru Tegh Bahadur gave his life for freedom of faith. Guru Gobind Singh, the tenth Guru, founded the Khalsa through initiation and passed the Guru\'s authority to the Granth and the Panth.</p>',
                'Practice' => '<p>Daily recitation of the Gurus\' hymns, congregational kirtan in the gurdwara, langar open to all, and seva (selfless service).</p>',
            ),
        ),

        /* ------------------------------------------------ Shramana */

        'jain-dharma' => array(
            'kind'     => 'sampradaya',
            'order'    => 600,
            'family'   => 'Shramana',
            'title'    => 'Jain Dharma',
            'name_hi'  => 'जैन धर्म',
            'summary'  => 'The ancient Shramana tradition of the twenty-four Tirthankaras, from Rishabhanatha to Mahavira, teaching liberation of the soul through right faith, right knowledge and right conduct.',
            'infobox'  => array(
                'Tradition'   => 'Shramana (also called Arhata Darshana)',
                'Tirthankaras' => 'Twenty-four, from Rishabhanatha (Adinatha) to Mahavira',
                'Last Tirthankara' => 'Vardhamana Mahavira (6th century BCE)',
                'Philosophy'  => 'Anekantavada and Syadvada',
                'Key texts'   => 'Agamas; Tattvartha Sutra of Umasvati',
                'Branches'    => 'Digambara and Shvetambara',
            ),
            'sections' => array(
                'Overview' => '<p>Jain Dharma has no single founder. It holds that truth was revealed again and again by Tirthankaras, "ford-makers" who crossed the ocean of rebirth and showed the way to others. The soul (jiva) is distinct from matter, conscious, and takes the size of the body it lives in. Freedom comes when every particle of karma that clings to the soul is shed.</p>',
                'History'  => '<p>Mahavira, the twenty-fourth Tirthankara, was born as Vardhamana at Kundagrama near Vaishali into the Jnatrika clan, son of King Siddhartha and Queen Trishala. He renounced the world, attained kevala jnana (perfect knowledge) in Bihar and taught for about thirty years. His chief disciple Gautama Indrabhuti arranged his teachings into the Angas. The community later divided into the Digambara and Shvetambara traditions.</p>',
                'Practice' => '<p>The five vows (non-violence, truth, non-stealing, chastity and non-possession) are kept fully by monks and nuns and in a lighter form by lay followers. Fasting, daily self-reflection (samayika), temple worship in most branches and the festival of Paryushana mark the year.</p>',
            ),
        ),

        'bauddha-dharma' => array(
            'kind'     => 'sampradaya',
            'order'    => 610,
            'family'   => 'Shramana',
            'title'    => 'Bauddha Dharma',
            'name_hi'  => 'बौद्ध धर्म',
            'summary'  => 'The path taught by Siddhartha Gautama, the Buddha, who awoke at Bodh Gaya and taught the Four Noble Truths and the Eightfold Path as the way out of suffering.',
            'infobox'  => array(
                'Tradition'   => 'Shramana',
                'Founder'     => 'Siddhartha Gautama, the Buddha (6th–5th century BCE)',
                'Philosophy'  => 'Four Noble Truths, dependent origination, no-self',
                'Key texts'   => 'Tripitaka (Sutta, Vinaya, Abhidhamma)',
                'Branches'    => 'Theravada, Mahayana, Vajrayana',
                'Key centres' => 'Bodh Gaya, Sarnath, Kushinagar, Rajgir, Vaishali',
            ),
            'sections' => array(
                'Overview' => '<p>The Buddha taught a middle way between indulgence and harsh self-torture. Everything that arises is impermanent, unsatisfying and without a lasting self; craving binds beings to rebirth; and when craving ends, nirvana is reached. Buddhist thinkers accept two means of knowledge: perception and inference.</p>',
                'History'  => '<p>Siddhartha was born at Lumbini to Shuddhodana, a Shakya ruler, and Mayadevi, who died soon after his birth; he was raised by Mahaprajapati Gautami. Moved by the sight of old age, sickness and death, he left the palace, studied with teachers such as Arada Kalama, practised austerities at Uruvela and awoke under the Bodhi tree at Bodh Gaya. He gave his first teaching to five companions at Sarnath, taught for decades in the Magadhi tongue and passed away at Kushinagar at the age of eighty. His followers held councils to fix the teaching, and King Ashoka sent missions far beyond India.</p>',
                'Practice' => '<p>Taking refuge in the Buddha, the Dharma and the Sangha; keeping the precepts; meditation; and pilgrimage to the places of the Buddha\'s life. Vesak (Buddha Purnima) marks his birth, awakening and passing.</p>',
            ),
        ),

        'ajivika' => array(
            'kind'     => 'sampradaya',
            'order'    => 620,
            'family'   => 'Shramana',
            'title'    => 'Ajivika',
            'name_hi'  => 'आजीविक सम्प्रदाय',
            'summary'  => 'An ascetic Shramana order of the 5th century BCE led by Makkhali Gosala, known for its doctrine of niyati (strict destiny); recorded here as a historical tradition.',
            'infobox'  => array(
                'Tradition'   => 'Shramana (ascetic)',
                'Founder'     => 'Makkhali Gosala (5th century BCE)',
                'Doctrine'    => 'Niyati (fixed destiny)',
                'Patron'      => 'Mauryan emperor Bindusara',
                'Key centre'  => 'Shravasti',
                'Status'      => 'Historical',
            ),
            'sections' => array(
                'Overview' => '<p>Ajivikas held that every event is fixed by destiny (niyati), so that free will and the effort of karma cannot change one\'s course; every soul passes through a set round of lives before release. They taught that all things are made of atoms and lived simple, severe ascetic lives.</p>',
                'History'  => '<p>Makkhali Gosala was a contemporary of Mahavira and the Buddha, and the order competed with both. The Mauryan ruler Bindusara is said to have followed it, Ashoka\'s seventh pillar edict names the Ajivikas, and the Barabar hill caves in Bihar were cut for them. Shravasti was an important centre. None of their own scriptures survive; what is known comes from Jain and Buddhist works and inscriptions.</p>',
            ),
        ),

        /* ------------------------------------------------ Darshana */

        'charvaka-lokayata' => array(
            'kind'     => 'sampradaya',
            'order'    => 700,
            'family'   => 'Darshana',
            'title'    => 'Charvaka (Lokayata) Darshana',
            'name_hi'  => 'चार्वाक (लोकायत) दर्शन',
            'summary'  => 'The materialist school of Indian thought, traced to the teacher Brihaspati, which accepts perception alone as a means of knowledge and denies an afterlife.',
            'infobox'  => array(
                'Tradition'   => 'Darshana (nastika, materialist)',
                'Founder'     => 'Brihaspati (author of the lost sutras)',
                'Other name'  => 'Lokayata',
                'Means of knowledge' => 'Perception (pratyaksha) only',
                'Elements'    => 'Earth, water, fire and air',
                'Known through' => 'Sarvadarshana Sangraha and critiques by other schools',
            ),
            'sections' => array(
                'Overview' => '<p>Charvaka thinkers hold that only what can be perceived is real. The world is made of four elements; consciousness appears when they combine in a body, just as a red colour appears when betel leaf, areca nut and lime are chewed together. The self is the living body, there is no rebirth, and death is the end. The aims worth pursuing are wealth (artha) and pleasure (kama). The name Lokayata, "spread among the people", reflects how widely these ideas were held.</p>',
                'History'  => '<p>The root text, the sutras ascribed to Brihaspati, is lost. The school is known mainly from the opening chapter of Madhavacharya\'s Sarvadarshana Sangraha and from the arguments of rival schools. It should not be read only as a call to indulgence; it also challenged dogma and priestly authority and kept Indian debate sharp.</p>',
            ),
        ),

        'nyaya-darshana' => array(
            'kind'     => 'sampradaya',
            'order'    => 710,
            'family'   => 'Darshana',
            'title'    => 'Nyaya Darshana',
            'name_hi'  => 'न्याय दर्शन',
            'summary'  => 'The astika school of logic and reasoning founded by Gautama (Akshapada), which teaches that liberation follows from true knowledge gained through valid means of knowing.',
            'infobox'  => array(
                'Tradition'   => 'Darshana (astika)',
                'Founder'     => 'Maharshi Gautama (Akshapada)',
                'Root text'   => 'Nyaya Sutra',
                'Other names' => 'Anvikshiki, Tarka Shastra',
                'Means of knowledge' => 'Perception, inference, comparison, testimony',
                'Paired with' => 'Vaisheshika',
            ),
            'sections' => array(
                'Overview' => '<p>Nyaya means "that by which a conclusion is reached". It examines how we know, how a sound argument is built and where reasoning goes wrong. Its method of debate was taken up by grammar, poetics and almost every other school, which is why Kautilya called it "the lamp of all sciences".</p>',
                'History'  => '<p>Vatsyayana wrote the classic commentary on the Nyaya Sutra, followed by Uddyotakara, Vachaspati Mishra and Udayana. In the 14th century Gangesha of Mithila founded Navya-Nyaya (New Logic) with the Tattvachintamani, a style of precise analysis later carried forward at Navadvipa.</p>',
            ),
        ),

        'vaisheshika-darshana' => array(
            'kind'     => 'sampradaya',
            'order'    => 720,
            'family'   => 'Darshana',
            'title'    => 'Vaisheshika Darshana',
            'name_hi'  => 'वैशेषिक दर्शन',
            'summary'  => 'The astika school of Kanada that analyses the world into categories and explains it through eternal atoms; closely allied with Nyaya.',
            'infobox'  => array(
                'Tradition'   => 'Darshana (astika)',
                'Founder'     => 'Maharshi Kanada',
                'Root text'   => 'Vaisheshika Sutra',
                'Key idea'    => 'Atomism (paramanuvada)',
                'Means of knowledge' => 'Perception and inference',
                'Paired with' => 'Nyaya',
            ),
            'sections' => array(
                'Overview' => '<p>Vaisheshika takes its name from vishesha, the ultimate particularity that marks each eternal substance as itself. It is a realist system: the world of many things is real and can be known, and liberation comes from true knowledge of the categories of reality.</p>',
                'History'  => '<p>After Kanada\'s sutras, Prashastapada\'s Padarthadharmasangraha became the main exposition. Shridhara\'s Nyayakandali and Udayana\'s Kiranavali commented on it, and later works such as Shivaditya\'s Saptapadarthi joined it with Nyaya into a single system.</p>',
            ),
        ),

        'sankhya-darshana' => array(
            'kind'     => 'sampradaya',
            'order'    => 730,
            'family'   => 'Darshana',
            'title'    => 'Sankhya Darshana',
            'name_hi'  => 'सांख्य दर्शन',
            'summary'  => 'The astika school of Maharshi Kapila, among the oldest systems of Indian thought, which explains the world through two realities, Purusha and Prakriti.',
            'infobox'  => array(
                'Tradition'   => 'Darshana (astika)',
                'Founder'     => 'Maharshi Kapila',
                'Root text'   => 'Sankhya Karika of Ishvarakrishna',
                'Realities'   => 'Purusha (consciousness) and Prakriti (nature)',
                'Means of knowledge' => 'Perception, inference, testimony',
                'Paired with' => 'Yoga',
            ),
            'sections' => array(
                'Overview' => '<p>Sankhya counts (sankhya) the principles of reality. Purusha is pure consciousness, many and unchanging; Prakriti is unconscious nature made of three gunas. Bondage is the confusion of Purusha with the workings of Prakriti, and freedom (kaivalya) is the clear discrimination between them.</p>',
                'History'  => '<p>Kapila is honoured as the first teacher and the Sankhya Karika of Ishvarakrishna is its classic text. Gaudapada and Vachaspati Mishra (Tattvakaumudi) wrote well-known commentaries. Its account of the tattvas shaped Yoga, the Puranas, Ayurveda and much of later Indian thought.</p>',
            ),
        ),

        'yoga-darshana' => array(
            'kind'     => 'sampradaya',
            'order'    => 740,
            'family'   => 'Darshana',
            'title'    => 'Yoga Darshana',
            'name_hi'  => 'योग दर्शन',
            'summary'  => 'The astika school systematised by Maharshi Patanjali in the Yoga Sutra, which teaches stilling of the mind through practice and detachment so that the seer rests in its own nature.',
            'infobox'  => array(
                'Tradition'   => 'Darshana (astika)',
                'Founder'     => 'Maharshi Patanjali',
                'Root text'   => 'Yoga Sutra (four padas)',
                'Definition'  => 'Yoga is the stilling of the movements of the mind',
                'Method'      => 'Ashtanga Yoga, the eight limbs',
                'Paired with' => 'Sankhya',
            ),
            'sections' => array(
                'Overview' => '<p>Yoga accepts the Sankhya picture of Purusha and Prakriti and adds a practical path and a special Purusha, Ishvara, untouched by affliction and karma. When the movements of the mind (chitta vritti) are stilled through steady practice (abhyasa) and detachment (vairagya), the seer abides in its own nature.</p>',
                'History'  => '<p>Patanjali arranged the Yoga Sutra in four chapters: Samadhi, Sadhana, Vibhuti and Kaivalya. Vyasa\'s commentary is the classic reading, with later works by Vachaspati Mishra (Tattvavaisharadi) and King Bhoja (Rajamartanda).</p>',
            ),
        ),

        'mimamsa-darshana' => array(
            'kind'     => 'sampradaya',
            'order'    => 750,
            'family'   => 'Darshana',
            'title'    => 'Purva Mimamsa Darshana',
            'name_hi'  => 'पूर्व मीमांसा दर्शन',
            'summary'  => 'The astika school of Maharshi Jaimini that interprets the ritual portion of the Vedas and defines dharma as the duty taught by Vedic injunction.',
            'infobox'  => array(
                'Tradition'   => 'Darshana (astika)',
                'Founder'     => 'Maharshi Jaimini',
                'Root text'   => 'Mimamsa Sutra',
                'Focus'       => 'Karma kanda of the Vedas',
                'Schools'     => 'Bhatta (Kumarila) and Prabhakara',
                'Paired with' => 'Uttara Mimamsa (Vedanta)',
            ),
            'sections' => array(
                'Overview' => '<p>Mimamsa means "reflective inquiry". It holds that the Veda is eternal and authorless, and that dharma is what Vedic injunction (chodana) directs. Its rules for reading a text, settling doubt and resolving apparent conflict became the basis of Indian legal and textual interpretation.</p>',
                'History'  => '<p>Shabarasvamin wrote the classic commentary on Jaimini\'s sutras. Kumarila Bhatta and Prabhakara Mishra then founded two schools, and Mandana Mishra and Parthasarathi Mishra developed them further. Vedanta, which studies the knowledge portion of the Vedas, is called Uttara Mimamsa in contrast.</p>',
            ),
        ),
    );
}

/**
 * All sampradaya profiles: base facts plus the template blocks from
 * inc/sampradaya-profile-data.php.
 */
function sp_sampradaya_data_all() {
    static $all = null;
    if ( null === $all ) {
        $all    = sp_sampradaya_base_data();
        $extras = function_exists( 'sp_sampradaya_profile_extras' ) ? sp_sampradaya_profile_extras() : array();
        foreach ( $extras as $slug => $blocks ) {
            if ( isset( $all[ $slug ] ) ) {
                $all[ $slug ] = array_merge( $all[ $slug ], $blocks );
            }
        }
    }
    return $all;
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
        'Sikh'      => __( 'Sikh tradition', 'sampreshan-child' ),
        'Shramana'  => __( 'Shramana tradition', 'sampreshan-child' ),
        'Darshana'  => __( 'Darshana (school of philosophy)', 'sampreshan-child' ),
    );
    return isset( $labels[ $family ] ) ? $labels[ $family ] : $family;
}
