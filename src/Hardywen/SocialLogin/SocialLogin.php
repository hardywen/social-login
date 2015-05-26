<?php

namespace Hardywen\SocialLogin;

use Illuminate\Support\Facades\Session;

class SocialLogin
{

    private $serviceInstance; //当前服务

    //调用服务
    public function consumer($serviceName)
    {
        $serviceClass = "\\Hardywen\\SocialLogin\\Services\\$serviceName";
        $this->serviceInstance = new $serviceClass($serviceName); //实例化服务
        return $this->serviceInstance;
    }

    //退出当前账号
    public function logout()
    {
        Session::forget("social_login");
    }

}
