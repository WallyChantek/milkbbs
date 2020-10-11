<?php

$cfg = require_once(dirname(__FILE__) . '/../config.php');
$cfg = array_merge($cfg, require_once('data.php'));

date_default_timezone_set('America/New_York');

// Only process data if a POST occurred.
if ($_POST) {
    savePost($cfg);
    
    // Redirect to main page.
    header( 'Location: ' . $_POST['callingScript'], true, 303 );
    exit();
}
else
{
    displayError($cfg, 'No POST data provided.');
}

/*
    Saves a new post to the database while also incrementing the total post
    counter.
*/
function savePost($cfg)
{
    // Retrieve table of contents and post counter.
    $toc;
    $postId;
    if (file_exists($cfg['file']['toc']))
        $toc = json_decode(file_get_contents($cfg['file']['toc']));
    if (file_exists($cfg['file']['postCount']))
        $postId = file_get_contents($cfg['file']['postCount']);
    
    if (!$toc)
    {
        displayError($cfg, 'Could not retrieve table of contents or table of contents is corrupted.', true);
    }
    
    if (!$postId || !is_numeric($postId))
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
    $post = array();
    $post['id'] = $postId;
    $post['date'] = date('Y-m-d (D) H:i:s');
    $post['name'] = !empty($_POST['name']) ? $_POST['name'] : 'Anonymous';
    if (!empty($_POST['email']))
        $post['email'] = $_POST['email'];
    if (!empty($_POST['url']))
        $post['url'] = $_POST['url'];
    if (!empty($_POST['subject']))
        $post['subject'] = $_POST['subject'];
    $post['comment'] = $_POST['comment'];
    if (!empty($_POST['password']))
        $post['password'] = $_POST['password'];
    
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
        error_log("thread found!");
        unset($toc[$threadPos]);
        array_unshift($toc, intval($threadId));
        array_values($toc);
    }
    // If thread doesn't exist in ToC, add it to the top
    else
    {
        error_log("THREAD NOT FOUND!");
        array_unshift($toc, $postId);
    }
    
    // Save updated table of contents.
    $toc = json_encode($toc, JSON_PRETTY_PRINT);
    if (!file_put_contents($cfg['file']['toc'], $toc))
    {
        displayError($cfg, "Cannot update the table of contents.", true);
    }
}

function displayError($cfg, $msg = '', $offerSupport)
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
