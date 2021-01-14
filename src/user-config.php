<?php

return [
    // Developer mode, enables error details. Set to false when ready to deploy.
    'devMode' => false,
    'demoMode' => false,
    
    // Admin password for deleting posts.
    // You should generate this on your own by using...
    //     > password_hash("your_password_here", PASSWORD_DEFAULT);
    // ...and store the hash in this key. Do not store a plaintext password,
    // not only because it's incredibly insecure, but also because it'll end up
    // failing every time anyway.
    'adminPasswordHash' => '$2y$10$0eKxzmKn/y3WVSzkxxyqPurkhom3Xn8j4Iuw5H.qk7pJbX5rNVzLe', // password
    
    // Anti-bot verification
    'antiBotVerificationEnabled' => true,
    'antiBotVerificationIsCaseSensitive' => false,
    
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
    'showSoftwareStamp' => true, // "Running milkGB ver x.xx"
    'timezone' => 'America/New_York',
    '24HourClock' => true,
    
    // Board functionality
    'entryDeletingEnabled' => true,
    'formattingColors' => [
        'red' => '#ff0000',
        'green' => '#00ff00',
        'blue' => '#0000ff'
    ],
    
    // Word filtering
    'wordFilterMode' => 'censor', // censor, error, or mislead
    'wordFilters' => [
    ],
    'showFilteredWords' => true // Tells the user what words were filtered when using mode "error"
];

?>
