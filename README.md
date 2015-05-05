
Laravel 5.0 的可以看看这个扩展（没用过，不过貌似不错的样子）。 https://github.com/liaol/socialite-cn


2015-05-05更新： 将Sina和QQ的getUserInfo方法统一返回为对象格式。如果你不想改你现在的代码，你可以使用 ```  "hardywen/social-login": "v0.1"```版本。


social-login
============

Sina and QQ login


Install:

1.Add hardywen/social-login to composer.json
```
"require": {
  "hardywen/social-login": "v0.2"
}
```

2.Use composer to install this package.

```
$ composer update
```

3.Publish the config files.
```
$ php artisan config:publish hardywen/social-login
```

### Registering the Package

Register the service provider within the ```providers``` array found in ```app/config/app.php```:
```php
'providers' => array(
	// ...
	
	'Hardywen\SocialLogin\SocialLoginServiceProvider'
)
```

Add an alias within the ```aliases``` array found in ```app/config/app.php```:
```php
'aliases' => array(
	// ...
	
	'SocialLogin' =>'Hardywen\SocialLogin\Facade\SocialLogin',
)
```

###Config

```php
return array(
    //services APPID  APPKEY  and so on

    'services' => array(
        'QQ' => array(
            'APP_ID' => 'xxxx', //Your app id from you App
            'APP_KEY' => 'xxxx',
            'CALL_BACK' => '', //blank means it will call back to where you call login() function
            'SCOPE' => '',
        ),
        
        'Sina' => array(
            'APP_KEY' => 'xxx',//Your app id from you App
            'APP_SERCET' => '',
            'CALL_BACK' => '', //blank means it will call back to where you call login() function
        ),
    ),
);
```


###Usage

login:
```php
SocialLogin::consumer('QQ')->login(); // call this function to jump to QQ login page.
```

And after you login QQ, it will return to the callback url with "code" and "state" params

Then in your callbak page call this function :  
```php
SocialLogin::consumer('QQ')->callBack(); //This will get access_token and opend_id for you.
```

Now you can use 
```php
SocialLogin::consumer('QQ')->getUserInfo();
```
to get the login user info.

