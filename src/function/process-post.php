<?php

$cfg = require_once(dirname(__FILE__) . '/../config.php');
$cfg = array_merge($cfg, require_once('data.php'));

date_default_timezone_set('America/New_York');

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
    $limits = [
        'name' => (is_numeric($cfg['limits']['name']) ? $cfg['limits']['name'] : 64),
        'email' => (is_numeric($cfg['limits']['email']) ? $cfg['limits']['email'] : 256),
        'url' => (is_numeric($cfg['limits']['url']) ? $cfg['limits']['url'] : 256),
        'subject' => (is_numeric($cfg['limits']['subject']) ? $cfg['limits']['subject'] : 72),
        'comment' => (is_numeric($cfg['limits']['comment']) ? $cfg['limits']['comment'] : 2048),
        'password' => (is_numeric($cfg['limits']['password']) ? $cfg['limits']['password'] : 32),
        'fileNameLength' => (is_numeric($cfg['limits']['fileNameLength']) ? $cfg['limits']['fileNameLength'] : 64),
        'fileSize' => (is_numeric($cfg['limits']['fileSize']) ? $cfg['limits']['fileSize'] : 1048576)
    ];
    
    $postData = [];
    $postData['date'] = date('Y-m-d (D) H:i:s');
    
    // Validate author name.
    if (isset($_POST['name']) && strlen($_POST['name']) > 0)
    {
        $postData['name'] = $_POST['name'];
        
        if (strlen($postData['name']) > $limits['name'])
        {
            displayError($cfg, 'Author name cannot exceed (' . $limits['name'] . ') characters.');
        }
    }
    else
    {
        $postData['name'] = 'Anonymous';
    }
    
    // Validate e-mail address.
    if (isset($_POST['email']) && strlen($_POST['email']) > 0)
    {
        $postData['email'] = $_POST['email'];
        
        if (strlen($postData['email']) > $limits['email'])
        {
            displayError($cfg, 'Email address cannot exceed (' . $limits['email'] . ') characters.');
        }
    }
    
    // Validate homepage URL.
    if (isset($_POST['url']) && strlen($_POST['url']) > 0)
    {
        $postData['url'] = $_POST['url'];
        
        if (strlen($postData['url']) > $limits['url'])
        {
            displayError($cfg, 'Homepage URL cannot exceed (' . $limits['url'] . ') characters.');
        }
    }
    
    // Validate subject.
    if (isset($_POST['subject']) && strlen($_POST['subject']) > 0)
    {
        $postData['subject'] = $_POST['subject'];
        
        if (strlen($postData['subject']) > $limits['subject'])
        {
            displayError($cfg, 'Post subject cannot exceed (' . $limits['subject'] . ') characters.');
        }
    }
    
    // Validate comment.
    $allowEmptyComments = (isset($cfg['allowEmptyComments']) ? $cfg['allowEmptyComments'] : false);
    if (isset($_POST['comment']) && strlen($_POST['comment']) > 0)
    {
        $postData['comment'] = $_POST['comment'];
        
        if (strlen($postData['comment']) > $limits['comment'])
        {
            displayError($cfg, 'Post comment cannot exceed (' . $limits['comment'] . ') characters.');
        }
    }
    else if (!$allowEmptyComments)
    {
        displayError($cfg, 'Comment field must not be blank.');
    }
    
    // Validate password.
    if (isset($_POST['password']) && strlen($_POST['password']) > 0)
    {
        $postData['password'] = $_POST['password'];
        
        if (strlen($postData['password']) > $limits['password'])
        {
            displayError($cfg, 'Post password cannot exceed (' . $limits['password'] . ') characters.');
        }
    }
    
    // Verify user isn't a bot (if enabled).
    if ($cfg['antiBotEnabled'])
    {
        if (
            !isset($_POST['verification-question-id']) || !is_numeric($_POST['verification-question-id']) ||
            !isset($_POST['verification-answer'])
        )
        {
            displayError($cfg, 'Could not perform anti-bot verification.', true);
        }
        
        if (strlen($_POST['verification-answer']) === 0)
        {
            displayError($cfg, 'Please provide an answer to the anti-bot verification question.', true);
        }
        
        // Check answer
        $questions = require_once(dirname(__FILE__) . '/../verification-questions.php');
        $userAnswer = trim($_POST['verification-answer']);
        $realAnswer = $questions[$_POST['verification-question-id']][1];
        
        if ($cfg['antiBotCaseSensitive'])
        {
            if ($userAnswer !== $realAnswer)
            {
                displayError($cfg, 'Invalid answer to the anti-bot verification question. Please try again. If you believe there to be an error please contact the server administrator.');
            }
        }
        else
        {
            if (strcasecmp($userAnswer, $realAnswer) !== 0)
            {
                displayError($cfg, 'Invalid answer to the anti-bot verification question. Please try again. If you believe there to be an error please contact the server administrator.');
            }
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
    // Retrieve table of contents and post counter. Create files if needed.
    $toc;
    $postId;
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
    
    // Verify data obtained from ToC and post counter.
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
    
    // Store data for new post.
    $post['id'] = $postId;
    
    // Get parent thread ID (if applicable).
    $threadId = $_POST['threadId'];
    
    // No parent thread ID found. Post is for a new thread. Create new thread.
    if (!$threadId)
    {
        if (!file_exists($cfg['path']['threads'] . "$postId.json"))
        {
            $json = json_encode(array($post), JSON_PRETTY_PRINT);
            if (!file_put_contents($cfg['path']['threads'] . "$postId.json", $json))
            {
                displayError($cfg, "Cannot create new thread. Something prevented it from being created in the database.", true);
            }
        }
        else
        {
            displayError($cfg, "Cannot create new thread. No parent thread ID was found, but the thread for this post number ($postId) exists in the database already.", true);
        }
    }
    // Parent thread ID found. Post is a reply to an existing thread. Update existing thread with new reply.
    else
    {
        if (file_exists($cfg['path']['threads'] . $threadId . '.json'))
        {
            $threadData = json_decode(file_get_contents($cfg['path']['threads'] . $threadId . '.json'));
            if (!$threadData)
            {
                displayError($cfg, "Could not retrieve data for thread number ($threadId) or data is corrupted.", true);
            }
            array_push($threadData, $post);
            $threadData = json_encode($threadData, JSON_PRETTY_PRINT);
            if (!file_put_contents($cfg['path']['threads'] . $threadId . '.json', $threadData))
            {
                displayError($cfg, "Cannot create new post number ($postId) for thread number ($threadId). Something prevent it from being created in the database.", true);
            }
        }    
        else
        {
            displayError($cfg, "Cannot reply to thread. Thread number ($threadId) was not located in database.", true);
        }
    }
    
    // Update the table of contents thread index.
    $threadPos = array_search($threadId, $toc);
    // If thread exists in ToC, bump it to the top.
    if ($threadPos !== false)
    {
        unset($toc[$threadPos]);
        array_unshift($toc, intval($threadId));
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
        displayError($cfg, "Cannot update the table of contents.", true);
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
           // . '<div class="milkbbs-error-logo">milkBBS</div>'
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
