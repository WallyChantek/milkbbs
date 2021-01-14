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

/*
    Outputs an error to the user and halts further execution.
*/
function displayError($msg, $showErrorToUser = false, $showContactNote = true)
{
    $msgHtml = false;
    $webLib = '';
    $html = '';
    
    /*
       Determine if error message should be shown.
       We show the error if:
       - We've specified it should be always shown, even to the user.
       - Developer mode is enabled.
       - Developer mode option hasn't had a chance to be defined yet (in this
         case, something failed very early on and a debug message should
         absolutely be shown).
    */
    if (
        $showErrorToUser
     || (defined(__namespace__ . '\DEV_MODE') && namespace\DEV_MODE)
     || !defined(__namespace__ . '\DEV_MODE')
    )
    {
        $msgHtml = '<div class="milkgb-error-details">' . $msg . '</div>';
    }
    
    // If this is the main page, then insert the error message HTML as a block
    // to fit in with the rest of the page content.
    if (!namespace\STANDALONE_PAGE)
    {
        // Build HTML.
        $html = '<div class="milkgb">'
              .     '<div class="milkgb-error-container">'
              .         '<div class="milkgb-error-title">Error</div>'
              . ($showContactNote ? '<div class="milkgb-error-message">An error occurred. Please contact the server administrator if this issue persists.</div>' : '')
              .         $msgHtml
              .     '</div>'
              . '</div>'
        ;
    }
    // If this is the processing page, then inser not only the error message
    // HTML, but also the necessary markup to construct a stand-alone page.
    else
    {
        // Determine location of milkGB for linking the stylesheet.
        if (defined(__namespace__ . '\WEB_LIB'))
        {
            $webLib = namespace\WEB_LIB;
        }
        
        // Build HTML.
        $html = '<!DOCTYPE html>'
              . '<html lang="en">'
              .     '<head>'
              .         '<title>milkBBS</title>'
              .         '<meta charset="utf-8">'
              .         ($webLib ? '<link rel="stylesheet" href="' . namespace\WEB_LIB . 'style/milkgb.css">' : '')
              .     '</head>'
              .     '<body>'
              .         '<div class="milkgb">'
              .             '<div class="milkgb-error-container milkgb-standalone-error-container">'
              .                 '<div class="milkgb-error-title">Error</div>'
              . ($showContactNote ? '<div class="milkgb-error-message">An error occurred. Please contact the server administrator if this issue persists.</div>' : '')
              .                 $msgHtml
              .                 '<div class="milkbbs-error-return-link"><a href="javascript:history.back()">[Return]</a></div>'
              .             '</div>'
              .         '</div>'
              .     '</body>'
              . '</html>'
        ;
    }
    
    echo $html;
    
    exit();
}

?>
