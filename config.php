<?php
// Central configuration. Edit these values before deployment.
return [
    'university_name' => 'IGNITE SCHOLARS UNIVERSITY',
    'short_name' => 'ISU',
    'admin_email' => 'admissions@isuife.edu.ng',
    'contact_phone' => '+234 803 351 7445',
    'contact_email' => 'admissions@isuife.edu.ng',
    'academic_year' => '2026',
    'max_post_size_note' => 'Set upload_max_filesize/post_max_size in hosting if needed.',
    'upload_dir' => __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR,
    'data_dir' => __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR,
    'passport_max_bytes' => 102400,
    'other_file_max_bytes' => 5242880,
    'allowed_uploads' => [
        'passport' => ['extensions' => ['jpg','jpeg','png'], 'mime' => ['image/jpeg','image/png'], 'max_bytes' => 102400],
        'olevel_result' => ['extensions' => ['pdf','jpg','jpeg','png'], 'mime' => ['application/pdf','image/jpeg','image/png'], 'max_bytes' => 5242880],
        'birth_certificate' => ['extensions' => ['pdf','jpg','jpeg','png'], 'mime' => ['application/pdf','image/jpeg','image/png'], 'max_bytes' => 5242880],
        'jamb_result' => ['extensions' => ['pdf','jpg','jpeg','png'], 'mime' => ['application/pdf','image/jpeg','image/png'], 'max_bytes' => 5242880],
    ],
    // Current ISU faculty names based on ISU public listings. Edit as needed for your admission cycle.
    'faculties' => [
        'Administration', 'Agriculture', 'Arts', 'Basic Medical Sciences', 'Clinical Sciences',
        'Computing Science and Engineering', 'Dentistry', 'Education', 'Environmental Design and Management',
        'Law', 'Pharmacy', 'Science', 'Social Sciences', 'Technology'
    ],
];
