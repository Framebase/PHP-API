<?php
namespace Framebase\Api;

/**
 * @internal
 */
class SignableRequest
{
    public $method;
    public $host;
    public $path;
    public $query;
    public $body;

    public function __construct($method, $host, $path = '/', $query = '', $body = '')
    {
        $this->method = $method;
        $this->host = $host;
        $this->path = $path;
        $this->query = $query;
        $this->body = $body;
    }

    /**
     * Gets a request representing the request which generated this script call
     * @param  [string]         $hostname   Hostname of the current server. Optional, but HIGHLY RECOMMENDED. If you're not using name-based hosts,
     *                                      not including this parameter will allow cross-domain replay attacks if you aren't storing the nonce in
     *                                      the same location.
     * @return SignableRequest              SignableRequest object
     */
    public static function current($hostname = null)
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($hostname === null) {
            $host = $_SERVER['SERVER_NAME'];
        }

        $path = $_SERVER['REQUEST_URI'];
        $query = (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0)? $_SERVER['QUERY_STRING'] :
                                                                                            NULL;
        if ($query !== NULL) {
            $path = substr($path, 0, strlen($path) - (strlen($query) + 1));

            // Remove the signature
            $new_query = [];
            foreach (explode('&', $query) as $part) {
                list($k, $v) = explode('=', $part);
                if ($k !== 'signature') {
                    $new_query[] = $part;
                }
            }
            $query = implode('&', $new_query);
        }

        $body = file_get_contents('php://input');

        return new self($method, $host, $path, $query, $body);
    }
}
