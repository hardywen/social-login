<?php

return array(

    //当auto_logout设置为true时，调用Auth::logout()方法或者其他方式触发auth.logout事件后，Social-login会清空所有当前登录的第三方账号（包括Sina 和 QQ）。如果设置为false，你需要手动调用logout()方法。 例如：SocialLogin::logout();

    'auto_logout' => true,


    //services APPID  APPKEY  and so on

    'services' => array(
        'QQ' => array(
            'APP_ID' => '',
            'APP_KEY' => '',
            'CALL_BACK' => '', //留空则返回当前页面。
            'SCOPE' => '',
        ),
        'Sina' => array(
            'APP_KEY' => '',
            'APP_SERCET' => '',
            'CALL_BACK' => '', //留空则返回当前页面。
        ),
    ),
);
