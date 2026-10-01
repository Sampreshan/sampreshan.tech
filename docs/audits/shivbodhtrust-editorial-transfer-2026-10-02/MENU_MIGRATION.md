# Menu migration map

The two complete legacy navigation branches move with their pages. They must not remain in the ShivBodh Trust menu after deployment.

## Target hierarchy for Sampreshan

### Aadyaguru Shankaracharya Ji

| Order | Legacy page ID | Label | Destination path | Verification |
|---:|---:|---|---|---|
| 0 | 83 | Aadyaguru Shankaracharya Ji | `/adi-shankaracharya/` | Canonical route already observed on Sampreshan; legacy Trust query target returned 404 |
| 1 | 70 | Purvamnaya Govardhan Matha (Puri, Odisha) | `/puri-peetha/` | Legacy route verified |
| 2 | 68 | Paschimamnaya Dwarka Sharada Peetham (Dwarka, Gujarat) | `/dwarka-peetha/` | Legacy route verified |
| 3 | 72 | Uttaraminaya Jyotirmath Peeth (Joshimath, Uttarakhand) | `/jyotishpeetha/` | Legacy route verified |
| 4 | 66 | Dakshinamnaya Sri Sharada Peetham (Sringeri, Karnataka) | `/sringeri-peetha/` | Legacy route verified |
| 5 | 74 | Kanchi Kamakoti Peetham | `/kanchi-peetha/` | Legacy route verified |

### Dharmasevaka

| Order | Legacy page ID | Label | Destination path | Verification |
|---:|---:|---|---|---|
| 0 | 59 | Dharmasevaka | `/dharmasevaka/` | Parent route verified; remove all puja/booking wrapper content during migration |
| 1 | 89 | Jagadguru Shankaracharya Swami-Sadanand Saraswat Ji | From WordPress export | Do not guess slug |
| 2 | 91 | Jagadguru Shankaracharya Swami Shri Avimukteshwaranand Saraswati Ji Maharaj | From WordPress export | Do not guess slug |
| 3 | 85 | Jagadguru Shankaracharya Sri Sri Vidhushekhara Bharati Sannidhanam | `/swami-vidhushekhara-bharati/` | Public route observed |
| 4 | 87 | Jagadguru Shankaracharya Swami Shri Nischalananda Saraswati Ji Maharaj | From WordPress export | Do not guess slug |
| 5 | 113 | Sri-Satya-Chandrashekarendra-Saraswathi-Shankaracharya | From WordPress export | Do not guess slug |

## WordPress menu procedure

1. Back up both databases and export pages/media/SEO metadata.
2. Import or recreate the pages in Sampreshan and record the new destination IDs.
3. In Sampreshan Appearance → Menus, create the two parent items and attach all five children to each parent in the order above.
4. Check desktop, mobile and logged-in BuddyBoss navigation.
5. Only after editorial cross-check, publish the pages and menu.
6. On ShivBodh Trust, remove both parent items and all descendants from every assigned menu location. The merged Trust theme also hides the verified legacy IDs and their menu descendants at render time.

## Separation rule

Puja, booking, dakshina, pandit, muhurat, sankalp and related service calls to action do not move to Sampreshan. They belong only to the dedicated Varanasi Pooja platform.
