# Welcome to PHP Download Tracker #

PHP Download Tracker is a single PHP script that helps you track downloads from your site.

You configure the script with an offline (or protected) folder that contains files available for download.

You can also configure a few display settings, including the ability to include a simple CAPTCHA

The script will present the user with a list of available files.

When the user selects a file, the request is logged and then the download window pups up in the user's browser for them to save the file.


## Downloading ##

You can find the latest version at the [Project Homepage](http://github.com/iNamik/PHP-Download-Tracker)


## Installing ##

* Copy the PHP script into a web-accessible folder.  The default script name is 'index.php' so that the webserver will automatically load the file when users enter the public download folder.  You can name it something else if you plan on linking to it directly within your site.

* Edit the file and check the "Config Section" for customizable options.

* If you want logging enabled, make sure that the configured 'logDir' is writable by the webserver.

* You should probably ensure that the configured directories are either Not web-accessible or protected against browser access.  To protect a directory using apache, create a file inside the directory called ".htaccess" and give it the following contents:

        order deny, allow
        deny from all

*  Edit the HTML at the bottom of the file.  There is a bit of embedded PHP within the HTML, but you should be able to work around it quite easily.


## Project Influence ##

This project is a heavy modification (i.e. near-complete rewrite) of [DocTrax's Download Manager](http://freshmeat.net/projects/dlmanager/) (link good as of 2008/03/02)
