social-login
============

Sina and QQ login


Install:

1. Add   "hardywen/social-login": "dev-master"   to composer.json

2.Composer run: composer update 

3.Artisan run : php artisan config:publish hardywen/social-login

4.Add  'Hardywen\SocialLogin\SocialLoginServiceProvider' to providers array in app/config/app.php

5. Add an alias to aliases array in app/config/app.php like : 'SocialLogin'     =>'Hardywen\SocialLogin\Facade\SocialLogin',


Usage:

login:

SocialLogin::consumer('QQ')->login(); // call this function to jump to QQ login page.

And after you login QQ, it will return to the callback url with "code" and "state" params

Then

in your callbak page call this function :  

SocialLogin::consumer('QQ')->callBack(); //This will get access_token and opend_id for you.

Now you can use 

SocialLogin::consumer('QQ')->getUserInfo()  

to get ther login user info.

