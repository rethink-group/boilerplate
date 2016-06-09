<?php
/**
* Boilerplate cURL class
*
* Basic boilerplate cURL class. Forked from Vo Ngoc Minh (https://gist.github.com/minhvn/1267301)
*
* @author jhoward@rethinkgroup.org
*/
class cURL {
    protected $initialized = false;
    public $headers;
    public $userAgent;
    public $compression;
    public $cookieFile;
    public $proxy;
    public $url;
    public $acceptType = 'application/json';
    public $data;
    /**
     * Construct the cURL object
     * @param string $url The url to call. Required, but can be omitted in construction.
     * @return bool State of initialization
    */
    function __construct($url = null)
    {
        if ( is_null( $url ) ) {
            /*
             * If url is not set in object construction,
             * init() must be called manually.
            */
            $this->url = $url;
            $this->init();
        }
        return $this->initialized;
    }
    /**
     * Initialize the cURL call
     * @param bool $cookies Toggles use of cookies
     * @param string $cookie Specifies filename for cookies
     * @param string $compression Specifies filename for cookies
     * @return bool Return status of initialization
    */
    public function init($cookies = true, $cookie = 'cookies.txt', $compression = 'gzip', $proxy = '')
    {
        try {
            $this->headers[] = "Accept: {$this->acceptType}";
            $this->headers[] = "Connection: Keep-Alive";
            $this->headers[] = "Content-type: {$this->acceptType};charset = UTF-8";
            $this->userAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36";
            $this->compression = $compression;
            $this->proxy = $proxy;
            $this->cookies = $cookies;
            if ($this->cookies == true) $this->cookie($cookie);
            $this->initialzed = true;
            return $this->initialized;
        } catch (Exception $e) {
            return $this->error('Caught exception: ',  $e->getMessage());
        }
    }
    /**
     * Initialize the cookies to be passed
     * @param resource $cookieFile Cookie file to read
     * @return bool Result of opening cookie file
    */
    private function cookie($cookieFile)
    {
        if (file_exists($cookieFile)) {
            $this->cookieFile = $cookieFile;
            return true;
        } else {
            try {
                fopen($cookieFile,'w') or $this->error('The cookie file could not be opened. Make sure this directory has the correct permissions');
                $this->cookieFile = $cookieFile;
                fclose($this->cookieFile);
                return true;
            } catch (Exception $e) {
                return $this->error('Caught exception: ',  $e->getMessage());
            }
        }
    }
    /**
     * Make the cURL call
     * @param bool $cookies Toggles use of cookies
     * @return mixed Response from cURL call
    */
    private function call()
    {
        if ( $this->initialized ) {
            try {
                $cURL = curl_init($this->url);
                curl_setopt($cURL, CURLOPT_HTTPHEADER, $this->headers);
                curl_setopt($cURL, CURLOPT_HEADER, 1);
                curl_setopt($cURL, CURLOPT_USERAGENT, $this->userAgent);
                curl_setopt($cURL, CURLOPT_ENCODING , $this->compression);
                curl_setopt($cURL, CURLOPT_TIMEOUT, 30);
                curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($cURL, CURLOPT_FOLLOWLOCATION, 1);
                if ( $this->cookies ) {
                    curl_setopt($cURL, CURLOPT_COOKIEFILE, $this->cookieFile);
                    curl_setopt($cURL, CURLOPT_COOKIEJAR, $this->cookieFile);
                }
                if ( ! empty($this->data) ) {
                    if ($this->acceptType == 'application/json') $this->data = json_encode($this->data);
                    curl_setopt($cURL, CURLOPT_POST, 1);
                    curl_setopt($cURL, CURLOPT_POSTFIELDS, $this->data);
                }
                if ($this->proxy) curl_setopt($cURL, CURLOPT_PROXY, $this->proxy);
                $response = curl_exec($cURL);
                curl_close($cURL);
                return $response;
            } catch (Exception $e) {
                return $this->error('Caught exception: ',  $e->getMessage());
            }
        } else {
            return false;
        }
    }
    /**
     * Display cURL error
     * @param string $error Error message to display
     * @return void
    */
    public function error($error = null)
    {
        if ( is_null($error) ) {
            $error = 'Server error';
        }
        echo "<pre style='color:red'>$error";
        die;
    }
}
