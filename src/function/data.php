<?php

$data = [];

$fsLib = realpath(dirname(__FILE__));
$webLib = str_replace($_SERVER['DOCUMENT_ROOT'], '', $fsLib);

$fsLib = str_replace('\\', '/', $fsLib);
$webLib = str_replace('\\', '/', $webLib);

$fsLib = (substr($fsLib, -1) !== '/' ? $fsLib . '/' : $fsLib);
$webLib = (substr($webLib, -1) !== '/' ? $webLib . '/' : $webLib);

$fsLib = str_replace('function/', '', $fsLib);
$webLib = str_replace('function/', '', $webLib);

$data['path']['fsLib'] = $fsLib;
$data['path']['webLib'] = $webLib;
$data['path']['db'] = $fsLib. 'db/';
$data['path']['threads'] = $fsLib . 'db/threads/';

$data['file']['originFile'] = $_SERVER['PHP_SELF'];
$data['file']['toc'] = $fsLib . 'db/toc.json';
$data['file']['postCount'] = $fsLib . 'db/postcounter.txt';
$data['file']['js'] = $fsLib . 'script/milkbbs.js';

// HTML Templates
$data['template']['form'] = file_get_contents($fsLib . 'template/posting-form.html');
$data['template']['post'] = file_get_contents($fsLib . 'template/post.html');
$data['template']['postManagement'] = file_get_contents($fsLib . 'template/post-management.html');
$data['template']['footer'] = file_get_contents($fsLib . 'template/footer.html');

// Strip linebreaks and tabs from templates
foreach ($data['template'] as $name => $template)
{
    $template = str_replace(array("\r", "\n"), '', $template);
    $template = str_replace('    ', '', $template);
    $data['template'][$name] = $template;
}

return $data;

?>
