# Interact PHP - simple comment system

## Purpose

Interact is a simple PHP open-source comment system that can be added to any website in minutes to improve interactivity and user's involvement. Client-side, it is very lightweight (<6KB CSS and JS combined) and intuitive (no logins, no cookies,...). Server-side, it is very easy to setup, manage and even customize to fit your needs.

![Interact PHP with the modern interface](http://charleslabs.fr/projects/20180817_Interact_PHP/modern.png)

Similarly to its older brother [HashOver](http://tildehash.com/?page=hashover), Interact does not use an SQL database but stores comments as XML files. Many third-party comment systems like Disqus, IntenseDebate, Livefyre, Facebook Comments and Google+ Comments, suffer from these problems and impose them and many restrictions onto their users, meaning you and your website's visitors.

## Features

* Fast and convenient for your users with responsive design.
* Very simple setup: no database to configure, just install it and add 2 lines of PHP where you want your comment section to be.
* Customizable style and behavior to match the look & feel of your website.
* Anti-Spam system using Google's reCAPTCHA v2 (optional).
* Pure JS (no JQuery required).
* Secure by design.

## Installation and setup

1. Download [Interact on GitHub](https://github.com/CGrassin/interact_php).
2. Extract under your website's root.
3. Customize Interact's behavior to your needs by editing the *Interact_PHP/settings.php* file.
4. Give the directory that will contain the comments 777 permission (`chmod 777 path/to/Interact_PHP/Comments`).

Hurray, Interact is ready to go! To add a comment section to a page, just insert the following PHP:
```php
<?php 
    include_once($_SERVER['DOCUMENT_ROOT'].'/lib/Interact_PHP/Interact_PHP.php');
    \Interact_PHP\Interact_PHP(); 
?>
``` 

## Parameters and cutomization

All of the parameters of Interact are in the *settings.php* file. There are a lot of comments to assist you in the configuration process.

If you want to add your own style, copy an existing to get started... and commit it on GitHub if you want to contribute to Interact's ongoing developpement!

![Interact PHP with various CSS](http://charleslabs.fr/projects/20180817_Interact_PHP/themes.png)

## Troubleshooting

This section describes the various problems you may encounter while installing/using Interact. If you don't find a solution here, please add a GitHub issue.

* **I can't add comments**

You should check the permissions of the folder containing the comments (*Interact/Comments* by default). The PHP user must be able to write there. Setting 777 works (`chmod -R 777 Comments`).
For some reason, the libxml PHP library was not enabled on PHP7 on one of my servers. Installing it fixed the issue (`apt-get install php7.0-xml`, reboot when completed).

* **The CSS does not load**

The path of the library relative to the root of your website is probably incorrect. Make sure it matches with the value in *settings.php*. By default, it is */lib/Interact*.

* **I am getting reCAPTCHA errors even after checking the box**

You need to make sure that your reCAPTACHA public and secret keys are correct. Interact uses reCAPTCHA v2. More info at https://developers.google.com/recaptcha/docs/display

## Future features for Interact PHP v2

* A simple admin interface to manage comments
