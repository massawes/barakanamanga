<?php
/**
 * Subjects shown on notes.php when a Form is picked (2023 new curriculum,
 * Ordinary Secondary – general stream). Each subject links to:
 *  - its official new syllabus (muhtasari) PDF on the TIE website,
 *  - its NECTA past papers on theory.php (ftna = Form 1-2, csee = Form 3-4),
 *  - for Form 4 only, the old syllabus on TETEA (the class that began
 *    Form 1 before 2024 still follows it).
 * 'keywords' match teachers' uploaded notes (by title) to the subject.
 * 'forms' limits a subject to some Forms; leave it out for all four.
 */

return [
    'kiswahili' => [
        'name' => 'Kiswahili',
        'syllabus' => 'sw-1741937359-MUHTASARI WA KISWAHILI KIDATO  I-IV FINAL.pdf',
        'ftna' => 'kiswahili', 'csee' => 'kiswahili',
        'old' => 'Syllabus - Kiswahili - F1-F4 - 2016.pdf',
        'keywords' => ['kiswahili'],
    ],
    'english' => [
        'name' => 'English Language',
        'syllabus' => 'sw-1756459411-ENGLISH lANGUAGE FINAL.pdf',
        'ftna' => 'english', 'csee' => 'english',
        'old' => 'Syllabus - English - F1-F4 - 2016.pdf',
        'keywords' => ['english'],
    ],
    'mathematics' => [
        'name' => 'Mathematics',
        'syllabus' => 'sw-1727256902-MATHEMATICS SYLLABUS - O level final.pdf',
        'ftna' => 'math', 'csee' => 'math',
        'old' => 'Syllabus - Basic Math - F1-F4 - 2017.pdf',
        'keywords' => ['math'],
    ],
    'historia-maadili' => [
        'name' => 'Historia ya Tanzania na Maadili',
        'syllabus' => 'sw-1788264155-Muht. Historia ya Tanzania & Maadili 2.pdf',
        'ftna' => 'historia-maadili',
        'keywords' => ['historia', 'maadili'],
        'forms' => ['form1', 'form2', 'form3'],
    ],
    'civics' => [
        'name' => 'Civics',
        'csee' => 'civics',
        'old' => 'Syllabus - Civics - F1-F4 - 2017.pdf',
        'keywords' => ['civics'],
        'forms' => ['form4'],
    ],
    'history' => [
        'name' => 'History',
        'syllabus' => 'sw-1727255952-HISTORY FOR FORM I-IV.pdf',
        'ftna' => 'history', 'csee' => 'history',
        'old' => 'Syllabus - History - F1-F4 - 2012.pdf',
        'keywords' => ['history'],
    ],
    'geography' => [
        'name' => 'Geography',
        'syllabus' => 'sw-1727255849-GEOGRAPHY O-Level.pdf',
        'ftna' => 'geography', 'csee' => 'geography',
        'old' => 'Syllabus - Geography - F1-F4 - 2016.pdf',
        'keywords' => ['geography'],
    ],
    'biology' => [
        'name' => 'Biology',
        'syllabus' => 'sw-1727192422-BIOLOGY SYLLABUS ORDINARY SECONDARY FINAL .pdf',
        'ftna' => 'biology', 'csee' => 'biology',
        'old' => 'Syllabus - Biology - F1-F4 - 2012.pdf',
        'keywords' => ['biology'],
    ],
    'chemistry' => [
        'name' => 'Chemistry',
        'syllabus' => "sw-1727192894-CHEMISTRY O'LEVEL EDUCATION FINAL.pdf",
        'ftna' => 'chemistry', 'csee' => 'chemistry',
        'keywords' => ['chemistry'],
    ],
    'physics' => [
        'name' => 'Physics',
        'syllabus' => 'sw-1727257838-PHYSICS FOR ORDINARY SECONDARY EDUCATION FINAL.pdf',
        'ftna' => 'physics', 'csee' => 'physics',
        'old' => 'Syllabus - Physics - F1-F4 - 2017.pdf',
        'keywords' => ['physics'],
    ],
    'computer-science' => [
        'name' => 'Computer Science',
        'syllabus' => 'sw-1727193276-COMPUTER SCIENCE SYLABUS I TO IV (12_07_2023).pdf',
        'ftna' => 'ics', 'csee' => 'computers',
        'old' => 'Syllabus - Information and Computer Studies - F1-F4 - 2005.pdf',
        'keywords' => ['computer', 'cs ', 'ict', 'ics', 'programming'],
    ],
    'business-studies' => [
        'name' => 'Business Studies',
        'syllabus' => 'sw-1727192697-BUSINESS STUDIES SYLLABUS FOR ORDINARY LEVEL SECONDARY EDUCATION.pdf',
        'ftna' => 'business', 'csee' => 'commerce',
        'old' => 'Syllabus - Commerce - F1-F4 - 2016.pdf',
        'keywords' => ['business', 'commerce'],
    ],
    'book-keeping' => [
        'name' => 'Book-keeping',
        'syllabus' => 'sw-1727192590-Book-keeping Form I-IV FINAL SYLLABUS.pdf',
        'ftna' => 'bookkeeping', 'csee' => 'bookkeeping',
        'old' => 'Syllabus - Book Keeping - F1-F4 - 2016.pdf',
        'keywords' => ['book-keeping', 'bookkeeping', 'book keeping'],
    ],
    'agriculture' => [
        'name' => 'Agriculture',
        'syllabus' => 'sw-1727192126-AGRICULTURE_ O-LEVEL SYLLABUS FINAL.pdf',
        'old' => 'Syllabus - Agriculture - F1-F4 - 2019.pdf',
        'keywords' => ['agriculture'],
    ],
    'food-nutrition' => [
        'name' => 'Food and Human Nutrition',
        'syllabus' => 'sw-1727255526-FOOD AND HUMAN NUTRITION STD I - IV.pdf',
        'ftna' => 'foodnutrition', 'csee' => 'foodnutrition',
        'keywords' => ['food', 'nutrition'],
    ],
    'literature' => [
        'name' => 'Literature in English',
        'syllabus' => 'sw-1727256752-LITERATURE IN ENGLISH O-LEVEL FINAL.pdf',
        'csee' => 'literature',
        'keywords' => ['literature'],
        'forms' => ['form3', 'form4'],
    ],
    'fasihi' => [
        'name' => 'Fasihi ya Kiswahili',
        'syllabus' => 'sw-1727194298-FASIHI YA KISWAHILI SEKONDARI KIDATO CHA III-IV.pdf',
        'keywords' => ['fasihi'],
        'forms' => ['form3', 'form4'],
    ],
    'kiislamu' => [
        'name' => 'Elimu ya Dini ya Kiislamu',
        'syllabus' => 'sw-1727257430-MUHTASARI WA ELIMU YA DINI YA KIISLAM KWA SHULE ZA SEKONDARI KIDATO CHA I - IV.pdf',
        'ftna' => 'kiislamu', 'csee' => 'kiislamu',
        'old' => 'Syllabus - Kiislamu - F1-F4 - 2012.pdf',
        'keywords' => ['kiislamu', 'islamic'],
    ],
    'bible' => [
        'name' => 'Bible Knowledge',
        'syllabus' => 'sw-1727192234-BIBLE KNOWLEDGE COMMISSIONER FINAL.pdf',
        'keywords' => ['bible'],
    ],
    'french' => [
        'name' => 'French',
        'syllabus' => 'sw-1727255679-FRENCH O-LEVEL FINAL.pdf',
        'keywords' => ['french'],
    ],
];
