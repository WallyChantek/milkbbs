<?php

$config = include('config.php');

function initMilkBBS()
{
    if ($_GET['page'] === 'thread' &&
        array_key_exists('id', $_GET) &&
        is_numeric($_GET['id']))
    {
        _generateThreadPage();
    }
    else if ($_GET['page'] === 'admin')
    {
        _generateAdminPage();
    }
    else
    {
        _generateIndexPage();
    }
}

function _generatePage($body)
{
    $html = '';
    
    return $html;
}

function _generateIndexPage()
{
    $pwd = dirname($_SERVER['PHP_SELF']);
    
    $html = '';
    $html .= '<p>Index page!</p>';
    
    // Posting form
    $html .= _generatePostingForm();
    
    // Threads
    $threadList = json_decode(file_get_contents('db/toc.json'));
    foreach ($threadList as $id)
    {
        $thread = file_get_contents("db/threads/$id.json");
        $thread = json_decode($thread, true);
        // TODO: Error handling
        
        $html .= _generateThread($thread, true, 3);
    }
    
    echo $html;
}

function _generateThreadPage()
{
    $pwd = dirname($_SERVER['PHP_SELF']);
    
    $html = '';
    $html .= '<p>Thread page!</p>';
    
    // Posting form
    $html .= _generatePostingForm($_GET['id']);
    
    // Threads
    if (file_exists('db/threads/' . $_GET['id'] . '.json'))
        $thread = json_decode(file_get_contents('db/threads/' . $_GET['id'] . '.json'), true);
    
    // TODO: Error handling
    
    $html .= _generateThread($thread, false);
    
    echo $html;
}

function _generateAdminPage()
{
    $html = '';
    $html .= '<p>Admin page!</p>';
    
    echo $html;
}

function _generatePostingForm($threadId = '')
{
    $html = '<form method="post" action="' . dirname($_SERVER['PHP_SELF']) . '/post-comment.php">'
          . '<table id="milkbbs-posting-form">'
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

function _generateThread($thread, $showReply = true, $limit = 0)
{
    $limit = ($limit <= 0 ? count($thread) : $limit);
    $html = '<div class="milkbbs-thread-container">';
    $threadId = $thread[0]['id'];
    for ($i = 0; $i < min(count($thread), $limit); $i++)
    {
        $post = $thread[$i];
        $html .= '<div class="milkbbs-entry">';
        
        // Line 01: Name, URL, post number, anchor link
        $html .= '<div>';
        $html .= '<div>'
            . (array_key_exists('email', $post) ? '<a href="mailto:' . $post['email'] . '" ' : '<span ')
            . 'class="milkbbs-post-name">' . $post['name']
            . (array_key_exists('email', $post) ? '</a>' : '</span>')
            . (array_key_exists('url', $post) ? '&nbsp;<a class="milkbbs-post-url" href="' . $post['url'] . '">[URL]</a>' : '')
            . '</div>';
        $html .= '<div><span class="milkbbs-post-number">No. ' . $post['id'] . '<span>&nbsp;<a href="#">#</a></div>';
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

?>
