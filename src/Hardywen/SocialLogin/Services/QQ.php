<?php

namespace Hardywen\SocialLogin\Services;

use \Redirect;
use \Request;

class QQ extends BaseService
{

    function __construct($serviceName)
    {
        parent::__construct($serviceName);
        $this->setConfig($this->config);
    }

    public function login()
    {

        $this->setState();

        $login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id="
            . $this->appid . "&redirect_uri=" . urlencode($this->callback)
            . "&state=" . $this->state
            . "&scope=" . $this->scope;

        return Redirect::to($login_url); //跳转到QQ登录页面登录
    }

    private function setConfig($config)
    {

        $this->appid = $this->config['APP_ID'];
        $this->appkey = $this->config['APP_KEY'];
        $this->scope = $this->config['SCOPE'];
        $this->callback = $this->config['CALL_BACK'] ? $this->config['CALL_BACK'] : Request::getUri();
    }

    // 一次性获得 access token 和 uid
    public function callBack()
    {
        if ($this->getAccessToken()) {
            $this->getOpenId();
        }
    }

    public function getAccessToken()
    {

        if ($this->accessToken) {
            return $this->accessToken;
        }

        if (Request::get('state') == $this->state) { //csrf
            $token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"
                . "client_id=" . $this->appid . "&redirect_uri=" . urlencode($this->callback)
                . "&client_secret=" . $this->appkey . "&code=" . Request::get('code');

            $response = file_get_contents($token_url);
            if (strpos($response, "callback") !== false) {
                $lpos = strpos($response, "(");
                $rpos = strrpos($response, ")");
                $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
                $msg = json_decode($response);
                if (isset($msg->error)) {
                    echo "<h3>error:</h3>" . $msg->error;
                    echo "<h3>msg  :</h3>" . $msg->error_description;
                    exit;
                }
            }

            $params = array();
            parse_str($response, $params);

            $this->saveAccessToken($params["access_token"]); //保存access token;
        } else {
            echo("The state does not match. You may be a victim of CSRF.");
        }

        return $this->accessToken;
    }

    public function getOpenId()
    {

        if ($this->uid) {
            return $this->uid;
        }

        $graph_url = "https://graph.qq.com/oauth2.0/me?access_token=" . $this->accessToken;

        $str = file_get_contents($graph_url);
        if (strpos($str, "callback") !== false) {
            $lpos = strpos($str, "(");
            $rpos = strrpos($str, ")");
            $str = substr($str, $lpos + 1, $rpos - $lpos - 1);
        }

        $user = json_decode($str);
        if (isset($user->error)) {
            echo "<h3>error:</h3>" . $user->error;
            echo "<h3>msg  :</h3>" . $user->error_description;
            exit;
        }

        $this->saveUid($user->openid);

        return $this->uid;
    }

    public function getUserInfo()
    {

        $graph_url = "https://graph.qq.com/user/get_user_info"
            . "?access_token=" . $this->accessToken
            . "&oauth_consumer_key=" . $this->appid
            . "&openid=" . $this->uid;

        $str = file_get_contents($graph_url);

        $user = json_decode($str);

        return $user;
    }

    //通用的 get 方式接口
    public function getInterface($inteface, $params = array())
    {
        $graph_url = "https://graph.qq.com/" . $inteface
            . "?access_token=" . $this->accessToken
            . "&oauth_consumer_key=" . $this->appid
            . "&openid=" . $this->openid
            . http_build_query($params);

        $str = file_get_contents($graph_url);

        if (!isset($params['format']) || $params['format'] == 'json') {
            return json_decode($str);
        }

        return $str;
    }

}
