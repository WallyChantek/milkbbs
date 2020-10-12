<?php

return [
    'addHrAfterThreads' => true,
    'allowEmptyComments' => true,
    'allowFileUploads' => false,
    'antiBotEnabled' => false,
    'antiBotCaseSensitive' => false,
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
    'threadPreviewPostLimit' => 3
];

?>
