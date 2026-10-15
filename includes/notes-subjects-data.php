<?php
/**
 * Subjects shown on notes.php when a Form is picked (Ordinary Secondary –
 * general stream). Each subject links to:
 *  - 'pdf': per Form, the textbook (and, where there is no full book, notes):
 *    one item or a list of items, each a Google Drive file ('id', mostly
 *    WazaElimu's copies of the TIE books), a TIE flipbook on FlipHTML5
 *    ('flip'), a TIE Online Library book ('tie', needs a free sign-in; only
 *    when no PDF exists) or another address ('url'). 'size' is in bytes,
 *    'label' replaces the "<Subject> Book" title, 'by' names a non-TIE
 *    source, 'view' marks a read-only file. Form 1-2 use new-syllabus books,
 *  - Form 1-2: its official new syllabus (muhtasari) PDF on the TIE website,
 *  - its NECTA past papers on theory.php (ftna = Form 1-2, csee = Form 3-4),
 *  - Form 3-4: its old syllabus PDF on TETEA ('old').
 * 'forms' limits a subject to some Forms; leave it out for all four.
 * Form 3-4 follow the old syllabus: 'old_name' is the subject's name there,
 * 'old' its old syllabus PDF on TETEA, and their 'pdf' books are old ones.
 */

return [
    'kiswahili' => [
        'name' => 'Kiswahili',
        'pdf' => [
            'form1' => ['id' => '19f8D1ConQV7g7pUyjoLWDZpiEcCIHb0P', 'size' => 78591189],
            'form2' => ['id' => '1TMJn-a60RQGeV90hJKzEIejZtzy4o1P8', 'size' => 11559434],
            'form3' => ['id' => '1bELI_bpBc-pydbd8Cc6wx1UBRwfuqXJo', 'size' => 18618322],
            'form4' => ['id' => '1lw0e2h9bdZNZkHIlpJBZClasUgIYLkrb'],
        ],
        'syllabus' => 'sw-1741937359-MUHTASARI WA KISWAHILI KIDATO  I-IV FINAL.pdf',
        'ftna' => 'kiswahili', 'csee' => 'kiswahili',
        'old' => 'Syllabus - Kiswahili - F1-F4 - 2016.pdf',
    ],
    'english' => [
        'name' => 'English Language',
        'pdf' => [
            'form1' => ['id' => '1cu_jLps6oK8Zo4O2q3-CTn1BUiYIx9D0', 'size' => 17639986],
            'form2' => ['id' => '1qNFIkcWEadOpWwjlisoJoRq9_9sPr-s2', 'size' => 10331862],
            'form3' => ['id' => '1-ozO_8DAuN__JYT2UqYPK2Cg3-8gWhtK'],
            'form4' => ['id' => '16taIGwD0LWFodMzoXaOb4VXX9B1ME3wo', 'size' => 19143062],
        ],
        'syllabus' => 'sw-1756459411-ENGLISH lANGUAGE FINAL.pdf',
        'ftna' => 'english', 'csee' => 'english',
        'old' => 'Syllabus - English - F1-F4 - 2016.pdf',
    ],
    'mathematics' => [
        'name' => 'Mathematics',
        'old_name' => 'Basic Mathematics',
        'pdf' => [
            'form1' => ['id' => '12VQV8b5tSCblfl7svz9vF99O3o0jkjVr', 'size' => 20162628],
            'form2' => ['id' => '1m9ojiRwaOjr48U2SbttLzc_HnbPc2OeL', 'size' => 19814926],
            'form3' => ['id' => '1zu7M1OcwOppIcdV7LKRfjIKnbDhMndZu', 'size' => 38011783],
            'form4' => ['id' => '1pdrUOzRZXEBcQPFV607aN2c5JfTL8DBO', 'size' => 22711218],
        ],
        'syllabus' => 'sw-1727256902-MATHEMATICS SYLLABUS - O level final.pdf',
        'ftna' => 'math', 'csee' => 'math',
        'old' => 'Syllabus - Basic Math - F1-F4 - 2017.pdf',
    ],
    'historia-maadili' => [
        'name' => 'Historia ya Tanzania na Maadili',
        'pdf' => [
            'form1' => ['id' => '1hRmD148IoOUo6fRGdkJ7wRNOzlnIKre3', 'size' => 17242190],
            'form2' => [
                ['id' => '1V5sOmQBRybtOMG5cnGTyofl-_-5NZo7u', 'size' => 20973781, 'label' => 'Historia ya Tanzania na Maadili — Kiongozi cha Mwalimu, Teacher\'s Guide'],
                ['tie' => '2a871b1d-974a-4e96-a00e-55023f9b6350', 'label' => 'Historia ya Tanzania na Maadili — Kitabu cha Mwanafunzi'],
                ['id' => '1UxnSmi1Qd-9FTEKSu8pcLLntgGFsnXHk', 'label' => 'Historia ya Tanzania na Maadili Notes — Mada ya 1: Ukoloni katika Jamii za Kitanzania', 'by' => 'WazaElimu'],
                ['id' => '1-nkwY7styh6LUoLGrbFnrksvZoIb5Y4O', 'label' => 'Historia ya Tanzania na Maadili Notes — Mada ya 2: Athari za Ukoloni katika Jamii ya Kitanzania', 'by' => 'WazaElimu'],
            ],
        ],
        'syllabus' => 'sw-1788264155-Muht. Historia ya Tanzania & Maadili 2.pdf',
        'ftna' => 'historia-maadili',
        'forms' => ['form1', 'form2'],
    ],
    'civics' => [
        'name' => 'Civics',
        'pdf' => [
            'form3' => ['id' => '1oSBTbHafAkEb36D-MofC4t_TY-uX4yfy', 'size' => 14986015],
            'form4' => ['id' => '1QDe2xeyQwUUrEctOAumHWtbm0G6RFcTz', 'size' => 15615267],
        ],
        'csee' => 'civics',
        'old' => 'Syllabus - Civics - F1-F4 - 2017.pdf',
        'forms' => ['form3', 'form4'],
    ],
    'history' => [
        'name' => 'History',
        'pdf' => [
            'form1' => ['id' => '1fCFPGiheSIHsS-7n7T8BV69XGbDez11L', 'size' => 15257431],
            'form2' => ['id' => '1YF5T-Lm_iu2oV-mMtmX0CVyswaAIkbst', 'size' => 9177902],
            'form3' => ['id' => '134Lkg_GyPyg0gpdHfT98QdnHiMKv7H7t', 'size' => 13111113],
            'form4' => ['id' => '1v_zmmEZko0HbJQmm1ApLWNsxfDKULYR7'],
        ],
        'syllabus' => 'sw-1727255952-HISTORY FOR FORM I-IV.pdf',
        'ftna' => 'history', 'csee' => 'history',
        'old' => 'Syllabus - History - F1-F4 - 2012.pdf',
    ],
    'geography' => [
        'name' => 'Geography',
        'pdf' => [
            'form1' => ['id' => '1fsrz3PCxQWcSZHN_HphGT1X23wvL_IHs', 'size' => 74542740],
            'form2' => ['id' => '1KteYmDjOl-p2jlAOV4mJn-VRX5Buzg4E', 'size' => 28568606],
            'form3' => ['id' => '1YR2ipx8EiUTg1r2PU-a688UgRbKSFfYj'],
            'form4' => ['id' => '1p5oEUAUmR541TrK9WIE2jPFBUP1HoYsZ'],
        ],
        'syllabus' => 'sw-1727255849-GEOGRAPHY O-Level.pdf',
        'ftna' => 'geography', 'csee' => 'geography',
        'old' => 'Syllabus - Geography - F1-F4 - 2016.pdf',
    ],
    'biology' => [
        'name' => 'Biology',
        'pdf' => [
            'form1' => ['id' => '1FgFNFnM6cujQYvGzqtinxRaxMe2rYX_C', 'size' => 17271674],
            'form2' => ['id' => '1NfEvClLyoY-qJlF6Zv6S4hZQK5UHOQTu', 'size' => 14874766],
            'form3' => ['id' => '1p_dGDLAwtUmlyUj-3zrE9nFwxMIHf0jq'],
            'form4' => ['id' => '1B2wmCdK1ir0fXLvIOutagyzAQWTxT6eN', 'size' => 37232427],
        ],
        'syllabus' => 'sw-1727192422-BIOLOGY SYLLABUS ORDINARY SECONDARY FINAL .pdf',
        'ftna' => 'biology', 'csee' => 'biology',
        'old' => 'Syllabus - Biology - F1-F4 - 2012.pdf',
    ],
    'chemistry' => [
        'name' => 'Chemistry',
        'pdf' => [
            'form1' => ['id' => '1l6kkYFUn1HbP8if3LYxv0EuSMGbqn8s-', 'size' => 13856648],
            'form2' => ['id' => '13fZuRrop5ebcCDD3gvrPX3vfGXBP7urz', 'size' => 11060929],
            'form3' => ['id' => '1MkGOoFpDoGcFs80ydv0BJkCbBz_gsDx7', 'size' => 29864895],
            'form4' => ['id' => '1CaCWRYQ_wltdTsE9ya-eZtCQDJBIxO9k'],
        ],
        'syllabus' => "sw-1727192894-CHEMISTRY O'LEVEL EDUCATION FINAL.pdf",
        'ftna' => 'chemistry', 'csee' => 'chemistry',
    ],
    'physics' => [
        'name' => 'Physics',
        'pdf' => [
            'form1' => ['id' => '1jp5e_nbEeK3MVxzvmYLzTckyGbHr2YL1', 'size' => 21570034],
            'form2' => ['id' => '1sEZZIsp2cSTvdeVBhsddvfwZFQVEsA4E', 'size' => 53112841],
            'form3' => ['id' => '1W8zvhi89h1ISG_RjUPGNLjKqnNSmB_XN'],
            'form4' => ['id' => '1u0B8UvAKsTaUAqey6axW0ZJisU1xCwJ_', 'size' => 26699153],
        ],
        'syllabus' => 'sw-1727257838-PHYSICS FOR ORDINARY SECONDARY EDUCATION FINAL.pdf',
        'ftna' => 'physics', 'csee' => 'physics',
        'old' => 'Syllabus - Physics - F1-F4 - 2017.pdf',
    ],
    'computer-science' => [
        'name' => 'Computer Science',
        'old_name' => 'Information & Computer Studies',
        'pdf' => [
            'form1' => ['id' => '16hS8VZN3ZdbL6N3rOS3pqaGIIMPoLZZ4', 'size' => 28211962],
            'form2' => ['id' => '1iVwDZaALjp4qYN-nzwcHdizby912H8CJ', 'size' => 22531298],
            'form3' => ['id' => '1LcVqVDSKoZQIwWMbnCvd8kN4yG04woqp'],
            'form4' => ['id' => '1Zz82eCQYVXvY2Ms_4qhQwBYe46ZzYiZT'],
        ],
        'syllabus' => 'sw-1727193276-COMPUTER SCIENCE SYLABUS I TO IV (12_07_2023).pdf',
        'ftna' => 'ics', 'csee' => 'computers',
        'old' => 'Syllabus - Information and Computer Studies - F1-F4 - 2005.pdf',
    ],
    'business-studies' => [
        'name' => 'Business Studies',
        'old_name' => 'Commerce',
        'pdf' => [
            'form1' => ['id' => '1Gjq7b5WEI5N0hBZtqJovsEkjJw-sKx7D', 'size' => 48096643],
            'form2' => ['id' => '1oMkkRo4fdwi12Ge84VB8X8c9klvEIaaI', 'size' => 14046237],
            'form3' => ['id' => '14LcQl3aF6idj0gITF7W0ryntYzMmo6xL'],
            'form4' => ['id' => '1r0d2Qc15WijY_JKF8IjQ5bnxM7Pp-1yo', 'size' => 8532188],
        ],
        'syllabus' => 'sw-1727192697-BUSINESS STUDIES SYLLABUS FOR ORDINARY LEVEL SECONDARY EDUCATION.pdf',
        'ftna' => 'business', 'csee' => 'commerce',
        'old' => 'Syllabus - Commerce - F1-F4 - 2016.pdf',
    ],
    'book-keeping' => [
        'name' => 'Book-keeping',
        'pdf' => [
            'form1' => ['id' => '1Ibk5UjovZ7nrVz_Bllb8A63geS2ZqRCp', 'size' => 18002875],
            'form2' => ['id' => '1tGewFRgXKMn5TcfaQBZn40g5IbbGc9f8', 'size' => 4903226],
            'form3' => ['id' => '1Ep9Lrnm6vNMLDnnX-E5Kb1zUf43ofwAQ', 'size' => 3082026],
            'form4' => ['id' => '1xBm0OIxjzB4QrQMzKY-38k2LpiyW47Kh', 'size' => 39535833],
        ],
        'syllabus' => 'sw-1727192590-Book-keeping Form I-IV FINAL SYLLABUS.pdf',
        'ftna' => 'bookkeeping', 'csee' => 'bookkeeping',
        'old' => 'Syllabus - Book Keeping - F1-F4 - 2016.pdf',
    ],
    'agriculture' => [
        'name' => 'Agriculture',
        'pdf' => [
            'form1' => ['id' => '1vIFzPZ4Sx0J7xT4ack4sWEQ7JAwC1282', 'size' => 23525507],
            'form2' => ['id' => '1aCFMQ1IA10atoXrZSUu-8Fq6h0JtEKZl', 'size' => 31116499],
            'form3' => ['id' => '1JHYQ08GWunXVNc7awJ-0zpTsafohEAoQ', 'size' => 67690473],
            'form4' => ['id' => '1ib5ID4mPU2eMB85XGwEkviD7KMUcrgba'],
        ],
        'syllabus' => 'sw-1727192126-AGRICULTURE_ O-LEVEL SYLLABUS FINAL.pdf',
        'old' => 'Syllabus - Agriculture - F1-F4 - 2019.pdf',
    ],
    'food-nutrition' => [
        'name' => 'Food and Human Nutrition',
        'old_name' => 'Food & Nutrition',
        'pdf' => [
            'form1' => ['tie' => '5f25b10c-80b9-458b-a4cd-aaba0dcc31ce'],
            'form2' => ['tie' => 'e1241f05-d589-46e4-bdc3-b68f6cf65909'],
            'form3' => ['flip' => 'dvtf'],
        ],
        'syllabus' => 'sw-1727255526-FOOD AND HUMAN NUTRITION STD I - IV.pdf',
        'ftna' => 'foodnutrition', 'csee' => 'foodnutrition',
    ],
    'literature' => [
        'name' => 'Literature in English',
        'pdf' => [
            'form3' => ['id' => '171Ys733DOUiARHr6OlmS5qutmgVSuPNf', 'size' => 62945498],
            'form4' => ['id' => '171Ys733DOUiARHr6OlmS5qutmgVSuPNf', 'size' => 62945498],
        ],
        'syllabus' => 'sw-1727256752-LITERATURE IN ENGLISH O-LEVEL FINAL.pdf',
        'csee' => 'literature',
        'forms' => ['form3', 'form4'],
    ],
    'kiislamu' => [
        'name' => 'Elimu ya Dini ya Kiislamu',
        'pdf' => [
            'form1' => ['id' => '1_z-zVa6QMJZX2LsCCKxwVqLycCoxffOi', 'size' => 59310903],
            'form2' => ['id' => '12abjtWeynhCFxjnGmkuu2GAXh3v5zMt6', 'size' => 27028118],
            'form3' => ['id' => '11VV-7yQe92L_JNnB-xEv9iC1P92XgZLp', 'label' => 'Elimu ya Dini ya Kiislamu — Kitabu cha 3', 'by' => 'Islamic Education Panel', 'view' => true],
            'form4' => ['url' => 'https://www.schoolpvh.ac.tz/p/edkf1.html', 'label' => 'Elimu ya Dini ya Kiislamu Notes — Kidato cha Nne (all topics)', 'by' => 'School PVH'],
        ],
        'syllabus' => 'sw-1727257430-MUHTASARI WA ELIMU YA DINI YA KIISLAM KWA SHULE ZA SEKONDARI KIDATO CHA I - IV.pdf',
        'ftna' => 'kiislamu', 'csee' => 'kiislamu',
        'old' => 'Syllabus - Kiislamu - F1-F4 - 2012.pdf',
    ],
    'bible' => [
        'name' => 'Bible Knowledge',
        'pdf' => [
            'form1' => ['id' => '1JeHJ4Dnn_mAbSFTOBIUrWa12IvsDxOas', 'size' => 23938571],
            'form2' => [
                ['tie' => '97fb09ae-d96a-4e97-a1ec-9cbb8ff7c818'],
                ['id' => '17LXN04HTZ0WUmphZboEXWo6iUdhtl7Ve', 'label' => 'Bible Knowledge Notes — Topic 1: God\'s Love and Compassion', 'by' => 'WazaElimu'],
            ],
            'form3' => [
                ['id' => '1gaE60vSBsqAXwZPBYwW7Oh7CYRe4z6hO', 'label' => 'Bible Knowledge Notes — Topic 1: General Introduction — Jewish Institutions and Sects', 'by' => 'WazaElimu'],
                ['id' => '1sElkTg53A-JUTBtisDZBW3GHnv3XkAtG', 'label' => 'Bible Knowledge Notes — Topic 2: Birth Stories of John the Baptist and Jesus', 'by' => 'WazaElimu'],
                ['id' => '1a1hXP__ts1AmWWSlgx4mKR3NYPRIQpJ4', 'label' => 'Bible Knowledge Notes — Topic 3: Preparation of Jesus\' Ministry', 'by' => 'WazaElimu'],
                ['id' => '1XnJm6DKSQ0t1bZ2SFM7VyAICQj_d4jQ6', 'label' => 'Bible Knowledge Notes — Topic 4: Jesus\' Public Ministry in Galilee', 'by' => 'WazaElimu'],
                ['id' => '1r0Dg2uuC0dxjHLzOiQlzhMRZgt9SUpxz', 'label' => 'Bible Knowledge Notes — Topic 5: The Sermon on the Plain', 'by' => 'WazaElimu'],
                ['id' => '107S2hCrKsiw3B_cS6tTUUTp66uu9AYn-', 'label' => 'Bible Knowledge Notes — Topic 6: The Parables of Jesus Christ', 'by' => 'WazaElimu'],
                ['id' => '1zQNTbcEhs7MSf3LpqslBpCqVT2i-fMt4', 'label' => 'Bible Knowledge Notes — Topic 7: The Commission of the Twelve and Jesus\' Authority', 'by' => 'WazaElimu'],
                ['id' => '1puPn39yAiD0pv_ue0L6z8co3pkIpg1ux', 'label' => 'Bible Knowledge Notes — Topic 8: Events and Teaching of Jesus on His Way to Jerusalem', 'by' => 'WazaElimu'],
            ],
            'form4' => [
                ['url' => 'https://bibleknowledgetz.com/wp-content/uploads/2021/08/BK-form-4-text-book-without-Acts.pdf', 'size' => 1646743, 'label' => 'Bible Knowledge Student Notes (textbook)', 'by' => 'Rev Andy Bennett, Anglican Diocese of Kagera'],
                ['id' => '1KKqQgNknfAIoY4XWWIUPySfQTiZUhq8z', 'label' => 'Bible Knowledge Full Notes', 'by' => 'WazaElimu'],
            ],
        ],
        'syllabus' => 'sw-1727192234-BIBLE KNOWLEDGE COMMISSIONER FINAL.pdf',
    ],
    'french' => [
        'name' => 'French',
        'pdf' => [
            'form1' => ['flip' => 'esfv', 'label' => 'French Book — Bonjour 1'],
            'form2' => ['flip' => 'fzjg', 'label' => 'French Book — Bonjour 2'],
            'form3' => ['flip' => 'smvc', 'label' => 'French Book — Bonjour 3'],
            'form4' => ['flip' => 'hymi', 'label' => 'French Book — Bonjour 4'],
        ],
        'syllabus' => 'sw-1727255679-FRENCH O-LEVEL FINAL.pdf',
    ],
];
