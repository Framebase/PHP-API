<?php
namespace Framebase\Api;
require_once('Signature.php');
require_once('SignableRequest.php');

/* Check if CURL is installed. */
if (!function_exists('curl_version')) {
    throw new \Exception('The Framebase PHP API requires CURL. You can install it with "sudo apt-get install php5-curl" (Debian) or "sudo yum install php-curl" (CentOS).');
}

/**
 * @internal
 */
class Request
{
    protected $is_https;
    protected $method;
    protected $hostname;
    protected $endpoint;
    protected $get_parameters;
    protected $post_parameters;
    protected $postdata;
    protected $token;
    protected $secret;

    public function __construct($token, $secret, $https, $method, $hostname, $endpoint,
                                   $get_parameters = array(), $post_parameters = array(), $postdata = array())
    {
        $this->token = $token;
        $this->secret = $secret;
        $this->https = $https;
        $this->method = $method;
        $this->hostname = $hostname;
        $this->endpoint = $endpoint;
        $this->get_parameters = $get_parameters;
        $this->post_parameters = $post_parameters;
        $this->postdata = $postdata;
    }

    /**
     * Signs and executes the request, and returns the result
     * @return string Result
     */
    public function execute()
    {
        /* ## Setup */
        $curl_opts = array(
            CURLOPT_URL => $this->get_url(),
            CURLOPT_CUSTOMREQUEST => strtoupper($this->method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'Framebase Official PHP API/1.0'
        );

        if ($this->get_body() !== null) {
            $curl_opts[CURLOPT_POSTFIELDS] = $this->get_body();
        }

        /* ## Do the request */
        $ch = curl_init();
        foreach ($curl_opts as $k => $v) {
            curl_setopt($ch, $k, $v);
        }
        $result = curl_exec($ch);
        $result_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        /* ## Return */
        if ($result_code === 403 || $result_code === 401) {
            throw new InvalidAuthException();
        } else if ($result_code !== 200) {
            throw new \Exception("An API error occured[$result_code]: $result");
        }

        return $result;
    }

    /**
     * Gets the full URL
     * @return string URL
     */
    protected function get_url()
    {
        $url = '';
        $url .= $this->is_https ? 'https' : 'http';
        $url .= '://';
        $url .= $this->hostname;
        $url .= $this->endpoint;
        $url .= '?' . $this->get_query_string(true);

        return $url;
    }

    /**
     * Gets the querystring
     * @param  [boolean] $sign If true, sign the request and include it in the querystring. Defaults to false.
     * @return string          Querystring.
     */
    protected function get_query_string($sign = false)
    {
        $params = $this->get_parameters;
        $params['token'] = $this->token;

        if ($sign) {
            $signature = \Framebase\Api\Signature::get_signature($this->to_signable_request(), $this->secret);
            $params['signature'] = $signature;
        }

        return static::build_query_string($params);
    }

    /**
     * Gets the postbody
     * @return string Postbody
     */
    protected function get_body()
    {
        // TODO: handle uploads
        return static::build_query_string($this->post_parameters);
    }

    /**
     * Generates a properly-encoded HTTP query string
     * @param  array  $params Parameters to encode
     * @return string         Query string
     */
    private static function build_query_string($params)
    {
        if ($params !== null && count($params) > 0) {
            $built_string = array();
            foreach ($params as $k=>$v) {
                $built_string[] = urlencode($k) . '=' . urlencode($v);
            }
            return implode('&', $built_string);
        } else {
            return null;
        }
    }

    /**
     * Converts the current request into a signablerequest, for use in generating signatures
     * @return SignableRequest Related SignableRequest
     */
    private function to_signable_request()
    {
        return new SignableRequest($this->method, $this->hostname, $this->endpoint, $this->get_query_string(), $this->get_body());
    }
}
