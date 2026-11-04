# Content sourcing rule (ShivBodh Research Group)

Applies to every page, blog post, landing page, campaign and profile on
sampreshan.tech, now and in future.

## 1. Credit

All information on the site is compiled by the **ShivBodh Research Group**.
Every content page shows the standard credit from
`buddyboss-theme-child/inc/research-credit.php` (`sp_research_credit()`).
The credit reads only:

> ShivBodh Research Group

Nothing else is added to it: no percentages, no source names, no references.

Blog posts and site-run campaigns get it automatically (`the_content`
filter). Templates for other content types call `sp_research_credit()`.
Do not write a different credit text; change the function if it must change.

## 2. No third-party source lists on pages

Pages do not carry "Sources", "References" or "Courtesy" lists, or source
meta. Research notes stay with the ShivBodh Research Group, not on the site.

## 3. Write it ourselves, never copy

- Research from the original institution first: the Peetham or Math, its
  official publications and journals, and people connected with it.
- Write every page **originally, in our own words and structure**. Do not
  paste or lightly reword someone else's article.
- Facts (dates, names, lineages) are not owned by anyone, but the wording,
  photographs, layout and reporting of others are. Use only facts, never
  their text.
- If someone's exact words matter (a statement, a shloka translation, an
  Acharya's message), quote them briefly in quotation marks and name the
  speaker in the sentence ("Swami ji said ...").
- Never put words in an Acharya's or institution's mouth. Statements
  attributed to a Peetham must be ones it actually made.
- News: report in our own words. Third-party headlines and excerpts may not
  be republished as our own; until a news item is rewritten, its original
  report link stays.
- Images: our own, the institution's with permission, or the SampreShan
  media library. No images taken from other websites.

## 4. Before publishing

- [ ] Text is our own writing, checked against the original institution.
- [ ] No copied paragraphs, no source list, no source meta.
- [ ] `sp_research_credit()` shows on the page.
- [ ] Icons from our kit only, no emoji (docs/ICON-RULES.md).

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
