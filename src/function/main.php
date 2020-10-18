<?php

namespace milkgb;
use Exception;

error_reporting(E_ALL);

require_once('handlers.php');
set_error_handler('milkgb\customErrorHandler');
set_exception_handler('milkgb\customExceptionHandler');

function loadMilkGB()
{
    // Load common functions and data.
    require_once('common-functions.php');
    
    // Load and validate user configuration.
    $cfg = include(dirname(__FILE__) . '/../user-config.php');
    $cfg = validateUserData($cfg);
    
    // Load system configuration.
    $cfg = array_merge($cfg, loadSystemData());
    
    // Set constant indicating whether enhanced debugging should be shown.
    define(__NAMESPACE__ . '\DEV_MODE', $cfg['devMode']);
    
    // Generate page.
    insertPageContent($cfg);
}

/*
    Generates and outputs the HTML markup for the main page content.
*/
function insertPageContent($cfg)
{
    $html = '<div class="milkgb">';
    
    // Get form for writing a new entry.
    $html .= generatePostingForm($cfg);
    
    // Load the entries from disk.
    $totalNumberOfPages = 0;
    $pageNum = 1;
    if (file_exists($cfg['file']['toc']))
    {
        $toc = json_decode(file_get_contents($cfg['file']['toc']));
        if (!is_array($toc))
            displayError('Corrupted table of contents.');
        
        $pageNum = (isset($_GET['pageNum']) && is_numeric($_GET['pageNum'])) ? intval($_GET['pageNum']) : 1;
        $maxEntriesPerPage = $cfg['maxEntriesPerPage'] > 0 ? min($cfg['maxEntriesPerPage'], count($toc)) : count($toc);
        $totalNumberOfPages = ceil(count($toc) / $maxEntriesPerPage);
        
        // Constrain page number if it exceeds boundaries.
        if ($pageNum < 1)
            $pageNum = 1;
        elseif ($pageNum > $totalNumberOfPages)
            $pageNum = $totalNumberOfPages;
        
        // Generate HTML for entries.
        for ($i = (($pageNum - 1) * $maxEntriesPerPage); $i < min(($maxEntriesPerPage * $pageNum), count($toc)); $i++)
        {
            try
            {
                $entryData = json_decode_ex(file_get_contents($cfg['path']['entries'] . $toc[$i] . '.json'));
                $html .= getEntry($cfg, $entryData);
            }
            catch (Exception $exc)
            {
                $badEntryHtml = $cfg['html']['entryError'];
                $badEntryHtml = str_replace('{ENTRY_ID}', $toc[$i], $badEntryHtml);
                $html .= $badEntryHtml;
            }
        }
    }
    
    // Get the entry management section.
    $html .= getEntryManagement($cfg);
    
    // Get the footer.
    $html .= getFooter($cfg, $totalNumberOfPages, $pageNum);
    
    // Get the JavaScript functions.
    $html .= getJavaScript($cfg);
    
    $html .= '</div>';
    
    echo $html;
}

/*
    Generates the HTML for the form used for writing new entries.
*/
function generatePostingForm($cfg)
{
    // Load verification question (if enabled).
    if ($cfg['antiBotVerificationEnabled'])
    {
        $questions = include($cfg['path']['fsLib'] . 'user-verification-questions.php');
        
        if (is_array($questions) && count($questions) > 0)
        {
            $qid = array_rand($questions);
            
            if (!isset($questions[$qid][0]) || !isset($questions[$qid][1]))
                displayError($cfg, 'Something went wrong loading the anti-bot verification. The data may be corrupt.', true);
            
            $q = $questions[$qid][0];
        }
    }
    
    // Build out form.
    $html = $cfg['html']['form'];
    $html = str_replace('{PROCESSING_SCRIPT}', $cfg['path']['webLib'] . 'function/process-entry.php', $html);
    $html = str_replace('{ORIGIN_FILE}', $cfg['file']['originFile'], $html);
    
    // Insert verification question (if enabled).
    $verification = '';
    if (isset($qid))
    {
        $verification =
            '<tr><td colspan="2"><label for="milkgb-posting-form-verification">' . $q . '<input type="hidden" name="verification-question-id" value="' . $qid . '"></label></td></tr>'
          . '<tr><td colspan="2"><input type="text" id="milkgb-posting-form-verification" name="verification-answer"></td>'
        ;
    }
    $html = str_replace('{VERIFICATION}', $verification, $html);
    
    // Insert error message if user previously attempted to posted but it failed.
    $html = str_replace('{ERROR_MSG}', '<tr><td colspan="2"><div class="milkgb-posting-forum-error">ERROR HERE!</div></td></tr>', $html);
    
    return $html;
}

/*
    Generates the HTML for an individual saved entry.
*/
function getEntry($cfg, $entry)
{
    // Retrieve and validate entry data
    $entry['id'] = (isset($entry['id']) && is_numeric($entry['id']) && $entry['id'] > 0) ? $entry['id'] : 0;
    $entry['author'] = isset($entry['author']) ? $entry['author'] : 'Anonymous';
    $entry['email'] = isset($entry['email']) ? $entry['email'] : '';
    $entry['url'] = isset($entry['url']) ? $entry['url'] : '';
    $entry['subject'] = isset($entry['subject']) ? $entry['subject'] : '';
    $entry['comment'] = isset($entry['comment']) ? $entry['comment'] : '';
    $entry['date'] = isset($entry['date']) ? $entry['date'] : '';
    
    // Render this thread as bad if certain data was bad or missing.
    if ($entry['id'] === 0)
    {
        throw new Exception('Bad ID for entry number ' . $entry['id']);
    }
        
    // Retrieve entry template.
    $html = $cfg['html']['entry'];
    
    // Remove tags for unnecessary fields
    if ($entry['email'] === '')
        $html = str_replace('<a href="mailto:{EMAIL}">{AUTHOR}</a>', '{AUTHOR}', $html);
    
    if ($entry['url'] === '')
        $html = str_replace('&nbsp;<a class="milkgb-entry-url" href="{URL}">[URL]</a>', '', $html);
    
    // Escape HTML characters in strings so they aren't parsed.
    foreach ($entry as $key => $val)
    {
        if (is_string($val))
        {
            $entry[$key] = htmlspecialchars($val);
        }
    }
    
    // Replace template tags with entry data.
    $html = str_replace('{ENTRY_ID}', $entry['id'], $html);
    $html = str_replace('{AUTHOR}', $entry['author'], $html);
    $html = str_replace('{EMAIL}', $entry['email'], $html);
    $html = str_replace('{URL}', $entry['url'], $html);
    $html = str_replace('{SUBJECT}', $entry['subject'], $html);
    $html = str_replace('{COMMENT}', $entry['comment'], $html);
    $html = str_replace('{DATE}', $entry['date'], $html);
    $html = str_replace('{DELETE}', '<a href="#milkgb-post-management">[Delete]</a>', $html);
    
    return $html;
}

/*
    Generates the HTML for the entry management section used to delete entries.
*/
function getEntryManagement($cfg)
{
    $html = $cfg['html']['entryManagement'];
    $html = str_replace('{PROCESSING_SCRIPT}', $cfg['path']['webLib'] . 'function/process-entry.php', $html);
    $html = str_replace('{ORIGIN_FILE}', $cfg['file']['originFile'], $html);
    
    return $html;
}

/*
    Generates the HTML for the footer with the page list and software info.
*/
function getFooter($cfg, $totalNumberOfPages = 0, $pageNum = 1)
{
    $html = $cfg['html']['footer'];
    
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
        $html = str_replace('<div class="milkgb-footer-pages">[<]{PAGES}[>]</div>', '', $html);
    }
    
    return $html;
}

/*
    Generates the JavaScript necessary for certain page functions.
*/
function getJavaScript($cfg)
{
    $html = '<script type="text/javascript">';
    $html .= $cfg['html']['javascript'];
    $html .= '</script>';
    
    return $html;
}

/*
    Outputs an error to the user and halts further execution.
*/
function displayError($msg)
{
    $devModeHtml = '';
    if (defined(__namespace__ . '\DEV_MODE') && namespace\DEV_MODE || !defined(__namespace__ . '\DEV_MODE'))
        $devModeHtml = '<div class="milkgb-error-details">' . $msg . '</div>';
    
    $html = '<div class="milkgb">'
          .     '<div class="milkgb-error-container">'
          .         '<div class="milkgb-error-logo">milkGB Logo</div>'
          .         '<div class="milkgb-error-title">milkGB</div>'
          .         '<div class="milkgb-error-message">An error occurred. Please contact the server administrator if this issue persists.</div>'
          .         $devModeHtml
          .     '</div>'
          . '</div>'
    ;
    
    echo $html;
    
    exit();
}


?>
