# MilkGB
MilkGB is a simple, flat-file, easy-to-deploy guestbook system. Because we all know how relevant those are these days.

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
### Configuration

## Notes