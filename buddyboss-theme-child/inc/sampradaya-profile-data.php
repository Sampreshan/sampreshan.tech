<?php
/**
 * Profile blocks for every Sampradaya page (kind = sampradaya).
 *
 * One template, one shape: each tradition may carry
 *   origin     short "when and where" line for the at-a-glance strip
 *   philosophy html, the school's view of reality and liberation
 *   teachings  title => text, shown as cards
 *   texts      title => text, the sacred and classic works
 *   timeline   when => event
 *   branches   name => text, schools and sub-traditions
 *   centres    name => text, places that matter to the tradition
 *   related    slugs of related sampradaya profiles
 * Merged into sp_sampradaya_data_all(). Written in our own words by the
 * ShivBodh Research Group (docs/CONTENT-SOURCING.md). Reviewed: October 2026.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function sp_sampradaya_profile_extras() {
    return array(

        'smarta-dashanami-sampradaya' => array(
            'origin'     => 'Systematised by Adi Shankaracharya, 8th century CE',
            'philosophy' => '<p>Advaita Vedanta, taught before Shankara by Gaudapada, holds that Brahman alone is real, pure consciousness and the true Self. The world has practical (vyavaharika) reality but not ultimate (paramarthika) reality; it is an appearance on Brahman, like a rope seen as a snake. Maya, also called avidya, both hides Brahman and projects the world. When knowledge dawns, the individual (jiva) knows itself as Brahman: "Brahma satyam jagan mithya, jivo brahmaiva naparah".</p>',
            'teachings'  => array(
                'Brahman alone is real' => 'One undivided consciousness is the ground of all; the self (atman) is not different from it.',
                'Knowledge frees'       => 'Liberation comes from knowledge (jnana) of the Self, prepared by a pure mind, discrimination and detachment.',
                'Panchayatana worship'  => 'Shiva, Vishnu, Devi, Surya and Ganesha are worshipped as forms of the one Brahman.',
                'Sadhana chatushtaya'   => 'Discrimination, dispassion, the six virtues such as calm and self-control, and longing for freedom.',
            ),
            'texts'      => array(
                'Prasthanatrayi'      => 'The Upanishads, the Bhagavad Gita and the Brahma Sutras with Shankara\'s commentaries.',
                'Mandukya Karika'     => 'Gaudapada\'s verses on the Mandukya Upanishad, the earliest Advaita treatise that survives.',
                'Vivekachudamani and Upadesha Sahasri' => 'Works on discrimination and teaching traditionally linked with Shankara.',
            ),
            'timeline'   => array(
                'c. 6th century CE' => 'Gaudapada writes the Mandukya Karika.',
                '8th century CE'    => 'Adi Shankaracharya writes his commentaries and, in tradition, sets up the Amnaya Peethas.',
                'Later centuries'   => 'The Dashanami order and the Peethas carry the teaching forward.',
            ),
            'branches'   => array(
                'Dashanami order' => 'Ten monastic names: Saraswati, Bharati, Tirtha, Ashrama, Giri, Puri, Parvata, Sagara, Vana and Aranya.',
                'Smarta householders' => 'Families who follow the smriti and worship the five deities.',
            ),
            'centres'    => array(
                'Sringeri'   => 'Dakshinamnaya Sharada Peetham, Karnataka.',
                'Dwarka'     => 'Paschimamnaya Sharada Peetham, Gujarat.',
                'Puri'       => 'Purvamnaya Govardhan Matha, Odisha.',
                'Jyotirmath' => 'Uttaramnaya Peetham, Uttarakhand.',
                'Kanchipuram' => 'Kanchi Kamakoti Peetham, Tamil Nadu.',
            ),
            'related'    => array( 'shakta-sampradaya', 'sri-vaishnava-sampradaya', 'mimamsa-darshana' ),
        ),

        'sri-vaishnava-sampradaya' => array(
            'origin'     => 'Tamil Nadu; systematised by Ramanujacharya (1017–1137)',
            'philosophy' => '<p>Vishishtadvaita, "non-dualism of the qualified", teaches that Brahman is one but not bare: it is Narayana, full of auspicious qualities, and souls (chit) and matter (achit) are real and form his body. They depend on him as a body depends on its soul. Liberation is eternal service of Narayana in his presence, gained through devotion and surrender (prapatti) by his grace.</p>',
            'teachings'  => array(
                'Three realities' => 'God (Ishvara), souls (chit) and matter (achit) are all real.',
                'Body and soul'   => 'The world and souls are the body of God, who is their inner controller.',
                'Prapatti'        => 'Complete surrender to Narayana with Sri is the surest way.',
                'Two Vedas'       => 'The Sanskrit Vedas and the Tamil Divya Prabandham are honoured together.',
            ),
            'texts'      => array(
                'Sri Bhashya'          => 'Ramanuja\'s commentary on the Brahma Sutras.',
                'Vedartha Sangraha and Gita Bhashya' => 'Ramanuja\'s summary of the Upanishads and his Gita commentary.',
                'Nalayira Divya Prabandham' => 'The four thousand Tamil hymns of the twelve Alvars, gathered by Nathamuni.',
                'Works of Yamunacharya' => 'Agama Pramanya, Siddhitraya and Gitartha Sangraha.',
            ),
            'timeline'   => array(
                'Before the 9th century' => 'The twelve Alvars sing their Tamil hymns to Vishnu.',
                '9th century'            => 'Nathamuni recovers and arranges the Divya Prabandham.',
                '10th–11th century'      => 'Yamunacharya, Nathamuni\'s grandson, writes in defence of the Pancharatra.',
                '1017–1137'              => 'Life of Ramanujacharya.',
                '13th–14th century'      => 'Vedanta Desika and Pillai Lokacharya; the Vadakalai and Tenkalai schools form.',
            ),
            'branches'   => array(
                'Vadakalai' => 'The northern school, following Vedanta Desika.',
                'Tenkalai'  => 'The southern school, following Pillai Lokacharya and Manavala Mamuni.',
            ),
            'centres'    => array(
                'Srirangam'  => 'The foremost of the 108 Divya Desams.',
                'Tirumala'   => 'Sri Venkateswara temple, Andhra Pradesh.',
                'Kanchipuram' => 'Varadaraja Perumal temple.',
                'Melkote'    => 'Where Ramanuja lived for many years, Karnataka.',
            ),
            'related'    => array( 'ramanandi-sampradaya', 'madhva-sampradaya', 'nimbarka-sampradaya' ),
        ),

        'madhva-sampradaya' => array(
            'origin'     => 'Udupi, Karnataka; Madhvacharya (13th century)',
            'philosophy' => '<p>Dvaita, also called Tattvavada, holds that God and individual souls exist as separate, independent realities. Vishnu alone is fully independent (svatantra); souls and matter are real but dependent (paratantra). Madhva taught five eternal differences: between God and soul, God and matter, soul and soul, soul and matter, and matter and matter. Liberation is reached through knowledge, devotion and the grace of Vishnu.</p>',
            'teachings'  => array(
                'Five differences' => 'God, souls and matter are eternally distinct from one another.',
                'Hari is supreme'   => 'Vishnu is the one fully independent reality.',
                'Grades of souls'   => 'Souls differ in nature and in the bliss they enjoy in liberation.',
                'Bhakti with knowledge' => 'Devotion grounded in right understanding of God.',
            ),
            'texts'      => array(
                'Sarvamula Granthas' => 'Madhva\'s commentaries on the Brahma Sutras, Upanishads and Gita, and other works.',
                'Anuvyakhyana'       => 'His verse exposition of the Brahma Sutras.',
                'Nyaya Sudha'        => 'Jayatirtha\'s great commentary on the Anuvyakhyana.',
            ),
            'timeline'   => array(
                '13th century'  => 'Madhvacharya teaches Dvaita and installs Sri Krishna at Udupi.',
                '14th century'  => 'Jayatirtha writes the classic commentaries.',
                '15th–16th century' => 'Vyasatirtha and the Haridasas such as Purandara Dasa and Kanaka Dasa.',
            ),
            'branches'   => array(
                'Ashta Mathas' => 'The eight mathas of Udupi, which take turns to serve the temple.',
                'Haridasa tradition' => 'Kannada devotional songs that carried Dvaita to the people.',
            ),
            'centres'    => array(
                'Udupi'     => 'Sri Krishna Matha and the Ashta Mathas.',
                'Mantralayam' => 'Brindavana of Sri Raghavendra Tirtha.',
            ),
            'related'    => array( 'sri-vaishnava-sampradaya', 'gaudiya-vaishnava-sampradaya' ),
        ),

        'nimbarka-sampradaya' => array(
            'origin'     => 'Nimbarkacharya; strong in Braj and Rajasthan',
            'philosophy' => '<p>Dvaitadvaita, also called svabhavika bhedabheda, teaches natural difference and non-difference. Souls and the world are different from Brahman because they are limited and dependent, and not different because they cannot exist apart from him, as rays from the sun. Brahman is Krishna with Radha.</p>',
            'teachings'  => array(
                'Difference and non-difference' => 'The soul is both one with and distinct from God.',
                'Radha–Krishna'  => 'The Lord is worshipped together with his eternal consort.',
                'Surrender'      => 'Taking refuge in the Lord and the guru is the means.',
            ),
            'texts'      => array(
                'Vedanta Parijata Saurabha' => 'Nimbarka\'s brief commentary on the Brahma Sutras.',
                'Dashashloki'   => 'Ten verses summing up the doctrine.',
                'Vedanta Kaustubha' => 'Commentary by Srinivasacharya.',
            ),
            'branches'   => array(
                'Sanaka (Hamsa) Sampradaya' => 'The traditional name of this line, traced to the four Kumaras.',
            ),
            'centres'    => array(
                'Salemabad' => 'Nimbarka Peeth, Rajasthan.',
                'Braj'      => 'Vrindavan, Govardhan and the villages of Braj.',
            ),
            'related'    => array( 'sri-vaishnava-sampradaya', 'pushtimarg-vallabha-sampradaya', 'gaudiya-vaishnava-sampradaya' ),
        ),

        'pushtimarg-vallabha-sampradaya' => array(
            'origin'     => 'Vallabhacharya (1479–1531), Braj and Gujarat',
            'philosophy' => '<p>Shuddhadvaita, "pure non-dualism", teaches that the world and souls are real and are Brahman itself, unchanged and unmixed with maya, as sparks are of the same nature as fire. Krishna is the full Brahman, and his grace (pushti) alone nourishes the soul towards loving service.</p>',
            'teachings'  => array(
                'Path of grace' => 'Pushti, the Lord\'s grace, is the cause of devotion and of liberation.',
                'Seva'          => 'Loving service of Krishna in the home and haveli, not renunciation.',
                'Pure Brahman'  => 'The world is a real manifestation of Brahman, not an illusion.',
            ),
            'texts'      => array(
                'Anubhashya'     => 'Vallabha\'s commentary on the Brahma Sutras.',
                'Shodasha Granthas' => 'Sixteen short works on devotion and seva.',
                'Subodhini'      => 'Commentary on the Bhagavata Purana.',
                'Ashtachhap poetry' => 'Songs of Surdas and seven other poets.',
            ),
            'timeline'   => array(
                '1479'       => 'Birth of Vallabhacharya.',
                'Around 1500' => 'Vallabhacharya begins the service of Shrinathji at Govardhan.',
                '16th century' => 'Vitthalnath (Gusainji) organises seva and the Ashtachhap poets.',
                '1672'       => 'Shrinathji is taken to Nathdwara in Rajasthan.',
            ),
            'branches'   => array(
                'Rudra Sampradaya' => 'The older line traced to Vishnuswami, with which Pushtimarg is linked.',
            ),
            'centres'    => array(
                'Nathdwara' => 'Haveli of Shrinathji, Rajasthan.',
                'Braj'      => 'Govardhan, Gokul and Vrindavan.',
            ),
            'related'    => array( 'nimbarka-sampradaya', 'gaudiya-vaishnava-sampradaya' ),
        ),

        'gaudiya-vaishnava-sampradaya' => array(
            'origin'     => 'Bengal; Sri Chaitanya Mahaprabhu (1486–1534)',
            'philosophy' => '<p>Achintya bhedabheda, "inconceivable difference and non-difference", holds that the soul and the world are at once one with and different from Krishna, in a way the mind cannot fully grasp. Krishna is the supreme person, and pure love (prema) for him is the highest goal.</p>',
            'teachings'  => array(
                'Holy name'    => 'Chanting the names of Krishna is the chief practice for this age.',
                'Prema'        => 'Pure love of God is the fifth and highest aim, beyond liberation.',
                'Humility'     => 'Be humbler than grass and more patient than a tree.',
            ),
            'texts'      => array(
                'Bhagavata Purana' => 'The central scripture of the tradition.',
                'Shikshashtaka'    => 'Eight verses attributed to Chaitanya.',
                'Chaitanya Charitamrita' => 'Life and teaching of Chaitanya by Krishnadasa Kaviraja.',
                'Works of the Six Goswamis' => 'Bhakti Rasamrita Sindhu and other works written at Vrindavan.',
            ),
            'timeline'   => array(
                '1486'         => 'Birth of Chaitanya at Navadvip.',
                '16th century' => 'The Six Goswamis restore the holy places of Vrindavan and write the theology.',
                '20th century' => 'The Gaudiya Math and ISKCON carry it worldwide.',
            ),
            'centres'    => array(
                'Navadvip and Mayapur' => 'Birthplace of Chaitanya, West Bengal.',
                'Vrindavan' => 'Temples of the Goswamis.',
                'Puri'      => 'Where Chaitanya spent his later years.',
            ),
            'related'    => array( 'madhva-sampradaya', 'nimbarka-sampradaya', 'ekasarana-dharma' ),
        ),

        'ramanandi-sampradaya' => array(
            'origin'     => 'Swami Ramananda, North India',
            'philosophy' => '<p>The order follows Vishishtadvaita, with Sita–Rama as the supreme Brahman. Devotion and the name of Rama are open to all, whatever their birth.</p>',
            'teachings'  => array(
                'Rama nama' => 'Repetition of the name of Rama as the chief practice.',
                'Bhakti for all' => 'Devotion does not depend on caste or birth.',
                'Vairagya'  => 'Renunciant sadhus keep a life of detachment and service.',
            ),
            'texts'      => array(
                'Ramcharitmanas' => 'Tulsidas\'s retelling of the Ramayana.',
                'Valmiki Ramayana' => 'The ancient epic of Rama.',
            ),
            'centres'    => array(
                'Ayodhya'    => 'Birthplace of Rama.',
                'Varanasi'   => 'Where Ramananda taught.',
                'Chitrakoot' => 'Where Rama lived in exile.',
                'Galta'      => 'Galta Peeth near Jaipur.',
            ),
            'related'    => array( 'sri-vaishnava-sampradaya', 'kabir-panth' ),
        ),

        'warkari-sampradaya' => array(
            'origin'     => 'Maharashtra, from the 13th century',
            'philosophy' => '<p>Warkari saints joined Advaita with devotion: Vitthala is the one Lord, and loving remembrance of him in daily life, without leaving home or work, leads to freedom.</p>',
            'teachings'  => array(
                'Wari'     => 'The yearly walk to Pandharpur is an act of devotion and equality.',
                'Nama'     => 'Singing the name of Vitthala is enough.',
                'Equality' => 'All pilgrims are equal before the Lord.',
            ),
            'texts'      => array(
                'Dnyaneshwari'   => 'Sant Dnyaneshwar\'s Marathi commentary on the Gita.',
                'Amritanubhav'   => 'Dnyaneshwar\'s work on non-dual experience.',
                'Tukaram Gatha'  => 'The abhangas of Sant Tukaram.',
                'Eknathi Bhagavat' => 'Eknath\'s Marathi rendering of the Bhagavata.',
            ),
            'timeline'   => array(
                '13th century' => 'Sant Dnyaneshwar and Sant Namdev.',
                '16th century' => 'Sant Eknath.',
                '17th century' => 'Sant Tukaram.',
            ),
            'centres'    => array(
                'Pandharpur' => 'Temple of Vitthala and Rakhumai.',
                'Alandi'     => 'Samadhi of Sant Dnyaneshwar.',
                'Dehu'       => 'Home of Sant Tukaram.',
            ),
            'related'    => array( 'kabir-panth', 'mahanubhava-sampradaya' ),
        ),

        'swaminarayan-sampradaya' => array(
            'origin'     => 'Gujarat; Swaminarayan (1781–1830)',
            'teachings'  => array(
                'Ekantika dharma' => 'Dharma, knowledge, detachment and devotion together.',
                'Moral code'      => 'Clear rules of conduct for householders and ascetics in the Shikshapatri.',
                'Seva'            => 'Service of temple and society.',
            ),
            'texts'      => array(
                'Vachanamrut' => 'Recorded discourses of Swaminarayan.',
                'Shikshapatri' => 'His 212 verses of conduct.',
            ),
            'centres'    => array(
                'Ahmedabad' => 'Nar Narayan Dev diocese.',
                'Vadtal'    => 'Laxmi Narayan Dev diocese.',
            ),
            'related'    => array( 'sri-vaishnava-sampradaya', 'pushtimarg-vallabha-sampradaya' ),
        ),

        'ekasarana-dharma' => array(
            'origin'     => 'Assam; Srimanta Sankardeva (15th–16th century)',
            'teachings'  => array(
                'One shelter' => 'Exclusive devotion to Krishna alone.',
                'Shravana and kirtana' => 'Hearing and singing of the Lord\'s names and deeds.',
                'Namghar'     => 'A common prayer hall for the whole village.',
            ),
            'texts'      => array(
                'Kirtana Ghosha' => 'Sankardeva\'s devotional verses.',
                'Naam Ghosha'    => 'Madhavdeva\'s collection of verses.',
                'Ankiya Naat'    => 'One-act devotional plays.',
            ),
            'centres'    => array(
                'Majuli' => 'River island with many Satras.',
                'Barpeta' => 'Barpeta Satra.',
            ),
            'related'    => array( 'gaudiya-vaishnava-sampradaya' ),
        ),

        'mahanubhava-sampradaya' => array(
            'origin'     => 'Varhad, Maharashtra, 1267 CE',
            'teachings'  => array(
                'Five Krishnas' => 'Devotion to five incarnations honoured by the tradition.',
                'Non-violence'  => 'A strict ethic of non-harm.',
                'Simple life'   => 'Renunciant life of wandering and few needs.',
            ),
            'texts'      => array(
                'Lilacharitra' => 'Marathi life of Chakradhara Swami.',
                'Sutrapatha'   => 'Sayings of Chakradhara.',
            ),
            'related'    => array( 'warkari-sampradaya' ),
        ),

        'shaiva-siddhanta' => array(
            'origin'     => 'Tamil Nadu; Agamas and the Nayanars',
            'philosophy' => '<p>Shaiva Siddhanta speaks of three eternal realities: Pati (Shiva, the Lord), pashu (the soul) and pasha (the bonds of anava, karma and maya). Through ritual, conduct, yoga and knowledge, and above all Shiva\'s grace, the soul is freed from its bonds and attains Shiva\'s nature.</p>',
            'teachings'  => array(
                'Pati, pashu, pasha' => 'Lord, soul and bonds are all real and eternal.',
                'Four paths'   => 'Charya, kriya, yoga and jnana.',
                'Grace'        => 'Shiva\'s grace, often through the guru, frees the soul.',
            ),
            'texts'      => array(
                'Shaiva Agamas'    => 'The twenty-eight Agamas.',
                'Tirumurai'        => 'Twelve books of Tamil hymns, including Tevaram and Tiruvasagam.',
                'Shivajnanabodham' => 'Meykandar\'s twelve sutras.',
            ),
            'centres'    => array(
                'Chidambaram' => 'Temple of Nataraja.',
                'Dharmapuram and Thiruvavaduthurai' => 'Adheenams (monasteries).',
            ),
            'related'    => array( 'kashmir-shaivism', 'lingayat-veerashaiva', 'pashupata-shaivism' ),
        ),

        'kashmir-shaivism' => array(
            'origin'     => 'Kashmir, from the 9th century',
            'philosophy' => '<p>The Pratyabhijna (recognition) school teaches that Shiva is pure consciousness, both light (prakasha) and self-awareness (vimarsha). The world is his free self-expression, real and not an illusion. Bondage is forgetting this, and liberation is recognising oneself as Shiva.</p>',
            'teachings'  => array(
                'Recognition'  => 'Freedom is recognising one\'s identity with Shiva.',
                'Spanda'       => 'Consciousness is a living, vibrating power.',
                'Thirty-six tattvas' => 'A map of reality from Shiva down to earth.',
            ),
            'texts'      => array(
                'Shiva Sutras'  => 'Revealed to Vasugupta.',
                'Ishvara Pratyabhijna Karika' => 'By Utpaladeva.',
                'Tantraloka'    => 'Abhinavagupta\'s great work.',
                'Pratyabhijnahridayam' => 'Kshemaraja\'s short guide.',
            ),
            'related'    => array( 'shaiva-siddhanta', 'shakta-sampradaya' ),
        ),

        'lingayat-veerashaiva' => array(
            'origin'     => 'Karnataka, 12th century',
            'philosophy' => '<p>The Veerashaiva reading of Vedanta, given by Shripati in the Shrikara Bhashya, is known as Veerashaiva Vishishtadvaita: Shiva is the one reality, qualified by his power, and the soul unites with him through stages of devotion (shatsthala).</p>',
            'teachings'  => array(
                'Ishtalinga' => 'A personal linga worn on the body as the presence of Shiva.',
                'Kayaka'     => 'Honest work is worship.',
                'Dasoha'     => 'Sharing what one earns with the community.',
                'Shatsthala' => 'Six stages of the soul\'s journey to Shiva.',
            ),
            'texts'      => array(
                'Vachanas' => 'Kannada sayings of Basavanna, Allama Prabhu, Akka Mahadevi and others.',
                'Shrikara Bhashya' => 'Shripati\'s commentary on the Brahma Sutras.',
            ),
            'centres'    => array(
                'Basavakalyan' => 'Site of the Anubhava Mantapa.',
                'Kudalasangama' => 'Where Basavanna attained samadhi.',
            ),
            'related'    => array( 'shaiva-siddhanta', 'pashupata-shaivism' ),
        ),

        'nath-sampradaya' => array(
            'origin'     => 'North India and Nepal, c. 11th–13th century',
            'teachings'  => array(
                'Hatha Yoga' => 'Mastery of body and breath as a way to awaken.',
                'Adinath'    => 'Shiva is the first teacher.',
                'Inner alchemy' => 'Transforming the body into a vessel of freedom.',
            ),
            'texts'      => array(
                'Goraksha Shataka' => 'Verses ascribed to Gorakhnath.',
                'Hatha Yoga Pradipika' => 'Svatmarama\'s manual of Hatha Yoga.',
            ),
            'centres'    => array(
                'Gorakhpur' => 'Gorakhnath Math.',
            ),
            'related'    => array( 'yoga-darshana', 'pashupata-shaivism' ),
        ),

        'pashupata-shaivism' => array(
            'origin'     => 'Lakulisha, early centuries CE',
            'teachings'  => array(
                'Pashupati' => 'Shiva is the lord of all bound beings.',
                'Ascetic discipline' => 'Austere practice to end suffering.',
            ),
            'texts'      => array(
                'Pashupata Sutra' => 'Root text with the commentary of Kaundinya.',
            ),
            'related'    => array( 'shaiva-siddhanta', 'lingayat-veerashaiva' ),
        ),

        'shakta-sampradaya' => array(
            'origin'     => 'Ancient; across India',
            'teachings'  => array(
                'Shakti is Brahman' => 'The Goddess is the supreme reality and power of creation.',
                'Mother'  => 'The Divine is approached as Mother.',
                'Yantra and mantra' => 'Worship through sacred diagrams and sound.',
            ),
            'texts'      => array(
                'Devi Mahatmya' => 'Seven hundred verses of the Markandeya Purana.',
                'Lalita Sahasranama' => 'Thousand names of Lalita.',
                'Saundarya Lahari' => 'Hymn to the Goddess.',
            ),
            'branches'   => array(
                'Srikula' => 'Sri Vidya, worship of Lalita Tripurasundari.',
                'Kalikula' => 'Worship of Kali and related forms.',
            ),
            'centres'    => array(
                'Kamakhya'  => 'Assam.',
                'Kalighat'  => 'Kolkata.',
                'Vaishno Devi' => 'Jammu.',
            ),
            'related'    => array( 'smarta-dashanami-sampradaya', 'kashmir-shaivism' ),
        ),

        'ganapatya-sampradaya' => array(
            'origin'     => 'Maharashtra; prominent from the medieval period',
            'teachings'  => array(
                'Ganesha is Brahman' => 'Ganesha is the source and goal of all.',
                'Remover of obstacles' => 'Worship begins every undertaking.',
            ),
            'texts'      => array(
                'Ganapati Atharvashirsha' => 'Upanishad praising Ganesha as Brahman.',
                'Ganesha Purana and Mudgala Purana' => 'Puranas of the tradition.',
            ),
            'centres'    => array(
                'Morgaon' => 'First of the Ashtavinayak temples.',
                'Chinchwad' => 'Seat of Morya Gosavi.',
            ),
            'related'    => array( 'smarta-dashanami-sampradaya' ),
        ),

        'kabir-panth' => array(
            'origin'     => 'Varanasi, 15th century',
            'teachings'  => array(
                'Formless God' => 'Devotion to the One without form.',
                'Against empty ritual' => 'Outer rites without inner truth are useless.',
                'Equality' => 'All humans are equal.',
            ),
            'texts'      => array(
                'Bijak' => 'The main collection of Kabir\'s sayings.',
                'Sakhis and Ramainis' => 'Couplets and songs.',
            ),
            'centres'    => array(
                'Kabir Chaura' => 'Varanasi.',
                'Maghar' => 'Where Kabir passed away.',
            ),
            'related'    => array( 'ramanandi-sampradaya', 'sikh-panth' ),
        ),

        'udasi-sampradaya' => array(
            'origin'     => 'Sri Chand, son of Guru Nanak, 16th century',
            'teachings'  => array(
                'Renunciation' => 'Detached ascetic life.',
                'Nanak\'s word' => 'Spreading the teaching of Guru Nanak.',
            ),
            'centres'    => array(
                'Akharas' => 'Udasin akharas at the Kumbh Mela.',
            ),
            'related'    => array( 'sikh-panth' ),
        ),

        'sikh-panth' => array(
            'origin'     => 'Punjab; Guru Nanak (1469–1539)',
            'teachings'  => array(
                'Naam japna' => 'Constant remembrance of the one God.',
                'Kirat karni' => 'Earning by honest work.',
                'Vand chhakna' => 'Sharing with others.',
                'Seva and langar' => 'Selfless service and the free community kitchen.',
            ),
            'texts'      => array(
                'Guru Granth Sahib' => 'Hymns of the Gurus and saints, the eternal Guru.',
                'Dasam Granth' => 'Writings linked with Guru Gobind Singh.',
            ),
            'timeline'   => array(
                '1469' => 'Birth of Guru Nanak.',
                '1604' => 'Guru Arjan Dev compiles the Adi Granth.',
                '1606' => 'Martyrdom of Guru Arjan Dev; Guru Hargobind builds the Akal Takht.',
                '1675' => 'Martyrdom of Guru Tegh Bahadur.',
                '1699' => 'Guru Gobind Singh founds the Khalsa.',
                '1708' => 'Guruship passes to the Guru Granth Sahib.',
            ),
            'branches'   => array(
                'Khalsa'     => 'Initiated Sikhs who keep the five articles of faith.',
                'Sahajdhari' => 'Sikhs who have not taken Khalsa initiation.',
            ),
            'centres'    => array(
                'Amritsar' => 'Harmandir Sahib and the Akal Takht.',
                'Anandpur Sahib' => 'Where the Khalsa was founded.',
                'Patna Sahib' => 'Birthplace of Guru Gobind Singh.',
            ),
            'related'    => array( 'udasi-sampradaya', 'kabir-panth' ),
        ),

        'jain-dharma' => array(
            'origin'     => 'Ancient; Mahavira, 6th century BCE, Bihar',
            'philosophy' => '<p>Jain thought divides all that exists into living (jiva) and non-living (ajiva). Karma is a fine matter that flows into the soul through action (asrava) and binds it (bandha). Stopping that inflow (samvara) and wearing out what is bound (nirjara) leads to moksha. Because reality has many sides, no single statement captures it fully (anekantavada); every judgement holds true only "in some respect" (syadvada). Jain logic accepts two kinds of knowledge, direct and indirect.</p>',
            'teachings'  => array(
                'Triratna' => 'Right faith (samyak darshana), right knowledge (samyak jnana) and right conduct (samyak charitra).',
                'Ahimsa'   => 'Non-violence towards every living being, in thought, word and deed.',
                'Anekantavada' => 'Truth has many aspects; respect other points of view.',
                'Aparigraha'   => 'Non-possession and limiting one\'s wants.',
            ),
            'texts'      => array(
                'Agamas'    => 'The canon arranged from Mahavira\'s teaching, including the twelve Angas.',
                'Tattvartha Sutra' => 'Umasvati\'s summary of Jain philosophy, accepted by all branches.',
                'Samayasara and Pravachanasara' => 'Works of Acharya Kundakunda.',
                'Nyayavatara and Sanmati Tarka' => 'Logic of Siddhasena Divakara.',
                'Pramana Mimamsa' => 'Hemachandra\'s work on knowledge, 12th century.',
                'Syadvada Manjari' => 'Mallishena\'s commentary, 13th century.',
            ),
            'timeline'   => array(
                'Ancient' => 'Rishabhanatha, the first Tirthankara.',
                'c. 9th–8th century BCE' => 'Parshvanatha, the twenty-third Tirthankara.',
                '6th century BCE' => 'Mahavira teaches in Bihar and attains nirvana at Pavapuri.',
                'Early centuries CE' => 'Umasvati and Kundakunda write the classic works.',
                '6th–9th century' => 'Siddhasena, Samantabhadra, Akalanka and Haribhadra.',
                '12th–17th century' => 'Hemachandra, Mallishena and Yashovijaya.',
            ),
            'branches'   => array(
                'Digambara'  => 'Monks renounce clothing; stricter discipline. Bhadrabahu is honoured as its great teacher.',
                'Shvetambara' => 'Monks and nuns wear white. Sthulabhadra, who stayed in Magadha, is honoured as its great teacher.',
                'Murtipujaka' => 'Shvetambaras who worship images in temples.',
                'Sthanakvasi and Terapanthi' => 'Groups that pray before saints rather than images.',
            ),
            'centres'    => array(
                'Shikharji'  => 'Sammed Shikhar, Jharkhand, where twenty Tirthankaras attained nirvana.',
                'Pavapuri'   => 'Where Mahavira attained nirvana, Bihar.',
                'Palitana'   => 'Shatrunjaya hill temples, Gujarat.',
                'Shravanabelagola' => 'Statue of Bahubali, Karnataka.',
                'Dilwara'    => 'Marble temples at Mount Abu, Rajasthan.',
            ),
            'related'    => array( 'bauddha-dharma', 'ajivika' ),
        ),

        'bauddha-dharma' => array(
            'origin'     => 'Magadha; the Buddha, 6th–5th century BCE',
            'philosophy' => '<p>All conditioned things are impermanent (anitya), unsatisfying (duhkha) and without a lasting self (anatman). Suffering arises through a chain of twelve linked causes (pratityasamutpada), beginning with ignorance. Later Indian schools read the teaching in four ways: Vaibhashika and Sautrantika, which accept outer objects; Yogachara, which holds that consciousness alone is real; and Madhyamaka of Nagarjuna, which teaches emptiness and two levels of truth, conventional (samvriti) and ultimate (paramartha).</p>',
            'teachings'  => array(
                'Four Noble Truths' => 'Suffering, its cause, its ending, and the path to its ending.',
                'Eightfold Path' => 'Right view, intention, speech, action, livelihood, effort, mindfulness and concentration.',
                'Middle way' => 'Neither indulgence nor self-torture.',
                'Nirvana' => 'The ending of craving and of rebirth.',
            ),
            'texts'      => array(
                'Tripitaka' => 'Sutta (discourses), Vinaya (monastic rule) and Abhidhamma (analysis).',
                'Milindapanha' => 'Questions of King Milinda answered by Nagasena.',
                'Mulamadhyamakakarika' => 'Nagarjuna\'s verses on the Middle Way.',
                'Visuddhimagga' => 'Buddhaghosa\'s Path of Purification, 5th century CE.',
            ),
            'timeline'   => array(
                '6th–5th century BCE' => 'Life of the Buddha; first council at Rajagriha under Mahakassapa.',
                'c. 4th century BCE' => 'Second council at Vaishali; the first great division of the Sangha.',
                '3rd century BCE' => 'Third council at Pataliputra under Ashoka; missions abroad.',
                '1st–2nd century CE' => 'Fourth council in Kashmir under Kanishka; rise of Mahayana.',
                '2nd century CE' => 'Nagarjuna founds the Madhyamaka school.',
                '8th century CE' => 'Padmasambhava carries Vajrayana to the Himalaya.',
            ),
            'branches'   => array(
                'Theravada' => 'The way of the elders, using Pali; follows the earliest teaching of personal liberation.',
                'Mahayana'  => 'The great vehicle, using Sanskrit; the bodhisattva seeks awakening for all beings.',
                'Vajrayana' => 'The diamond vehicle, using mantra, mandala and ritual; strong in the Himalaya.',
            ),
            'centres'    => array(
                'Lumbini'   => 'Birthplace of the Buddha.',
                'Bodh Gaya' => 'Where he awoke under the Bodhi tree, Bihar.',
                'Sarnath'   => 'First teaching, near Varanasi; source of India\'s lion capital.',
                'Kushinagar' => 'Mahaparinirvana, Uttar Pradesh.',
                'Rajgir and Vaishali' => 'Places of long stay and teaching.',
            ),
            'related'    => array( 'jain-dharma', 'ajivika' ),
        ),

        'ajivika' => array(
            'origin'     => 'Magadha, 5th century BCE',
            'teachings'  => array(
                'Niyati'   => 'All that happens is fixed by destiny.',
                'Atoms'    => 'All things are made of atoms.',
                'Austerity' => 'A severe, simple ascetic life.',
            ),
            'timeline'   => array(
                '5th century BCE' => 'Makkhali Gosala leads the order.',
                '4th–3rd century BCE' => 'Bindusara follows it; Ashoka\'s edict names it; Barabar caves cut for its monks.',
            ),
            'centres'    => array(
                'Shravasti' => 'Main centre of the order.',
                'Barabar caves' => 'Rock-cut caves in Bihar.',
            ),
            'related'    => array( 'jain-dharma', 'bauddha-dharma', 'charvaka-lokayata' ),
        ),

        'charvaka-lokayata' => array(
            'origin'     => 'Ancient India; teacher Brihaspati',
            'teachings'  => array(
                'Perception only' => 'Only what is directly perceived is accepted as knowledge.',
                'Four elements' => 'Earth, water, fire and air make up all things.',
                'Body is the self' => 'Consciousness arises from matter and ends with the body.',
                'This life' => 'There is no heaven, hell or rebirth; live well here.',
            ),
            'texts'      => array(
                'Brihaspati Sutra' => 'The root text, now lost.',
                'Tattvopaplavasimha' => 'Jayarashi\'s sceptical work, 8th century.',
                'Sarvadarshana Sangraha' => 'Madhavacharya\'s survey, whose first chapter presents the school.',
            ),
            'related'    => array( 'nyaya-darshana', 'ajivika' ),
        ),

        'nyaya-darshana' => array(
            'origin'     => 'Maharshi Gautama; ancient India',
            'philosophy' => '<p>Nyaya lists sixteen topics (padarthas), from the means of knowledge (pramana) and objects of knowledge (prameya) to doubt, purpose, example, tenet, the parts of an argument, debate and its faults. It accepts God as the maker, sustainer and destroyer of the world. Freedom is the complete ending of pain, reached by true knowledge of reality.</p>',
            'teachings'  => array(
                'Four pramanas' => 'Perception, inference, comparison and reliable testimony.',
                'Five-part argument' => 'Thesis, reason, example, application and conclusion.',
                'Hetvabhasa' => 'Study of fallacies, reasons that only look valid.',
                'Realism' => 'Things are known as they are.',
            ),
            'texts'      => array(
                'Nyaya Sutra' => 'Root text of Gautama.',
                'Nyaya Bhashya' => 'Commentary of Vatsyayana.',
                'Nyaya Vartika' => 'By Uddyotakara.',
                'Nyaya Kusumanjali' => 'Udayana\'s proof of God.',
                'Tattvachintamani' => 'Gangesha\'s work that began Navya-Nyaya.',
            ),
            'timeline'   => array(
                'Ancient' => 'Gautama composes the Nyaya Sutra.',
                'c. 5th century CE' => 'Vatsyayana\'s commentary.',
                '10th–11th century' => 'Vachaspati Mishra and Udayana.',
                '14th century' => 'Gangesha founds Navya-Nyaya in Mithila.',
            ),
            'branches'   => array(
                'Prachina Nyaya' => 'The old school of the sutras and commentaries.',
                'Navya-Nyaya' => 'The new logic of Mithila and Navadvipa.',
            ),
            'related'    => array( 'vaisheshika-darshana', 'mimamsa-darshana', 'charvaka-lokayata' ),
        ),

        'vaisheshika-darshana' => array(
            'origin'     => 'Maharshi Kanada; ancient India',
            'philosophy' => '<p>Reality falls into seven categories: substance (dravya), quality (guna), action (karma), universal (samanya), particularity (vishesha), inherence (samavaya) and absence (abhava). There are nine substances: earth, water, fire, air, ether (akasha), time, direction (dik), self (atman) and mind (manas). Visible things are built from eternal, partless atoms (paramanu); an effect is a new thing not present in its cause (asatkaryavada).</p>',
            'teachings'  => array(
                'Seven padarthas' => 'Every knowable thing fits one of seven categories.',
                'Atomism' => 'Earth, water, fire and air are made of eternal atoms.',
                'Nine substances' => 'Including time, direction, self and mind.',
                'Liberation by knowledge' => 'True knowledge of the categories ends bondage.',
            ),
            'texts'      => array(
                'Vaisheshika Sutra' => 'Root text of Kanada.',
                'Padarthadharmasangraha' => 'Prashastapada\'s exposition.',
                'Nyayakandali' => 'Shridhara\'s commentary.',
                'Kiranavali' => 'Udayana\'s commentary.',
                'Saptapadarthi' => 'Shivaditya\'s joint Nyaya–Vaisheshika manual.',
            ),
            'related'    => array( 'nyaya-darshana', 'sankhya-darshana' ),
        ),

        'sankhya-darshana' => array(
            'origin'     => 'Maharshi Kapila; among the oldest schools',
            'philosophy' => '<p>Prakriti in balance holds three gunas: sattva (light), rajas (activity) and tamas (inertia). When the balance is disturbed in the presence of Purusha, twenty-three evolutes unfold: intellect (mahat or buddhi), ego (ahankara), mind, ten senses, five subtle elements and five gross elements. With Purusha these make twenty-five tattvas. An effect already exists in its cause (satkaryavada). Liberation is kaivalya, the aloneness of Purusha, which simply witnesses.</p>',
            'teachings'  => array(
                'Purusha and Prakriti' => 'Two independent realities: consciousness and nature.',
                'Three gunas' => 'Sattva, rajas and tamas make up all of nature.',
                'Twenty-five tattvas' => 'The principles from Prakriti to the gross elements.',
                'Viveka' => 'Discrimination between seer and seen brings freedom.',
            ),
            'texts'      => array(
                'Sankhya Karika' => 'Ishvarakrishna\'s seventy verses.',
                'Gaudapada Bhashya' => 'Early commentary on the Karika.',
                'Tattvakaumudi' => 'Vachaspati Mishra\'s commentary.',
                'Sankhya Sutra' => 'Later sutras ascribed to Kapila.',
            ),
            'related'    => array( 'yoga-darshana', 'vaisheshika-darshana' ),
        ),

        'yoga-darshana' => array(
            'origin'     => 'Maharshi Patanjali',
            'philosophy' => '<p>The mind passes through five conditions, from scattered to fully one-pointed and stilled. Concentration with an object (samprajnata samadhi) leads to concentration without object (asamprajnata), where the seeds of karma are burnt. Ishvara is a special Purusha, the first teacher, whose sign is Om; devotion to him (Ishvara pranidhana) speeds the way. The goal is kaivalya, as in Sankhya.</p>',
            'teachings'  => array(
                'Chitta vritti nirodha' => 'Yoga is the stilling of the mind\'s movements.',
                'Abhyasa and vairagya' => 'Steady practice and detachment.',
                'Ashtanga' => 'Yama, niyama, asana, pranayama, pratyahara, dharana, dhyana and samadhi.',
                'Ishvara pranidhana' => 'Surrender to Ishvara.',
            ),
            'texts'      => array(
                'Yoga Sutra' => 'Patanjali\'s sutras, about 195, in four padas: Samadhi, Sadhana, Vibhuti, Kaivalya.',
                'Vyasa Bhashya' => 'The classic commentary.',
                'Tattvavaisharadi' => 'By Vachaspati Mishra.',
                'Rajamartanda' => 'By King Bhoja.',
            ),
            'related'    => array( 'sankhya-darshana', 'nath-sampradaya' ),
        ),

        'mimamsa-darshana' => array(
            'origin'     => 'Maharshi Jaimini',
            'philosophy' => '<p>Mimamsa holds that a ritual act produces an unseen force (apurva) that later brings its fruit. Words and their meanings are eternal, and so the Veda needs no author to be valid. The Bhatta school accepts six means of knowledge, the Prabhakara school five. Early Mimamsa aimed at heaven; later teachers spoke of freedom from rebirth through duty done without desire.</p>',
            'teachings'  => array(
                'Dharma by injunction' => 'Duty is known from the commands of the Veda.',
                'Apaurusheya Veda' => 'The Veda is eternal and authorless.',
                'Apurva' => 'Ritual action yields an unseen force that bears fruit.',
                'Rules of reading' => 'Principles for interpreting texts and resolving conflict.',
            ),
            'texts'      => array(
                'Mimamsa Sutra' => 'Jaimini\'s sutras in twelve chapters.',
                'Shabara Bhashya' => 'The classic commentary.',
                'Shlokavartika' => 'Kumarila Bhatta\'s verse work.',
                'Brihati' => 'Prabhakara\'s commentary.',
            ),
            'branches'   => array(
                'Bhatta' => 'School of Kumarila Bhatta.',
                'Prabhakara' => 'School of Prabhakara Mishra.',
            ),
            'related'    => array( 'smarta-dashanami-sampradaya', 'nyaya-darshana' ),
        ),
    );
}
