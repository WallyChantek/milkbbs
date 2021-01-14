<?php

namespace milkgb;
use Exception;

function loadSystemData()
{
    $data = [];
    
    $data['softwareVersion'] = '0.70';
    
    $fsLib = realpath(dirname(__FILE__));
    $webLib = str_replace($_SERVER['DOCUMENT_ROOT'], '', $fsLib);

    $fsLib = str_replace('\\', '/', $fsLib);
    $fsLib = (substr($fsLib, -1) !== '/' ? $fsLib . '/' : $fsLib);
    $fsLib = str_replace('function/', '', $fsLib);
    
    $webLib = str_replace('\\', '/', $webLib);
    $webLib = (substr($webLib, -1) !== '/' ? $webLib . '/' : $webLib);
    $webLib = str_replace('function/', '', $webLib);
    
    // Paths
    $data['path']['fsLib'] = $fsLib;
    $data['path']['webLib'] = $webLib;
    $data['path']['db'] = $fsLib. 'db/';
    $data['path']['entries'] = $fsLib . 'db/entries/';
    $data['path']['webFiles'] = $webLib . 'db/files/';
    $data['path']['fsFiles'] = $fsLib . 'db/files/';
    $data['path']['avatars'] = $webLib . 'db/avatars/';
    
    // File locations
    $data['file']['originFile'] = $_SERVER['PHP_SELF'];
    $data['file']['toc'] = $fsLib . 'db/toc.json';
    $data['file']['entryCount'] = $fsLib . 'db/entrycount.txt';

    // HTML & JS Templates
    $data['html']['entryForm'] = file_get_contents($fsLib . 'template/posting-form.html');
    $data['html']['previewForm'] = file_get_contents($fsLib . 'template/posting-form-preview.html');
    $data['html']['entry'] = file_get_contents($fsLib . 'template/entry.html');
    $data['html']['entryError'] = file_get_contents($fsLib . 'template/entry-error.html');
    $data['html']['entryManagement'] = file_get_contents($fsLib . 'template/entry-management.html');
    $data['html']['footer'] = file_get_contents($fsLib . 'template/footer.html');
    $data['html']['javascript'] = file_get_contents($fsLib . 'script/milkgb.js');

    // Strip linebreaks and tabs from templates
    foreach ($data['html'] as $name => $html)
    {
        $html = str_replace(array("\r", "\n"), '', $html);
        $html = str_replace('    ', '', $html);
        $data['html'][$name] = $html;
    }

    return $data;
}

function validateUserData($cfg)
{
    if (!isset($cfg['adminPasswordHash']))
        displayError('Admin password not set.');
    
    $defaults = [
        'devMode' => false,
        'demoMode' => false,
        'antiBotVerificationEnabled' => true,
        'antiBotVerificationIsCaseSensitive' => false,
        'maxAuthorFieldLength' => 64,
        'maxEmailFieldLength' => 256,
        'maxUrlFieldLength' => 256,
        'maxSubjectFieldLength' => 72,
        'maxCommentFieldLength' => 2048,
        'maxPasswordFieldLength' => 32,
        'maxEntriesPerPage' => 3,
        'maxNavigationPageLinks' => 5,
        'showSoftwareStamp' => true,
        'timezone' => 'America/New_York',
        '24HourClock' => true,
        'entryDeletingEnabled' => true,
        'formattingColors' => [
            'red' => '#ff0000',
            'green' => '#00ff00',
            'blue' => '#0000ff'
        ],
        'wordFilterMode' => 'censor',
        'wordFilters' => [
            'apple',
            'orange'
        ],
        'showFilteredWords' => true
    ];
    
    foreach($defaults as $k => $v) {
        if (!isset($cfg[$k])) {
            $cfg[$k] = $v;
        }
    }
    
    return $cfg;
}

/**
    This function serves as a shorthand helper function for json_decode().
    It will always returns an array, and it'll always throw an error if the
    JSON string is malformed.
**/
function json_decode_ex($json)
{
    
    $data = json_decode($json, true);
    
    if (JSON_ERROR_NONE !== json_last_error()) {
        throw new Exception(
            'json_decode error: ' . json_last_error_msg()
          . '<br>json string:<br>' . $json
        );
    }
    
    return $data;
    
    
}

/**
    This function serves as a shorthand helper function for json_decode().
    It will always throw an error if some sort of error occurred.
**/
function json_encode_ex($arr, $options = 0)
{
    $data = json_encode($arr, $options);
    
    if (JSON_ERROR_NONE !== json_last_error()) {
        throw new Exception(
            'json_encode error: ' . json_last_error_msg()
          . '<br>array data:<br>' . print_r($arr, true)
        );
    }
    
    return $data;
}

?>