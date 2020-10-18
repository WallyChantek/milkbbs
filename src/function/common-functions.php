<?php

namespace milkgb;

function loadSystemData()
{
    $data = [];
    
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
    
    // File locations
    $data['file']['originFile'] = $_SERVER['PHP_SELF'];
    $data['file']['toc'] = $fsLib . 'db/toc.json';
    $data['file']['entryCount'] = $fsLib . 'db/entrycount.txt';

    // HTML Templates
    $data['html']['form'] = file_get_contents($fsLib . 'template/posting-form.html');
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
    // TODO: Validate all user configuration data
    
    return $cfg;
}

/**
    This function serves as a shorthand helper function for json_decode().
    It will always returns an array, and it'll always throw an error if the
    JSON string is malformed.
**/
function json_decode_ex($json)
{
    return json_decode($json, true, $depth=512, JSON_THROW_ON_ERROR);
}

/**
    This function serves as a shorthand helper function for json_decode().
    It will always throw an error if some sort of error occurred.
**/
function json_encode_ex($arr)
{
    return json_encode($arr, JSON_THROW_ON_ERROR);
}

?>