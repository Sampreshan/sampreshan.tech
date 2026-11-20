# Language rules

English is the first and only display language of sampreshan.tech.

## Rules

1. **English everywhere:** every page, post, petition, profile, template, menu,
   notification and email is written in English. This covers headings, body
   text, buttons, labels, image `alt` text, titles, excerpts and SEO fields.
2. **No Devanagari display text:** do not show Hindi or Sanskrit in Devanagari
   script. Write Sanskrit terms in Roman transliteration, for example
   *Vasudhaiva Kutumbakam*, *Prajnanam Brahma*, *Om Gurave Namah*.
3. **No Hinglish:** Hindi written in Roman letters ("Aapka swagat hai") is not
   English. Translate it.
4. **Translate faithfully:** translate the meaning without adding, removing or
   guessing facts. The content-sourcing rules in
   [CONTENT-SOURCING.md](CONTENT-SOURCING.md) still apply.
5. **Keep URLs:** when you translate an old page, change its title and text but
   keep its slug, so existing links do not break.

## Allowed exceptions

- The SampreShan brand tagline, shown in Devanagari together with its Roman
  transliteration and English meaning (owner decision, 2026-11-19):
  - Devanagari: परस्परानुकथनं पावनं भगवद्यशः
  - Roman: *Parasparānukathanaṁ pāvanaṁ bhagavad-yaśaḥ*
  - Meaning: "Sharing the glory of the Divine with one another purifies us all."

  Print it only through `sp_brand_tagline_e()` / `sp_brand_tagline()` from
  `inc/brand-tagline.php`; that file is the only place the checker accepts the
  Devanagari line. Never show the Devanagari line without the Roman line and
  the meaning.
- The sacred symbol ॐ used as a decorative glyph (`aria-hidden`).
- The `name_hi` keys in `inc/*-data.php`. They hold original-script names as
  reference data and must not be rendered on any page.
- Uploaded media file names.

## Check

Run this before every pull request. CI runs it as well.

```sh
python3 tools/check-english-only.py buddyboss-theme-child
```
