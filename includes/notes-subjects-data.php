<?php
/**
 * Subjects shown on notes.php when a Form is picked (2023 new curriculum,
 * Ordinary Secondary – general stream). Each subject links to:
 *  - 'books': its latest TIE Student's Book per Form on the TIE Online
 *    Library (book ids from ol.tie.go.tz/api/books/published),
 *  - its official new syllabus (muhtasari) PDF on the TIE website,
 *  - its NECTA past papers on theory.php (ftna = Form 1-2, csee = Form 3-4),
 *  - for Form 4 only, the old syllabus on TETEA (the class that began
 *    Form 1 before 2024 still follows it).
 * 'forms' limits a subject to some Forms; leave it out for all four.
 */

return [
    'kiswahili' => [
        'name' => 'Kiswahili',
        'books' => [
            'form1' => 'e63e8cc6-22bf-4c06-937f-f48fa14800b2',
            'form2' => '9050c317-48af-4da2-a4e7-6cf4dee7b807',
        ],
        'syllabus' => 'sw-1741937359-MUHTASARI WA KISWAHILI KIDATO  I-IV FINAL.pdf',
        'ftna' => 'kiswahili', 'csee' => 'kiswahili',
        'old' => 'Syllabus - Kiswahili - F1-F4 - 2016.pdf',
    ],
    'english' => [
        'name' => 'English Language',
        'books' => [
            'form1' => 'ea30a50f-515b-471c-b4fc-08c315896840',
            'form2' => '5707df5b-4eec-4c53-ac48-c8665612c080',
            'form3' => 'b25513e8-b33c-4458-8389-c9598f2cee42',
        ],
        'syllabus' => 'sw-1756459411-ENGLISH lANGUAGE FINAL.pdf',
        'ftna' => 'english', 'csee' => 'english',
        'old' => 'Syllabus - English - F1-F4 - 2016.pdf',
    ],
    'mathematics' => [
        'name' => 'Mathematics',
        'books' => [
            'form1' => '1e76377d-1003-43dc-adc1-1fb465071c02',
            'form2' => '67c5da7f-d5a7-47fc-a179-3eb7d73c011b',
            'form3' => '7f968e5d-6e70-4a6b-89b5-426820be4a22',
        ],
        'syllabus' => 'sw-1727256902-MATHEMATICS SYLLABUS - O level final.pdf',
        'ftna' => 'math', 'csee' => 'math',
        'old' => 'Syllabus - Basic Math - F1-F4 - 2017.pdf',
    ],
    'historia-maadili' => [
        'name' => 'Historia ya Tanzania na Maadili',
        'books' => [
            'form1' => 'cc9e26f8-98a4-41b8-b4c0-b78b7bcefa18',
            'form2' => '2a871b1d-974a-4e96-a00e-55023f9b6350',
        ],
        'syllabus' => 'sw-1788264155-Muht. Historia ya Tanzania & Maadili 2.pdf',
        'ftna' => 'historia-maadili',
        'forms' => ['form1', 'form2', 'form3'],
    ],
    'civics' => [
        'name' => 'Civics',
        'csee' => 'civics',
        'old' => 'Syllabus - Civics - F1-F4 - 2017.pdf',
        'forms' => ['form4'],
    ],
    'history' => [
        'name' => 'History',
        'books' => [
            'form1' => '3dd04f77-93e1-473c-a153-550ba0ebce37',
            'form2' => 'ca11dfdd-e559-44e9-9183-dd14c9cc1524',
        ],
        'syllabus' => 'sw-1727255952-HISTORY FOR FORM I-IV.pdf',
        'ftna' => 'history', 'csee' => 'history',
        'old' => 'Syllabus - History - F1-F4 - 2012.pdf',
    ],
    'geography' => [
        'name' => 'Geography',
        'books' => [
            'form1' => '6412241b-c299-41b6-acba-2cb0b28c6a49',
            'form2' => '2c6ad559-36e0-40a2-80f1-d8d75988a6e8',
        ],
        'syllabus' => 'sw-1727255849-GEOGRAPHY O-Level.pdf',
        'ftna' => 'geography', 'csee' => 'geography',
        'old' => 'Syllabus - Geography - F1-F4 - 2016.pdf',
    ],
    'biology' => [
        'name' => 'Biology',
        'books' => [
            'form1' => '0b95cad1-3f9a-4dc5-a4da-87fffc607723',
            'form2' => '398a3b44-4f58-49a8-b86d-0395ce679a5b',
            'form3' => '9694f3fa-028d-4f46-807e-e24f6e7f2c4d',
        ],
        'syllabus' => 'sw-1727192422-BIOLOGY SYLLABUS ORDINARY SECONDARY FINAL .pdf',
        'ftna' => 'biology', 'csee' => 'biology',
        'old' => 'Syllabus - Biology - F1-F4 - 2012.pdf',
    ],
    'chemistry' => [
        'name' => 'Chemistry',
        'books' => [
            'form1' => '4bdcf6e4-96dd-4795-9812-9b2f258cf370',
            'form2' => '7192877b-d227-4f2d-b5cb-1ee65be5ed86',
        ],
        'syllabus' => "sw-1727192894-CHEMISTRY O'LEVEL EDUCATION FINAL.pdf",
        'ftna' => 'chemistry', 'csee' => 'chemistry',
    ],
    'physics' => [
        'name' => 'Physics',
        'books' => [
            'form2' => '285e3237-8e2a-4458-b382-6ab338892a0d',
        ],
        'syllabus' => 'sw-1727257838-PHYSICS FOR ORDINARY SECONDARY EDUCATION FINAL.pdf',
        'ftna' => 'physics', 'csee' => 'physics',
        'old' => 'Syllabus - Physics - F1-F4 - 2017.pdf',
    ],
    'computer-science' => [
        'name' => 'Computer Science',
        'books' => [
            'form1' => 'a7a9dfeb-36a0-4834-b8e4-41d4e1862d57',
            'form2' => '94a47b01-d42f-4c65-9468-d158fe1c8ca6',
        ],
        'syllabus' => 'sw-1727193276-COMPUTER SCIENCE SYLABUS I TO IV (12_07_2023).pdf',
        'ftna' => 'ics', 'csee' => 'computers',
        'old' => 'Syllabus - Information and Computer Studies - F1-F4 - 2005.pdf',
    ],
    'business-studies' => [
        'name' => 'Business Studies',
        'books' => [
            'form1' => '7359a5b6-c72d-4ade-99c4-75e1239fff44',
            'form2' => '1854e7e1-4799-49cf-9538-3e4bc8f48f97',
            'form3' => '9e25d35a-6073-4139-97f4-a63e0b462db4',
        ],
        'syllabus' => 'sw-1727192697-BUSINESS STUDIES SYLLABUS FOR ORDINARY LEVEL SECONDARY EDUCATION.pdf',
        'ftna' => 'business', 'csee' => 'commerce',
        'old' => 'Syllabus - Commerce - F1-F4 - 2016.pdf',
    ],
    'book-keeping' => [
        'name' => 'Book-keeping',
        'books' => [
            'form1' => 'def6d3d3-9f61-4c45-b6d7-abe4b4119b07',
            'form2' => 'fb10247e-61aa-4a95-bb88-dafe02b5d2fc',
        ],
        'syllabus' => 'sw-1727192590-Book-keeping Form I-IV FINAL SYLLABUS.pdf',
        'ftna' => 'bookkeeping', 'csee' => 'bookkeeping',
        'old' => 'Syllabus - Book Keeping - F1-F4 - 2016.pdf',
    ],
    'agriculture' => [
        'name' => 'Agriculture',
        'books' => [
            'form1' => '6cadab2b-1ebd-4ded-bf8f-e77f123b590f',
            'form2' => '57416487-0d93-4eb1-a165-6795bdaac858',
            'form3' => '72640db1-c86b-4bc3-ae59-ee03c0b14a7e',
        ],
        'syllabus' => 'sw-1727192126-AGRICULTURE_ O-LEVEL SYLLABUS FINAL.pdf',
        'old' => 'Syllabus - Agriculture - F1-F4 - 2019.pdf',
    ],
    'food-nutrition' => [
        'name' => 'Food and Human Nutrition',
        'books' => [
            'form1' => '5f25b10c-80b9-458b-a4cd-aaba0dcc31ce',
            'form2' => 'e1241f05-d589-46e4-bdc3-b68f6cf65909',
        ],
        'syllabus' => 'sw-1727255526-FOOD AND HUMAN NUTRITION STD I - IV.pdf',
        'ftna' => 'foodnutrition', 'csee' => 'foodnutrition',
    ],
    'literature' => [
        'name' => 'Literature in English',
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
        'syllabus' => 'sw-1727257430-MUHTASARI WA ELIMU YA DINI YA KIISLAM KWA SHULE ZA SEKONDARI KIDATO CHA I - IV.pdf',
        'ftna' => 'kiislamu', 'csee' => 'kiislamu',
        'old' => 'Syllabus - Kiislamu - F1-F4 - 2012.pdf',
    ],
    'bible' => [
        'name' => 'Bible Knowledge',
        'books' => [
            'form1' => '1d90dba2-591b-47d6-b447-61a5617bf694',
            'form2' => '97fb09ae-d96a-4e97-a1ec-9cbb8ff7c818',
        ],
        'syllabus' => 'sw-1727192234-BIBLE KNOWLEDGE COMMISSIONER FINAL.pdf',
    ],
    'french' => [
        'name' => 'French',
        'books' => [
            'form1' => 'bcc626de-314c-47cf-8e7d-e8aae9667f18',
            'form2' => '6fffe84c-93e9-4991-8efe-813fe0c4e352',
            'form3' => 'd44b802f-296d-4238-a873-334c84b028a9',
        ],
        'syllabus' => 'sw-1727255679-FRENCH O-LEVEL FINAL.pdf',
    ],
];
