"""Build stable card metadata and concise recall notes; never claims scholarly sign-off."""
from pathlib import Path
import json, re, hashlib
root = Path(__file__).resolve().parents[1]
titles = ['Sanatan Foundations', 'Deities and Sacred Symbols', 'Sacred Texts', 'Sacred Geography', 'Arts and Living Traditions', 'Devotion and Festivals', 'Rituals and Life', 'Sages and Celestial Traditions', 'Learning and Knowledge', 'Philosophy and Shastra']
context = [
'Learning the distinction between a rite, a goal of life and a form of practice helps these terms become meaningful beyond memorisation.',
'Names, attributes and sacred symbols help distinguish the forms through which devotees approach the Divine. Depictions can vary by tradition.',
'Keep the text, its literary category and its traditional attribution distinct. A Veda, an epic and a Purana belong to different textual groupings.',
'Connect a sacred place with its location and devotional association. The larger Char Dham circuit differs from the Himalayan Chota Char Dham.',
'Living traditions include arts, sacred objects, offerings and regional customs. Their forms are not identical across all communities.',
'Devotion takes the forms of remembrance, worship and service. Festival observances can differ with region and calendar tradition.',
'Ritual names describe different intentions and stages of life. Follow the practice of your own family and tradition when performing a rite.',
'Sages, celestial beings and guardians belong to distinct traditional groupings. Lists can depend on the text and cosmic period being described.',
'Indian knowledge traditions cover language, mathematics, arts and care of life. A traditional classification describes its own historical framework.',
'Each philosophical school has its own vocabulary and approach to knowledge. Distinguishing the school from its teacher helps avoid confusion.'
]
# Original concise teaching notes. No third-party prose is copied.
concepts = {
'Moksha': 'Moksha means liberation from the cycle of rebirth; it is one of the four Purusharthas, alongside dharma, artha and kama.',
'Yajna': 'The four Purusharthas are dharma, artha, kama and moksha. Yajna is a sacrificial or offering practice, rather than a fifth Purushartha.',
'Rigveda': 'The Rigveda is a collection of Vedic hymns. The Samaveda emphasises chants, while the Yajurveda supplies ritual formulas.',
'Namakarana': 'Namakarana gives a child a name. Annaprashana marks the first ceremonial feeding, and Upanayana begins a distinct stage of study.',
'Annaprashana': 'Anna refers to food. Annaprashana marks a child’s ceremonial first feeding of solid food, distinct from the naming ceremony.',
'Upanayana': 'Upanayana is associated with initiation into Vedic study and the sacred thread. It differs from Vidyarambha, which marks the beginning of learning.',
'Antyeshti': 'Antyeshti names the concluding life-cycle rites after death. It is distinct from Shraddha, the subsequent rites of remembrance and offering.',
'Japa': 'Japa is repetition of a mantra. It may be spoken, whispered or mental; group devotional singing is called kirtan.',
'Sankalpa': 'Sankalpa sets out the intention for a rite. It states the purpose before the worship or offering is performed.',
'Pradakshina': 'Pradakshina is reverential circumambulation, conventionally keeping the object of worship to one’s right. Specific rites can follow their own rules.',
'Abhisheka': 'Abhisheka is ceremonial bathing of the deity. Aarti instead offers the light of a lamp through a waving motion.',
'Tarpana': 'Tarpana is an offering, commonly of water, made with remembrance and appropriate mantras. It differs from bathing a deity in Abhisheka.',
'Samadhi': 'In Patanjali’s eightfold path, dharana is concentration, dhyana is meditation and samadhi is absorption. The terms describe distinct limbs.',
'Pranayama': 'Pranayama is the regulation of breath in the eightfold path of yoga. It is distinct from asana, the limb concerned with posture.',
'Dhyana': 'Dhyana is meditation. Dharana means concentration, while samadhi describes absorption; these are related but distinct limbs of yoga.',
'Yama': 'Ahimsa and satya are ethical restraints in the yamas. The niyamas are a separate group of observances.',
'Garuda': 'Garuda is associated with Vishnu as his vahana. Nandi belongs to Shiva’s iconography, and the hamsa is associated with Brahma.',
'Nandi': 'Nandi is the bull associated with Shiva. In many Shiva temples, Nandi faces the shrine.',
'Nataraja': 'Nataraja presents Shiva as the cosmic dancer. Dakshinamurti presents Shiva as the teacher; these are different sacred forms.',
'Makara': 'The makara is a composite aquatic creature in sacred art. It is associated with both Varuna and Ganga.',
'Vajra': 'The vajra is Indra’s thunderbolt weapon. Shiva’s trishula and Vishnu’s chakra belong to different divine iconographies.',
'Airavata': 'Airavata is Indra’s elephant. Uchchaihshravas is the celestial horse, so the two names should not be interchanged.',
'Veena': 'The veena in Saraswati’s iconography expresses her association with learning and the arts. Krishna is especially associated with the flute.',
'Matsya': 'Matsya is Vishnu’s fish incarnation. Kurma is the tortoise incarnation, and Varaha is the boar incarnation.',
'Kurma': 'In the ocean-churning narrative, Kurma supports Mount Mandara. Vasuki serves as the churning rope.',
'Vamana': 'Vamana approaches Bali for three steps of land and manifests as Trivikrama. This differs from Narasimha’s protection of Prahlada.',
'Shruti': 'Shruti means what is heard and is the traditional category of the Vedas. Smriti means what is remembered.',
'Smriti': 'Smriti is the remembered tradition, distinct from Shruti. Dharma Shastra is placed within this broad remembered literature.',
'Vedanta': 'Vedanta means the end or culmination of the Vedas and is associated with the Upanishads. It is distinct from the six Vedangas.',
'Katha Upanishad': 'The Katha Upanishad develops Nachiketa’s dialogue with Yama concerning death, the self and the choice between the good and the pleasant.',
'Mandukya Upanishad': 'The Mandukya Upanishad has twelve mantras and examines Om and the states of consciousness. Its short length does not make its subject simple.',
'Brihadaranyaka Upanishad': 'The Brihadaranyaka Upanishad includes Yajnavalkya’s teaching to Maitreyi. It is also associated with the Mahavakya Aham Brahmasmi.',
'Bhagavata Purana': 'The tenth canto of the Bhagavata Purana narrates Krishna’s birth and divine acts. The Bhagavad Gita is a different work within the Mahabharata.',
'Markandeya Purana': 'The Devi Mahatmya is embedded in the Markandeya Purana. Its devotional recitation is also known as Durga Saptashati.',
'Sundara Kanda': 'Sundara Kanda centres on Hanuman’s journey to Lanka and meeting with Sita. The main battle is narrated in Yuddha Kanda.',
'Samaveda': 'The Samaveda arranges Vedic verses for chanting. This explains its traditional connection with melody and sacred music.',
'Yajurveda': 'The Yajurveda supplies formulas used in sacrifice. Its ritual emphasis distinguishes it from the hymn collection of the Rigveda.',
'Itihasa': 'The Ramayana and Mahabharata are traditionally grouped as Itihasa. This category is distinct from the Puranas.',
'Varanasi': 'Varanasi is also known as Kashi. Kashi Vishwanath is its celebrated Jyotirlinga, while Kumbh gatherings are associated with other named host cities.',
'Puri': 'Puri belongs to the eastern side of the larger Char Dham circuit. The four shrines of Uttarakhand form a separate Himalayan circuit.',
'Badrinath': 'Badrinath belongs to both the larger Char Dham circuit and the Himalayan Chota Char Dham. Kedarnath belongs to the Himalayan circuit.',
'Saptapadi': 'Saptapadi means the seven steps taken by the couple. It is a distinct wedding rite and is not another name for Kanyadaan.',
'Sattvic dana': 'The Gita describes sattvic giving as appropriate giving without expectation of repayment. The donor’s intention matters alongside the gift.',
'Guna and karma': 'Bhagavad Gita 4.13 describes the fourfold order in relation to guna and karma. The wording of this question concerns that verse.',
'Prarabdha Karma': 'Prarabdha refers to karma already bearing fruit. Sanchita denotes the accumulated store of karma.',
'Sanchita Karma': 'Sanchita is accumulated karma. Prarabdha is the portion understood as already ripening into present experience.',
'Vasistha': 'Vasistha appears in a commonly recited list for the present Vaivasvata Manvantara. Saptarishi lists change across Manvantaras, so “always” would be too broad.',
'Vasuki': 'Vasuki is the serpent used as the rope in the ocean-churning narrative. Adishesha is associated with Vishnu’s resting form.',
'Adishesha': 'Adishesha is associated with Vishnu’s resting form. Vasuki, a different serpent, serves as the rope in the ocean-churning narrative.',
'Kubera': 'Kubera is associated with wealth and the northern direction. Indra guards the east, Yama the south and Varuna the west in the familiar eight-guardian scheme.',
'Dhanvantari': 'Dhanvantari appears in the ocean-churning narrative holding the vessel of amrita and is revered in the Ayurvedic tradition.',
'Vyakarana': 'Vyakarana is grammar; Shiksha concerns pronunciation, Chandas metre, and Nirukta the explanation of words. They are separate Vedangas.',
'Shiksha': 'Shiksha concerns the correct sounds and pronunciation of Vedic recitation. It differs from Vyakarana, which studies grammar.',
'Chandas': 'Chandas studies poetic metre and syllabic patterns. Shiksha studies pronunciation rather than the pattern of a verse.',
'Nirukta': 'Nirukta explains words and their derivations. It is one of the six Vedangas, distinct from Vyakarana or grammar.',
'Jyotisha': 'As a Vedanga, Jyotisha supports the determination of ritual times through celestial cycles. It is distinct from Kalpa, which addresses ritual procedure.',
'Raga': 'A raga provides a melodic framework, while tala organises a rhythmic cycle. Melody and rhythm play different roles in a performance.',
'Tala': 'Tala is the recurring rhythmic cycle. Raga concerns melodic organisation, and laya concerns tempo.',
'Prasad': 'Prasad is an offering received back with reverence and shared after worship. Dakshina is a gift made to a teacher or officiant.',
'Dakshina': 'Dakshina is a gift offered to a teacher or ritual officiant. It is distinct from prasad, the offering shared after worship.',
'Rahu and Ketu': 'Rahu and Ketu are the two lunar nodes in Jyotisha. Their description as shadow grahas distinguishes them from visible celestial bodies.',
'Prakriti': 'Samkhya distinguishes Purusha, consciousness, from Prakriti, nature. The distinction is central to the school’s explanation of experience.',
'Adi Shankaracharya': 'Adi Shankaracharya is a central teacher of Advaita Vedanta. Ramanuja teaches Vishishtadvaita, and Madhva teaches Dvaita.',
'Ramanujacharya': 'Ramanuja is associated with Vishishtadvaita, qualified non-dualism. His Sri Bhashya comments on the Brahma Sutras.',
'Madhvacharya': 'Madhva is associated with Dvaita Vedanta. Distinguish this school from Shankara’s Advaita and Ramanuja’s Vishishtadvaita.',
'Brahmasthana': 'Brahmasthana is the central zone of a Vastu layout. Ishanya names the north-east rather than the centre.',
'Tat Tvam Asi': 'Tat Tvam Asi is associated with the Chandogya Upanishad. The traditional Mahavakya grouping connects distinct sayings with the four Vedas.'
}
notes, metadata = {}, []
for level, f in enumerate(sorted((root/'content/sangyani').glob('*.txt')), 1):
    lines=f.read_text().splitlines(); lines[0]='title: '+titles[level-1]
    text='\n'.join(lines)+'\n'
    text=text.replace('Which is the largest of the principal Upanishads?', 'Which principal Upanishad contains Yajnavalkya’s dialogues with Maitreyi?')
    text=text.replace('Ayurveda is regarded as an Upaveda attached to which Veda?', 'In a common classification linking Ayurveda with the Atharvan tradition, which Veda is it attached to?')
    text=text.replace('Which herb is commonly called Giloy and praised for immunity?', 'Which Ayurvedic plant name refers to the climbing plant commonly called Giloy?')
    text=text.replace('Which spice is famous in Ayurveda for its healing yellow colour?', 'Which spice is known in Ayurveda as Haridra and has a yellow colour?')
    text=text.replace('Which ancient text by Lagadha is the oldest Indian text on astronomy?', 'Which early Indian text on calendrical astronomy is attributed to Lagadha?')
    text=text.replace('The science of sound and mantra holds that the universe arose from which primordial sound?', 'Which primordial sound is revered as Nada Brahman in the tradition of sound and mantra?')
    text=text.replace('Who is revered as the founder of the Nath yoga tradition?', 'Which early master of the Nath tradition was the guru of Gorakhnath?')
    f.write_text(text)
    for index, line in enumerate(text.splitlines()[1:],1):
        q, answer, *_ = map(str.strip,line.split('|')); key=f'L{level:02d}-Q{index:03d}'
        # Named concepts receive a teaching note; other cards get a card-specific recall association and a topic distinction.
        note=concepts.get(answer)
        if note is None:
            if q.startswith('How many '):
                subject=re.sub(r'\s+(are there|were there|does .+? have|make up .+|are traditionally .+|are recognised|are described.+|are in .+)\?$', '',q[9:])
                note=f'The count to remember for this card is {answer.lower()}. {context[level-1]}'
            else:
                note=f'{answer} is the answer to this card’s association: {q.rstrip("?")}. {context[level-1]}'
        notes[key]=note
        metadata.append({'id':key,'topic':titles[level-1],'questionSha256':hashlib.sha256(q.encode()).hexdigest(),'kind':'recall','editorialStatus':'independent-review-pending','noteKind':'concept' if answer in concepts else 'recall','compiledBy':'ShivBodh Research Group'})
(root/'buddyboss-theme-child/inc/quiz/explanations.json').write_text(json.dumps(notes,ensure_ascii=False,indent=2)+'\n')
(root/'content/sangyani/review-register.json').write_text(json.dumps({'version':'2026-10-27','cards':metadata,'reviewPolicy':'Editorial learning material. No independent scholarly sign-off is asserted. Published administrator corrections record actual reviewer and internal evidence.','internalEvidence':[{'id':'L08-Q001','reference':'Bhagavata Purana 8.13.15–16','url':'https://vedabase.io/en/library/sb/8/13/15-16/','reason':'Lists of seven sages differ across Manvantaras; qualify the present-period question.'}]},ensure_ascii=False,indent=2)+'\n')
print(f'{len(notes)} notes and review records built')
