<?php
namespace milkgb;
use Exception;

error_reporting(E_ALL);

define(__NAMESPACE__ . '\STANDALONE_PAGE', false);

require_once('handlers.php');
set_error_handler('milkgb\customErrorHandler');
set_exception_handler('milkgb\customExceptionHandler');

// Set default time zone.
date_default_timezone_set('UTC');

function loadMilkGB()
{
    // Load common functions and data.
    include('common-functions.php');
    
    // Load and validate user configuration.
    $cfg = validateUserData(include(dirname(__FILE__) . '/../user-config.php'));
    
    // Load system configuration.
    $cfg = array_merge($cfg, loadSystemData());
    
    // Set constant indicating whether enhanced debugging should be shown.
    define(__NAMESPACE__ . '\DEV_MODE', $cfg['devMode']);
    
    // Generate page.
    insertPageContent($cfg);
    
    if ($_FILES)
    {
        // move_uploaded_file($_FILES['file']['tmp_name'], $cfg['path']['fsFiles'] . $_FILES['file']['name']);
    }
}

/*
    Generates and outputs the HTML markup for the main page content.
*/
function insertPageContent($cfg)
{
    $html = '<div class="milkgb">';
    
    // Get form for writing a new entry.
    $previewPage = (isset($_GET['preview']) && $_GET['preview'] === 'true' && $_POST);
    if ($previewPage)
        $html .= generateNewEntryPreviewForm($cfg);
    else
        $html .= generateNewEntryForm($cfg);
    
    // Load the entries from disk.
    $totalNumberOfPages = 0;
    $pageNum = 1;
    if (!$previewPage && file_exists($cfg['file']['toc']))
    {
        $toc = json_decode_ex(file_get_contents($cfg['file']['toc']));
        
        if (count($toc) > 0)
        {
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
                $entryId = $toc[$i];
                try
                {
                    $entryData = json_decode_ex(file_get_contents($cfg['path']['entries'] . $entryId . '.json'));
                    $entryData['id'] = $entryId;
                    $html .= getEntry($cfg, $entryData) . '<hr class="milkgb-hr">';
                }
                catch (Exception $exc)
                {
                    $badEntryHtml = $cfg['html']['entryError'];
                    $badEntryHtml = str_replace('{ENTRY_ID}', $entryId, $badEntryHtml);
                    $html .= $badEntryHtml;
                }
            }
        }
    }
    
    // Get the entry management & footer sections.
    if (!$previewPage)
    {
        $html .= getEntryManagement($cfg);
        $html .= getFooter($cfg, $totalNumberOfPages, $pageNum);
    }
    else
    {
        $html .= getFooter($cfg);
    }
    
    // Get the JavaScript functions.
    $html .= getJavaScript($cfg);
    
    $html .= '</div>';
    
    echo $html;
}

/*
    Generates the HTML for the form used for writing new entries.
*/
function generateNewEntryForm($cfg)
{
    // Build out form.
    $html = $cfg['html']['entryForm'];
    $html = str_replace('{ACTION}', '?preview=true', $html);
    
    // Add file uploader if enabled.
    if ($cfg['fileUploadingEnabled'])
    {
        $fileHtml = '<tr>'
                  .     '<td><label for="milkgb-posting-form-file">File</label></td>'
                  .     '<td><input type="file" name="file" id="milkgb-posting-form-file"></td>'
                  . '</tr>'
        ;
        $html = str_replace('{FILE_UPLOADER}', $fileHtml, $html);
    }
    else
    {
        $html = str_replace('{FILE_UPLOADER}', '', $html);
    }
    
    return $html;
}

/*
    Generates the HTML for the form used for previewing a new entry before the
    user actually posts it.
*/
function generateNewEntryPreviewForm($cfg)
{
    // Load verification question (if enabled).
    if ($cfg['antiBotVerificationEnabled'])
    {
        $questions = include($cfg['path']['fsLib'] . 'user-verification-questions.php');
        
        if (is_array($questions) && count($questions) > 0)
        {
            $qid = array_rand($questions);
            
            if (!isset($questions[$qid][0]) || !isset($questions[$qid][1]))
                displayError('Something went wrong loading the anti-bot verification. The data may be corrupt.');
            
            $q = $questions[$qid][0];
        }
    }
    
    // Build out form.
    $html = $cfg['html']['previewForm'];
    $html = str_replace('{ACTION}', $cfg['path']['webLib'] . 'function/process-entry.php', $html);
    $html = str_replace('{ORIGIN_FILE}', $cfg['file']['originFile'], $html);
    
    // Get entry ID for new post.
    $entryId = 0;
    if (file_exists($cfg['file']['entryCount']))
        $entryId = file_get_contents($cfg['file']['entryCount']);
    if (!is_numeric($entryId))
        $entryId = 0;
    $entryId++;
    
    // Get user data from previous page.
    $entry = [];
    $entry['id'] = $entryId;
    $entry['date'] = date('Y-m-d H:i');
    $entry['author'] = isset($_POST['author']) ? (string)trim($_POST['author']) : 'Anonymous';
    $entry['author'] = $entry['author'] !== '' ? $entry['author'] : 'Anonymous';
    $entry['email'] = isset($_POST['email']) ? (string)trim($_POST['email']) : '';
    $entry['url'] = isset($_POST['url']) ? (string)trim($_POST['url']) : '';
    $entry['subject'] = isset($_POST['subject']) ? (string)trim($_POST['subject']) : '';
    $entry['comment'] = isset($_POST['comment']) ? (string)trim($_POST['comment']) : '';
    $entry['comment'] = str_replace("\r\n", '{NEW_LINE}', $entry['comment']);
    $entry['comment'] = str_replace(array("\r", "\n"), '{NEW_LINE}', $entry['comment']);
    $entry['file'] = isset($_FILES['file']['name']) ? $_FILES['file']['name'] : '';
    $entry['password'] = isset($_POST['password']) ? (string)trim($_POST['password']) : '';
    
    // Populate hidden input fields with user data.
    $html = str_replace('{INPUT_AUTHOR}', $entry['author'], $html);
    $html = str_replace('{INPUT_EMAIL}', $entry['email'], $html);
    $html = str_replace('{INPUT_URL}', $entry['url'], $html);
    $html = str_replace('{INPUT_SUBJECT}', $entry['subject'], $html);
    $html = str_replace('{INPUT_COMMENT}', $entry['comment'], $html);
    $html = str_replace('{INPUT_PASSWORD}', $entry['password'], $html);
    $html = str_replace('{INPUT_FILE}', $entry['file'], $html);
    
    // Generate an entry preview.
    $html = str_replace('{ENTRY_PREVIEW}', getEntry($cfg, $entry, true), $html);
    
    // Insert verification question (if enabled).
    $verification = '';
    if (isset($qid))
    {
        $verification =
            '<tr><td colspan="2"><label for="milkgb-posting-form-verification">' . $q . '<input type="hidden" name="verification-question-id" value="' . $qid . '"></label></td></tr>'
          . '<tr><td colspan="2"><input type="text" id="milkgb-posting-form-verification" name="verification-answer"></td></tr>'
        ;
    }
    $html = str_replace('{VERIFICATION}', $verification, $html);
    
    return $html;
    
}

/*
    Generates the HTML for an individual saved entry.
*/
function getEntry($cfg, $entry, $isPreview = false)
{
    // Retrieve and validate entry data
    $entry['id'] = (isset($entry['id']) && is_numeric($entry['id']) && $entry['id'] > 0) ? $entry['id'] : 0;
    $entry['author'] = isset($entry['author']) ? $entry['author'] : 'Anonymous';
    $entry['email'] = isset($entry['email']) ? $entry['email'] : '';
    $entry['url'] = isset($entry['url']) ? $entry['url'] : '';
    $entry['subject'] = isset($entry['subject']) ? $entry['subject'] : '';
    $entry['comment'] = isset($entry['comment']) ? $entry['comment'] : '';
    $entry['file'] = isset($entry['file']) ? $entry['file'] : '';
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
    
    // Convert datetime to match timezone specified in configuration.
    if ($entry['date'] !== '')
    {
        $dt = new \DateTime($entry['date']);
        $dt->setTimeZone(new \DateTimeZone($cfg['timezone']));
        if ($cfg['24HourClock'])
            $entry['date'] = $dt->format('Y-m-d (D) H:i');
        else
            $entry['date'] = $dt->format('Y-m-d (D) h:i A');
    }
    
    // Escape HTML characters in strings so they aren't parsed.
    foreach ($entry as $key => $val)
    {
        if (is_string($val))
        {
            $entry[$key] = htmlspecialchars($val);
        }
    }
    
    // Replace formatting tags in commment with appropriate markup.
    $entry['comment'] = str_replace('{NEW_LINE}', '<br>', $entry['comment']);
    
    // Replace template tags with entry data.
    $html = str_replace('{ENTRY_ID}', $entry['id'], $html);
    $html = str_replace('{AUTHOR}', $entry['author'], $html);
    $html = str_replace('{EMAIL}', $entry['email'], $html);
    $html = str_replace('{URL}', $entry['url'], $html);
    $html = str_replace('{SUBJECT}', $entry['subject'], $html);
    $html = str_replace('{COMMENT}', $entry['comment'], $html);
    $html = str_replace('{DATE}', $entry['date'], $html);
    $html = str_replace('{FILE}', $entry['file'], $html);
    if (!$isPreview)
        $html = str_replace('{DELETE}', '<a href="#milkgb-post-management">[Delete]</a>', $html);
    else
        $html = str_replace('<div class="milkgb-entry-delete">{DELETE}</div>', '', $html);
    
    // Hide rows that are empty.
    $html = str_replace('{ROW_STYLE_SUBJECT}', ($entry['subject'] === '' ? ' style="display: none;"' : ''), $html);
    $html = str_replace('{ROW_STYLE_COMMENT}', ($entry['comment'] === '' ? ' style="display: none;"' : ''), $html);
    $html = str_replace('{ROW_STYLE_FILE}', ($entry['file'] === '' ? ' style="display: none;"' : ''), $html);
    
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
        
        // Constrain ends if we're exceeding boundaries.
        $rLower = max((int)$rLower, 1);
        $rUpper = min((int)$rUpper, $totalNumberOfPages);
        
        // Ensure values are integers.
        $rLower = (int) $rLower;
        $rUpper = (int) $rUpper;
        
        // Build out page navigation links.
        for ($i = $rLower; $i <= $rUpper; $i++)
        {
            if ($i !== $pageNum)
                $n .= '<a href="?pageNum=' . $i . '">[' . $i . ']</a> ';
            else
                $n .= '[' . $i . '] ';
        }
        $html = str_replace('{PAGES}', $n, $html);
        
        // Set hrefs for next/previous page buttons.
        if ($totalNumberOfPages > 1)
        {
            if ($pageNum - 1 >= 1)
                $html = str_replace('[<]', '<a href="?pageNum='. ($pageNum - 1) .'">[<]</a>', $html);
            
            if ($pageNum + 1 <= $totalNumberOfPages)
                $html = str_replace('[>]', '<a href="?pageNum=' . ($pageNum + 1) . '">[>]</a>', $html);
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

?>
