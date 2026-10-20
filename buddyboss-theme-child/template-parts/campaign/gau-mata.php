<?php
/**
 * Gau Mata Rashtramata campaign section.
 *
 * Design ported from the ShivBodh Trust homepage (four Peethams around the
 * Gau Mata andolan). Rendered on a petition whose meta sp_campaign is
 * "gau-mata-rashtramata". Captions state facts about each Peetham only;
 * no statement is attributed to an Acharya who has not made it.
 *
 * Expects $sp_camp_champion (WP_Post|null): the leading Acharya's profile.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$sp_up = content_url( 'uploads/' );
$sp_profile_url = static function ( $slug ) {
    $p = get_page_by_path( $slug, OBJECT, 'dharma_profile' );
    return $p ? get_permalink( $p ) : home_url( '/dharma-acharya/' );
};
?>
<div class="sp-camp" id="gau-mata-abhiyan">
<section class="unity-focus-section universe-bg">
    <div class="stars" aria-hidden="true"></div>
    <div class="twinkling" aria-hidden="true"></div>

    <div class="unity-container">
        <div class="unity-header-focus">
            <span class="support-badge"><?php sp_icon( 'seva-goseva', 'sp-icon sp-icon--sm', '' ); ?> गौ माता राष्ट्र माता</span>
            <h2 class="unity-title-focus">हिंदू धर्म में असली - नकली की लड़ाई में आप किसके साथ हैं?</h2>
            <p class="unity-statement-focus">
                हम सभी सनातन धर्म के सर्वोच्च पद पर आसीन
                <span class="highlight-text">श्री अविमुक्तेश्वरानंद स्वामी जी 1008</span>
                शंकराचार्य जी के साथ हैं।
            </p>
        </div>

        <div class="compass-layout">
            <div class="flower-container" data-sp-flowers aria-hidden="true"></div>

            <svg class="compass-lasers" aria-hidden="true" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <filter id="spc-glow-laser" x="-20%" y="-20%" width="140%" height="140%">
                        <feGaussianBlur stdDeviation="1.5" result="blur" />
                        <feMerge>
                            <feMergeNode in="blur" />
                            <feMergeNode in="SourceGraphic" />
                        </feMerge>
                    </filter>
                </defs>
                <circle cx="50" cy="50" r="38" class="unity-orbit-line" />
                <line x1="50" y1="15" x2="50" y2="12" class="unity-connector" />
                <line x1="15" y1="50" x2="12" y2="50" class="unity-connector" />
                <line x1="85" y1="50" x2="88" y2="50" class="unity-connector" />
                <line x1="50" y1="85" x2="50" y2="88" class="unity-connector" />
                <line x1="50" y1="15" x2="50" y2="50" class="wave-laser top-laser" filter="url(#spc-glow-laser)" />
                <line x1="15" y1="50" x2="50" y2="50" class="wave-laser left-laser" filter="url(#spc-glow-laser)" />
                <line x1="85" y1="50" x2="50" y2="50" class="wave-laser right-laser" filter="url(#spc-glow-laser)" />
                <line x1="50" y1="85" x2="50" y2="50" class="wave-laser bottom-laser" filter="url(#spc-glow-laser)" />
            </svg>

            <a class="compass-node node-top" href="<?php echo esc_url( home_url( '/adi-shankaracharya/' ) ); ?>">
                <div class="framed-image-box gold-frame">
                    <img src="<?php echo esc_url( $sp_up . '2026/10/AFTERS-SANYAS-ARROUND-3-YEAR-AADI-GURU-.png' ); ?>" alt="आदि शंकराचार्य" class="framed-img" loading="lazy" decoding="async">
                </div>
                <h4 class="node-title gold-text">आदि शंकराचार्य</h4>
                <span class="node-caption">शिवोऽहम् शिवोऽहम्</span>
            </a>

            <a class="compass-node node-left" href="<?php echo esc_url( $sp_profile_url( 'paschimamnaya-dwarka-sharada-peetham' ) ); ?>">
                <div class="framed-image-box saffron-frame">
                    <img src="<?php echo esc_url( $sp_up . '2026/03/swamisadananda.png' ); ?>" alt="द्वारका शारदा पीठ" class="framed-img" loading="lazy" decoding="async">
                </div>
                <h4 class="node-title saffron-text">द्वारका पीठ</h4>
                <span class="node-caption">पश्चिम आम्नाय · सामवेद</span>
            </a>

            <a class="compass-node node-right" href="<?php echo esc_url( $sp_profile_url( 'purvamnaya-govardhan-matha-puri' ) ); ?>">
                <div class="framed-image-box red-frame">
                    <img src="<?php echo esc_url( $sp_up . '2026/03/swaminischalananda.png' ); ?>" alt="गोवर्धन मठ, पुरी" class="framed-img" loading="lazy" decoding="async">
                </div>
                <h4 class="node-title red-text">पुरी पीठ</h4>
                <span class="node-caption">पूर्व आम्नाय · ऋग्वेद</span>
            </a>

            <a class="compass-node node-bottom" href="<?php echo esc_url( $sp_profile_url( 'dakshinamnaya-sri-sharada-peetham' ) ); ?>">
                <div class="framed-image-box yellow-frame">
                    <img src="<?php echo esc_url( $sp_up . '2026/03/swamividhushekhar.png' ); ?>" alt="शृंगेरी शारदा पीठ" class="framed-img" loading="lazy" decoding="async">
                </div>
                <h4 class="node-title yellow-text">शृंगेरी पीठ</h4>
                <span class="node-caption">दक्षिण आम्नाय · यजुर्वेद</span>
            </a>

            <div class="compass-node node-center">
                <div class="rotating-dial-container">
                    <svg viewBox="0 0 200 200" class="rotating-text-svg">
                        <path id="spc-dial-path" d="M 100, 100 m -80, 0 a 80,80 0 1,1 160,0 a 80,80 0 1,1 -160,0" fill="none"></path>
                        <text class="dial-text sanskrit-dial">
                            <textPath href="#spc-dial-path" startOffset="0%">सनातनधर्मः एकः • वयम् एकम् • हिन्दवः एकाः • सनातनधर्मः एकः •</textPath>
                        </text>
                        <text class="dial-text english-dial">
                            <textPath href="#spc-dial-path" startOffset="0%">WE ARE ONE • WE ARE SANATANI • WE ARE ONE • WE ARE SANATANI •</textPath>
                        </text>
                    </svg>
                </div>
                <div class="absorb-ring ring-1"></div>
                <div class="absorb-ring ring-2"></div>
                <div class="absorb-ring ring-3"></div>
                <div class="out-of-frame-box center-focus-img">
                    <img src="<?php echo esc_url( $sp_up . '2026/10/gau-mata-rastra-mata-1008.png' ); ?>" alt="गौ माता राष्ट्र माता अभियान, स्वामी श्री अविमुक्तेश्वरानंद सरस्वती जी" class="out-of-frame-img" loading="lazy" decoding="async">
                </div>
                <span class="support-tag">ज्योतिर्मठ</span>
            </div>
        </div>

        <div class="campaign-section">
            <div class="campaign-title"><?php sp_icon( 'sym-dhwaja', 'sp-icon sp-icon--md', '' ); ?> गौ माता को राष्ट्रमाता का पुनीत अभियान <?php sp_icon( 'sym-dhwaja', 'sp-icon sp-icon--md', '' ); ?></div>
            <div class="campaign-message">
                <p><?php sp_icon( 'sym-diya', 'sp-icon sp-icon--sm', '' ); ?> हम सब मिलकर इस परम पवित्र अभियान का समर्थन करें, सहयोग करें।</p>
                <p><?php sp_icon( 'seva-goseva', 'sp-icon sp-icon--sm', '' ); ?> गौ माता हमारी सनातन संस्कृति की आधारशिला हैं। उन्हें राष्ट्रमाता का दर्जा दिलाना हर सनातनी का कर्तव्य है।</p>
                <p><?php sp_icon( 'act-support', 'sp-icon sp-icon--sm', '' ); ?> जो भी गाय को माता मानते हैं, वे अब इस आंदोलन में सम्मिलित हो जाएँ और अपना योगदान दें।</p>
                <p><?php sp_icon( 'sym-lotus', 'sp-icon sp-icon--sm', '' ); ?> आइए, मिलकर गौ माता के सम्मान के लिए एकजुट हों।</p>
            </div>
            <div class="spc-actions">
                <a href="#sp-support" class="glow-button">समर्थन करें</a>
                <a class="spc-call" href="tel:+918448531008">गो मतदाता अभियान: मिस्ड कॉल दें 844 853 1008</a>
            </div>
        </div>

        <?php if ( ! empty( $sp_camp_champion ) ) : ?>
            <div class="spc-champion">
                <?php $sp_img = function_exists( 'sp_dharma_profile_image_url' ) ? sp_dharma_profile_image_url( $sp_camp_champion->ID, 'medium' ) : ''; ?>
                <?php if ( $sp_img ) : ?><img src="<?php echo esc_url( $sp_img ); ?>" alt="" loading="lazy"><?php endif; ?>
                <span>
                    अभियान के प्रणेता:
                    <a href="<?php echo esc_url( get_permalink( $sp_camp_champion ) ); ?>"><?php echo esc_html( get_the_title( $sp_camp_champion ) ); ?></a>,
                    उत्तराम्नाय ज्योतिर्मठ
                </span>
            </div>
        <?php endif; ?>
    </div>
</section>
</div>
