<?php
/**
 * Curated reference data for Acharya and Peeth profiles.
 *
 * Compiled by the ShivBodh Research Group from the peethams' own
 * publications and people connected with them, written in our own words
 * (docs/CONTENT-SOURCING.md). Items that rest on tradition rather than
 * documentary record are labelled as such in the text.
 * Unverified details are deliberately omitted rather than guessed.
 *
 * Keyed by profile post slug. Reviewed: October 2026.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function sp_dharma_reviewed_on() {
    return 'October 2026';
}

function sp_dharma_data_all() {
    static $all = null;
    if ( null === $all ) {
        $all = sp_dharma_core_data();
        if ( function_exists( 'sp_sampradaya_data_all' ) ) {
            $all = array_merge( $all, sp_sampradaya_data_all() );
        }
    }
    return $all;
}

function sp_dharma_core_data() {
    return array(

        /* ------------------------------------------------------------ Peeths */

        'purvamnaya-govardhan-matha-puri' => array(
            'kind'     => 'peeth',
            'order'    => 10,
            'name_hi'  => 'पूर्वाम्नाय श्री गोवर्धन पीठ, पुरी',
            'summary'  => 'Purvamnaya Sri Govardhana Peetham at Puri, Odisha, is the eastern of the four Amnaya Peethas traditionally established by Adi Shankaracharya.',
            'infobox'  => array(
                'Official name'      => 'Purvamnaya Sri Govardhana Peetham',
                'Location'           => 'Puri, Odisha',
                'Amnaya (direction)' => 'Purva (East)',
                'Veda'               => 'Rig Veda',
                'Mahavakya'          => 'Prajnanam Brahma',
                'First Acharya'      => 'Padmapadacharya (per common tradition)',
                'Sampradaya'         => 'Bhogavala',
                'Present Acharya'    => 'Swami Nischalananda Saraswati (145th)',
                'Website'            => 'govardhanpeeth.org',
            ),
            'acharyas' => array( 'swami-nishchalananda-saraswati' ),
            'sections' => array(
                'Overview' => '<p>The Govardhana Peetham is the Purvamnaya (eastern) seat of the Advaita Vedanta tradition associated with Adi Shankaracharya. It is linked with the Rig Veda and the Mahavakya <em>Prajnanam Brahma</em> ("Consciousness is Brahman") from the Aitareya Upanishad.</p>',
                'History'  => '<p>Peetham tradition holds that Adi Shankaracharya founded the Math at Puri and placed his disciple Padmapada at its head. Some accounts, including the Sringeri tradition, name Hastamalaka for this seat instead; both views are recorded here as traditions.</p><p>The present Jagadguru, Swami Nischalananda Saraswati, was appointed on 9 February 1992 by his predecessor Swami Niranjanadeva Tirtha and is counted as the 145th Shankaracharya of the Peetham.</p>',
                'Activities' => '<ul><li>Vedic education through the Peetham\'s Ved Gurukulam.</li><li>Public discourses and writings of the Jagadguru on Vedanta, dharma and Vedic mathematics.</li><li>Associated organisations Aditya Vahini and Ananda Vahini.</li></ul>',
            ),
        ),

        'paschimamnaya-dwarka-sharada-peetham' => array(
            'kind'     => 'peeth',
            'order'    => 20,
            'name_hi'  => 'पश्चिमाम्नाय श्री शारदा पीठ, द्वारका',
            'summary'  => 'Paschimamnaya Sri Sharada Peetham at Dwarka, Gujarat, also called Dwarka Sharada Math or Kalika Math, is the western Amnaya Peetha of the Shankara tradition.',
            'infobox'  => array(
                'Official name'      => 'Paschimamnaya Sri Sharada Peetham',
                'Also known as'      => 'Dwarka Sharada Math, Kalika Math',
                'Location'           => 'Dwarka, Gujarat',
                'Amnaya (direction)' => 'Paschima (West)',
                'Veda'               => 'Sama Veda',
                'Mahavakya'          => 'Tat Tvam Asi',
                'First Acharya'      => 'Hastamalakacharya (per tradition)',
                'Sampradaya'         => 'Kitavala',
                'Present Acharya'    => 'Swami Sadanand Saraswati (since 2022)',
            ),
            'acharyas' => array( 'swami-sadanand-saraswati' ),
            'sections' => array(
                'Overview' => '<p>The Dwarka Sharada Peetham is the Paschimamnaya (western) seat. It is associated with the Sama Veda and the Mahavakya <em>Tat Tvam Asi</em> ("That thou art") from the Chandogya Upanishad.</p>',
                'History'  => '<p>Tradition credits Adi Shankaracharya with founding the Math and appointing Hastamalaka as its first head. In the twentieth century Swami Bharati Krishna Tirtha headed the Math from 1921 before moving to the Govardhana Peetham at Puri in 1925.</p><p>Swami Swaroopanand Saraswati led the Peetham from 1982 until his death on 11 September 2022. On 12 September 2022, at Paramhansi Ganga Ashram in Narsinghpur, Madhya Pradesh, Swami Sadanand Saraswati was announced as his successor.</p>',
            ),
        ),

        'uttaraminaya-jyotirmath-peeth' => array(
            'kind'     => 'peeth',
            'order'    => 30,
            'title'    => 'Uttaramnaya Jyotirmath Peeth (Joshimath, Uttarakhand)',
            'name_hi'  => 'उत्तराम्नाय ज्योतिर्मठ, जोशीमठ',
            'summary'  => 'Uttaramnaya Jyotirmath, also called Jyotish Peeth, at Jyotirmath (Joshimath) in Uttarakhand, is the northern Amnaya Peetha of the Shankara tradition.',
            'infobox'  => array(
                'Official name'      => 'Uttaramnaya Jyotirmath (Jyotish Peeth)',
                'Location'           => 'Jyotirmath (Joshimath), Uttarakhand',
                'Amnaya (direction)' => 'Uttara (North)',
                'Veda'               => 'Atharva Veda',
                'Mahavakya'          => 'Ayam Atma Brahma',
                'First Acharya'      => 'Totakacharya (per tradition)',
                'Sampradaya'         => 'Anandavala',
                'Present Acharya'    => 'Succession before the Supreme Court of India',
            ),
            'acharyas' => array( 'swami-avimukta' ),
            'sections' => array(
                'Overview' => '<p>Jyotirmath is the Uttaramnaya (northern) seat, associated with the Atharva Veda and the Mahavakya <em>Ayam Atma Brahma</em> ("This Self is Brahman") from the Mandukya Upanishad. Tradition places Totakacharya as its first head.</p>',
                'History'  => '<p>According to press histories the seat remained vacant for a long period before it was revived in 1941 with Swami Brahmananda Saraswati. Since 1953 the headship has been claimed by rival lines, and Swami Swaroopanand Saraswati\'s claim from 1973 was contested by Swami Vasudevanand Saraswati.</p>',
                'Succession matter' => '<p>After Swami Swaroopanand Saraswati died in September 2022, Swami Avimukteshwaranand Saraswati was declared Shankaracharya of Jyotish Peeth on 12 September 2022. On 15 October 2022 a Supreme Court bench stayed his coronation in the pending succession case. As of the review date of this page, the final verdict was still awaited. Sampreshan presents this matter neutrally and does not take a position on it.</p>',
            ),
        ),

        'dakshinamnaya-sri-sharada-peetham' => array(
            'kind'     => 'peeth',
            'order'    => 40,
            'name_hi'  => 'दक्षिणाम्नाय श्री शारदा पीठ, शृंगेरी',
            'summary'  => 'Dakshinamnaya Sri Sharada Peetham at Sringeri, on the banks of the Tunga in Chikkamagaluru district, Karnataka, is the southern Amnaya Peetha of the Shankara tradition.',
            'infobox'  => array(
                'Official name'      => 'Dakshinamnaya Sri Sharada Peetham',
                'Location'           => 'Sringeri, Chikkamagaluru district, Karnataka',
                'Amnaya (direction)' => 'Dakshina (South)',
                'Veda'               => 'Yajur Veda',
                'Mahavakya'          => 'Aham Brahmasmi',
                'First Acharya'      => 'Sureshvaracharya',
                'Sampradaya'         => 'Bhurivala',
                'Present Jagadguru'  => 'Sri Bharati Tirtha Mahaswamiji (36th)',
                'Successor-designate' => 'Sri Vidhushekhara Bharati Sannidhanam',
                'Website'            => 'sringeri.net',
            ),
            'acharyas' => array( 'sri-bharati-tirtha-mahaswamiji', 'swami-vidhushekhara-bharati' ),
            'sections' => array(
                'Overview' => '<p>Sringeri is the Dakshinamnaya (southern) seat, associated with the Yajur Veda and the Mahavakya <em>Aham Brahmasmi</em> ("I am Brahman") from the Brihadaranyaka Upanishad. The Peetham\'s presiding deity is Sri Sharadamba.</p>',
                'History'  => '<p>The Peetham traces an unbroken line of Jagadgurus from Adi Shankaracharya, with Sureshvaracharya as the first Acharya of the seat.</p><p>The 36th Jagadguru, Sri Bharati Tirtha Mahaswamiji, received sannyasa from his guru Sri Abhinava Vidyatirtha Mahaswamiji (35th) on 11 November 1974 and assumed the Peetham in 1989. On 23 January 2015 he initiated Sri Vidhushekhara Bharati as his successor-designate, who will be the 37th Jagadguru.</p>',
            ),
        ),

        'kanchi-kamakoti-peetham' => array(
            'kind'     => 'peeth',
            'order'    => 50,
            'name_hi'  => 'श्री काञ्ची कामकोटि पीठ, काञ्चीपुरम्',
            'summary'  => 'Sri Kanchi Kamakoti Peetham at Kanchipuram, Tamil Nadu, is a prominent Advaita monastic institution that traces its lineage to Adi Shankaracharya.',
            'infobox'  => array(
                'Official name'      => 'Sri Kanchi Kamakoti Peetham',
                'Location'           => 'Kanchipuram, Tamil Nadu',
                'Lineage'            => '70 Acharyas, per the Peetham\'s tradition',
                'Present Acharya'    => 'Sri Shankara Vijayendra Saraswati (70th)',
                'Junior pontiff'     => 'Sri Satya Chandrashekarendra Saraswati (71st)',
                'Website'            => 'kamakoti.org',
            ),
            'acharyas' => array( 'sri-shankara-vijayendra-saraswati', 'sri-satya-chandrashekarendra-saraswathi-shankaracharya' ),
            // Direction/Veda were never verified for Kanchi; do not display them.
            'clear_meta' => array( '_sp_dharma_direction', '_sp_dharma_veda', '_sp_dharma_mahavakya' ),
            'sections' => array(
                'Overview' => '<p>The Kanchi Kamakoti Peetham is closely associated with the Kamakshi Amman temple at Kanchipuram. The Peetham states that it was founded by Adi Shankaracharya and has an unbroken line of 70 Acharyas.</p>',
                'Note on status' => '<p>The four-Amnaya account of the Shankara tradition lists Puri, Dwarka, Jyotirmath and Sringeri. Kanchi\'s standing as a Shankara-founded Peetha is held by its own tradition and is not accepted by every Math; this page records it as the Peetham\'s tradition.</p>',
                'Present Acharyas' => '<p>Sri Shankara Vijayendra Saraswati, who received sannyasa on 29 May 1983, became the presiding (70th) Acharya on 28 February 2018. On 30 April 2025 (Akshaya Tritiya) he initiated Sri Satya Chandrashekarendra Saraswati as junior pontiff and successor, counted as the 71st.</p>',
                'Institutions' => '<p>Institutions linked with the Peetham include Sri Chandrasekharendra Saraswathi Viswa Mahavidyalaya at Kanchipuram.</p>',
            ),
        ),

        /* ---------------------------------------------------------- Acharyas */

        'swami-nishchalananda-saraswati' => array(
            'kind'      => 'acharya',
            'order'     => 11,
            'peeth'     => 'purvamnaya-govardhan-matha-puri',
            'role'      => '145th Jagadguru Shankaracharya, Govardhana Peetham, Puri',
            'name_hi'   => 'जगद्गुरु शंकराचार्य स्वामी श्री निश्चलानन्द सरस्वती',
            'summary'   => 'Jagadguru Shankaracharya of the Purvamnaya Govardhana Peetham, Puri, since 1992, known for his teaching of Vedanta and Vedic mathematics.',
            'facts'     => array(
                'Purvashrama name'    => 'Nilambar Jha',
                'Born'                => '30 June 1943, Haripur Bakshi Tol, Madhubani, Bihar',
                'Guru'                => 'Swami Karpatri (sannyasa); appointed by Swami Niranjanadeva Tirtha',
                'Sannyasa'            => '18 April 1974, Haridwar',
                'Peethadhipati since' => '9 February 1992',
                'Lineage'             => '145th Shankaracharya, Govardhana Peetham',
            ),
            'about'     => array(
                'Born into a Maithil family of Sanskrit scholars, the son of Pandit Lalvamshi Jha and Smt Gita Devi, he left home at seventeen in search of spiritual life. As a brahmachari named Dhruvachaitanya he studied the Vedas and shastras at Kashi, Vrindavan, Naimisharanya, Badrikashram, Rishikesh, Haridwar, Puri and Sringeri.',
                'He received sannyasa from Swami Karpatri in 1974, studied the Prasthanatrayi and Advaita texts under him, and from 1982 to 1987 studied with Swami Niranjanadeva Tirtha of Puri, who anointed him the 145th Shankaracharya in 1992.',
            ),
            'timeline'  => array(
                '1943' => 'Born on 30 June at Haripur Bakshi Tol, Madhubani district, Bihar.',
                '1966' => 'Held in Tihar jail for 52 days from 9 November for taking part in Swami Karpatri\'s cow-protection movement.',
                '1974' => 'Received sannyasa from Swami Karpatri at Haridwar on 18 April and was named Nischalananda Saraswati.',
                '1982' => 'Began five years of study with Swami Niranjanadeva Tirtha, Shankaracharya of Puri (to 1987).',
                '1992' => 'Anointed 145th Shankaracharya of the Govardhana Peetham on 9 February.',
            ),
            'work'      => array(
                'Discourses and writings on Advaita Vedanta, dharma and national questions, delivered across India.',
                'Scholarship on Vedic mathematics.',
                'Founded Aditya Vahini and Ananda Vahini under the Peeth Parishad.',
                'Oversees the Peetham\'s Ved Gurukulam and Gaushala.',
            ),
        ),

        'swami-sadanand-saraswati' => array(
            'kind'      => 'acharya',
            'order'     => 21,
            'peeth'     => 'paschimamnaya-dwarka-sharada-peetham',
            'role'      => 'Jagadguru Shankaracharya, Dwarka Sharada Peetham',
            'name_hi'   => 'जगद्गुरु शंकराचार्य स्वामी सदानन्द सरस्वती',
            'summary'   => 'Shankaracharya of the Paschimamnaya Sri Sharada Peetham, Dwarka, since September 2022, and a long-time disciple of Swami Swaroopanand Saraswati.',
            'facts'     => array(
                'Purvashrama name'    => 'Ramesh Awasthi',
                'Born'                => '31 August 1958, Bargi (near Karakbel), Narsinghpur, Madhya Pradesh',
                'Guru'                => 'Swami Swaroopanand Saraswati',
                'Sannyasa'            => '15 April 2003 (Dandi sannyasa), Kashi',
                'Peethadhipati since' => '12 September 2022',
                'Lineage'             => 'Shankaracharya, Dwarka Sharada Peetham',
            ),
            'about'     => array(
                'The son of Ayurvedacharya Pandit Vidyadhar Awasthi and Smt Mankunwar Devi, he had his early schooling at Bargi and Sanskrit education at the Jyotirishwar Rishikul Sanskrit Vidyalaya, Jhoteshwar, and later studied vyakarana, nyaya, Veda and Vedanta in Kashi.',
                'He came to the ashram of Swami Swaroopanand Saraswati as a boy and served him for about five decades, becoming Dandi Swami (second-in-command) of the Dwarka Peetham. After his guru\'s passing he was announced as Shankaracharya of Dwarka in 2022.',
            ),
            'timeline'  => array(
                '1958' => 'Born on 31 August at Bargi, near Karakbel, Narsinghpur district, Madhya Pradesh.',
                '1970' => 'Left school as a boy and joined the ashram of Swami Swaroopanand Saraswati at Jhoteshwar.',
                '2003' => 'Received Dandi sannyasa from his guru at Kashi on 15 April and was named Swami Sadanand Saraswati.',
                '2022' => 'Announced as Shankaracharya of the Dwarka Sharada Peetham on 12 September at Paramhansi Ganga Ashram, Narsinghpur.',
            ),
            'work'      => array(
                'Served for decades as disciple and Dandi Swami of Swami Swaroopanand Saraswati.',
                'Has written or translated about a dozen books in Hindi, Sanskrit, Gujarati and English.',
                'Founded Shri Shankaracharya Bal Vidya Niketan, Jhoteshwar, for children of tribal areas.',
            ),
        ),

        'swami-avimukta' => array(
            'kind'      => 'acharya',
            'order'     => 31,
            'peeth'     => 'uttaraminaya-jyotirmath-peeth',
            'title'     => 'Swami Avimukteshwaranand Saraswati',
            'role'      => 'Declared Shankaracharya of Jyotish Peeth (succession sub judice)',
            'name_hi'   => 'स्वामी अविमुक्तेश्वरानन्द सरस्वती',
            'summary'   => 'Disciple of Swami Swaroopanand Saraswati, declared Shankaracharya of Jyotish Peeth in September 2022; the succession is before the Supreme Court of India.',
            'facts'     => array(
                'Purvashrama name'    => 'Umashankar (surname reported as Upadhyay or Pandey)',
                'Born'                => '15 August 1969, Brahmanpur, Pratapgarh, Uttar Pradesh',
                'Guru'                => 'Swami Swaroopanand Saraswati',
                'Sannyasa'            => '15 April 2003 (Dandi sannyasa), Kashi',
                'Peethadhipati since' => 'Declared 12 September 2022; coronation stayed by the Supreme Court',
                'Lineage'             => 'Claimant, Jyotish Peeth (sub judice)',
            ),
            'about'     => array(
                'Born in Pratapgarh district, he studied at Varanasi, completing Shastri and Acharya at Sampurnanand Sanskrit University. While serving Swami Karpatri he came into contact with Swami Swaroopanand Saraswati and became his disciple.',
                'He received Dandi sannyasa in Kashi in 2003 together with Swami Sadanand Saraswati. After Swami Swaroopanand Saraswati\'s death he was declared Shankaracharya of Jyotish Peeth; the Supreme Court stayed his coronation in October 2022 and the succession case remains pending.',
            ),
            'timeline'  => array(
                '1969' => 'Born on 15 August at Brahmanpur, Pratapgarh district, Uttar Pradesh.',
                '2003' => 'Received Dandi sannyasa from Swami Swaroopanand Saraswati at Kashi on 15 April.',
                '2022' => 'Declared Shankaracharya of Jyotish Peeth on 12 September; on 15 October the Supreme Court stayed his coronation.',
                '2026' => 'The Supreme Court matter remained pending at the time of review.',
            ),
            'work'      => array(
                'Studied Sanskrit and shastra at Sampurnanand Sanskrit University, Varanasi.',
                'Long association with Swami Swaroopanand Saraswati as disciple.',
                'Public campaigns on the Ganga and on the heritage of Kashi, as reported in the press.',
            ),
            'notice'    => 'The succession to Jyotish Peeth is sub judice before the Supreme Court of India. This profile records publicly reported facts and takes no position on the dispute.',
        ),

        'sri-bharati-tirtha-mahaswamiji' => array(
            'kind'      => 'acharya',
            // Successor declared: shown only on the Peeth page and the successor's profile.
            'listed'    => false,
            'order'     => 41,
            'peeth'     => 'dakshinamnaya-sri-sharada-peetham',
            'title'     => 'Jagadguru Sri Bharati Tirtha Mahaswamiji',
            'role'      => '36th Jagadguru Shankaracharya, Sringeri Sharada Peetham',
            'name_hi'   => 'जगद्गुरु श्री भारती तीर्थ महास्वामीजी',
            'summary'   => 'The 36th and presiding Jagadguru of the Dakshinamnaya Sri Sharada Peetham, Sringeri.',
            'facts'     => array(
                'Purvashrama name'    => 'Seetharama Anjaneyalu',
                'Born'                => '1951, Andhra Pradesh',
                'Guru'                => 'Sri Abhinava Vidyatirtha Mahaswamiji (35th)',
                'Sannyasa'            => '11 November 1974',
                'Peethadhipati since' => '1989',
                'Lineage'             => '36th Jagadguru, Sringeri Sharada Peetham',
            ),
            'about'     => array(
                'Born into a Telugu Vedic family, he learnt Sanskrit and the Vedas from his father alongside school. At fifteen, in 1966, he approached the 35th Jagadguru, Sri Abhinava Vidyatirtha Mahaswamiji, who accepted him as a disciple.',
                'He was initiated into sannyasa and named successor in 1974, and has presided over the Peetham since 1989. In 2015 he initiated Sri Vidhushekhara Bharati as his successor-designate.',
            ),
            'timeline'  => array(
                '1951' => 'Born in Andhra Pradesh.',
                '1966' => 'Became a disciple of Sri Abhinava Vidyatirtha Mahaswamiji.',
                '1974' => 'Received sannyasa on 11 November and was named successor by Sri Abhinava Vidyatirtha Mahaswamiji.',
                '1989' => 'Assumed the Peetham as the 36th Jagadguru.',
                '2015' => 'Initiated Sri Vidhushekhara Bharati as successor-designate on 23 January.',
            ),
            'work'      => array(
                'Presides over the Sringeri Sharada Peetham and its temples and institutions.',
                'Teaching of Vedanta and the shastras.',
                'Vijaya yatras (tours) across India to guide devotees.',
            ),
        ),

        'swami-vidhushekhara-bharati' => array(
            'kind'      => 'acharya',
            'guru'      => 'sri-bharati-tirtha-mahaswamiji',
            'order'     => 42,
            'peeth'     => 'dakshinamnaya-sri-sharada-peetham',
            'title'     => 'Jagadguru Sri Vidhushekhara Bharati Sannidhanam',
            'role'      => 'Successor-designate (37th), Sringeri Sharada Peetham',
            'name_hi'   => 'जगद्गुरु श्री विधुशेखर भारती सन्निधानम्',
            'summary'   => 'Successor-designate of the Sringeri Sharada Peetham, initiated in 2015 by the 36th Jagadguru, Sri Bharati Tirtha Mahaswamiji.',
            'facts'     => array(
                'Purvashrama name'    => 'Kuppa Venkateshwara Prasada Sharma',
                'Born'                => '24 July 1993, Tirupati, Andhra Pradesh',
                'Guru'                => 'Sri Bharati Tirtha Mahaswamiji (36th)',
                'Sannyasa'            => '23 January 2015, Sringeri',
                'Peethadhipati since' => 'Successor-designate since 23 January 2015',
                'Lineage'             => '37th Jagadguru (designate), Sringeri Sharada Peetham',
            ),
            'about'     => array(
                'Born into a Vedic family at Tirupati, he chanted the entire Krishna Yajur Veda in Moola and Krama and from 2009 studied the shastras directly under Sri Bharati Tirtha Mahaswamiji at Sringeri.',
                'Named successor on 4 January 2015, he received sannyasa on 23 January 2015 and now assists the Jagadguru in the work of the Peetham, giving discourses in several languages.',
            ),
            'timeline'  => array(
                '1993' => 'Born on 24 July at Tirupati, Andhra Pradesh.',
                '2009' => 'Began studying the shastras under the Jagadguru.',
                '2015' => 'Named successor on 4 January; received sannyasa and was initiated as successor-designate on 23 January.',
            ),
            'work'      => array(
                'Chanted the entire Krishna Yajur Veda in Moola and Krama.',
                'Studied Mimamsa and Vedanta under the Jagadguru.',
                'Delivers discourses in several languages.',
            ),
        ),

        'sri-shankara-vijayendra-saraswati' => array(
            'kind'      => 'acharya',
            // Successor declared: shown only on the Peeth page and the successor's profile.
            'listed'    => false,
            'order'     => 51,
            'peeth'     => 'kanchi-kamakoti-peetham',
            'title'     => 'Jagadguru Sri Shankara Vijayendra Saraswati Swamigal',
            'role'      => '70th Peethadhipati, Kanchi Kamakoti Peetham',
            'name_hi'   => 'जगद्गुरु श्री शङ्कर विजयेन्द्र सरस्वती स्वामिगल',
            'summary'   => 'The 70th and presiding Acharya of the Kanchi Kamakoti Peetham since 2018.',
            'facts'     => array(
                'Purvashrama name'    => 'Sankaranarayanan',
                'Born'                => '13 March 1969, Periyapalayam, near Arani, Tamil Nadu',
                'Guru'                => 'Sri Jayendra Saraswati (69th)',
                'Sannyasa'            => '29 May 1983, Kanchipuram',
                'Peethadhipati since' => '28 February 2018',
                'Lineage'             => '70th Acharya, Kanchi Kamakoti Peetham',
            ),
            'about'     => array(
                'He was taken into the Peetham as Sri Sankara Vijayendra Saraswati on 29 May 1983, while still a student at Polur, and trained under both his guru Sri Jayendra Saraswati and his parama-guru Sri Chandrasekharendra Saraswati, travelling with them across India.',
                'He became the presiding Acharya in February 2018. He has started youth training centres such as Bhakti Kendra, Dharma Kendra and Karma Kendra and supported many publications of the Matam in Indian and foreign languages.',
            ),
            'timeline'  => array(
                '1969' => 'Born on 13 March at Periyapalayam, near Arani.',
                '1983' => 'Received sannyasa on 29 May and was named successor by Sri Jayendra Saraswati.',
                '2018' => 'Became the presiding Acharya on 28 February.',
                '2025' => 'Initiated Sri Satya Chandrashekarendra Saraswati as junior pontiff on 30 April.',
            ),
            'work'      => array(
                'Presides over the Peetham and its institutions at Kanchipuram.',
                'Youth training centres: Bhakti Kendra, Dharma Kendra, Karma Kendra.',
                'Publications of the Matam in Indian and foreign languages.',
            ),
        ),

        'sri-satya-chandrashekarendra-saraswathi-shankaracharya' => array(
            'kind'      => 'acharya',
            'guru'      => 'sri-shankara-vijayendra-saraswati',
            'order'     => 52,
            'peeth'     => 'kanchi-kamakoti-peetham',
            'title'     => 'Sri Satya Chandrashekarendra Saraswati',
            'role'      => 'Junior pontiff and successor (71st), Kanchi Kamakoti Peetham',
            'name_hi'   => 'श्री सत्य चन्द्रशेखरेन्द्र सरस्वती',
            'summary'   => 'A Rig Vedic scholar from Andhra Pradesh, initiated as the 71st Acharya and successor of the Kanchi Kamakoti Peetham on 30 April 2025.',
            'facts'     => array(
                'Purvashrama name'    => 'Duddu Satya Venkata Surya Subrahmanya Ganesha Sharma Dravid',
                'Born'                => 'Annavaram region, Andhra Pradesh',
                'Guru'                => 'Sri Shankara Vijayendra Saraswati (70th)',
                'Sannyasa'            => '30 April 2025 (Akshaya Tritiya), Kanchipuram',
                'Peethadhipati since' => 'Junior pontiff since 30 April 2025',
                'Lineage'             => '71st Acharya (successor), Kanchi Kamakoti Peetham',
            ),
            'about'     => array(
                'He comes from a priestly family of Annavaram, where his father serves as a vrata purohit at the Sri Satyanarayana Swamy temple. He studied the Rig Veda and served as a Rig Vedic scholar at the Sri Gnana Saraswathi Devasthanam, Basara, Telangana.',
                'Under the guidance of the Peetham he went on to study the Yajur and Sama Vedas, the Shadangas and the Dashopanishads. On Akshaya Tritiya 2025 he received sannyasa at Kanchipuram as the 71st Acharya.',
            ),
            'timeline'  => array(
                '2009' => 'First had darshan of the Kanchi Shankaracharya, at Tirupati, in May.',
                '2024' => 'From 14 April, continued advanced study under a plan set by the 70th Acharya.',
                '2025' => 'Received sannyasa and was anointed junior pontiff at Kanchipuram on 30 April.',
            ),
            'work'      => array(
                'Rig Vedic scholar.',
                'Served at the Sri Gnana Saraswathi Devasthanam, Basara.',
                'Studies of the Yajur and Sama Vedas, Shadangas and Dashopanishads.',
            ),
        ),
    );
}

function sp_dharma_data( $profile ) {
    $post = get_post( $profile );
    if ( ! $post ) {
        return null;
    }
    $all = sp_dharma_data_all();
    return isset( $all[ $post->post_name ] ) ? $all[ $post->post_name ] : null;
}

/**
 * Published Acharya profiles listed for a Peeth data entry.
 */
function sp_dharma_peeth_acharya_posts( $item ) {
    $posts = array();
    foreach ( isset( $item['acharyas'] ) ? $item['acharyas'] : array() as $slug ) {
        $post = sp_dharma_profile_by_slug( $slug );
        if ( $post ) {
            $posts[] = $post;
        }
    }
    return $posts;
}

/**
 * Whether a profile appears in general listings (home, directory). Acharyas who have
 * declared a successor are shown only on their Peeth page and the successor's profile.
 */
function sp_dharma_is_listed( $slug ) {
    $all = sp_dharma_data_all();
    return ! isset( $all[ $slug ] ) || ! isset( $all[ $slug ]['listed'] ) || false !== $all[ $slug ]['listed'];
}

/**
 * Peeth acharyas that appear in general listings.
 */
function sp_dharma_listed_acharya_posts( $item ) {
    return array_values( array_filter( sp_dharma_peeth_acharya_posts( $item ), static function ( $post ) {
        return sp_dharma_is_listed( $post->post_name );
    } ) );
}

/**
 * Post IDs of profiles kept out of general listings.
 */
function sp_dharma_unlisted_ids() {
    $ids = array();
    foreach ( sp_dharma_data_all() as $slug => $item ) {
        if ( ! sp_dharma_is_listed( $slug ) && ( $post = sp_dharma_profile_by_slug( $slug ) ) ) {
            $ids[] = $post->ID;
        }
    }
    return $ids;
}

function sp_dharma_profile_by_slug( $slug ) {
    $post = get_page_by_path( $slug, OBJECT, sp_dharma_profile_post_type() );
    return ( $post && 'publish' === $post->post_status ) ? $post : null;
}

/**
 * One-time sync: titles, order, excerpts and the two missing presiding
 * Acharya profiles. Bump the version to re-run after editing the data.
 */
function sp_dharma_sync_reference_data() {
    $version = '2026-10-d';
    if ( get_option( 'sp_dharma_data_version' ) === $version ) {
        return;
    }
    $all = sp_dharma_data_all();
    foreach ( $all as $slug => $item ) {
        $post = get_page_by_path( $slug, OBJECT, sp_dharma_profile_post_type() );
        if ( ! $post ) {
            $peeth = isset( $item['peeth'], $all[ $item['peeth'] ] ) ? get_page_by_path( $item['peeth'], OBJECT, sp_dharma_profile_post_type() ) : null;
            $id    = wp_insert_post(
                array(
                    'post_type'   => sp_dharma_profile_post_type(),
                    'post_status' => 'publish',
                    'post_name'   => $slug,
                    'post_title'  => isset( $item['title'] ) ? $item['title'] : $slug,
                    'post_author' => 1,
                ),
                true
            );
            if ( is_wp_error( $id ) ) {
                continue;
            }
            update_post_meta( $id, '_sp_dharma_kind', $item['kind'] );
            if ( $peeth ) {
                foreach ( array( '_sp_dharma_peeth', '_sp_dharma_direction', '_sp_dharma_veda', '_sp_dharma_mahavakya' ) as $key ) {
                    update_post_meta( $id, $key, get_post_meta( $peeth->ID, $key, true ) );
                }
                wp_set_object_terms( $id, wp_get_object_terms( $peeth->ID, 'dharma_peeth', array( 'fields' => 'ids' ) ), 'dharma_peeth' );
            }
            $post = get_post( $id );
        }
        $update = array(
            'ID'           => $post->ID,
            'menu_order'   => (int) $item['order'],
            'post_excerpt' => $item['summary'],
        );
        if ( ! empty( $item['title'] ) ) {
            $update['post_title'] = $item['title'];
        }
        wp_update_post( $update );
        $clear = ! empty( $item['clear_meta'] ) ? $item['clear_meta'] : ( isset( $item['peeth'], $all[ $item['peeth'] ]['clear_meta'] ) ? $all[ $item['peeth'] ]['clear_meta'] : array() );
        foreach ( $clear as $key ) {
            delete_post_meta( $post->ID, $key );
        }
    }
    update_option( 'sp_dharma_data_version', $version );
}
add_action( 'init', 'sp_dharma_sync_reference_data', 30 );
