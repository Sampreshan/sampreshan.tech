# Rules for AI agents and contributors

These rules are binding for any page, post, template or feature created for sampreshan.tech.

1. **Icons:** use only SampreShan's own icon kit, through `sp_icon()` / `sp_icon_e()`. Do not use stock icon packs, icon fonts or CDN icons.
2. **No emoji:** never use emoji anywhere — not in code, CSS `content:`, page or post content, menus, titles, notifications or emails.
3. **Missing icon:** if the icon you need does not exist, add it to `docs/icon-set-brief.md` for the next phase. Do not substitute an emoji or a stock icon.

The full rules are in [docs/ICON-RULES.md](docs/ICON-RULES.md).

## Language

1. **English first:** English is the site's first and only display language. All pages, posts, profiles, petitions and their details are written in English.
2. **No Devanagari or Hinglish:** write Sanskrit terms in Roman transliteration; translate any Hindi or Hinglish text.
3. **Old pages:** translate their text and title, and keep the slug.

The full rules are in [docs/LANGUAGE-RULES.md](docs/LANGUAGE-RULES.md).

## Brand tagline (owner decision, 2026-11-19)

SampreShan's own tagline is:

> परस्परानुकथनं पावनं भगवद्यशः
> *Parasparānukathanaṁ pāvanaṁ bhagavad-yaśaḥ*
> Sharing the glory of the Divine with one another purifies us all.

1. **One source:** print it with `sp_brand_tagline_e( $class )` (or read the parts from `sp_brand_tagline()`) in `inc/brand-tagline.php`. Do not copy the words into templates.
2. **Always three lines:** Devanagari, then Roman transliteration, then the English meaning. It is the only Devanagari allowed on the site (see `docs/LANGUAGE-RULES.md`).
3. **Where it appears:** the tagline band at the top of the site footer (every page) and the WordPress Site Tagline (Settings > General, Roman form with the meaning). Use it on new brand surfaces such as certificates, emails and campaign heroes when a tagline is needed.
4. **Style:** `.sp-tagline` in `assets/css/site-footer.css` (saffron-gold Devanagari, italic Roman line, muted meaning).

## Site footer

The footer markup is `template-parts/footer/site-footer.php` and its whole layout for every width is `assets/css/site-footer.css`, which loads last (priority 1002). Change footer layout only in that file; desktop shows brand + 3 columns, tablet shows the brand row above 3 columns, phones show Platform and Community side by side.

## Content sourcing (ShivBodh Research Group)

Every page, post, landing page and campaign follows `docs/CONTENT-SOURCING.md`:
content is compiled by the ShivBodh Research Group, written in our own words, never copied, with no
third-party source lists on the page. Show the credit with
`sp_research_credit()`; it reads only "ShivBodh Research Group".

## Lists and pagination

1. **Infinite scroll:** every list on the site (Acharyas, Peeths, Sampradayas, petitions, members, posts, archives) loads more items automatically as the reader scrolls. Do not show page-number buttons to the reader.
2. **How:** render the list with normal WordPress pagination (`paginate_links()` / `the_posts_pagination()`) and mark the list container with `data-sp-infinite`. `assets/js/sp-infinite.js` hides the page links and appends the next page. The links remain as a no-JS and crawler fallback.
3. **Dynamic items:** scripts that bind to list items must use event delegation or listen for the `sp:infinite-loaded` event.

## Buttons and CTAs

1. **Slide effect:** every CTA and button on the site uses the shared slide effect (`assets/css/sp-slide.css` + `assets/js/sp-slide.js`): a fill slides through the inside of the button on hover, followed by a shine streak.
2. **New buttons:** anything built later follows the same effect. Use a normal `<button>`, `a.button`, or a class containing `btn`, `button`, `cta` or `__play` and it is applied automatically. Do not write a different hover effect for buttons.
3. **Opt-out:** only for icon-only or tiny controls, with class `sp-noslide`.

## Public copy and user trust (owner rule, 2026-11-04)

- Applies to all existing and future websites, apps, services, calculators, landing
  pages, promotions, onboarding, result screens, reports and PDFs, in every language.
- Never add unsolicited disclaimers or editorial qualifications that undermine
  confidence in a released service. This includes “Calculated positions, not a
  guaranteed prediction”, “outcomes are not guaranteed”, “for entertainment only”,
  “AI-reviewed, not independently verified”, and equivalent Hindi or other-language
  wording. Do not present validated services as research demos or experiments.
- Describe what the service provides in clear, positive, factual language. Do not
  replace removed wording with another banner, tooltip, footnote, collapsed detail,
  mobile-only message, translation or PDF notice carrying the same qualification.
- Keep research provenance, review status, model caveats and developer QA limitations
  in private technical records and handoffs instead of adding them to public copy.
- This rule does not create a guarantee, certification or review that has not occurred.
  Keep calculation rules, evidence, signatures, publication gates and unavailable
  features accurate. Show specific, actionable input or service errors when needed.
- Preserve necessary privacy consent, data-use controls, dependency licences and
  existing statutory notices. Do not introduce new boilerplate for hypothetical risks.
- Before release, check the changed public copy in all supported languages, guest
  and signed-in views, promotional entry points, responsive layouts and printable
  reports. Do not reintroduce the wording removed on 2026-11-04 in a later build.
