<?php

error_reporting(E_ALL);

// Load common functions
require_once('common-functions.php');

// Load and validate user configuration.
$cfg = require_once(dirname(__FILE__) . '/../user-config.php');
$cfg = validateUserData($cfg);

// Load system configuration
$cfg = array_merge($cfg, require_once('data.php'));

// Set default time zone.
date_default_timezone_set($cfg['timezone']);

// Only process data if a POST occurred.
if ($_POST) {
    $postData = validatePostData($cfg);
    savePost($cfg, $postData);
    
    // Redirect to main page.
    header( 'Location: ' . $_POST['callingScript'], true, 303 );
    exit();
}
else
{
    displayError($cfg, 'No POST data provided.');
}

/*
    Validates the post data.
*/
function validatePostData($cfg)
{
    $maxFieldLengths = [
        'name' => (is_numeric($cfg['maxNameFieldLength']) ? $cfg['maxNameFieldLength'] : 64),
        'email' => (is_numeric($cfg['maxEmailFieldLength']) ? $cfg['maxEmailFieldLength'] : 256),
        'url' => (is_numeric($cfg['maxUrlFieldLength']) ? $cfg['maxUrlFieldLength'] : 256),
        'subject' => (is_numeric($cfg['maxSubjectFieldLength']) ? $cfg['maxSubjectFieldLength'] : 72),
        'comment' => (is_numeric($cfg['maxCommentFieldLength']) ? $cfg['maxCommentFieldLength'] : 2048),
        'password' => (is_numeric($cfg['maxPasswordFieldLength']) ? $cfg['maxPasswordFieldLength'] : 32),
        // 'maxFilenameLength' => (is_numeric($cfg['maxFileNameLength']) ? $cfg['maxFileNameLength'] : 64),
        // 'maxFileSize' => (is_numeric($cfg['maxFileSizeInBytes']) ? $cfg['maxFileSizeInBytes'] : 1048576)
    ];
    
    $postData = [];
    $postData['date'] = date('Y-m-d (D) H:i:s');
    $postData['name'] = (isset($_POST['name']) && strlen($_POST['name']) > 0) ? $_POST['name'] : 'Anonymous';
    $postData['email'] = isset($_POST['email']) ? $_POST['email'] : '';
    $postData['url'] = isset($_POST['url']) ? $_POST['url'] : '';
    $postData['subject'] = isset($_POST['subject']) ? $_POST['subject'] : '';
    $postData['comment'] = isset($_POST['comment']) ? $_POST['comment'] : '';
    $postData['password'] = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validate author name.
    if (strlen($postData['name']) > $maxFieldLengths['name'])
    {
        displayError($cfg, 'Author name cannot exceed (' . $maxFieldLengths['name'] . ') characters.');
    }
    
    // Validate e-mail.
    if (strlen($postData['email']) > $maxFieldLengths['email'])
    {
        displayError($cfg, 'Email address cannot exceed (' . $maxFieldLengths['email'] . ') characters.');
    }
    
    
    // Validate homepage URL.
    if (strlen($postData['url']) > $maxFieldLengths['url'])
    {
        displayError($cfg, 'Homepage URL cannot exceed (' . $maxFieldLengths['url'] . ') characters.');
    }
    
    if (strlen($postData['url']) > 0 && (stripos($postData['url'], 'http://') !== 0 && stripos($postData['url'], 'https://') !== 0))
    {
        displayError($cfg, 'Homepage URL must specify the protocol (http:// or https://). Please ensure your URL begins with one of these.');
    }
    
    // Validate subject.
    if (strlen($postData['subject']) > $maxFieldLengths['subject'])
    {
        displayError($cfg, 'Post subject cannot exceed (' . $maxFieldLengths['subject'] . ') characters.');
    }
    
    // Validate comment.
    if (strlen($postData['comment']) > $maxFieldLengths['comment'])
    {
        displayError($cfg, 'Post comment cannot exceed (' . $maxFieldLengths['comment'] . ') characters.');
    }
    // TODO: Make it so that comment can only be empty if a file was attached.
    
    // Validate password.
    if (strlen($postData['password']) > $maxFieldLengths['password'])
    {
        displayError($cfg, 'Post password cannot exceed (' . $maxFieldLengths['password'] . ') characters.');
    }
    
    // Verify user isn't a bot (if enabled).
    if ($cfg['antiBotVerificationEnabled'])
    {
        if (
            !isset($_POST['verification-question-id'])
         || !is_numeric($_POST['verification-question-id'])
         || !isset($_POST['verification-answer'])
        )
        {
            displayError($cfg, 'Could not perform anti-bot verification.', true);
        }
        
        if (strlen($_POST['verification-answer']) === 0)
        {
            displayError($cfg, 'Please provide an answer to the anti-bot verification question.');
        }
        
        // Check answer
        $questions = require_once(dirname(__FILE__) . '/../user-verification-questions.php');
        if (!is_array($questions) || count($questions) === 0)
        {
            displayError($cfg, 'Could not load server-side verification data.', true);
        }
        
        $userAnswer = trim($_POST['verification-answer']);
        $realAnswer = $questions[$_POST['verification-question-id']][1];
        
        if ($cfg['antiBotVerificationIsCaseSensitive'] && $userAnswer !== $realAnswer)
        {
            displayError($cfg, 'Invalid answer to the anti-bot verification question. Please try again. If you believe there to be an error please contact the server administrator.');
        }
        else if (strcasecmp($userAnswer, $realAnswer) !== 0)
        {
            displayError($cfg, 'Invalid answer to the anti-bot verification question. Please try again. If you believe there to be an error please contact the server administrator.');
        }
    }
    
    // Trim whitespace from post data.
    foreach ($postData as $key => $val)
    {
        if (is_string($val))
        {
            $postData[$key] = trim($val);
        }
    }
    
    return $postData;
}

/*
    Saves a new post to the database while also incrementing the total post
    counter.
*/
function savePost($cfg, $post)
{
    // Make directories if they don't exist
    if (!is_dir($cfg['path']['db']) && !file_exists($cfg['path']['db']))
    {
        mkdir($cfg['path']['db']);
    }
    
    if (!is_dir($cfg['path']['threads']) && !file_exists($cfg['path']['threads']))
    {
        mkdir($cfg['path']['threads']);
    }
    
    // Retrieve table of contents and post counter. Create files if needed.
    if (file_exists($cfg['file']['toc']))
    {
        $toc = json_decode(file_get_contents($cfg['file']['toc']));
    }
    else
    {
        $toc = array();
        if (!file_put_contents($cfg['file']['toc'], json_encode($toc)))
        {
            displayError($cfg, 'No table of contents was found. An attempt to initialize it was made, but the attempt failed.', true);
        }
    }
    
    if (file_exists($cfg['file']['postCount']))
    {
        $postId = file_get_contents($cfg['file']['postCount']);
    }
    else
    {
        $postId = 0;
        if (!file_put_contents($cfg['file']['postCount'], $postId))
        {
            displayError($cfg, 'No post counter was found. An attempt to initialize it was made, but the attempt failed.', true);
        }
    }
    
    // Verify data potentially obtained from DB.
    if (!is_array($toc))
    {
        displayError($cfg, 'Could not retrieve table of contents or table of contents is corrupted.', true);
    }
    
    if (!is_numeric($postId))
    {
        displayError($cfg, 'Could not retrieve post counter or data is bad.', true);
    }
    
    // Increment post counter & save new value to disk.
    $postId = intval($postId) + 1;
    if (!file_put_contents($cfg['file']['postCount'], $postId))
    {
        displayError($cfg, 'Could not update post counter.', true);
    }
    
    // Begin storing data for new post.
    $post['id'] = $postId;
    
    // Get parent thread ID (if applicable).
    $parentThreadId = $_POST['parentThreadId'];
    
    // No parent thread ID found. Post is for a new thread. Create new thread.
    if (!$parentThreadId)
    {
        if (!file_exists($cfg['path']['threads'] . "$postId.json"))
        {
            $json = json_encode(array($post), JSON_PRETTY_PRINT);
            if (!file_put_contents($cfg['path']['threads'] . "$postId.json", $json))
            {
                displayError($cfg, "Could not create new thread. Something prevented it from being created in the database.", true);
            }
        }
        else
        {
            displayError($cfg, "Could not create new thread. No parent thread ID was found, but the thread for this post number ($postId) exists in the database already.", true);
        }
    }
    // Parent thread ID found. Post is a reply to an existing thread. Update existing thread with new reply.
    else
    {
        if (file_exists($cfg['path']['threads'] . $parentThreadId . '.json'))
        {
            $threadData = json_decode(file_get_contents($cfg['path']['threads'] . $parentThreadId . '.json'));
            if (!$threadData)
            {
                displayError($cfg, "Could not retrieve data for thread number ($parentThreadId) or data is corrupted.", true);
            }
            array_push($threadData, $post);
            $threadData = json_encode($threadData, JSON_PRETTY_PRINT);
            if (!file_put_contents($cfg['path']['threads'] . $parentThreadId . '.json', $threadData))
            {
                displayError($cfg, "Could not create new post number ($postId) for thread number ($parentThreadId). Something prevent it from being created in the database.", true);
            }
        }    
        else
        {
            displayError($cfg, "Could not reply to thread. Thread number ($parentThreadId) was not located in database.", true);
        }
    }
    
    // Update the table of contents thread index.
    $threadPos = array_search($parentThreadId, $toc);
    // If thread exists in ToC, bump it to the top.
    if ($threadPos !== false)
    {
        unset($toc[$threadPos]);
        array_unshift($toc, intval($parentThreadId));
        array_values($toc);
    }
    // If thread doesn't exist in ToC, add it to the top
    else
    {
        array_unshift($toc, $postId);
    }
    
    // Save updated table of contents.
    $toc = json_encode($toc, JSON_PRETTY_PRINT);
    if (!file_put_contents($cfg['file']['toc'], $toc))
    {
        displayError($cfg, "Could not update the table of contents.", true);
    }
}

function displayError($cfg, $msg = '', $offerSupport = false)
{
    $html = '<!DOCTYPE html>'
          . '<html lang="en">'
          . '<head>'
          . '<title>milkBBS</title>'
          . '<meta charset="utf-8">'
          . '<link rel="stylesheet" href="' . $cfg['path']['webLib'] . 'style/milkbbs.css">'
          . '</head>'
          . '<body>'
    ;
    
    $html .= '<div class="milkbbs-error-container milkbbs-standalone-error-container">'
           . '<div class="milkbbs-error-logo">milkBBS Logo</div>'
           . '<div class="milkbbs-error-title">milkBBS</div>'
           . '<div class="milkbbs-error-message">Error: ' . $msg . ($offerSupport ? ' Please contact the server administrator if this issue persists.' : '') . '</div>'
           . '<div class="milkbbs-error-return-link"><a href="javascript:history.back()">[Return]</a></div>'
           . '</div>'
    ;
    
    $html .= '</body>'
           . '</html>'
    ;
    
    echo $html;
    exit();
}

?>
