<?php

return [
    // Developer mode, enables error details. Set to false when ready to deploy.
    'devMode' => true,
    
    // Anti-bot verification
    'antiBotVerificationEnabled' => false,
    'antiBotVerificationIsCaseSensitive' => false,
    
    // File uploader
    'fileUploadingEnabled' => false,
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
    'maxEntriesPerPage' => 10,
    'maxNavigationPageLinks' => 0, // The visible pages in the navigation bar
    'hideSoftwareStamp' => false, // "Running milkBBS ver 1.32"
    'timezone' => 'America/New_York',
    
    // Board functionality
    'entryDeletingEnabled' => true,
    'maxRepliesPerThread' => 256,
    
    // Word filtering
    'wordFilterMode' => 'censor', // censor, error, or mislead
    'wordFilters' => [
        'sample_bad_word'
    ]
];

?>
