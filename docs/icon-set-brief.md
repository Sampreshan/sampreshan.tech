# SampreShan 3D Icon Set — Brief & AI Prompts

Ek hi style mein poore platform ka apna icon set: 3D, glossy, backgroundless, dharmik sparsh ke saath.
Kul **~240 icons**, 16 categories. Pehle Phase 1 (★ wale, ~120) banayein; baaki aage ke profiles/features ke liye hain.

---

## 1. Master style (har prompt ke shuru mein yahi lagayein)

```
STYLE: premium 3D icon, soft clay + glossy enamel finish, gentle specular highlights,
subtle gold (#C9A227) rim accents, primary saffron (#FF9933) and deep maroon (#7A1F1F),
secondary sandalwood cream (#F5E6C8) and temple-stone grey; tiny touch of sacred motif
(lotus petal, kalash curve, temple-shikhara line) only where it fits naturally.
Front 3/4 view, ~15° tilt, single soft key light from top-left, soft contact shadow under
the object only. Clean silhouette readable at 32px, no text, no letters, no numbers,
no human faces of real persons, no deity faces in detail (use symbols instead).
Consistent scale: object fills 80% of its cell, centered.
Modern UI-grade quality like Apple/Microsoft Fluent 3D emoji, respectful and serene.
```

**Negative / avoid:** `photo-realistic, text, watermark, logo, cluttered, multiple objects
merged, harsh shadows, neon, cartoon eyes, gore, weapons pointed at viewer, flat 2D`

### Background (zaroori)
AI tools aksar transparent nahi dete. Isliye:
- Sheet ko **plain solid #FFFFFF** (ya chroma **#00FF00**) background par banwayein, cells ke beech **kam se kam 64px khaali jagah**.
- Main background hata kar transparent PNG kaat lunga. Agar tool sidha transparent PNG de sake (jaise "transparent background" option), to wahi best hai.

### Size & delivery
| Cheez | Size |
|---|---|
| Category sheet | **2048×2048 px**, grid **4×4 = 16 icons**, har cell 512×512 |
| Bade category (16 se zyada) | Do sheet: `A-1`, `A-2` |
| Ek-ek icon (agar alag banayein) | **1024×1024 px** transparent PNG |
| File naam | `sp-<category>-<name>.png` jaise `sp-nav-home.png` |

Main isse 256 / 128 / 64 / 48 / 32 px ke WebP banakar child theme mein `sp_icon('nav-home')` function se har jagah laga dunga.

**Prompt format (har sheet):**
```
[MASTER STYLE] + "A 4x4 icon sheet on solid white background, 16 separate icons in a
grid, equal spacing, each icon isolated. Row by row, left to right: 1) ... 2) ... 16) ..."
```
Sheet ke icons ki list neeche har category mein **wahi kram** mein di hai.

---

## 2. Categories & icons

`★` = Phase 1 (abhi site par chahiye) · `◇` = future

### A. Navigation & core UI — 16 (sheet A)
1. ★ `nav-home` — chhota mandir-shikhara wala ghar
2. ★ `nav-community` — teen deepak ek gol thali mein (Community Center)
3. ★ `nav-directory` — khuli pothi par lotus (Dharmacharya directory)
4. ★ `nav-peeth` — mandir-shikhara + dhwaja
5. ★ `nav-petition` — scroll + mohar (petitions)
6. ★ `nav-awareness` — shankh se nikalti dhwani-tarangein
7. ★ `nav-dashboard` — mandala jaisa 4-khaana panel
8. ★ `nav-profile` — gol pagdi/uttariya silhouette (bina chehre)
9. ★ `nav-notifications` — mandir ghanti
10. ★ `nav-messages` — kabootar ke saath patra (pigeon brand se mel)
11. ★ `nav-search` — magnifier jiske kaanch mein chhota lotus
12. ★ `nav-settings` — dharma-chakra jaisa gear
13. ★ `nav-menu` — teen sone ki patti (hamburger)
14. ★ `nav-calendar` — panchang patra
15. ★ `nav-help` — deepak + prashna-chinh ke bina, khuli hatheli mein jyoti
16. ★ `nav-logout` — khula mandir dwaar

### B. Actions — 16 (sheet B)
1. ★ `act-follow` — shankh (pehle wala brand shankh, 3D)
2. ★ `act-following` — shankh + sone ka tick-ring
3. ★ `act-support` — I-Eye (brand) 3D
4. ★ `act-like` — kamal ka phool khilta hua
5. ★ `act-comment` — baat ka bubble, kinare par rangoli rekha
6. ★ `act-share` — teen jude diye/bindu (share)
7. ★ `act-sign` — kalam + taamra-patra (petition sign)
8. ★ `act-donate` — kalash jisme sikka
9. ★ `act-bookmark` — rudraksha mala wala bookmark ribbon
10. ★ `act-edit` — mor-pankh kalam
11. ★ `act-delete` — mitti ka khaali patra (kumbh) ulta
12. ★ `act-upload` — thali se upar uthti jyoti/teer
13. ★ `act-download` — neeche jaata teer, kalash mein
14. ★ `act-report` — dhwaja par chetavni chinh (lal-saffron)
15. ★ `act-verify` — sone ka seal + lotus (verified)
16. ★ `act-invite` — nimantran patra, swastik-free saada mohar

### C. Community & social — 12 (sheet C)
1. ★ `com-group` — teen dhaage se bandhe diye (satsang mandali)
2. ★ `com-event` — mandap (chaar khambhe + toran)
3. ★ `com-live` — deepak + laal live bindu
4. ★ `com-photo` — tamra frame mein lotus
5. ★ `com-video` — shankh-aakar play button
6. ★ `com-audio` — damru se nikalti tarange (pravachan audio)
7. ★ `com-poll` — teen kalash alag oonchai
8. ★ `com-story` — khuli pothi + mor-pankh
9. ★ `com-friends` — do haath namaste mudra
10. ★ `com-volunteer` — haath mein jhadu/seva dhwaja (sevak)
11. ◇ `com-mentor` — guru-paduka
12. ◇ `com-leaderboard` — teen seedhi wala vijay stambh

### D. Seva, petition & awareness — 12 (sheet D)
1. ★ `seva-goseva` — gaay + bachhda silhouette, gale mein ghanti
2. ★ `seva-ganga` — kalash se behti neeli dhaara
3. ★ `seva-temple-heritage` — pracheen mandir khambha
4. ★ `seva-education` — gurukul: pothi + taad-patra
5. ★ `seva-annadaan` — patta-thali mein bhojan
6. ★ `seva-health` — tulsi patta + plus
7. ★ `seva-environment` — peepal patta
8. ★ `seva-women` — kumkum thali + lotus
9. ★ `seva-legal` — taraazu, kinare par lotus
10. ★ `seva-disaster` — haathon mein deepak bachate hue
11. ◇ `seva-youth` — ugta surya + chhota pustak
12. ◇ `seva-elder` — lathi + rudraksha

### E. Peeth types — 12 (sheet E) — aage ki Peeth profiles ke liye
1. ★ `peeth-amnaya` — chaar dishaon wala mandir (4 Amnaya Peeth)
2. ★ `peeth-purva` — mandir + ugta surya (Puri, Purva)
3. ★ `peeth-paschim` — mandir + samudra lehar (Dwarka)
4. ★ `peeth-uttar` — mandir + barf ke pahaad (Jyotirmath)
5. ★ `peeth-dakshin` — mandir + veena (Sringeri)
6. ★ `peeth-kanchi` — gopuram (Kanchi)
7. ◇ `peeth-shakti` — trikona yantra + laal chunri (Shakti/Devi Peeth)
8. ◇ `peeth-jyotirlinga` — jyoti se ghira shivling-aakar (symbol)
9. ◇ `peeth-vaishnav` — tilak + shankh-chakra (Vaishnav peeth/gaddi)
10. ◇ `peeth-akhara` — trishul + dhwaja (Akhara)
11. ◇ `peeth-math` — saada math bhawan + tulsi chaura
12. ◇ `peeth-ashram` — kutiya + vriksh

### F. Acharya roles & titles — 12 (sheet F)
1. ★ `role-shankaracharya` — danda + kamandalu + rudraksha
2. ★ `role-jagadguru` — sone ka chhatra (chhatri) ke neeche paduka
3. ★ `role-successor` — do paduka, ek chhoti (uttaradhikari)
4. ★ `role-senior` — paduka par mala (purva/varishth Acharya)
5. ◇ `role-mahamandaleshwar` — chhatra + chamar
6. ◇ `role-acharya` — pothi + danda
7. ◇ `role-sannyasi` — bhagwa vastra moda hua + kamandalu
8. ◇ `role-vidwan` — taad-patra granth dher
9. ◇ `role-pujari` — aarti thali
10. ◇ `role-mahant` — gaddi (aasan) + chhatra
11. ◇ `role-yogi` — padmasan silhouette (bina chehre)
12. ◇ `role-guru-parampara` — jude hue paduka ki shrinkhala

### G. Dashnami sampradaya — 10 (sheet G, 4×3 grid, 2 cell khaali)
Har naam ka apna pratik:
1. ◇ `dash-giri` — pahaad
2. ◇ `dash-puri` — nagar-dwaar / kila
3. ◇ `dash-bharati` — veena / vidya
4. ◇ `dash-van` — van (ped samooh)
5. ◇ `dash-aranya` — ghana jungle + kutiya
6. ◇ `dash-parvat` — oonchi chotiyaan
7. ◇ `dash-sagar` — samudra lehar
8. ◇ `dash-tirtha` — ghaat ki seedhiyan + jal
9. ◇ `dash-ashram` — ashram kutiya
10. ◇ `dash-saraswati` — pothi + kamal

### H. Sampradaya tilak (3D) — 20 (sheet H-1, H-2)
Abhi site par 20 flat tilak hain; inka 3D glossy roop, mathe ke patch par (bina chehre):
1. ★ Smarta / Shaiva tripundra 2. ★ Shrivaishnava (Vadakalai) 3. ★ Shrivaishnava (Tenkalai)
4. ★ Madhva 5. ★ Gaudiya 6. ★ Pushtimarg (Vallabh) 7. ★ Nimbarka 8. ★ Ramanandi
9. ★ Swaminarayan 10. ★ Shakta (laal bindu/trikona) 11. ★ Ganapatya 12. ★ Saura
13. ★ Kaumara 14. ★ Lingayat/Veerashaiva 15. ★ Natha 16. ★ Kabirpanthi
17. ★ Warkari 18. ★ Dashanami 19. ★ Udasi 20. ★ Sant-mat/neutral bindu
(Naam wahi rahenge jo `inc/brand/tilak.php` mein hain: `tilak-<slug>.png`.)

### I. Devi / Shakti — 12 (sheet I) — Devi Peeth profiles ke liye
1. ◇ `devi-shakti-peeth` — trikona + laal chunri + diya
2. ◇ `devi-durga` — sinh-vaahan silhouette + trishul
3. ◇ `devi-lakshmi` — kamal par sone ke sikke
4. ◇ `devi-saraswati` — veena + hans
5. ◇ `devi-kali` — khadg nahin; laal-kaali jyoti + kapaal-mukt saada damru (shant roop)
6. ◇ `devi-parvati` — kamal + nandi chinh
7. ◇ `devi-annapurna` — kalchhi + annapatra
8. ◇ `devi-vaishno` — gufa dwaar + teen pindi
9. ◇ `devi-kamakhya` — laal kamal yantra
10. ◇ `devi-navaratri` — nau diye ka ghera
11. ◇ `devi-shringar` — chunri, choodi, bindi thali
12. ◇ `devi-yantra` — Shri Yantra (sone ka)

### J. Kathavachak & pravachan — 12 (sheet J)
1. ◇ `katha-vachak` — vyas-peeth (oonchi gaddi) + pothi
2. ◇ `katha-bhagwat` — Bhagwat pothi + mor-pankh
3. ◇ `katha-ramkatha` — dhanush-baan (aaram mudra) + pothi
4. ◇ `katha-shivpuran` — damru + rudraksha + pothi
5. ◇ `katha-devi-bhagwat` — trikona + pothi
6. ◇ `katha-gita` — rath ka pahiya + pothi
7. ◇ `katha-bhajan` — harmonium
8. ◇ `katha-kirtan` — kartaal + mridang
9. ◇ `katha-satsang` — mandap mein dariyan
10. ◇ `katha-live` — vyas-peeth + broadcast tarang
11. ◇ `katha-schedule` — pothi + panchang patra
12. ◇ `katha-mic` — sone ka mic par phool-mala

### K. Programmes & events — 16 (sheet K) — homepage news section ke liye
1. ★ `evt-yatra` — dhwaja + paduka path
2. ★ `evt-vijaya-yatra` — chhatra + dhwaja
3. ★ `evt-chaturmasya` — barish ki boondein + kutiya
4. ★ `evt-aradhana` — samadhi + diye
5. ★ `evt-jayanti` — diya + phool-mala
6. ★ `evt-puja` — pooja thali
7. ★ `evt-homa` — havan kund jwala
8. ★ `evt-abhishek` — kalash se jal dhaara
9. ★ `evt-kumbhabhishek` — gopuram par kalash
10. ★ `evt-darshan` — khula garbhagriha dwaar + jyoti
11. ★ `evt-pravachan` — vyas-peeth + tarangein
12. ★ `evt-sabha` — vidwat sabha: teen pothi ek ghere mein
13. ★ `evt-inauguration` — toran + kainchi (feeta)
14. ★ `evt-news` — taad-patra akhbaar
15. ◇ `evt-kumbh` — ghaat + kumbh
16. ◇ `evt-festival` — rangoli + diya

### L. Sacred objects & symbols — 20 (sheet L-1, L-2)
1. ★ `sym-om` 2. ★ `sym-shankh` 3. ★ `sym-chakra` 4. ★ `sym-trishul` 5. ★ `sym-damru`
6. ★ `sym-kalash` 7. ★ `sym-diya` 8. ★ `sym-lotus` 9. ★ `sym-rudraksha` 10. ★ `sym-tulsi`
11. ★ `sym-ghanti` 12. ★ `sym-paduka` 13. ★ `sym-kamandalu` 14. ★ `sym-danda`
15. ★ `sym-mor-pankh` 16. ★ `sym-bel-patra` 17. ◇ `sym-swastik` 18. ◇ `sym-dhwaja`
19. ◇ `sym-chhatra` 20. ◇ `sym-nandi`
(`sym-om` sabse zaroori: homepage par khaali photo wale card mein abhi saada "ॐ" dikhta hai.)

### M. Tirtha & places — 12 (sheet M)
1. ★ `place-kashi` — ghaat + Ganga aarti diye
2. ★ `place-puri` — Jagannath mandir shikhara + chakra
3. ★ `place-dwarka` — Dwarkadhish shikhara + samudra
4. ★ `place-badri` — pahaad + mandir (Jyotirmath/Badri)
5. ★ `place-sringeri` — Tunga nadi + Vidyashankar mandir
6. ★ `place-kanchi` — gopuram
7. ◇ `place-prayag` — sangam do dhaarein
8. ◇ `place-ayodhya` — mandir + dhanush
9. ◇ `place-mathura` — bansuri + mor-pankh
10. ◇ `place-ujjain` — Mahakal shikhara + chandra
11. ◇ `place-haridwar` — Har ki Pauri ghaat
12. ◇ `place-rameshwaram` — samudra + setu

### N. Status, badges & notifications — 16 (sheet N)
1. ★ `st-verified` — sone ka lotus seal
2. ★ `st-pending` — ret-ghadi + diya
3. ★ `st-approved` — hari pattiyon wala tick
4. ★ `st-rejected` — band kamal (gentle laal)
5. ★ `st-success` — khila kamal + chamak
6. ★ `st-warning` — saffron dhwaja + ! bina text (aakar se)
7. ★ `st-error` — bujhta diya
8. ★ `st-info` — chhota deepak + prakash kiran
9. ★ `st-new` — ugta surya
10. ★ `st-trending` — upar jaati jyoti
11. ★ `badge-seva` — sone ka kalash padak
12. ★ `badge-sanyojak` — dhwaja padak (organiser)
13. ★ `badge-pratham` — pehla diya padak
14. ★ `badge-niyamit` — rudraksha mala padak (regular)
15. ◇ `badge-guru-bhakt` — paduka padak
16. ◇ `badge-shatak` — 108 manke ki mala padak

### O. Festivals & calendar — 16 (sheet O)
1. ◇ `fest-diwali` 2. ◇ `fest-holi` 3. ◇ `fest-navaratri` 4. ◇ `fest-shivratri`
5. ◇ `fest-janmashtami` 6. ◇ `fest-ramnavami` 7. ◇ `fest-ganesh` 8. ◇ `fest-makar-sankranti`
9. ◇ `fest-guru-purnima` 10. ◇ `fest-ekadashi` 11. ◇ `fest-purnima` 12. ◇ `fest-amavasya`
13. ◇ `fest-dev-deepawali` 14. ◇ `fest-chhath` 15. ◇ `fest-shankara-jayanti` 16. ◇ `fest-kartik-snan`

### P. Empty states & illustrations (bade, 1024×1024, alag-alag) — 6
1. ★ `empty-feed` — khaali thali par ek diya ("abhi kuch nahi")
2. ★ `empty-search` — magnifier + band kamal
3. ★ `empty-notifications` — shaant ghanti
4. ★ `empty-profile-photo` — sone ke gol frame mein Om (Acharya photo placeholder)
5. ★ `error-404` — bhatakti paduka ka path
6. ★ `welcome` — mandir dwaar par toran + diye

---

## 3. Count summary

| Category | Icons | Phase 1 ★ |
|---|---|---|
| A Navigation | 16 | 16 |
| B Actions | 16 | 16 |
| C Community | 12 | 10 |
| D Seva | 12 | 10 |
| E Peeth types | 12 | 6 |
| F Roles | 12 | 4 |
| G Dashnami | 10 | 0 |
| H Tilak 3D | 20 | 20 |
| I Devi | 12 | 0 |
| J Kathavachak | 12 | 0 |
| K Programmes | 16 | 14 |
| L Symbols | 20 | 16 |
| M Places | 12 | 6 |
| N Status/badges | 16 | 14 |
| O Festivals | 16 | 0 |
| P Empty states | 6 | 6 |
| **Total** | **240** | **138** |

---

## 4. Ready-to-paste example (sheet A)

```
STYLE: premium 3D icon, soft clay + glossy enamel finish, gentle specular highlights,
subtle gold (#C9A227) rim accents, primary saffron (#FF9933) and deep maroon (#7A1F1F),
secondary sandalwood cream (#F5E6C8); tiny touch of sacred motif only where it fits.
Front 3/4 view, ~15° tilt, soft key light top-left, soft contact shadow only.
No text, no letters, no faces. Object fills 80% of its cell, centered.

A 4x4 icon sheet, 2048x2048, on solid pure white background, 16 separate isolated
icons, equal spacing with at least 64px gap. Row by row, left to right:
1) small home with a temple shikhara roof, 2) three oil lamps on a round brass plate,
3) open scripture book with a lotus on it, 4) temple shikhara with a saffron flag,
5) rolled scroll with a wax seal, 6) conch shell emitting sound waves,
7) mandala-styled four-panel dashboard tile, 8) round silhouette with turban and
shawl, no face, 9) temple bell, 10) white pigeon carrying a letter,
11) magnifying glass with a tiny lotus inside the lens, 12) gear shaped like a
dharma wheel, 13) three gold horizontal bars, 14) traditional panchang calendar
leaf, 15) open palm holding a small flame, 16) open temple door.

Avoid: photo-realism, text, watermark, harsh shadows, neon, flat 2D, merged objects.
```
Baaki sheets ke liye bas list (1–16) badal dein; STYLE block hamesha same rakhein taaki poora set ek jaisa dikhe.

---

## 5. Mujhe kaise dena hai
1. Har sheet ek file: `sheet-A.png`, `sheet-B.png`, … (ya ek-ek icon `sp-<name>.png`).
2. Folder `sampreshan.tech/icons-source/` mein daal dein, ya chat mein path bata dein.
3. Main kaat kar, transparent karke, sizes banakar, `sp_icon()` helper ke saath har page par laga dunga
   (nav, buttons, directory, feed, notifications, homepage news, empty states).
