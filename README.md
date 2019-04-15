# VacationPanel
PHP/HTML/Bootstrap full web app made at NETLOR SAS (during a 2 weeks internship in high school) in 2015. Free of content from this enterprise.

![GitHub](https://img.shields.io/github/license/fanfan54/VacationPanel.svg) ![GitHub repo size](https://img.shields.io/github/repo-size/fanfan54/VacationPanel.svg) ![Maintenance](https://img.shields.io/maintenance/no/2019.svg) ![GitHub stars](https://img.shields.io/github/stars/fanfan54/VacationPanel.svg?style=social)

![Screenshots](https://github.com/fanfan54/VacationPanel/raw/master/screenshots.png "Screenshots")

## What is VacationPanel?
VacationPanel is a full-featured intranet application (you'll need to log in with your account in order to use it) that allows enterprises to manage vacations/paid holidays for their staff.
It uses PHP on the Apache2 web server (tested on PHP 5.3.10) and a MySQL database (tested on MySQL 5.5.37) as a backend, and a simple HTML5 front-end (using jQuery plugins, AJAX for some features), styled with Twitter Bootstrap.
The front-end, and some parts of the code, are in french.

I wrote it during a 2 weeks internship at [NETLOR SAS](https://www.netlor.fr), a company that develops business applications, e-commerce websites, B2B and B2C solutions since 2000.
They asked me to do it in order to acquire web development skills, and to (maybe) include my code in their web platform/framework/CMS, DIMS (Dynamic Information Management System).

This code is **very** old, I wasn't at EPITECH and had no formation (I used various MOOCs and tutorials on the web to write this code, that's all), so there can be some errors or bad code.

**I never finished it, many functions are missing/not implemented. Feel free to make pull request to add some new code! :-)**

## Download
[Download the repository (zip archive)](https://github.com/fanfan54/VacationPanel/archive/master.zip)

## How to use it?
1. Install the prerequisites:

* Apache2 (any version compatible with PHP 5.3)
* PHP 5.3
* MySQL Server Community (5.5.37)

2. Download the VacationPanel repository from GitHub

3. Import the `vacationpanel.sql` file to your database

4. Fill your MySQL server informations manually in the `login.class.php` file *(for now, the `config.php` file is not used in the code)*

5. Go to the index.php page on your web server

6. Log in to the built-in user account:

* Identifiant: francoislefevre
* Mot de passe (password): stagiaire1

7. You'll be redirected to the sign-up page. Create any account you want!

## User roles

There are 3 roles available for the accounts, with different permissions:

* admin : he can go to every module, he's the only one that has access to system settings (Paramètres système). He can create "gestionnaire" and/or "employé" accounts.
* gestionnaire ("manager" in french): he can manage vacations ("Gérer les congés"), manage the staff he's affected to (in "Gérer les employés"), and create "employé" accounts.
* employé ("staff/employee" in french, referenced in code as "worker"): he can only ask for vacations (in "Demander mes congés") on the datepicker

Please note that a user can have multiple roles, and that there can be as many users as you want. You can event create multiple "admin" accounts.

## Modules

5 modules (pages) are available in the application (not all of them have been implemented during my internship), defined in the `vacationpanel.class.php` file and shown in the tabs bar on top of the page:

* vacationask / "Demander mes congés" (available for "employé" accounts)
* vacationmanage / "Gérer les congés" (available for "manager" accounts)
* manageusers / "Gérer les employés" (for "manager" accounts) or "Gérer les gestionnaires" (for "admin" accounts)
* registerusers / "Ajouter des employés" (for "manager" accounts) or "Ajouter des gestionnaires" (for "admin" accounts) (fully implemented)
* systemsettings / "Paramètres système" (available for "admin" accounts)

Note: there is also an automatic installer module I started (in the `_install.php` file), but I didn't have time to finish it, it's not working (for now), that's why I didn't talk about it.

## Database structure

The default name for the database is "vacationpanel".
It contains two tables:

### dims_mod_vacationpanel_users
Contains 7 columns:

* id
* user (login, between 1 and 255 chars)
* password
* relog_time (timestamp of the last time when the account has been modified (password changed, etc). If an account was logged in (using PHP session cookies) before this time, it has to log back in)
* role ("worker", "manager", "admin", there can be multiple roles separated by semicolons, for example "worker;manager;sysadmin" is a valid input)
* days (number of vacation days available, only used for "worker" accounts)
* isEnabled (boolean, true if the user can log in to his account)

## dims_mod_vacationpanel_vacations
Contains 5 columns:

* id
* userid
* date (all the dates included in the vacation, comma-separated)
* comment (some text that can be saved with the vacation)
* state ("allowed", "asked", "revoked", or "ended")

## License, and libraries I used

I chose to distribute this code under the GNU General Public License v3.0, as it contains no confidential or non-free content from NETLOR.
This project includes some piece of code from the Internet, and some libraries. All of the licenses they use are GPL3-compatible:

* [php-login-one-file](https://github.com/panique/php-login-one-file/) by **panique** (MIT license), (I use `libraries/password_compatibility_library.php` and `index.php` renamed to `login.class.php`, and I added some code to it)
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE

* [Déterminer rapidement si un jour est férie (fêtes mobiles incluses) - PHP SOURCES](https://phpsources.net/code/php/date-heure/382_determiner-rapidement-si-un-jour-est-feriefetes-mobiles-incluses) by **Olravet** (free to use and download, no license specified) (`checkWorkingDay_api.php` with some code I added)
* [Bootstrap v3.3.5](https://github.com/twbs/bootstrap) by **Twitter** (MIT license)
* [Datepicker for Bootstrap v1.5.0-dev](https://github.com/eternicode/bootstrap-datepicker) by **eternicode** (Apache License v2.0)
* [Icomoon icon pack (Free Version)](https://icomoon.io/) by **Icomoon** (GPL license)
* [Moment.js](https://github.com/moment/moment/) by **moment** (MIT license)
* [Moment-ferie-fr](https://github.com/datakode/moment-ferie-fr) by **Damien Labat** (no license specified)
* [jQuery v2.1.4](https://github.com/jquery/jquery) by **jquery** (MIT license)
* [jQuery.initialize](https://github.com/AdamPietrasiak/jquery.initialize) by **pie6k** (MIT license)
* [Glyphicons Halflings](https://www.glyphicons.com) (free to use with Twitter Bootstrap)
