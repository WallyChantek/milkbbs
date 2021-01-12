# MilkGB
MilkGB is a simple, flat-file, easy-to-deploy guestbook system. Because we all know how relevant those are these days.<br>I wouldn't advise using this yet as I still have a good bit of testing to do.
<p align="center"><img src="https://raw.githubusercontent.com/kevinmaddox/yogurtgallery/main/images/img01.png" alt="YogurtGallery Preview"/></p>

## Demo
[MilkGB Demo](https://www.kevinmaddox.com/demo/milkgb/milkgb.html)<br>
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
| devMode | boolean | false | Blah |
| adminPasswordHash | string | N/A | Blah |
| antiBotVerificationEnabled | boolean | true | Blah |
| antiBotVerificationIsCaseSensitive | boolean | false | Blah |
| maxAuthorFieldLength | integer | 64 | Blah |
| maxEmailFieldLength | integer | 256 | Blah |
| maxUrlFieldLength | integer | 256 | Blah |
| maxSubjectFieldLength | integer | 72 | Blah |
| maxCommentFieldLength | integer | 2048 | Blah |
| maxPasswordFieldLength | integer | 32 | Blah |
| maxEntriesPerPage | integer | 10 | Blah |
| maxNavigationPageLinks | integer | 10 | Blah |
| showSoftwareStamp | boolean | true | Blah |
| timezone | string | 'America/New_York' | Blah |
| 24HourClock | boolean | true | Blah |
| entryDeletingEnabled | boolean | true | Blah |
| formattingColors | array[string => string] | red, green, blue colors | Blah |
| wordFilterMode | string | censor | Blah |
| wordFilters | array[string] | empty array | Blah |
| showFilteredWords | boolean | true | Blah |
## Notes