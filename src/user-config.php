<?php

return [
    // Developer mode, enables error details. Set to false when ready to deploy.
    'devMode' => true,
    
    // Admin password for deleting posts.
    // You should generate this on your own by using...
    //     > password_hash("your_password_here", PASSWORD_DEFAULT);
    // ...and store the hash in this key. Do not store a plaintext password,
    // not only because it's incredibly insecure, but also because it'll end up
    // failing every time anyway.
    'adminPasswordHash' => '$2y$10$0eKxzmKn/y3WVSzkxxyqPurkhom3Xn8j4Iuw5H.qk7pJbX5rNVzLe', // password
    
    // Anti-bot verification
    'antiBotVerificationEnabled' => false,
    'antiBotVerificationIsCaseSensitive' => false,
    
    // File uploader
    'fileUploadingEnabled' => true, //  Ensure that "file_uploads = On" is set in php.ini
    'fileTypeListMode' => 'allow', // allow or deny
    'fileTypes' => [
        'jpg',
        'zip'
    ],
    'maxFileNameLength' => 64,
    'maxFileSizeInBytes' => 1048576,
    
    // Max field character lengths
    'maxAuthorFieldLength' => 64,
    'maxEmailFieldLength' => 256,
    'maxUrlFieldLength' => 256,
    'maxSubjectFieldLength' => 72,
    'maxCommentFieldLength' => 2048,
    'maxPasswordFieldLength' => 32,
    
    // Board visual display
    'maxEntriesPerPage' => 3,
    'maxNavigationPageLinks' => 5, // The visible pages in the navigation bar
    'showSoftwareStamp' => true, // "Running milkGB ver 1.32"
    'timezone' => 'America/New_York',
    '24HourClock' => true,
    
    // Board functionality
    'entryDeletingEnabled' => true,
    
    // Word filtering
    'wordFilterMode' => 'error', // censor, error, or mislead
    'wordFilters' => [
        'apple',
        'orange'
    ],
    'showFilteredWords' => true // Tells the user what words were filtered when using mode "error"
];

?>
