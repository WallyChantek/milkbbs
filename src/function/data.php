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

// HTML Templates
$data['template']['form'] =
    '<form method="post" action="{PROCESSING_SCRIPT}">'
  . '<table class="milkbbs-posting-form">'
  . '<tr><td>Name</td><td><input name="name" type="text" placeholder="Anonymous"></td>'
  . '<tr><td>Email</td><td><input name="email" type="text"></td>'
  . '<tr><td>Homepage</td><td><input name="url" type="text"></td>'
  . '<tr><td>Subject</td><td><input name="subject" type="text"></td>'
  . '<tr><td>Comment</td><td><textarea name="comment"></textarea></td>'
  . '<tr><td>Password</td><td><input name="password" type="text" placeholder="(optional, for post deletion)"></td>'
  . '{VERIFICATION}'
  . '<tr><td colspan="2">'
  .     '<input name="parentThreadId" type="hidden" value="{PARENT_THREAD_ID}">'
  .     '<input name="callingScript" type="hidden" value="{ORIGIN_FILE}">'
  .     '<input type="submit">'
  . '</td></tr>'
  . '</table>'
  . '</form>'
;

$data['template']['post'] =
    '<div class="milkbbs-entry" id="{POST_ID}">'
  .     '<div>'
  .         '<div class="milkbbs-post-name"><a href="mailto:{EMAIL}">{AUTHOR}</a>&nbsp;<a class="milkbbs-post-url" href="{URL}">[URL]</a></div>'
  .         '<div class="milkbbs-post-number">No. {POST_ID}&nbsp;<a href="{ANCHOR}">#</a></div>'
  .     '</div>'
  .     '<div>'
  .         '<div class="milkbbs-post-subject">{SUBJECT}</div>'
  .     '</div>'
  .     '<div>'
  .         '<div class="milkbbs-post-comment">{COMMENT}</div>'
  .     '</div>'
  .     '<div>'
  .         '<div class="milkbbs-post-date">{DATE}</div>'
  .         '<div>'
  .             '<span class="milkbbs-post-report">{REPORT}</span>&nbsp;'
  .             '<span class="milkbbs-post-delete">{DELETE}</span>'
  .         '</div>'
  .     '</div>'
  . '</div>'
;

$data['template']['footer'] =
    '<div class="milkbbs-footer">'
        . '<div class="milkbbs-footer-pages">[<]{PAGES}[>]</div>'
        . '<div class="milkbbs-footer-info">[Admin]</div>'
        . '<div class="milkbbs-footer-info">Running milkBBS v0.54</div>'
    . '</div>'

;

return $data;

?>
