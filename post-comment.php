<?php
if ($_POST) {
    $postNum;
    $toc;
    if (file_exists('db/postnum.txt'))
        $postNum = file_get_contents('db/postnum.txt');
    if (file_exists('db/toc.json'))
        $toc = file_get_contents('db/toc.json');
    
    // Execute code (such as database updates) here.
    if ($postNum && is_numeric($postNum))
    {
        $postNum = intval($postNum);
        $postNum++;
    }
    else
    {
        file_put_contents('db/postnum.txt', '1');
        $postNum = 1;
    }
    
    // Store data for new post
    $newPost = array();
    $newPost['id'] = $postNum;
    $newPost['name'] = !empty($_POST['name']) ? $_POST['name'] : 'Anonymous';
    if (!empty($_POST['email']))
        $newPost['email'] = $_POST['email'];
    if (!empty($_POST['url']))
        $newPost['url'] = $_POST['url'];
    if (!empty($_POST['subject']))
        $newPost['subject'] = $_POST['subject'];
    $newPost['comment'] = $_POST['comment'];
    if (!empty($_POST['password']))
        $newPost['password'] = $_POST['password'];
    
    // Post is a reply to an existing thread.
    if (!empty($_POST['threadId']) && is_numeric($_POST['threadId']))
    {
        
    }
    // Post is for a new thread.
    else
    {
        if (!file_exists("db/threads/$postNum.json"))
        {
            $json = json_encode(array($newPost), JSON_PRETTY_PRINT);
            file_put_contents("db/threads/$postNum.json", $json);
        }
        else
        {
            // Throw an error or something
        }
    }
    
    // Update the table-of-contents thread index
    // ToC doesn't exist yet and needs to be created
    if (!$toc)
    {
        $json = json_encode(array($postNum), JSON_PRETTY_PRINT);
        file_put_contents('db/toc.json', $json);
    }
    // ToC does exist and needs to be updated
    else
    {
        $toc = json_decode($toc);
        
        $threadPos = array_search($_POST['threadId'], $toc);
        // If thread exists in ToC, bump it to the top
        if ($threadPos)
        {
            
        }
        // If thread doesn't exist in ToC, add it to the top
        else
        {
            array_unshift($toc, $postNum);
        }
        
        $toc = json_encode($toc, JSON_PRETTY_PRINT);
        file_put_contents('db/toc.json', $toc);
    }
    
    // Write to current post number/count text file with incremented number
    file_put_contents('db/postnum.txt', $postNum);
    
    // Redirect to main page
    header( 'Location: ' . dirname($_SERVER['PHP_SELF']), true, 303 );
    exit();
}
?>
