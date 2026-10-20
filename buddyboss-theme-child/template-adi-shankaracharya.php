<?php
/**
 * Template Name: Adi Shankaracharya
 *
 * Aadyaguru Shankaracharya Ji: life, the four Amnaya Peethams and links to
 * each Peetham profile. Replaces the imported ShivBodh Trust Elementor page
 * (content-archive/shivbodhtrust/adi-shankaracharya/).
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$sp_portrait = content_url( 'uploads/2026/03/Aadiguru1staacharya.png' );

// Direction, Veda and profile slug for each Peetham.
$sp_peeths = array(
    array( 'peeth-purva', __( 'East', 'sampreshan-child' ), 'ऋग्वेद · Rig Veda', 'गोवर्धन मठ, पुरी', 'Govardhan Matha, Puri (Odisha)', 'purvamnaya-govardhan-matha-puri' ),
    array( 'peeth-dakshin', __( 'South', 'sampreshan-child' ), 'यजुर्वेद · Yajur Veda', 'शृंगेरी शारदा पीठ', 'Sringeri Sharada Peetham (Karnataka)', 'dakshinamnaya-sri-sharada-peetham' ),
    array( 'peeth-paschim', __( 'West', 'sampreshan-child' ), 'सामवेद · Sama Veda', 'द्वारका शारदा पीठ', 'Dwarka Sharada Peetham (Gujarat)', 'paschimamnaya-dwarka-sharada-peetham' ),
    array( 'peeth-uttar', __( 'North', 'sampreshan-child' ), 'अथर्ववेद · Atharva Veda', 'ज्योतिर्मठ', 'Jyotirmath, Joshimath (Uttarakhand)', 'uttaraminaya-jyotirmath-peeth' ),
);

get_header();
?>

<main id="main" class="site-main sp-page sp-adiguru" role="main">

    <header class="sp-page__hero sp-adiguru__hero">
        <img class="sp-adiguru__portrait" src="<?php echo esc_url( $sp_portrait ); ?>" alt="<?php esc_attr_e( 'Jagadguru Adi Shankaracharya', 'sampreshan-child' ); ?>" width="220" height="220" loading="eager" decoding="async">
        <p class="sp-adiguru__mantra">॥ ॐ गुरवे नमः ॥</p>
        <h1 class="sp-page__hero-title"><?php esc_html_e( 'Jagadguru Adi Shankaracharya', 'sampreshan-child' ); ?></h1>
        <p class="sp-page__hero-sub"><?php esc_html_e( 'The eternal reviver of Sanatana Dharma', 'sampreshan-child' ); ?></p>
    </header>

    <div class="sp-page__body">

        <section class="sp-page__card">
            <h2 class="sp-page__section-title"><?php sp_icon( 'sym-om', 'sp-icon sp-icon--lg', '' ); ?> आदि शंकराचार्य</h2>
            <p lang="hi">आदि शंकराचार्य सनातन धर्म के महानतम दार्शनिक और अद्वैत वेदान्त के प्रणेता थे। उनका जन्म केरल के कालडी गाँव में शिवगुरु और आर्याम्बा के घर हुआ। बाल्यकाल में ही उन्होंने वेदों का अध्ययन पूर्ण कर लिया और अपने गुरु गोविन्द भगवत्पाद से ज्ञान प्राप्त कर पूरे भारतवर्ष में अद्वैत दर्शन का प्रसार किया।</p>
            <p>Jagadguru Adi Shankaracharya was a philosopher, Vedic scholar and the foremost teacher of Advaita Vedanta (non-dualism). Born in Kalady, Kerala, to Shivaguru and Aryamba, he showed extraordinary intellect as a child. He left home young and went to the banks of the Narmada, where he became the disciple of Guru Govinda Bhagavatpada and mastered the Vedic scriptures.</p>
            <p>In a short life he wrote commentaries (Bhashyas) on the Upanishads, the Brahma Sutras and the Bhagavad Gita, brought the many streams of Hindu worship together, and revived Sanatana Dharma across the subcontinent.</p>
        </section>

        <section class="sp-page__card">
            <h2 class="sp-page__section-title"><?php sp_icon( 'peeth-amnaya', 'sp-icon sp-icon--lg', '' ); ?> <?php esc_html_e( 'The four Amnaya Peethams', 'sampreshan-child' ); ?></h2>
            <p lang="hi">सनातन धर्म और वेदों की शाश्वत रक्षा के लिए उन्होंने भारत की चारों दिशाओं में चार आम्नाय पीठों की स्थापना की और अपने चार प्रमुख संन्यासी शिष्यों को उनका आचार्य नियुक्त किया। प्रत्येक पीठ को एक वेद की रक्षा का दायित्व मिला, और यही गुरु-शिष्य परम्परा आज तक अखण्ड चल रही है।</p>
            <p>To keep the Vedas and Sanatana Dharma alive for all time, he founded four cardinal monastic seats, the Amnaya Peethams, at the four corners of India, and placed one of his four chief disciples at the head of each. Each seat was entrusted with one Veda, and the Guru-Shishya Parampara he began continues unbroken today.</p>

            <ul class="sp-adiguru__peeths">
                <?php foreach ( $sp_peeths as $sp_p ) : ?>
                    <?php $sp_profile = get_page_by_path( $sp_p[5], OBJECT, 'dharma_profile' ); ?>
                    <li>
                        <a class="sp-adiguru__peeth" href="<?php echo esc_url( $sp_profile ? get_permalink( $sp_profile ) : home_url( '/dharma-acharya/' ) ); ?>">
                            <?php sp_icon( $sp_p[0], 'sp-icon sp-icon--xl', '' ); ?>
                            <span class="sp-adiguru__dir"><?php echo esc_html( $sp_p[1] ); ?> · <?php echo esc_html( $sp_p[2] ); ?></span>
                            <strong lang="hi"><?php echo esc_html( $sp_p[3] ); ?></strong>
                            <span><?php echo esc_html( $sp_p[4] ); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php $sp_kanchi = get_page_by_path( 'kanchi-kamakoti-peetham', OBJECT, 'dharma_profile' ); ?>
            <?php if ( $sp_kanchi ) : ?>
                <p class="sp-adiguru__note">
                    <?php sp_icon( 'peeth-kanchi', 'sp-icon sp-icon--md', '' ); ?>
                    <?php esc_html_e( 'Kanchi Kamakoti Peetham in Tamil Nadu also traces its foundation to Adi Shankaracharya by its own tradition.', 'sampreshan-child' ); ?>
                    <a href="<?php echo esc_url( get_permalink( $sp_kanchi ) ); ?>"><?php esc_html_e( 'Read the profile', 'sampreshan-child' ); ?></a>
                </p>
            <?php endif; ?>
        </section>

        <?php sp_research_credit(); ?>
    </div>
</main>

<?php
get_footer();
