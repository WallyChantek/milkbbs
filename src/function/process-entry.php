<?php

namespace milkgb;
use Exception;

error_reporting(E_ALL);

define(__NAMESPACE__ . '\STANDALONE_PAGE', true);

require_once('handlers.php');
set_error_handler('milkgb\customErrorHandler');
set_exception_handler('milkgb\customExceptionHandler');

// Load common functions and data.
include('common-functions.php');

// Load and validate user configuration.
$cfg = validateUserData(include(dirname(__FILE__) . '/../user-config.php'));

// Load system configuration
$cfg = array_merge($cfg, loadSystemData());

// Set constant indicating whether enhanced debugging should be shown.
define(__NAMESPACE__ . '\DEV_MODE', $cfg['devMode']);
define(__NAMESPACE__ . '\WEB_LIB', $cfg['path']['webLib']);

// Set default time zone.
date_default_timezone_set('UTC');

// Only process data if a POST occurred.
if ($_POST)
{
    echo print_r($_FILES, true);
    if (isset($_POST['createNewEntry']))
    {
        createNewEntry($cfg, validateNewEntryData($cfg));
    }
    else if (isset($_POST['deleteEntry']))
    {
        deleteEntry($cfg);
    }
    else
    {
        displayError('No POST data provided or something else went wrong.', true);
    }
    
    // Redirect to main page.
    header( 'Location: ' . $_POST['callingScript'], true, 303 );
    exit();
}

/*
    Validates the data for the new entry, retrieved from the POST.
*/
function validateNewEntryData($cfg)
{
    $maxFieldLengths = [
        'author' => $cfg['maxAuthorFieldLength'],
        'email' => $cfg['maxEmailFieldLength'],
        'url' => $cfg['maxUrlFieldLength'],
        'subject' => $cfg['maxSubjectFieldLength'],
        'comment' => $cfg['maxCommentFieldLength'],
        'password' => $cfg['maxPasswordFieldLength']
        // 'maxFilenameLength' => (is_numeric($cfg['maxFileNameLength']) ? $cfg['maxFileNameLength'] : 64),
        // 'maxFileSize' => (is_numeric($cfg['maxFileSizeInBytes']) ? $cfg['maxFileSizeInBytes'] : 1048576)
    ];
    
    $entry = [];
    $entry['date'] = date('Y-m-d H:i');
    $entry['author'] = isset($_POST['author']) ? (string)trim($_POST['author']) : 'Anonymous';
    $entry['author'] = $entry['author'] !== '' ? $entry['author'] : 'Anonymous';
    $entry['email'] = isset($_POST['email']) ? (string)trim($_POST['email']) : '';
    $entry['url'] = isset($_POST['url']) ? (string)trim($_POST['url']) : '';
    $entry['subject'] = isset($_POST['subject']) ? (string)trim($_POST['subject']) : '';
    $entry['comment'] = isset($_POST['comment']) ? (string)trim($_POST['comment']) : '';
    $entry['password'] = isset($_POST['password']) ? (string)trim($_POST['password']) : '';
    
    // Validate author name.
    if (strlen($entry['author']) > $maxFieldLengths['author'])
        displayError('Author name cannot exceed (' . $maxFieldLengths['author'] . ') characters.', true);
    
    // Validate e-mail.
    if (strlen($entry['email']) > $maxFieldLengths['email'])
        displayError('Email address cannot exceed (' . $maxFieldLengths['email'] . ') characters.', true);
    
    
    // Validate homepage URL.
    if (strlen($entry['url']) > $maxFieldLengths['url'])
        displayError('Homepage URL cannot exceed (' . $maxFieldLengths['url'] . ') characters.', true);
    if ($entry['url'] !== '' && stripos($entry['url'], 'http://') !== 0 && stripos($entry['url'], 'https://') !== 0)
        displayError('Homepage URL must specify the protocol (http:// or https://). Please ensure your URL begins with one of these.', true);
    
    // Validate subject.
    if (strlen($entry['subject']) > $maxFieldLengths['subject'])
        displayError('Entry subject cannot exceed (' . $maxFieldLengths['subject'] . ') characters.', true);
    
    // Validate comment.
    if (strlen($entry['comment']) > $maxFieldLengths['comment'])
        displayError('Entry comment cannot exceed (' . $maxFieldLengths['comment'] . ') characters.', true);
    if (strlen($entry['comment']) > $maxFieldLengths['comment'])
        displayError('Entry comment cannot be empty.', true);
    
    // Handle word filtering.
    if (count($cfg['wordFilters']) > 0)
    {
        $filteredWords = [];
        
        foreach ($cfg['wordFilters'] as $word)
        {
            if (strpos($entry['comment'], $word) !== false)
            {
                array_push($filteredWords, $word);
            }
        }
        
        if (count($filteredWords) > 0)
        {
            switch ($cfg['wordFilterMode'])
            {
                case 'censor':
                    foreach ($filteredWords as $word)
                        $entry['comment'] = str_replace($word, str_repeat('*', strlen($word)), $entry['comment']);
                    break;
                case 'error':
                    $html = $filteredWords[0];
                    for ($i = 1; $i < count($filteredWords); $i++)
                        $html .= '<br>' . $filteredWords[$i];
                    displayError('Your comment contained certain words that cannot be posted.' . ($cfg['showFilteredWords'] ? "The words were:<br>$html<br>Please remove these words and try posting again." : ''));
                    break;
                case 'mislead':
                    displayError('');
                    break;
            }
        }
    }
    
    // Replace escaped line breaks in comment with line-break tags.
    $entry['comment'] = str_replace("\r\n", '{NEW_LINE}', $entry['comment']);
    $entry['comment'] = str_replace(array("\r", "\n"), '{NEW_LINE}', $entry['comment']);
    
    // Validate password.
    if (strlen($entry['password']) > $maxFieldLengths['password'])
        displayError('Entry password cannot exceed (' . $maxFieldLengths['password'] . ') characters.', true);
    
    // Verify user isn't a bot (if enabled).
    if ($cfg['antiBotVerificationEnabled'])
    {
        // Validate POST data.
        if (
            !isset($_POST['verification-question-id'])
         || !is_numeric($_POST['verification-question-id'])
         || !isset($_POST['verification-answer'])
        )
        {
            displayError('Could not perform anti-bot verification.', true);
        }
        
        // Get and validate user answer.
        $userAnswer = (string)trim($_POST['verification-answer']);
        if ($userAnswer === '')
            displayError('Please provide an answer to the anti-bot verification question.', true);
        
        // Get actual answer to question.
        $questions = include(dirname(__FILE__) . '/../user-verification-questions.php');
        $realAnswer = $questions[(int)$_POST['verification-question-id']][1];
        
        // Check if user's answer was correct.
        if ($cfg['antiBotVerificationIsCaseSensitive'] && $userAnswer !== $realAnswer)
            displayError('Invalid answer to the anti-bot verification question. Please try again. If you believe there to be an error please contact the server administrator.', true);
        else if (strcasecmp($userAnswer, $realAnswer) !== 0)
            displayError('Invalid answer to the anti-bot verification question. Please try again. If you believe there to be an error please contact the server administrator.', true);
    }
    
    // Ensure all fields are strings, and also remove empty fields.
    foreach ($entry as $key => $val)
    {
        // Re-store as string if value is not a string.
        $entry[$key] = !is_string($val) ? (string)$val : $val;
        
        // Remove if value is an emptry string.
        if ($val === '')
            unset($entry[$key]);
    }
    
    return $entry;
}

/*
    Saves a new entry to the database while also incrementing the total entry
    counter.
*/
function createNewEntry($cfg, $entry)
{
    // Make directories if they don't exist
    if (!is_dir($cfg['path']['db']) && !file_exists($cfg['path']['db']))
        mkdir($cfg['path']['db']);
    if (!is_dir($cfg['path']['entries']) && !file_exists($cfg['path']['entries']))
        mkdir($cfg['path']['entries']);
    
    // Retrieve table of contents from disk. Create new file if it doesn't exist.
    $toc = array();
    if (file_exists($cfg['file']['toc']))
        $toc = json_decode_ex(file_get_contents($cfg['file']['toc']));
    else
        file_put_contents($cfg['file']['toc'], json_encode_ex($toc));
    
    // Retrieve entry counter from disk. Create new file if it doesn't exist.
    $entryId = 0;
    if (file_exists($cfg['file']['entryCount']))
        $entryId = file_get_contents($cfg['file']['entryCount']);
    else
        file_put_contents($cfg['file']['entryCount'], $entryId);
    
    if (!is_numeric($entryId))
        displayError('Could not retrieve entry counter or data is bad.');
    
    // Increment entry counter & save new value to disk.
    $entryId = intval($entryId) + 1;
    file_put_contents($cfg['file']['entryCount'], $entryId);
    
    // Save new entry JSON file.
    file_put_contents($cfg['path']['entries'] . "$entryId.json", json_encode_ex($entry, JSON_PRETTY_PRINT));
    
    // Update the table of contents thread index.
    array_unshift($toc, $entryId);
    
    // Save updated table of contents.
    file_put_contents($cfg['file']['toc'], json_encode_ex($toc, JSON_PRETTY_PRINT));
}

/*
    Deletes an entry from the database.
*/
function deleteEntry($cfg)
{
    // Validate data.
    $entryId = isset($_POST['entryId']) ? (int)$_POST['entryId'] : 0;
    $userPassword = isset($_POST['password']) ? (string)$_POST['password'] : '';
    
    if (!is_numeric($entryId) || $entryId <= 0)
        displayError('Could not delete entry. Please ensure you selected a entry by clicking the entry\'s [Delete] button.', true);
    
    if ($userPassword === '')
        displayError("Could not delete entry number ($entryId). Please ensure you entered a password. Please note that if you did not set a password when creating this entry then it is not eligible for deletion.", true);
    
    // Retrieve table of contents.
    $toc = json_decode_ex(file_get_contents($cfg['file']['toc']));
    
    // Retrieve entry data.
    $entry = json_decode_ex(file_get_contents($cfg['path']['entries'] . $entryId . '.json'));
    $entry['password'] = isset($entry['password']) ? $entry['password'] : '';
    
    // Verify entry password is correct.
    if ($userPassword !== $entry['password']
     && ($cfg['adminPasswordHash'] !== '' && !password_verify($userPassword, $cfg['adminPasswordHash'])))
    {
        displayError("Could not delete entry number ($entryId) as the provided password did not match what was in the database.", true);
    }
    
    // Remove entry from the table of contents.
    $entryPos = array_search($entryId, $toc);
    // If entry exists in ToC, remove it.
    if ($entryPos !== false)
    {
        unset($toc[$entryPos]);
        $toc = array_values($toc);
    }
    
    // Save updated table of contents.
    file_put_contents($cfg['file']['toc'], json_encode_ex($toc, JSON_PRETTY_PRINT));
    
    // Delete the entry file from disk.
    unlink($cfg['path']['entries'] . $entryId . '.json');
}

?>
