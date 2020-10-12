<?php

return [
    'addHrAfterThreads' => true,
    'allowEmptyComments' => true,
    'antiBotEnabled' => false,
    'antiBotCaseSensitive' => false,
    'fileUploadsEnabled' => false,
    'filetypeMode' => 'blacklist', // blacklist or whitelist
    'filetypes' => [
        'jpg',
        'zip'
    ],
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
    'threadPreviewPostLimit' => 3,
    'threadReplyLimit' => 256,
    'timezone' => 'America/New_York',
    'wordFilterMode' => 'censor', // censor, error, or misleading
    'wordFilters' => [
        'sample_bad_word'
    ]
];

?>
