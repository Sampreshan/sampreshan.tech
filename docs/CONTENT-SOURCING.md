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
