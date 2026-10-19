# SampreShan Icon Rules (mandatory)

These rules apply to everything built on sampreshan.tech, now and in the future:
theme templates, blog posts, landing pages, campaign pages, emails, widgets and
any new feature.

## 1. Only our own icons

- Use only SampreShan's self-created icons:
  - **Phase 1 kit:** `buddyboss-theme-child/assets/icons/kit/` — the own 3D artwork, 138 icons.
  - **SVG set:** `buddyboss-theme-child/assets/icons/3d/` — small UI glyphs.
- Do not use stock or third-party icon packs anywhere. That means no Font Awesome, Dashicons in front-end UI, Material Icons, Icons8, Flaticon or CDN icon fonts.
- Always render icons through the helper. It picks the right size for desktop, tablet and mobile:

  ```php
  <?php sp_icon_e( 'nav-home', 'sp-icon--lg' ); ?>      // decorative
  <?php sp_icon_e( 'st-verified', 'sp-icon--md', 'Verified' ); ?>  // meaningful: give a label
  ```

- For Peeth, Acharya and Sampradaya profiles, use `sp_dharma_icon_name( $post )`.
- If a needed icon does not exist, do not substitute an emoji or a stock icon. Add it to the
  next phase of `docs/icon-set-brief.md`, generate it in the same style, and then add it to the kit.

## 2. No emoji, anywhere

- No emoji in PHP, JS, CSS (`content:` values included), page and post content, menus, titles,
  notifications or emails.
- Replace an emoji with the matching kit icon. `.github/workflows` checks the theme and fails the build
  if an emoji is committed.

## 3. Sizes and devices

- Sizes: `sp-icon--xs | sm | md | lg | xl | 2xl | 3xl`. The breakpoints in `assets/css/icon-system.css`
  shrink them on phones automatically. Kit icons never render below 20px, so their detail stays legible.
- Kit files are WebP at 64, 128 and 256px with `srcset`. Browsers fetch 64px on phones, 128px on retina
  screens and 256px for hero art. Never link the 512px masters in `assets/icons/kit-src/`; they are only for
  regenerating the kit and are not deployed.

## 4. Adding or updating icons

1. Name the file `sp-<category>-<name>.png` (1024×1024, transparent), following `docs/icon-set-brief.md`.
2. Run `python3 tools/build-icon-kit.py <folder>`. It trims, pads, and writes the 64/128/256 WebP files plus the masters.
3. If an old SVG name should now show the new art, add the mapping to `sp_icon_aliases()` in
   `inc/icon-helper.php`.

## Kit names (Phase 1 + Phase 2, 220 icons)

Full visual list: https://sampreshan.tech/icons/ (template `template-icon-library.php`, reads `kit/manifest.json`).

| Prefix | Use |
|---|---|
| `nav-*` | Header, menus, tab bar: home, dashboard, profile, notifications, messages, search, settings, menu, calendar, help, logout, petition, peeth, directory, community, awareness. |
| `act-*` | Buttons and actions: follow, following, support, like, comment, share, bookmark, sign, invite, donate, report, edit, delete, upload, download, verify. |
| `com-*` | Community: group, friends, event, live, photo, video, audio, poll, story, volunteer. |
| `seva-*` | Causes: goseva, ganga, annadaan, education, health, environment, legal, women, temple-heritage, disaster. |
| `peeth-*` | Peeth type: purva, paschim, uttar, dakshin, kanchi, amnaya. |
| `role-*` | Acharya role: jagadguru, shankaracharya, successor, senior. |
| `evt-*` | Programmes and news: pravachan, puja, homa, yatra, vijaya-yatra, jayanti, darshan, sabha, abhishek, aradhana, chaturmasya, kumbhabhishek, inauguration, news. |
| `sym-*` | Sacred symbols: om, shankh, trishul, damru, diya, ghanti, kalash, kamandalu, lotus, tulsi, bel-patra, rudraksha, paduka, danda, chakra, mor-pankh. |
| `place-*` | Tirthas: kashi, puri, dwarka, badri, sringeri, kanchi. |
| `st-*` | Status: approved, pending, rejected, success, error, warning, info, new, trending, verified. |
| `badge-*` | Member badges: pratham, niyamit, sanyojak, seva. |
| `tilak-*` | Sampradaya tilaks (20). |
| `dash-*` | Phase 2. Dashanami names: giri, puri, bharati, saraswati, tirtha, ashram, van, aranya, parvat, sagar. |
| `devi-*` | Phase 2. Devi: annapurna, durga, kali, kamakhya, lakshmi, navaratri, parvati, saraswati, shakti-peeth, shringar, vaishno, yantra. |
| `fest-*` | Phase 2. Festivals and tithis: amavasya, chhath, dev-deepawali, diwali, ekadashi, ganesh, guru-purnima, holi, janmashtami, kartik-snan, makar-sankranti, navaratri, purnima, ramnavami, shankara-jayanti, shivratri. |
| `katha-*` | Phase 2. Katha and satsang: bhagwat, bhajan, devi-bhagwat, gita, kirtan, live, mic, ramkatha, satsang, schedule, shivpuran, vachak. |
| Phase 2 additions to older prefixes | peeth: akhara, ashram, jyotirlinga, math, shakti, vaishnav. role: acharya, guru-parampara, mahamandaleshwar, mahant, pujari, sannyasi, vidwan, yogi. place: ayodhya, haridwar, mathura, prayag, rameshwaram, ujjain. sym: chhatra, dhwaja, nandi, swastik. evt: festival, kumbh. com: leaderboard, mentor. badge: guru-bhakt, shatak. seva: elder, youth. |
| `empty-*`, `error-404`, `welcome` | Empty states and illustrations. |
