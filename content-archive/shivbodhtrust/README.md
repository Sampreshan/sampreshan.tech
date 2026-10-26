# ShivBodh Trust content archive (for SampreShan)

Copied from shivbodhtrust.org on 18 October 2026, before the old site was retired.
This is reference material only. Nothing in this folder is deployed.

## Layout

| Folder | What it is |
|---|---|
| `home-sections/01-kaun-hai-avimukteshwaranand.html` | Homepage section below the hero: "सम्मान, सत्य और सनातन का संघर्ष" (who Swami Avimukteshwaranand Saraswati is). |
| `home-sections/02-2015-anyay-pratikar-yatra-gallery.html` | 2015 Anyay Pratikar Yatra gallery. |
| `home-sections/03-asli-nakli-chaar-shankaracharya-gau-mata.html` | Section above the footer: four Shankaracharyas on four sides, Gau Mata andolan image in the centre. Full style and content. |
| `home-sections/00-hero-original.html`, `90-*`, `91-*`, `92-*` | Rest of the old homepage (hero, about, Shiva Gita list, campaigns teaser), kept for completeness. |
| `adi-shankaracharya/` | The "Aadyaguru Shankaracharya Ji" menu page plus its 5 Peeth sub-pages. |
| `dharmasevaka/` | The Dharmasevaka menu page plus its 5 Acharya sub-pages. |
| `manifest.json` | Old ID, slug and title for every page, the target URL on sampreshan.tech, and the image and URL maps. |
| `raw/` | Unmodified export: `post-*.json`, `meta-*.json` and `elementor-*.json` (Elementor `_elementor_data`). |

## Changes made while copying

These changes apply to the `.html` files only. `raw/` is untouched.

**Images**
- Every image now points to the same file in the SampreShan media library (`sampreshan.tech/wp-content/uploads/...`). All 17 images were found there.

**Links**
- Old `shivbodhtrust.org/<slug>/` links now point to the matching SampreShan page. For example, `/jyotishpeetha/` becomes `/dharmacharya/uttaraminaya-jyotirmath-peeth/`.
- `?page_id=` links were removed.

**Branding**
- "ShivBodh Trust" / "शिवबोध ट्रस्ट" became "SampreShan" / "संप्रेषण".
- `shivbodhtrust.org` became `sampreshan.tech`.
- The old "Vishwaas se Shivbodh" top-nav strip was removed.

**SEO**
- The old `<!DOCTYPE>/<html>/<head>/<body>` shell, `<title>`, `<meta>` tags, canonical/alternate links and JSON-LD schema were removed.
- The page `<style>` blocks were kept.
- SEO and schema on SampreShan are produced by the theme for each page, so no old SEO is carried over.

## Before reusing

- **Icons:** some blocks use Font Awesome classes (`fas fa-*`). The SampreShan child theme does not load Font Awesome, so swap these for the theme's 3D icons or enqueue Font Awesome.
- **Third-party script:** `dharmasevaka/04-swami-nischalananda-saraswati.html` loads `widget.rss.app`. Review it or drop it.
- **CSS scope:** the CSS uses global selectors (`body`, `h1`, `.container`, ...). Scope it under a wrapper class before embedding, or it will restyle the whole page.
- **Facts:** Acharya and Peeth facts must match `buddyboss-theme-child/inc/dharma-data.php`. The copied texts still use the old titles; for example, Vidhushekhara Bharati is the successor-designate, and the Jyotirmath succession is sub judice.
- **Planned use:** `03-asli-nakli-...` and `01-kaun-hai-...` are meant for the Jyotirpeeth campaign. That campaign is run by the Peetham, and any user can support it.
