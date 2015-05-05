<?php

namespace Hardywen\SocialLogin\Services;

use \Redirect;
use \Request;

class Sina extends BaseService {

    //下列参数从Sina SDK 中复制过来的
    /**
     * @ignore
     */
    public $refresh_token;

    /**
     * Contains the last HTTP status code returned. 
     *
     * @ignore
     */
    public $http_code;

    /**
     * Contains the last API call.
     *
     * @ignore
     */
    public $url;

    /**
     * Set up the API root URL.
     *
     * @ignore
     */
    public $host = "https://api.weibo.com/2/";

    /**
     * Set timeout default.
     *
     * @ignore
     */
    public $timeout = 30;

    /**
     * Set connect timeout.
     *
     * @ignore
     */
    public $connecttimeout = 30;

    /**
     * Verify SSL Cert.
     *
     * @ignore
     */
    public $ssl_verifypeer = FALSE;

    /**
     * Respons format.
     *
     * @ignore
     */
    public $format = 'json';

    /**
     * Decode returned json data.
     *
     * @ignore
     */
    public $decode_json = TRUE;

    /**
     * Contains the last HTTP headers returned.
     *
     * @ignore
     */
    public $http_info;

    /**
     * Set the useragnet.
     *
     * @ignore
     */
    public $useragent = 'Sae T OAuth2 v0.1';

    /**
     * print the debug info
     *
     * @ignore
     */
    public $debug = FALSE;

    /**
     * boundary of multipart
     * @ignore
     */
    public static $boundary = '';

    function __construct($serviceName) {
        parent::__construct($serviceName);
        $this->setConfig($this->config);
    }

    public function login() {

        $this->setState();

        $params = array();
        $params['client_id'] = $this->appid;
        $params['redirect_uri'] = $this->callback;
        $params['response_type'] = 'code';
        $params['state'] = $this->state;
        $params['display'] = 'default';
        return Redirect::to($this->authorizeURL() . "?" . http_build_query($params));
    }

    private function setConfig($config) {

        $this->appid = $this->config['APP_KEY'];
        $this->appkey = $this->config['APP_SERCET'];
        $this->callback = $this->config['CALL_BACK'] ? $this->config['CALL_BACK'] : Request::getUri();
    }

    public function callBack() {
        $this->getAccessToken();
    }

    public function getAccessToken() {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        if (Request::get('state') == $this->state) { //csrf
            $params = array();
            $params['client_id'] = $this->appid;
            $params['client_secret'] = $this->appkey;
            $params['grant_type'] = 'authorization_code';
            $params['code'] = Request::get('code');
            $params['redirect_uri'] = $this->callback;

            $response = $this->oAuthRequest($this->accessTokenURL(), 'POST', $params);
            $token = json_decode($response, true);
            if (is_array($token) && !isset($token['error'])) {
                $this->accessToken = $token['access_token'];
                $this->uid = $token['uid'];
            } else {
                die($token['error']);
            }
        }

        $this->saveAccessToken($this->accessToken);
        $this->saveUid($this->uid);
        return $this->accessToken;
    }

    public function getUserInfo() {

        $params = array();
        $params['source'] = $this->appid;
        $params['access_token'] = $this->accessToken;
        $params['uid'] = $this->uid;

        $response = $this->oAuthRequest("users/show", 'GET', $params);
        $user = json_decode($response);

        return $user;
    }

    //从微博SKD中复制过来的涵数

    /**
     * @ignore
     */
    protected function id_format(&$id) {
        if (is_float($id)) {
            $id = number_format($id, 0, '', '');
        } elseif (is_string($id)) {
            $id = trim($id);
        }
    }

    /**
     * Set API URLS
     */

    /**
     * @ignore
     */
    private function accessTokenURL() {
        return 'https://api.weibo.com/oauth2/access_token';
    }

    /**
     * @ignore
     */
    private function authorizeURL() {
        return 'https://api.weibo.com/oauth2/authorize';
    }

    /**
     * Format and sign an OAuth / API request
     *
     * @return string
     * @ignore
     */
    private function oAuthRequest($url, $method, $parameters, $multi = false) {

        if (strrpos($url, 'http://') !== 0 && strrpos($url, 'https://') !== 0) {
            $url = "{$this->host}{$url}.{$this->format}";
        }

        switch ($method) {
            case 'GET':
                $url = $url . '?' . http_build_query($parameters);
                return $this->http($url, 'GET');
            default:
                $headers = array();
                if (!$multi && (is_array($parameters) || is_object($parameters))) {
                    $body = http_build_query($parameters);
                } else {
                    $body = self::build_http_query_multi($parameters);
                    $headers[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
                }
                return $this->http($url, $method, $body, $headers);
        }
    }

    /**
     * Make an HTTP request
     *
     * @return string API results
     * @ignore
     */
    private function http($url, $method, $postfields = NULL, $headers = array()) {
        $this->http_info = array();
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, FALSE);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
        }

        if (isset($this->access_token) && $this->access_token)
            $headers[] = "Authorization: OAuth2 " . $this->access_token;

        if (!empty($this->remote_ip)) {
            if (defined('SAE_ACCESSKEY')) {
                $headers[] = "SaeRemoteIP: " . $this->remote_ip;
            } else {
                $headers[] = "API-RemoteIP: " . $this->remote_ip;
            }
        } else {
            if (!defined('SAE_ACCESSKEY')) {
                $headers[] = "API-RemoteIP: " . $_SERVER['REMOTE_ADDR'];
            }
        }
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);

        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
        $this->url = $url;

        if ($this->debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);

            echo "=====headers======\r\n";
            print_r($headers);

            echo '=====request info=====' . "\r\n";
            print_r(curl_getinfo($ci));

            echo '=====response=====' . "\r\n";
            print_r($response);
        }
        curl_close($ci);
        return $response;
    }

    /**
     * Get the header info to store.
     *
     * @return int
     * @ignore
     */
    function getHeader($ch, $header) {
        $i = strpos($header, ':');
        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }
        return strlen($header);
    }

}
