# MilkGB
Note: This is still heavily a work-in-progress and you are not advised to use this yet.<br><br>
MilkGB is a simple, flat-file, easy-to-deploy guestbook system. Because we all know how relevant those are these days. Still, you could use it as a general comment/feedback form.
<p align="center"><img src="https://raw.githubusercontent.com/kevinmaddox/milkgb/main/images/img01.png" alt="MilkGB Preview"/></p>

## Installation
1. Copy/paste the `src/` directory to wherever you want to store MilkGB on your web server. This accounts for one deployment.
1. Edit the configuration file `user-config.php` to configure MilkGB to suit your needs.
1. (Optional) Edit the configuration file `user-verification-questions.php` and add your own verification questions (if desired, and if using anti-bot verification [which you should be])
1. On your HTML page, where you want to insert MilkGB into the page, add the following block:
```
<?php

include('src/autoload.php');
milkgb\loadMilkGB();

?>
```
## Specification
### Configuration (user-config.php)
| Option | Type | Def. Value | Description |
| --- | --- | --- | --- |
| devMode | boolean | false | Enables error details. Set to false when ready to deploy. |
| demoMode | boolean | false | Disables posting so that the core board functionality can be shown off without having to worry about needing to moderate it. |
| adminPasswordHash | string | N/A | The global password used for deleting any posts. Store as previously-hashed value generated via `password_hash("your_pass", PASSWORD_DEFAULT)`. |
| antiBotVerificationEnabled | boolean | true | Forces user to fill out a verification question when posting. |
| antiBotVerificationIsCaseSensitive | boolean | false | If enabled, case sensitivity will matter when user fills out an answer ("APPLE" will NOT work if answer is "apple"). Recommended to be false. |
| maxAuthorFieldLength | integer | 64 | The maximum character length for the poster's name. |
| maxEmailFieldLength | integer | 256 | The maximum character length for the poster's e-mail address. |
| maxUrlFieldLength | integer | 256 | The maximum character length for the poster's website URL. |
| maxSubjectFieldLength | integer | 72 | The maximum character length for the post subject. |
| maxCommentFieldLength | integer | 2048 | The maximum character length for the post message/content. |
| maxPasswordFieldLength | integer | 32 | The maximum character length for the post password (used for deletion by user). |
| maxEntriesPerPage | integer | 10 | The maximum number of posts to show on a single page. |
| maxNavigationPageLinks | integer | 10 | How many page links should be displayed at one time in the page navigation bar. Prevents excessively-long navigation bars in populated guestbooks. |
| showSoftwareStamp | boolean | true | Displays a software watermark at the bottom of the page (`Running milkGB ver x.xx`). |
| timezone | string | 'America/New_York' | The server's timezone. This should be a PHP-supported timezone string. |
| 24HourClock | boolean | true | Whether the post time should be in `01:00-12:00 AM/PM` or `00:00-23:00`. |
| entryDeletingEnabled | boolean | true | Allows users to delete their posts via a password set when creating the post. |
| formattingColors | array[string => string] | red, green, blue colors | Formatting colors the user can use in their post message. The key is the color name that appears to the user and the value is the actual color used in the message (e.g. `'red' => '#ff0000'`). |
| wordFilterMode | string | censor | What kind of wordfiltering should be used in comments. Choices are `censor`, `error`, or `mislead`. `censor` will replace words with asterisks, `error` will tell the user there was a filtered word when attempting to post, and `mislead` will throw an error when the user attempts to post, but won't tell them the reason, hopefully causing them to move on from the site (used to potentially filter out abusive/worthless posters). If you don't want to filter any words, simply don't add any in `wordFilters`. |
| wordFilters | array[string] | empty array | Which words should be filtered in post comments. |
| showFilteredWords | boolean | true | Whether the user should be notified which words were filtered when `wordFilterMode` is set to `error`. |
## Todo
* Thoroughly bug test everything.
* Better CSS organization.
* Better-looking error messages (and stylization in general).
* Code clean-up.