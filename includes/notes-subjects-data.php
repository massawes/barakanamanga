<?php
/**
 * Subjects shown on notes.php when a Form is picked (2023 new curriculum,
 * Ordinary Secondary – general stream). Each subject links to:
 *  - 'pdf': the TIE textbook PDF for each Form, shared on Google Drive by
 *    WazaElimu (wazaelimu.com/tie-books-download-pdf-form-1-6/); size is in
 *    bytes, label overrides the default "<Subject> Book" title,
 *  - its official new syllabus (muhtasari) PDF on the TIE website,
 *  - its NECTA past papers on theory.php (ftna = Form 1-2, csee = Form 3-4),
 *  - for Form 4 only, the old syllabus on TETEA (the class that began
 *    Form 1 before 2024 still follows it).
 * 'forms' limits a subject to some Forms; leave it out for all four.
 */

return [
    'kiswahili' => [
        'name' => 'Kiswahili',
        'pdf' => [
            'form1' => ['id' => '1jlGbu0xF-lYBWIKXS3QnH1K_JOOKHQbh', 'size' => 15513614],
            'form2' => ['id' => '1ANx2N-2eBElyvoSmcjCcuRS9gH9RPFoj', 'size' => 12922675],
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
            'form1' => ['id' => '1XrM3jFtUd7WnVN7FJVn4JOb_n4FbvSzh', 'size' => 10304327],
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
        'pdf' => [
            'form1' => ['id' => '1mQNfCK8QB5wFbEDWiivWB4YC-VzHVw_y'],
            'form2' => ['id' => '1e-kbumiZvB6MoJcl0B9Jfi0exVcXMNOK'],
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
            'form2' => ['id' => '1V5sOmQBRybtOMG5cnGTyofl-_-5NZo7u', 'size' => 20973781],
        ],
        'syllabus' => 'sw-1788264155-Muht. Historia ya Tanzania & Maadili 2.pdf',
        'ftna' => 'historia-maadili',
        'forms' => ['form1', 'form2', 'form3'],
    ],
    'civics' => [
        'name' => 'Civics',
        'pdf' => [
            'form4' => ['id' => '1QDe2xeyQwUUrEctOAumHWtbm0G6RFcTz', 'size' => 15615267],
        ],
        'csee' => 'civics',
        'old' => 'Syllabus - Civics - F1-F4 - 2017.pdf',
        'forms' => ['form4'],
    ],
    'history' => [
        'name' => 'History',
        'pdf' => [
            'form1' => ['id' => '1qZDcV-SlvNsnSV9ucBnc16zxsVKJ9IZv', 'size' => 12522597],
            'form2' => ['id' => '1_MWXafY3DLal2vWTHOn46Fll8qaw0kx1', 'size' => 11218492],
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
            'form1' => ['id' => '1hhNYfHrJGIAw1-v4p0tuCTAK1wB_5gbr', 'size' => 17573114],
            'form2' => ['id' => '1NToEnVKoaXid6JTquZyOKDv3hD-Fy3xV'],
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
            'form1' => ['id' => '1I-6u73TBQeR2-ukJqatwqJnJvnD8Aubg'],
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
            'form1' => ['id' => '1gYY69DDKBTfDSK7fNOP-HAOW2kqP-pcB'],
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
            'form1' => ['id' => '1C-u7CkVMY5Mc83a9PDPbE1skGFeVeTA9'],
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
        'pdf' => [
            'form1' => ['id' => '1Lu-ObPjWAy3aioBRSvPPjZW1oHqFQyEA', 'size' => 23098530, 'label' => 'ICT Computer Studies Book'],
            'form2' => ['id' => '1oMLgHLvS9hw2Xn35P3zQa27-H6WRJ0FY', 'size' => 23589442, 'label' => 'ICT Computer Studies Book'],
            'form3' => ['id' => '1LcVqVDSKoZQIwWMbnCvd8kN4yG04woqp', 'label' => 'ICT Computer Studies Book'],
            'form4' => ['id' => '1Zz82eCQYVXvY2Ms_4qhQwBYe46ZzYiZT', 'label' => 'ICT Computer Studies Book'],
        ],
        'syllabus' => 'sw-1727193276-COMPUTER SCIENCE SYLABUS I TO IV (12_07_2023).pdf',
        'ftna' => 'ics', 'csee' => 'computers',
        'old' => 'Syllabus - Information and Computer Studies - F1-F4 - 2005.pdf',
    ],
    'business-studies' => [
        'name' => 'Business Studies',
        'pdf' => [
            'form1' => ['id' => '1UjUkgOcFtDS1ngIAG_rZlEu7wrOTWmdz', 'size' => 17092509, 'label' => 'Business Studies Teacher\'s Guide'],
            'form2' => ['id' => '1oMkkRo4fdwi12Ge84VB8X8c9klvEIaaI', 'size' => 14046237],
            'form3' => ['id' => '14LcQl3aF6idj0gITF7W0ryntYzMmo6xL', 'label' => 'Commerce Book'],
            'form4' => ['id' => '1r0d2Qc15WijY_JKF8IjQ5bnxM7Pp-1yo', 'size' => 8532188, 'label' => 'Commerce Book'],
        ],
        'syllabus' => 'sw-1727192697-BUSINESS STUDIES SYLLABUS FOR ORDINARY LEVEL SECONDARY EDUCATION.pdf',
        'ftna' => 'business', 'csee' => 'commerce',
        'old' => 'Syllabus - Commerce - F1-F4 - 2016.pdf',
    ],
    'book-keeping' => [
        'name' => 'Book-keeping',
        'pdf' => [
            'form1' => ['id' => '1sQ9JDmr4mEnE4eq1PXRXr35mquXB_727'],
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
            'form1' => ['id' => '1yW5VlA7VlHL2iNcWFtgC76C1iTIqp0zy'],
            'form2' => ['id' => '1aCFMQ1IA10atoXrZSUu-8Fq6h0JtEKZl', 'size' => 31116499],
            'form3' => ['id' => '1JHYQ08GWunXVNc7awJ-0zpTsafohEAoQ', 'size' => 67690473],
            'form4' => ['id' => '1ib5ID4mPU2eMB85XGwEkviD7KMUcrgba'],
        ],
        'syllabus' => 'sw-1727192126-AGRICULTURE_ O-LEVEL SYLLABUS FINAL.pdf',
        'old' => 'Syllabus - Agriculture - F1-F4 - 2019.pdf',
    ],
    'food-nutrition' => [
        'name' => 'Food and Human Nutrition',
        'pdf' => [
            'form1' => ['id' => '1UN-8TekLGQnx4GY1CyV35lgLvjx496QU', 'size' => 17990349, 'label' => 'Home Economics Book'],
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
    'fasihi' => [
        'name' => 'Fasihi ya Kiswahili',
        'syllabus' => 'sw-1727194298-FASIHI YA KISWAHILI SEKONDARI KIDATO CHA III-IV.pdf',
        'forms' => ['form3', 'form4'],
    ],
    'kiislamu' => [
        'name' => 'Elimu ya Dini ya Kiislamu',
        'pdf' => [
            'form1' => ['id' => '1_z-zVa6QMJZX2LsCCKxwVqLycCoxffOi', 'size' => 59310903],
            'form2' => ['id' => '12abjtWeynhCFxjnGmkuu2GAXh3v5zMt6', 'size' => 27028118],
        ],
        'syllabus' => 'sw-1727257430-MUHTASARI WA ELIMU YA DINI YA KIISLAM KWA SHULE ZA SEKONDARI KIDATO CHA I - IV.pdf',
        'ftna' => 'kiislamu', 'csee' => 'kiislamu',
        'old' => 'Syllabus - Kiislamu - F1-F4 - 2012.pdf',
    ],
    'bible' => [
        'name' => 'Bible Knowledge',
        'syllabus' => 'sw-1727192234-BIBLE KNOWLEDGE COMMISSIONER FINAL.pdf',
    ],
    'french' => [
        'name' => 'French',
        'syllabus' => 'sw-1727255679-FRENCH O-LEVEL FINAL.pdf',
    ],
];
