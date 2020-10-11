<?php

$config = include('config.php');

function loadMilkBBS()
{
    if ($_GET['page'] === 'thread' && is_numeric($_GET['id']))
    {
        generateThreadViewPage();
    }
    else if ($_GET['page'] === 'admin')
    {
        generateAdminPage();
    }
    else
    {
        generateIndexPage();
    }
}

/*
    Generates and outputs the HTML markup for the "main" landing page (the
    thread list, or catalog page).
*/
function generateIndexPage()
{
    $html = generatePostingForm();
    
    // Threads
    if (file_exists('db/toc.json'))
    {
        $threadList = json_decode(file_get_contents('db/toc.json'));
        if (!$threadList)
        {
            displayError('Corrupted index. Please contact the server administrator if this issue persists.');
        }
        
        foreach ($threadList as $id)
        {
            $thread = '';
            
            if (file_exists("db/threads/$id.json"))
            {
                $thread = json_decode(file_get_contents("db/threads/$id.json"), true);
            }
            
            if ($thread)
            {
                $html .= generateThread($thread, true, 3);
            }
            else
            {
                $html .= '<div class="milkbbs-thread-container">'
                       . '<div class="milkbbs-entry">'
                       . "<div>Post number ($id) could not be displayed due to errors.</div>"
                       . '</div>'
                       . '</div>'
                ;
            }
        }
    }
    
    echo $html;
}

/*
    Generates and outputs the HTML markup for any thread-viewing pages.
*/
function generateThreadViewPage()
{
    $id = $_GET['id'];
    $html = generatePostingForm($id);
    
    // Threads
    if (file_exists("db/threads/$id.json"))
    {
        $thread = json_decode(file_get_contents("db/threads/$id.json"), true);
    }
    
    if ($thread)
    {
        $html .= generateThread($thread, false);
    }
    else
    {
        displayError('This thread could not be displayed due to errors. Please contact the server administrator if this issue persists.');
    }
    
    echo $html;
}

/*
    Generates and outputs the HTML markup for the administrative control panel.
*/
function generateAdminPage()
{
    $html = '';
    $html .= '<p>Admin page!</p>';
    
    echo $html;
}

/*
    Generates the form used for making new posts, both for creating new threads
    and for replying to existing threads.
*/
// TODO: Continue cleanup here
function generatePostingForm($threadId = '')
{
    $html = '<form method="post" action="' . dirname($_SERVER['PHP_SELF']) . '/post-comment.php">'
          . '<table class="milkbbs-posting-form">'
          . '<tr><td>Name</td><td><input name="name" type="text" placeholder="Anonymous" /></td>'
          . '<tr><td>Email</td><td><input name="email" type="text" /></td>'
          . '<tr><td>Homepage</td><td><input name="url" type="text" /></td>'
          . '<tr><td>Subject</td><td><input name="subject" type="text" /></td>'
          . '<tr><td>Comment</td><td><textarea name="comment"></textarea></td>'
          . '<tr><td>Password</td><td><input name="password" type="text" placeholder="(optional, for post deletion)" /></td>'
          . '<tr><td colspan="2">What is the name of Mario\'s green brother?</td></tr>'
          . '<tr><td colspan="2"><input name="verification" type="text" /></td>'
          . '<tr><td colspan="2"><input name="threadId" type="hidden" value="' . $threadId . '" /><input type="submit" /></td></tr>'
          . '</table>'
          . '</form>'
    ;
    
    return $html;
}

function generateThread($thread, $showReply = true, $limit = 0)
{
    $limit = ($limit <= 0 ? count($thread) : $limit);
    $html = '<div class="milkbbs-thread-container">';
    $threadId = $thread[0]['id'];
    for ($i = 0; $i < min(count($thread), $limit); $i++)
    {
        $post = $thread[$i];
        $html .= '<div class="milkbbs-entry" id="' . $post['id'] . '">';
        
        // Line 01: Name, URL, post number, anchor link
        $html .= '<div>';
        $html .= '<div>'
            . (isset($post['email']) ? '<a href="mailto:' . $post['email'] . '" ' : '<span ')
            . 'class="milkbbs-post-name">' . $post['name']
            . (isset($post['email']) ? '</a>' : '</span>')
            . (isset($post['url']) ? '&nbsp;<a class="milkbbs-post-url" href="' . $post['url'] . '">[URL]</a>' : '')
            . '</div>';
        $html .= '<div><span class="milkbbs-post-number">No. ' . $post['id'] . '<span>&nbsp;<a href="' . dirname($_SERVER['PHP_SELF']) . '/' . basename($_SERVER['PHP_SELF']) . '?page=thread&id=' . $threadId . '#' . $post['id'] . '">#</a></div>';
        $html .= '</div>';
        // Line 02: Subject
        $html .= '<div>';
        $html .= '<span class="milkbbs-post-subject">' . $post['subject'] . '</span>';
        $html .= '</div>';
        // Line 03: Comment
        $html .= '<div class="milkbbs-post-comment">' . $post['comment'] . '</div>';
        // Line 04: Date, delete button
        $html .= '<div>';
        $html .= '<span class="milkbbs-post-date">' . $post['date'] . '</span>';
        $html .= '<span class="milkbbs-post-delete">[Delete]</span>';
        $html .= '</div>';
        
        $html .= '</div>';
    }
    if ($showReply)
        $html .= '<div class="milkbbs-entry milkbbs-reply"><div><a class="milkbbs-reply" href="' . dirname($_SERVER['PHP_SELF']) . '/' . basename($_SERVER['PHP_SELF']) . '?page=thread&id=' . $threadId . '">[Reply to this thread...]</a></div></div>';
    $html .= '</div>';
    
    return $html;
}

function displayError($msg = '')
{
    $html = '<div class="milkbbs-error-container">'
           // . '<div class="milkbbs-error-logo">milkBBS</div>'
           . '<div class="milkbbs-error-title">milkBBS</div>'
           . '<div class="milkbbs-error-message">Error: ' . $msg . '</div>'
           . '</div>'
    ;
    
    echo $html;
    exit();
}

?>
