<?php
if ($_POST) {
   // Execute code (such as database updates) here.
    error_log('yeah!');
    
   // Redirect to this page.
   header( 'Location: ' . dirname($_SERVER['PHP_SELF']), true, 303 );
   exit();
}
?>
