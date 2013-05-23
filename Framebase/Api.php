<?php
namespace Framebase;
require_once('Framebase/Api/Request.php');

class Api
{
    protected $token;
    protected $secret;

    public function __construct($token, $secret)
    {
        $this->token = $token;
        $this->secret = $secret;
    }

    /**
     * Performs a GET request
     * @param  string    $hostname       Hostname (e.g. api.framebase.io)
     * @param  string    $enedpoint      Endpoint to connect to (e.g. /videos.json)
     * @param  array     $get_parameters Key-value pair of parameters to send in the query string (e.g. array('x' => 'foo'))
     * @param  [boolean] $https          True for HTTPS, false for HTTP. Defaults true.
     * @return Request                   Result request object
     */
    public function get($hostname, $endpoint = '/', $get_parameters = array(), $https = true)
    {
        $r = new \Framebase\Api\Request($this->token, $this->secret, $https, 'GET', $hostname, $endpoint, $parameters);
        return $r->execute();
    }

    /**
     * Performs a POST request
     * @param  string    $hostname       Hostname (e.g. api.framebase.io)
     * @param  string    $enedpoint      Endpoint to connect to (e.g. /videos.json)
     * @param  array     $get_parameters Key-value pair of parameters to send in the query string (e.g. array('x' => 'foo'))
     * @param  array     $post_parameters Key-value pair of parameters to send in the postbody
     * @param  [boolean] $https          True for HTTPS, false for HTTP. Defaults true.
     * @return Request                   Result request object
     */
    public function post($hostname, $enedpoint = '/', $get_parameters = array(), $post_parameters = array(), $https = true)
    {
        $r = new \Framebase\Api\Request($this->token, $this->secret, $https, 'POST', $hostname, $endpoint, $get_parameters, $post_parameters);
        return $r->execute();
    }

    /**
     * Performs a DELETE request
     * @param  string    $hostname       Hostname (e.g. api.framebase.io)
     * @param  string    $enedpoint      Endpoint to connect to (e.g. /videos.json)
     * @param  array     $get_parameters Key-value pair of parameters to send in the query string (e.g. array('x' => 'foo'))
     * @param  [boolean] $https          True for HTTPS, false for HTTP. Defaults true.
     * @return Request                   Result request object
     */
    public function delete($hostname, $enedpoint = '/', $get_parameters = array(), $https = true)
    {
        $r = new \Framebase\Api\Request($this->token, $this->secret, $https, 'DELETE', $hostname, $endpoint, $get_parameters);
        return $r->execute();
    }
}
