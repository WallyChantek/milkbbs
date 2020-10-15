<?php

namespace milkbbs;

error_reporting(E_ALL);

function loadMilkBBS()
{
    // Load common functions
    require_once('common-functions.php');
    
    // Load and validate user configuration.
    $cfg = require_once(dirname(__FILE__) . '/../user-config.php');
    $cfg = validateUserData($cfg);
    
    // Load system configuration
    $cfg = array_merge($cfg, require_once('data.php'));
    
    // Generate page based on URL's GET data.
    $requestedPage = isset($_GET['page']) ? $_GET['page'] : '';
    switch ($requestedPage)
    {
        case 'thread':
            insertThreadPage($cfg);
            break;
        case 'admin':
            insertAdinPage($cfg);
            break;
        default:
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
    $totalNumberOfPages = 0;
    $pageNum = 1;
    if (file_exists($cfg['file']['toc']))
    {
        $toc = json_decode(file_get_contents($cfg['file']['toc']));
        if (!is_array($toc))
        {
            displayError($cfg, 'Corrupted table of contents.', true);
        }
        
        $pageNum = (isset($_GET['pageNum']) && is_numeric($_GET['pageNum'])) ? intval($_GET['pageNum']) : 1;
        
        $totalThreadsPerPage = $cfg['maxThreadsPerPage'] > 0 ? min($cfg['maxThreadsPerPage'], count($toc)) : count($toc);
        
        $totalNumberOfPages = ceil(count($toc) / $totalThreadsPerPage);
        
        if ($pageNum < 1)
        {
            $pageNum = 1;
        }
        elseif ($pageNum > $totalNumberOfPages)
        {
            $pageNum = $totalNumberOfPages;
        }
        
        for ($i = (($pageNum - 1) * $totalThreadsPerPage); $i < min(($totalThreadsPerPage * $pageNum), count($toc)); $i++)
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
    
    // Get the footer
    $html .= getFooter($cfg, $totalNumberOfPages, $pageNum);
    
    $html .= '</div>';
    
    echo $html;
}

/*
    Generates and outputs the HTML markup for an individual thread page.
*/
function insertThreadPage($cfg)
{
    $threadId = (isset($_GET['id']) && is_numeric($_GET['id'])) ? intval($_GET['id']) : 0;
    
    if ($threadId === 0)
    {
        displayError($cfg, 'Could not display thread, possibly due to bad thread ID. This thread may not exist.', true);
    }
    
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
    if ($cfg['antiBotVerificationEnabled'])
    {
        $questions = require_once(dirname(__FILE__) . '/../user-verification-questions.php');
        
        if (is_array($questions) && count($questions) > 0)
        {
            $qid = array_rand($questions);
            
            if (isset($questions[$qid][0]) && isset($questions[$qid][1]))
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
    $html = $cfg['template']['form'];
    $html = str_replace('{PROCESSING_SCRIPT}', $cfg['path']['webLib'] . 'function/process-post.php', $html);
    $html = str_replace('{PARENT_THREAD_ID}', $threadId, $html);
    $html = str_replace('{ORIGIN_FILE}', $cfg['file']['originFile'], $html);
    
    // Insert verification question (if enabled).
    $verification = '';
    if (isset($qid))
    {
        $verification =
            '<tr><td colspan="2">' . $q . '<input name="verification-question-id" type="hidden" value="' . $qid . '"></td></tr>'
          . '<tr><td colspan="2"><input name="verification-answer" type="text"></td>'
        ;
    }
    $html = str_replace('{VERIFICATION}', $verification, $html);
    
    return $html;
}

/*
    Generates a thread and its associated posts.
*/
function getThread($cfg, $threadData, $showAllReplies = true)
{
    $numberOfDisplayedPosts = $showAllReplies ? count($threadData) : min($cfg['maxPostsPerThreadPreview'], count($threadData));
    $threadId = $threadData[0]['id'];
    
    $html = '<div class="milkbbs-thread-container">';
    
    // Generate each post for the thread
    for ($i = 0; $i < $numberOfDisplayedPosts; $i++)
    {
        // Retrieve and validate post data
        $p = $threadData[$i];
        $p['id'] = (isset($p['id']) && is_numeric($p['id']) && $p['id'] > 0) ? $p['id'] : 0;
        $p['author'] = isset($p['author']) ? $p['author'] : '';
        $p['email'] = isset($p['email']) ? $p['email'] : '';
        $p['url'] = isset($p['url']) ? $p['url'] : '';
        $p['subject'] = isset($p['subject']) ? $p['subject'] : '';
        $p['comment'] = isset($p['comment']) ? $p['comment'] : '';
        $p['date'] = isset($p['date']) ? $p['date'] : '';
        
        // Render this thread as bad if certain data was bad or missing.
        if (
            $p['id'] === 0
         || $p['author'] === ''
         || $p['date'] === ''
        )
        {
            $html .= '<div class="milkbbs-thread-container">'
                   . '<div class="milkbbs-entry">'
                   . "<div>Post number ($threadId) could not be displayed due to errors.</div>"
                   . '</div>'
                   . '</div>'
            ;
            
            continue;
        }
        
        // Retrieve post template.
        $html .= $cfg['template']['post'];
        
        // Remove tags for unnecessary fields
        if ($p['email'] === '')
        {
            $html = str_replace('<a href="mailto:{EMAIL}">{AUTHOR}</a>', '{AUTHOR}', $html);
        }
        
        if ($p['url'] === '')
        {
            $html = str_replace('&nbsp;<a class="milkbbs-post-url" href="{URL}">[URL]</a>', '', $html);
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
        $html = str_replace('{AUTHOR}', $p['author'], $html);
        $html = str_replace('{EMAIL}', $p['email'], $html);
        $html = str_replace('{URL}', $p['url'], $html);
        $html = str_replace('{SUBJECT}', $p['subject'], $html);
        $html = str_replace('{COMMENT}', $p['comment'], $html);
        $html = str_replace('{DATE}', $p['date'], $html);
        $html = str_replace('{DELETE}', '[Delete]', $html);
        $html = str_replace('{REPORT}', '[Report]', $html);
        $html = str_replace('{ANCHOR}', $cfg['file']['originFile'] . '?page=thread&id=' . $threadId . '#' . $p['id'], $html);
    }
    
    // Add reply button if thread is truncated.
    if (!$showAllReplies)
    {
        $html .= '<div class="milkbbs-entry milkbbs-reply"><div><a class="milkbbs-reply" href="' . $cfg['file']['originFile'] . '?page=thread&id=' . $threadId . '">[Reply to this thread...]</a></div></div>';
    }
    
    $html .= '</div>';
    $html .= '<hr class="milkbbs-hr">';
    
    return $html;
}

/*
    Generates a footer with the page list and other things.
*/
function getFooter($cfg, $totalNumberOfPages = 0, $pageNum = 1)
{
    $html = $cfg['template']['footer'];
    
    // Page navigation
    if ($totalNumberOfPages > 0)
    {
        $n = ' ';
        
        // Find display ranges if constrained via configuration.
        $rLower = 1;
        $rUpper = $totalNumberOfPages;
        $maxNavLinks = $cfg['maxNavigationPageLinks'];
        if ($cfg['maxNavigationPageLinks'] > 0)
        {
            // Lower
            $f = floor($maxNavLinks / 2);

            $rLower = $pageNum - $f;
            $rUpper = $pageNum + $f;
            
            // If max links is even we have to make a correction.
            if ($maxNavLinks % 2 === 0)
            {
                $rLower++;
            }
            
            // Shift right if we hit the left side.
            while ($rLower < 1)
            {
                $rLower++;
                $rUpper++;
            }
            
            // Shift left if we hit the right side.
            while ($rUpper > $totalNumberOfPages)
            {
                $rLower--;
                $rUpper--;
            }
        }
        
        $rLower = intval($rLower);
        $rUpper = intval($rUpper);
        
        // Build out page navigation links.
        for ($i = $rLower; $i <= $rUpper; $i++)
        {
            if ($i !== $pageNum)
            {
                $n .= '<a href="?pageNum=' . $i . '">[' . $i . ']</a> ';
            }
            else
            {
                $n .= '[' . $i . '] ';
            }
        }
        $html = str_replace('{PAGES}', $n, $html);
        
        // Set hrefs for next/previous page buttons.
        if ($totalNumberOfPages > 1)
        {
            if ($pageNum - 1 >= 1)
            {
                $html = str_replace('[<]', '<a href="?pageNum='. ($pageNum - 1) .'">[<]</a>', $html);
            }
            
            if ($pageNum + 1 <= $totalNumberOfPages)
            {
                $html = str_replace('[>]', '<a href="?pageNum=' . ($pageNum + 1) . '">[>]</a>', $html);
            }
        }
    }
    else
    {
        $html = str_replace('<div class="milkbbs-footer-pages">[<]{PAGES}[>]</div>', '', $html);
    }
    
    return $html;
}

/*
    Outputs an error to the user.
*/
function displayError($cfg, $msg = '', $offerSupport)
{
    $html = '<div class="milkbbs-error-container">'
           . '<div class="milkbbs-error-logo">milkBBS Logo</div>'
           . '<div class="milkbbs-error-title">milkBBS</div>'
           . '<div class="milkbbs-error-message">Error: ' . $msg . ($offerSupport ? ' Please contact the server administrator if this issue persists.' : '') . '</div>'
           . '</div>'
    ;
    
    echo $html;
    exit();
}

?>
