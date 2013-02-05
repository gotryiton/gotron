<?php

namespace Gotron;

/**
 * Http Client library
 *
 * derived from gam-http - https://code.google.com/p/gam-http
 **/

class Http extends Singleton {

    private $_host = null;
    private $_port = null;
    private $_user = null;
    private $_pass = null;
    private $_protocol = null;

    private $has_mock = false;
    private $mock_urls = array();
    private $persistent = false;

    private $_append = array();
    private $_agent;
    private $_debugMode = false;
    private $_referer;
    private $_silentMode = false;
    private $_cookie;
    private $_headers = array();
    private $_connMultiple = false;

    const HTTP  = 'http';
    const HTTPS = 'https';

    const POST   = 'POST';
    const GET    = 'GET';
    const DELETE = 'DELETE';
    const PUT    = 'PUT';

    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACEPTED = 202;

    static public function connect($host, $port = null, $protocol = self::HTTP) {
        $instance = self::instance();
        $instance->_host = $host;
        $instance->_port = $port;
        $instance->_protocol = $protocol;

        return $instance;
    }

    /**
     *
     * @return Http
     */
    static public function multiConnect() {
        $instance = self::instance();
        $this->_host = null;
        $this->_port = null;
        $this->_protocol = null;
        $this->_connMultiple = true;
        return $instance;
    }

    public function add($http) {
        $this->_append[] = $http;
        return $this;
    }

    /**
     *
     * @param bool $mode
     * @return Http
     */
    public function silentMode($mode=true) {
        $this->_silentMode = $mode;
        return $this;
    }

    public function debugMode($mode=true) {
        $this->_debugMode = $mode;
        return $this;
    }

    public function setCredentials($user, $pass) {
        $this->_user = $user;
        $this->_pass = $pass;
        return $this;
    }

    public function setUserAgent($agent) {
        $this->_agent = $agent;
    }

    public function setReferer($referer) {
        $this->_referer = $referer;
    }

    public function setCookie($cookie) {
        $this->_cookie = $cookie;
    }

    private $_requests = array();

    /**
     * @param string $url
     * @param array $params
     * @return Http
     */
    public function put($url, $params=array()) {
        $this->_requests[] = array(self::PUT, $this->_url($url), $params);
        return $this;
    }

    /**
     * @param string $url
     * @param array $params
     * @return Http
     */
    public function post($url, $params=array()) {
        $this->_requests[] = array(self::POST, $this->_url($url), $params);
        return $this;
    }

    /**
     * @param string $url
     * @param array $params
     * @return Http
     */
    public function get($url, $params=array()) {
        $this->_requests[] = array(self::GET, $this->_url($url), $params);
        return $this;
    }

    /**
     * @param string $url
     * @param array $params
     * @return Http
     */
    public function delete($url, $params=array()) {
        $this->_requests[] = array(self::DELETE, $this->_url($url), $params);
        return $this;
    }

    public function _getRequests() {
        return $this->_requests;
    }

    /**
     * PUT request
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    public function doPut($url, $params=array()) {
        return $this->_exec(self::PUT, $this->_url($url), $params);
    }

    /**
     * POST request
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    public function doPost($url, $params=array()) {
        return $this->_exec(self::POST, $this->_url($url), $params);
    }

    /**
     * GET Request
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    public function doGet($url, $params=array()) {
        return $this->_exec(self::GET, $this->_url($url), $params);
    }

    /**
     * DELETE Request
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    public function doDelete($url, $params=array()) {
        return $this->_exec(self::DELETE, $this->_url($url), $params);
    }

    /**
     * setHeaders
     *
     * @param array $headers
     * @return Http
     */
    public function setHeaders($headers) {
        $this->_headers = $headers;
        return $this;
    }

    private $_timeout = array();
    /**
     * setTimeout
     *
     * @param array $timeout
     * @return Http
     */
    public function setTimeout($timeout) {
        $this->_timeout = $timeout;
        return $this;
    }

    /**
     * Builds absolute url
     *
     * @param string $url
     * @return string
     */
    private function _url($url = null) {
        if(substr($url,0,1) == "/")
            $url = substr($url, 1, strlen($url) - 1);

        //don't add the port to url unless it's explicitly set in constructor
        if(empty($this->_port)){
            return "{$this->_protocol}://{$this->_host}/{$url}";
        }
        else{
            return "{$this->_protocol}://{$this->_host}:{$this->_port}/{$url}";
        }
    }

    public static function mock_url($url, $data, $persistent = false) {
        $instance = self::instance();
        $instance->mock($url, $data, $persistent);
        return true;
    }

    public static function mock_all($data, $persistent = false) {
        $instance = self::instance();
        $instance->mock('mock_all', $data, $persistent);
        return true;
    }

    public function mock($url, $data, $persistent = false) {
        $this->has_mock = true;
        $this->mock_urls[$url] = $data;
        $this->persistent = $persistent;
    }

    public function has_mock_all() {
        return array_key_exists('mock_all', $this->mock_urls);
    }

    public function mock_contains($url) {
        return array_key_exists($url, $this->mock_urls);
    }

    public function mock_data_for($url) {
        if ($this->has_mock_all()) {
            return $this->mock_urls['mock_all'];
        }
        else {
            return $this->mock_urls[$url];
        }
    }

    public static function disable_mock() {
        $instance = self::instance();
        $instance->close_mock();
    }

    public function close_mock() {
        $this->mock_urls = array();
        $this->has_mock = false;
    }

    /**
     * Performing the real request
     *
     * @param string $type
     * @param string $url
     * @param array $params
     * @return string
     */
    private function _exec($type, $url, $params = array()) {
        if ($this->has_mock) {
            if ($this->has_mock_all() || $this->mock_contains($url)) {
                $data = $this->mock_data_for($url);
                if (!$this->persistent) {
                    $this->close_mock();
                }
                return $data;
            }
        }

        $headers = $this->_headers;
        $s = curl_init();

        curl_setopt($s, CURLOPT_FOLLOWLOCATION, 1);

        if(!is_null($this->_user)){
           curl_setopt($s, CURLOPT_USERPWD, $this->_user.':'.$this->_pass);
        }
        if(!is_null($this->_agent)){
           curl_setopt($s, CURLOPT_USERAGENT, $this->_agent);
        }
        if(!is_null($this->_referer)){
           curl_setopt($s, CURLOPT_REFERER, $this->_referer);
        }
        if(!is_null($this->_timeout)){
           curl_setopt($s, CURLOPT_TIMEOUT, $this->_timeout);
        }
        if(!is_null($this->_cookie)){
           curl_setopt($s, CURLOPT_COOKIESESSION, TRUE);
           curl_setopt($s, CURLOPT_COOKIE, $this->_cookie);
        }
        if ($this->_debugMode)
            curl_setopt($s, CURLOPT_VERBOSE, 1);

        switch ($type) {
            case self::DELETE:
                curl_setopt($s, CURLOPT_URL, $url . '?' . http_build_query($params));
                curl_setopt($s, CURLOPT_CUSTOMREQUEST, self::DELETE);
                break;
            case self::PUT:
                curl_setopt($s, CURLOPT_URL, $url);
                curl_setopt($s, CURLOPT_CUSTOMREQUEST, self::PUT);
                curl_setopt($s, CURLOPT_POSTFIELDS, $params);
                break;
            case self::POST:
                curl_setopt($s, CURLOPT_URL, $url);
                curl_setopt($s, CURLOPT_POST, true);
                curl_setopt($s, CURLOPT_POSTFIELDS, $params);
                break;
            case self::GET:
                curl_setopt($s, CURLOPT_URL, $url . '?' . http_build_query($params));
                break;
        }
        curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
        $_out = curl_exec($s);
        $status = curl_getinfo($s, CURLINFO_HTTP_CODE);
        $error = curl_error($s);
        curl_close($s);
        switch ($status) {
            case self::HTTP_OK:
            case self::HTTP_CREATED:
            case self::HTTP_ACEPTED:
                $out = $_out;
                break;
            default:
                if (!$this->_silentMode) {
                    throw new HttpException("http error: {$status} - {$error}", $status);
                }
                return NULL;
        }
        return $out;
    }

    public function run() {
        if ($this->_connMultiple) {
            return $this->_runMultiple();
        } else {
            return $this->_run();
        }
    }

    private function _runMultiple() {
        $out= null;
        if (count($this->_append) > 0) {
            $arr = array();
            foreach ($this->_append as $_append) {
                $arr = array_merge($arr, $_append->_getRequests());
            }

            $this->_requests = $arr;
            $out = $this->_run();
        }
        return $out;
    }

    private function _run() {
        if ($this->has_mock) {

        }
        else {
            $headers = $this->_headers;
            $curly = $result = array();

            $mh = curl_multi_init();
            foreach ($this->_requests as $id => $reg) {
                $curly[$id] = curl_init();

                $type   = $reg[0];
                $url    = $reg[1];
                $params = $reg[2];

                if(!is_null($this->_user)){
                   curl_setopt($curly[$id], CURLOPT_USERPWD, $this->_user.':'.$this->_pass);
                }

                if ($this->_debugMode)
                    curl_setopt($curly[$id], CURLOPT_VERBOSE, 1);

                switch ($type) {
                    case self::DELETE:
                        curl_setopt($curly[$id], CURLOPT_URL, $url . '?' . http_build_query($params));
                        curl_setopt($curly[$id], CURLOPT_CUSTOMREQUEST, self::DELETE);
                        break;
                    case self::PUT:
                        curl_setopt($curly[$id], CURLOPT_URL, $url);
                        curl_setopt($curly[$id], CURLOPT_CUSTOMREQUEST, self::PUT);
                        curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $params);
                        break;
                    case self::POST:
                        curl_setopt($curly[$id], CURLOPT_URL, $url);
                        curl_setopt($curly[$id], CURLOPT_POST, true);
                        curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $params);
                        break;
                    case self::GET:
                        curl_setopt($curly[$id], CURLOPT_URL, $url . '?' . http_build_query($params));
                        break;
                }
                curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curly[$id], CURLOPT_HTTPHEADER, $headers);

                curl_multi_add_handle($mh, $curly[$id]);
            }

            $running = null;
            do {
                curl_multi_exec($mh, $running);
                sleep(0.2);
            } while($running > 0);

            foreach($curly as $id => $c) {
                $status = curl_getinfo($c, CURLINFO_HTTP_CODE);
                switch ($status) {
                    case self::HTTP_OK:
                    case self::HTTP_CREATED:
                    case self::HTTP_ACEPTED:
                        $result[$id] = curl_multi_getcontent($c);
                        break;
                    default:
                        if (!$this->_silentMode) {
                            $result[$id] = new HttpMultipleError($status, $type, $url, $params);
                        }
                }
                curl_multi_remove_handle($mh, $c);
            }

            curl_multi_close($mh);
            return $result;
        }
    }

    public static function http_get($url, $params = array(), $silent = false, $timeout = null) {
        $http = self::connect_with_url($url);

        if (!is_null($timeout)) {
            $http->setTimeout($timeout);
        }

        $http->silentMode($silent);
        $parsed = parse_url($url);
        $path = isset($parsed['path']) ? $parsed['path'] : "/";
        return $http->doGet($path, $params);
    }

    public static function http_post($url, $params = array(), $silent = false, $timeout = null) {
        $http = self::connect_with_url($url);

        if (!is_null($timeout)) {
            $http->setTimeout($timeout);
        }

        $http->silentMode($silent);
        $parsed = parse_url($url);
        $path = $parsed['path'];
        return $http->doPost($path, $params);
    }

    public static function connect_with_url($url) {
        $parsed_url = parse_url($url);
        $host = $parsed_url['host'];
        $port = isset($parsed_url['port']) ? $parsed_url['port'] : null;
        $scheme = (isset($parsed_url['scheme']) && $parsed_url['scheme'] == 'https') ? 'https' : 'http';
        return self::connect($host, $port, $scheme);
    }
}
