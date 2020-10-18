<?php

namespace milkgb;
use Exception;

/*
    This function gets called auotmatically when an error of any type occurs.
    It will raise an exception, which will then display an error and halt
    execution if it is not caught and handled accordingly.
*/
function customErrorHandler($errno, $errstr, $errfile, $errline)
{
    // Escape error string since we're going to display it as HTML.
    $errstr = htmlspecialchars($errstr);
    
    // Build user-friendly markup for reading error details.
    $msg = "<div>Error Details:</div>"
         . "<div>Error</div>"
         . "<div>$errstr</div>"
         . "<div>File</div>"
         . "<div>$errfile</div>"
         . "<div>Line</div>"
         . "<div>$errline</div>"
    ;
    
    throw new Exception($msg);
}

/*
    This function gets called automaticaly when an exception is thrown. It will
    display an error and halt execution if it is not caught and handled
    accordingly.
*/
function customExceptionHandler($exception)
{
    displayError($exception->getMessage());
}

?>
