<?php

namespace Hardywen\SocialLogin\Services;

use \Config;

session_start();

class BaseService {

    public $appid; //应用分配的APP_ID, QQ的是 APP_ID, Sina的是APP_KEY
    public $appkey; //应用分配的APP_KEY, QQ的是APP_KEY, Sina的是 APP_SERCET
    public $scope; //需要用户授权的接口(QQ)
    public $config; //配置
    public $callback; //登录成功后回调地址
    public $serviceName; //服务名
    public $state; //用于对比回调地址的state, 需要保存至Session
    public $accessToken; //登录成功后获取的Token值,需要保存至Session
    public $uid; //用户唯标识id. （sina的是uid, qq的是openid, 统一保存于 $uid)

    function __construct($serviceName) {
        $this->serviceName = $serviceName;
        $this->config = $this->getConfig($serviceName);

        $this->uid = $this->getSession('social_login_uid');
        $this->state = $this->getSession('social_login_state');
        $this->accessToken = $this->getSession('social_login_access_token');
    }

    private function getSession($key) {
        return isset($_SESSION[$this->serviceName][$key]) ? $_SESSION[$this->serviceName][$key] : null;
    }

    //获得服务的配置
    private function getConfig($serviceName) {

        return Config::get("social-login::services.$serviceName");
    }

    //保存state到session
    public function setState() {
        $state = md5(uniqid(rand(), TRUE));
        $this->state = $state;
        $_SESSION[$this->serviceName]['social_login_state'] = $state;
    }

    //保存access token到Session

    public function saveAccessToken($accessToken) {
        $this->accessToken = $accessToken;
        $_SESSION[$this->serviceName]['social_login_access_token'] = $accessToken;
    }

    public function saveUid($uid) {
        $this->uid = $uid;
        $_SESSION[$this->serviceName]['social_login_uid'] = $uid;
    }

}
