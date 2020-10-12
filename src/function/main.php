<?php

namespace milkbbs;

function loadMilkBBS()
{
    // Load user and system configuration.
    $cfg = require_once(dirname(__FILE__) . '/../user-config.php');
    $cfg = array_merge($cfg, require_once('data.php'));
    
    // Generate page based on URL's GET data.
    if ($_GET['page'] === 'thread')
    {
        insertThreadPage($cfg);
    }
    else if ($_GET['page'] === 'admin')
    {
        insertAdminPage($cfg);
    }
    else
    {
        insertIndexPage($cfg);
    }
}

/*
    Generates and outputs the HTML markup for the "main" page (the catalogue
    page which shows all the available threads).
*/
function insertIndexPage($cfg)
{
    $html = '<div class="milkbbs">';
    
    // Get form for making a new post.
    $html .= getPostForm($cfg);
    
    // Get the threads.
    if (file_exists($cfg['file']['toc']))
    {
        $toc = json_decode(file_get_contents($cfg['file']['toc']));
        if (!is_array($toc))
        {
            displayError($cfg, 'Corrupted table of contents.', true);
        }
        
        $displayLimit = (isset($cfg['threadsPerPageLimit']) && is_numeric($cfg['threadsPerPageLimit'])) ? $cfg['threadsPerPageLimit'] : count($toc);
        
        for ($i = 0; $i < $displayLimit; $i++)
        {
            $threadId = $toc[$i];
            $threadData = '';
            
            if (file_exists($cfg['path']['threads'] . "$threadId.json"))
            {
                $threadData = json_decode(file_get_contents($cfg['path']['threads'] . "$threadId.json"), true);
            }
            
            if ($threadData)
            {
                $html .= getThread($cfg, $threadData, false);
            }
            else
            {
                $html .= '<div class="milkbbs-thread-container">'
                       . '<div class="milkbbs-entry">'
                       . "<div>Post number ($threadId) could not be displayed due to errors.</div>"
                       . '</div>'
                       . '</div>'
                ;
            }
        }
    }
    
    $html .= getFooter($cfg);
    
    $html .= '</div>';
    
    echo $html;
}

/*
    Generates and outputs the HTML markup for an individual thread page.
*/
function insertThreadPage($cfg)
{
    $threadId = $_GET['id'];
    
    $html = '<div class="milkbbs">';
    
    // Get form for replying to an existing thread.
    $html .= getPostForm($cfg, $threadId);
    
    // Get the thread.
    if (file_exists($cfg['path']['threads'] . "$threadId.json"))
    {
        $thread = json_decode(file_get_contents($cfg['path']['threads'] . "$threadId.json"), true);
    }
    
    if ($thread)
    {
        $html .= getThread($cfg, $thread, true);
    }
    else
    {
        displayError($cfg, 'This thread could not be displayed due to errors.', true);
    }
    
    $html .= getFooter($cfg);
    
    $html .= '</div>';
    
    echo $html;
}

/*
    Generates and outputs the HTML markup for the administrative control panel.
*/
function insertAdminPage($cfg)
{
    $html = '<div class="milkbbs">';
    
    $html .= '<p>Admin page!</p>';
    
    $html .= '</div>';
    
    echo $html;
}

/*
    Generates the form used for making new posts, both for creating new threads
    and for replying to existing threads.
*/
function getPostForm($cfg, $threadId = '')
{
    // Load verification question (if enabled).
    if (isset($cfg['antiBotEnabled']) && $cfg['antiBotEnabled'])
    {
        $questions = require_once(dirname(__FILE__) . '/../user-verification-questions.php');
        
        if (is_array($questions) && count($questions) > 0)
        {
            $qid = array_rand($questions);
            
            if (isset($questions[$qid][1]))
            {
                $q = $questions[$qid][0];
            }
            else
            {
                displayError($cfg, 'Something went wrong loading the anti-bot verification.', true);
            }
        }
    }
    
    // Build out form.
    $html = '<form method="post" action="' . $cfg['path']['webLib'] . 'function/process-post.php">'
          . '<table class="milkbbs-posting-form">'
          . '<tr><td>Name</td><td><input name="name" type="text" placeholder="Anonymous"></td>'
          . '<tr><td>Email</td><td><input name="email" type="text"></td>'
          . '<tr><td>Homepage</td><td><input name="url" type="text"></td>'
          . '<tr><td>Subject</td><td><input name="subject" type="text"></td>'
          . '<tr><td>Comment</td><td><textarea name="comment"></textarea></td>'
          . '<tr><td>Password</td><td><input name="password" type="text" placeholder="(optional, for post deletion)"></td>'
          . '{VERIFICATION}'
          . '<tr><td colspan="2">'
          .     '<input name="threadId" type="hidden" value="' . $threadId . '">'
          .     '<input name="callingScript" type="hidden" value="' . $cfg['path']['originFile'] . '">'
          .     '<input type="submit">'
          . '</td></tr>'
          . '</table>'
          . '</form>'
    ;
    
    // Insert verification question (if enabled).
    if (isset($qid))
    {
        $v = '<tr><td colspan="2">' . $q . '<input name="verification-question-id" type="hidden" value="' . $qid . '"></td></tr>'
           . '<tr><td colspan="2"><input name="verification-answer" type="text"></td>'
        ;
        
        $html = str_replace('{VERIFICATION}', $v, $html);
    }
    else
    {
        $html = str_replace('{VERIFICATION}', '', $html);
    }
    
    return $html;
}

/*
    Generates a thread and its associated posts.
*/
function getThread($cfg, $threadData, $showAllReplies = true)
{
    $displayLimit = ($showAllReplies ? count($threadData) : min($cfg['threadPreviewPostLimit'], count($threadData)));
    $threadId = $threadData[0]['id'];
    
    $html = '<div class="milkbbs-thread-container">';
    
    for ($i = 0; $i < $displayLimit; $i++)
    {
        $p = $threadData[$i];
        
        $html .= $cfg['template']['post'];
        
        if (!isset($p['email']))
        {
            $html = str_replace('<a href="mailto:{POST_EMAIL}">{POST_AUTHOR}</a>', '{POST_AUTHOR}', $html);
        }
        
        if (!isset($p['url']))
        {
            $html = str_replace('&nbsp;<a class="milkbbs-post-url" href="{POST_URL}">[URL]</a>', '', $html);
        }
        
        // Escape HTML characters in strings so they aren't parsed.
        foreach ($p as $key => $val)
        {
            if (is_string($val))
            {
                $p[$key] = htmlspecialchars($val);
            }
        }
        
        // Replace template tags with post data.
        $html = str_replace('{POST_ID}', $p['id'], $html);
        $html = str_replace('{POST_AUTHOR}', $p['name'], $html);
        $html = str_replace('{POST_EMAIL}', $p['email'], $html);
        $html = str_replace('{POST_URL}', $p['url'], $html);
        $html = str_replace('{POST_SUBJECT}', $p['subject'], $html);
        $html = str_replace('{POST_COMMENT}', $p['comment'], $html);
        $html = str_replace('{POST_DATE}', $p['date'], $html);
        $html = str_replace('{POST_DELETE}', '[Delete]', $html);
        $html = str_replace('{POST_REPORT}', '[Report]', $html);
        $html = str_replace('{POST_ANCHOR_LINK}', $cfg['path']['originFile'] . '?page=thread&id=' . $threadId . '#' . $p['id'], $html);
    }
    
    if (!$showAllReplies)
    {
        $html .= '<div class="milkbbs-entry milkbbs-reply"><div><a class="milkbbs-reply" href="' . $cfg['path']['originFile'] . '?page=thread&id=' . $threadId . '">[Reply to this thread...]</a></div></div>';
    }
    
    $html .= '</div>';
    
    if (!$showAllReplies && $cfg['addHrAfterThreads'])
    {
        $html .= '<hr class="milkbbs-hr">';
    }
    
    return $html;
}

/*
    Generates a footer with the page list and other things.
*/
function getFooter($cfg)
{
    $html .= $cfg['template']['footer'];
    
    return $html;
}

/*
    Outputs an error to the user.
*/
function displayError($cfg, $msg = '', $offerSupport)
{
    $html = '<div class="milkbbs-error-container">'
           // . '<div class="milkbbs-error-logo">milkBBS</div>'
           . '<div class="milkbbs-error-title">milkBBS</div>'
           . '<div class="milkbbs-error-message">Error: ' . $msg . ($offerSupport ? ' Please contact the server administrator if this issue persists.' : '') . '</div>'
           . '</div>'
    ;
    
    echo $html;
    exit();
}

?>
