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
$data['path']['originFile'] = $_SERVER['PHP_SELF'];
$data['path']['threads'] = $fsLib . 'db/threads/';

$data['file']['toc'] = $fsLib . 'db/toc.json';
$data['file']['postCount'] = $fsLib . 'db/postcount.txt';

// HTML Templates
$data['postTemplate'] =
    '<div class="milkbbs-entry" id="{POST_ID}">'
  .     '<div>'
  .         '<div class="milkbbs-post-name"><a href="mailto:{POST_EMAIL}">{POST_AUTHOR}</a>&nbsp;<a class="milkbbs-post-url" href="{POST_URL}">[URL]</a></div>'
  .         '<div class="milkbbs-post-number">No. {POST_ID}&nbsp;<a href="{POST_ANCHOR_LINK}">#</a></div>'
  .     '</div>'
  .     '<div class="milkbbs-post-subject">{POST_SUBJECT}</div>'
  .     '<div class="milkbbs-post-comment">{POST_COMMENT}</div>'
  .     '<div>'
  .         '<div class="milkbbs-post-date">{POST_DATE}</div>'
  .         '<div class="milkbbs-post-delete">{POST_DELETE}</div>'
  .     '</div>'
  . '</div>'
;

return $data;

?>
