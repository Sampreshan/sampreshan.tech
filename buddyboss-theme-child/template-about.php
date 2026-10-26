<?php
/**
 * Template Name: About
 *
 * About SampreShan — mission, vision, ShivBodh Trust, team, timeline.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
?>

<main id="main" class="site-main sp-page" role="main">

    <!-- HERO -->
    <header class="sp-page__hero">
        <div class="sp-page__hero-icon">
            <?php sp_icon_auto( 'dharma', 'sp-icon--3xl sp-icon--saffron sp-icon--float', __( 'SampreShan', 'sampreshan-child' ) ); ?>
        </div>
        <h1 class="sp-page__hero-title"><?php esc_html_e( 'About SampreShan', 'sampreshan-child' ); ?></h1>
        <p class="sp-page__hero-sub">
            <?php esc_html_e( 'A non-commercial digital platform by ShivBodh Trust — giving the Sanatana Dharma community a voice that travels farther, together.', 'sampreshan-child' ); ?>
        </p>
    </header>

    <div class="sp-page__body">

        <!-- MISSION -->
        <section class="sp-page__card">
            <h2 class="sp-page__section-title">
                <?php sp_icon_auto( 'heart', 'sp-icon--md sp-icon--saffron', __( 'Mission', 'sampreshan-child' ) ); ?>
                <?php esc_html_e( 'Our Mission', 'sampreshan-child' ); ?>
            </h2>
            <div class="sp-page__content">
                <p>
                    <?php esc_html_e( 'SampreShan exists to unify the voices of Sanatana Dharma followers across the world. We believe that every community issue — whether it concerns a local temple, cultural heritage, religious education, or environmental protection — deserves to be heard, discussed, and acted upon collectively.', 'sampreshan-child' ); ?>
                </p>
                <p>
                    <?php esc_html_e( 'Our platform enables individuals to raise petitions, gather signatures, and build awareness around causes that matter to the Dharmic community. No donations. No membership fees. No financial transactions. Purely a community-driven awareness platform.', 'sampreshan-child' ); ?>
                </p>
            </div>
        </section>

        <!-- VISION -->
        <section class="sp-page__card">
            <h2 class="sp-page__section-title">
                <?php sp_icon_auto( 'star', 'sp-icon--md sp-icon--gold', __( 'Vision', 'sampreshan-child' ) ); ?>
                <?php esc_html_e( 'Our Vision', 'sampreshan-child' ); ?>
            </h2>
            <div class="sp-page__content">
                <p>
                    <?php esc_html_e( 'We envision a world where every Sanatana Dharma follower — regardless of their sampradaya, region, or language — has access to a trusted platform to voice concerns, seek support, and connect with like-minded individuals who share their commitment to Dharma.', 'sampreshan-child' ); ?>
                </p>
                <p>
                    <?php esc_html_e( 'One Dharma. Many paths. All traditions respected equally.', 'sampreshan-child' ); ?>
                </p>
            </div>
        </section>

        <!-- WHAT WE DO -->
        <section class="sp-page__card">
            <h2 class="sp-page__section-title">
                <?php sp_icon_auto( 'petition', 'sp-icon--md sp-icon--saffron', __( 'What We Do', 'sampreshan-child' ) ); ?>
                <?php esc_html_e( 'What We Do', 'sampreshan-child' ); ?>
            </h2>
            <div class="sp-page__features">
                <div class="sp-feature-card">
                    <div class="sp-feature-card__icon">
                        <?php sp_icon_auto( 'petition', 'sp-icon--2xl sp-icon--saffron', __( 'Petitions', 'sampreshan-child' ) ); ?>
                    </div>
                    <h3 class="sp-feature-card__title"><?php esc_html_e( 'Raise Petitions', 'sampreshan-child' ); ?></h3>
                    <p class="sp-feature-card__desc"><?php esc_html_e( 'Start a petition on any social, religious, or cultural issue affecting the Dharmic community. Bring local problems to a wider audience.', 'sampreshan-child' ); ?></p>
                </div>
                <div class="sp-feature-card">
                    <div class="sp-feature-card__icon">
                        <?php sp_icon_e( 'ibadge', 'sp-icon--2xl', __( 'Support', 'sampreshan-child' ) ); ?>
                    </div>
                    <h3 class="sp-feature-card__title"><?php esc_html_e( 'Gather Support', 'sampreshan-child' ); ?></h3>
                    <p class="sp-feature-card__desc"><?php esc_html_e( 'Collect signatures and build momentum. Every signature amplifies your voice and brings you closer to collective impact.', 'sampreshan-child' ); ?></p>
                </div>
                <div class="sp-feature-card">
                    <div class="sp-feature-card__icon">
                        <?php sp_icon_auto( 'network', 'sp-icon--2xl sp-icon--saffron', __( 'Connect', 'sampreshan-child' ) ); ?>
                    </div>
                    <h3 class="sp-feature-card__title"><?php esc_html_e( 'Connect & Discuss', 'sampreshan-child' ); ?></h3>
                    <p class="sp-feature-card__desc"><?php esc_html_e( 'Join a community of like-minded Sanatana Dharma followers. Share perspectives, discuss issues, and build meaningful connections.', 'sampreshan-child' ); ?></p>
                </div>
                <div class="sp-feature-card">
                    <div class="sp-feature-card__icon">
                        <?php sp_icon_auto( 'om', 'sp-icon--2xl sp-icon--saffron', __( 'Awareness', 'sampreshan-child' ) ); ?>
                    </div>
                    <h3 class="sp-feature-card__title"><?php esc_html_e( 'Acharya Awareness', 'sampreshan-child' ); ?></h3>
                    <p class="sp-feature-card__desc"><?php esc_html_e( 'Learn about the four sacred peethas, various sampradayas, and the wisdom of traditional spiritual institutions.', 'sampreshan-child' ); ?></p>
                </div>
            </div>
        </section>

        <!-- SHIVBODH TRUST -->
        <section class="sp-page__card">
            <h2 class="sp-page__section-title">
                <?php sp_icon_auto( 'verified', 'sp-icon--md sp-icon--saffron', __( 'Trust', 'sampreshan-child' ) ); ?>
                <?php esc_html_e( 'About ShivBodh Trust', 'sampreshan-child' ); ?>
            </h2>
            <div class="sp-page__content">
                <p>
                    <?php esc_html_e( 'SampreShan is an initiative of ShivBodh Trust — a non-profit organization dedicated to the preservation and promotion of Sanatana Dharma values. The Trust operates without any commercial interests, focusing entirely on community welfare and Dharmic awareness.', 'sampreshan-child' ); ?>
                </p>
                <p>
                    <?php esc_html_e( 'For more information about the Trust, its activities, and its mission,', 'sampreshan-child' ); ?>
                    <a href="https://shivbodhtrust.org" target="_blank" rel="noopener"><?php esc_html_e( 'visit shivbodhtrust.org', 'sampreshan-child' ); ?></a>.
                </p>
            </div>
        </section>

        <!-- SYMBOLS -->
        <section class="sp-page__card" id="symbols">
            <h2 class="sp-page__section-title">
                <?php sp_icon_auto( 'dharma', 'sp-icon--md sp-icon--gold', __( 'Symbols', 'sampreshan-child' ) ); ?>
                <?php esc_html_e( 'Symbols and their meaning', 'sampreshan-child' ); ?>
            </h2>
            <dl class="sp-symbols">
                <div class="sp-symbols__item"><dt><?php esc_html_e( 'The messenger dove (Kabootar)', 'sampreshan-child' ); ?></dt><dd><?php esc_html_e( 'For centuries the trained pigeon carried letters between kingdoms, ashrams and families. On SampreShan the dove stands for Sampreshan itself: carrying a message honestly from one heart to another. The grey dove is the voice of the people; the white dove is the reply of peace and dialogue. When they meet at the shikhar they exchange scrolls, showing that every issue raised here is meant to be heard and answered.', 'sampreshan-child' ); ?></dd></div>
                <div class="sp-symbols__item"><dt><?php esc_html_e( 'The scroll in the beak (Sandesh)', 'sampreshan-child' ); ?></dt><dd><?php esc_html_e( 'The rolled scroll is the message: a petition, a question or an issue. It is sealed with a red thread, a reminder that what you share should be truthful and respectful.', 'sampreshan-child' ); ?></dd></div>
                <div class="sp-symbols__item"><dt><?php esc_html_e( 'The temple shikhar', 'sampreshan-child' ); ?></dt><dd><?php esc_html_e( 'The shikhar is the highest point of a mandir, pointing to the divine. The doves land on it because every voice on this platform is offered in the spirit of Dharma, above personal interest.', 'sampreshan-child' ); ?></dd></div>
                <div class="sp-symbols__item"><dt><?php esc_html_e( 'The saffron dhwaja (flag)', 'sampreshan-child' ); ?></dt><dd><?php esc_html_e( 'Bhagwa is the colour of tyaga (renunciation) and courage. The flag on the shikhar carries the name Sampreshan, marking the platform as a home for the Sanatan voice.', 'sampreshan-child' ); ?></dd></div>
                <div class="sp-symbols__item"><dt><?php esc_html_e( 'The rising sun rays', 'sampreshan-child' ); ?></dt><dd><?php esc_html_e( 'The rays behind the logo stand for gyana (knowledge) and clarity. Information on SampreShan should bring light, not confusion.', 'sampreshan-child' ); ?></dd></div>
                <div class="sp-symbols__item"><dt><?php esc_html_e( 'The open hands', 'sampreshan-child' ); ?></dt><dd><?php esc_html_e( 'The two hands holding the temple in the logo stand for seva and protection: the community together protects its heritage, temples and traditions.', 'sampreshan-child' ); ?></dd></div>
                <div class="sp-symbols__item"><dt><?php esc_html_e( 'The khadau (Paduka) on the Anusaran button', 'sampreshan-child' ); ?></dt><dd><?php esc_html_e( 'Khadau are the wooden sandals of saints. Following someone\'s paduka means walking their path. That is why the Anusaran (follow) button shows a khadau walking and writing SAMPRESHAN with its footsteps: when you follow a Peeth, Acharya or sampradaya, you walk with them and receive their updates.', 'sampreshan-child' ); ?></dd></div>
                <div class="sp-symbols__item"><dt><?php esc_html_e( 'The shankh icon', 'sampreshan-child' ); ?></dt><dd><?php esc_html_e( 'The conch is blown to announce an important moment and to call people together. It marks Issue Sampreshan, the place where you raise your voice on an issue.', 'sampreshan-child' ); ?></dd></div>
                <div class="sp-symbols__item"><dt><?php esc_html_e( 'The temple bell (Ghanti)', 'sampreshan-child' ); ?></dt><dd><?php esc_html_e( 'The ghanti is rung to awaken attention before darshan. In the Sanatan Quiz it rings at the start and with a correct answer, followed by the word Satya (truth).', 'sampreshan-child' ); ?></dd></div>
            </dl>
        </section>

        <!-- FAQ -->
        <section class="sp-page__card" id="faq">
            <h2 class="sp-page__section-title">
                <?php sp_icon_auto( 'dharma', 'sp-icon--md sp-icon--gold', __( 'FAQ', 'sampreshan-child' ) ); ?>
                <?php esc_html_e( 'Frequently asked questions', 'sampreshan-child' ); ?>
            </h2>
            <?php $sp_faq = array(
                array( __( 'What does the word Sampreshan mean?', 'sampreshan-child' ), __( 'Sampreshan means communication: sending a message so that it truly reaches the other person. The platform is built for clear, respectful communication on matters of Sanatan Dharma.', 'sampreshan-child' ) ),
                array( __( 'Why are there pigeons on the home page?', 'sampreshan-child' ), __( 'The flying doves are the Kabootar post, the traditional messenger. One comes from far away, the other replies; together they show that your voice travels and is answered. See Symbols and their meaning above.', 'sampreshan-child' ) ),
                array( __( 'What is the Anusaran button?', 'sampreshan-child' ), __( 'Anusaran means following. Press it on a Peeth, Acharya or sampradaya profile to follow it. You get in-site notifications and an email digest when new updates are published. The walking khadau on the button shows you are following their footsteps.', 'sampreshan-child' ) ),
                array( __( 'What is Issue Sampreshan?', 'sampreshan-child' ), __( 'It is the section where you raise an issue or start a petition, marked with the shankh icon. Others can support it, share it and follow its progress.', 'sampreshan-child' ) ),
                array( __( 'What sounds does the Sanatan Quiz use?', 'sampreshan-child' ), __( 'A temple bell and the voice saying Satya for a correct answer, and a warning horn for a wrong answer. You can mute sounds in the quiz at any time.', 'sampreshan-child' ) ),
                array( __( 'Who runs SampreShan?', 'sampreshan-child' ), __( 'SampreShan is built and maintained by ShivBodh Research Group under ShivBodh Trust.', 'sampreshan-child' ) ),
            ); ?>
            <div class="sp-faq">
                <?php foreach ( $sp_faq as $sp_qa ) : ?>
                <details class="sp-faq__item"><summary><?php echo esc_html( $sp_qa[0] ); ?></summary><p><?php echo esc_html( $sp_qa[1] ); ?></p></details>
                <?php endforeach; ?>
            </div>
            <script type="application/ld+json"><?php echo wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map( function ( $qa ) { return array( '@type' => 'Question', 'name' => $qa[0], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $qa[1] ) ); }, $sp_faq ) ) ); ?></script>
        </section>

        <!-- TIMELINE -->
        <section class="sp-page__card">
            <h2 class="sp-page__section-title">
                <?php sp_icon_auto( 'dharma', 'sp-icon--md sp-icon--gold', __( 'Journey', 'sampreshan-child' ) ); ?>
                <?php esc_html_e( 'Our Journey', 'sampreshan-child' ); ?>
            </h2>
            <div class="sp-timeline">
                <div class="sp-timeline__item">
                    <div class="sp-timeline__year"><?php esc_html_e( '2024', 'sampreshan-child' ); ?></div>
                    <div class="sp-timeline__title"><?php esc_html_e( 'ShivBodh Trust Established', 'sampreshan-child' ); ?></div>
                    <p class="sp-timeline__desc"><?php esc_html_e( 'The Trust was founded with a vision to create a digital platform for the Sanatana Dharma community.', 'sampreshan-child' ); ?></p>
                </div>
                <div class="sp-timeline__item">
                    <div class="sp-timeline__year"><?php esc_html_e( '2025', 'sampreshan-child' ); ?></div>
                    <div class="sp-timeline__title"><?php esc_html_e( 'SampreShan Platform Launched', 'sampreshan-child' ); ?></div>
                    <p class="sp-timeline__desc"><?php esc_html_e( 'The platform went live with petition system, community feed, and member profiles.', 'sampreshan-child' ); ?></p>
                </div>
                <div class="sp-timeline__item">
                    <div class="sp-timeline__year"><?php esc_html_e( '2026', 'sampreshan-child' ); ?></div>
                    <div class="sp-timeline__title"><?php esc_html_e( 'Growing Community', 'sampreshan-child' ); ?></div>
                    <p class="sp-timeline__desc"><?php esc_html_e( 'Expanding features, onboarding sampradayas, and building a stronger collective voice for Dharma.', 'sampreshan-child' ); ?></p>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="sp-page__cta">
            <h2 class="sp-page__cta-title"><?php esc_html_e( 'Ready to Make a Difference?', 'sampreshan-child' ); ?></h2>
            <p class="sp-page__cta-sub"><?php esc_html_e( 'Join thousands of community members who are raising their voice for Dharma.', 'sampreshan-child' ); ?></p>
            <div class="sp-page__cta-actions">
                <a class="btn-3d btn-3d--lg" href="<?php echo esc_url( home_url( '/start-a-petition/' ) ); ?>">
                    <?php sp_icon_e( 'plus', 'sp-icon--sm sp-icon--white', __( 'Start', 'sampreshan-child' ) ); ?>
                    <?php esc_html_e( 'Start a Petition', 'sampreshan-child' ); ?>
                </a>
                <a class="btn-3d btn-3d--lg btn-3d--royal" href="<?php echo esc_url( home_url( '/login/' ) ); ?>">
                    <?php sp_icon_e( 'lock', 'sp-icon--sm sp-icon--white', __( 'Join', 'sampreshan-child' ) ); ?>
                    <?php esc_html_e( 'Join the Community', 'sampreshan-child' ); ?>
                </a>
            </div>
        </section>

        <p class="sp-page__credit"><?php esc_html_e( 'Original artwork created for Sampreshan by ShivBodh Trust.', 'sampreshan-child' ); ?></p>

    </div>
</main>

<?php
$footer_tpl = get_stylesheet_directory() . '/template-parts/footer/site-footer.php';
if ( file_exists( $footer_tpl ) ) {
    include $footer_tpl;
} else {
    get_footer();
}
