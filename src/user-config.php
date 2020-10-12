<?php

return [
    'addHrAfterThreads' => true,
    'allowEmptyComments' => true,
    'antiBotEnabled' => true,
    'antiBotCaseSensitive' => false,
    'fileUploadsEnabled' => false,
    'filetypeMode' => 'blacklist', // blacklist or whitelist
    'filetypes' => [
        'jpg',
        'zip'
    ],
    'hideSoftwareStamp' => true, // "Running milkBBS ver 1.32"
    'limits' => [
        'name' => 64,
        'email' => 256,
        'url' => 256,
        'subject' => 72,
        'comment' => 2048,
        'password' => 32,
        'fileNameLength' => 64,
        'fileSize' => 1048576
    ],
    'threadsPerPageLimit' => 3,
    'threadPreviewPostLimit' => 3,
    'threadReplyLimit' => 256,
    'timezone' => 'America/New_York',
    'wordFilterMode' => 'censor', // censor, error, or misleading
    'wordFilters' => [
        'sample_bad_word'
    ]
];

?>
