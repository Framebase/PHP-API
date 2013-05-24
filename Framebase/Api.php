<?php
namespace Framebase;
require_once('Framebase/Api/Request.php');

class Api
{
    protected $token;
    protected $secret;

    public static $api_host = 'api.framebase.io';
    public static $use_https = true;

    public function __construct($token, $secret)
    {
        $this->token = $token;
        $this->secret = $secret;
    }

    /**
     * Gets details about a video
     * @param  string $video_id The UUID of the video to request information on
     * @return object           Information
     */
    public function video_details($video_id) {
        return json_decode($this->get(static::$api_host, '/videos/' . $video_id . '.json', array(), static::$use_https));
    }

    /**
     * Deletes a video
     * @param  string $video_id The UUID of the video to delete
     * @return object           Result
     */
    public function video_delete($video_id) {
        return json_decode($this->delete(static::$api_host, '/videos/' . $video_id . '.json', array(), static::$use_https));
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
        $r = new \Framebase\Api\Request($this->token, $this->secret, $https, 'GET', $hostname, $endpoint, $get_parameters);
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
