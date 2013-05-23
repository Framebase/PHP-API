<?php
namespace Framebase\Api;
require_once('SignableRequest.php');

/**
 * @internal
 */
class Signature
{
    const signature_separator = '$$';

    /* # Signing Methods */

    /**
     * Generate a signature for a request
     * @param  SignableRequest  $request                SignableRequest to sign
     * @param  string           $private_key            Private key
     * @param  [string]         $signature_algorithm    Signature algorithm. Defaults to sha512.
     * @return string                                   Signature
     */
    public static function get_signature(SignableRequest $request, $private_key, $signature_algorithm = 'sha512')
    {
        // Make sure the user set a reasonable private key
        if ($private_key === null || strlen($private_key) === 0) {
            throw new \Exception("Private key was not set");
        }



        // Calculate the nonce
        $nonce = mt_rand(0, mt_getrandmax());
        $time = time();

        // Calculate the HMAC
        $hmac = static::get_hmac($request, $time, $nonce, $signature_algorithm, $private_key);

        // Calculate the signature
        return static::pack_signature_information($signature_algorithm, $time, $nonce, $hmac);
    }

    /* # Validation Methods */

    /**
     * Amount of time requests are valid in seconds. Used for signature checking
     * @var int
     */
    public static $validity_window = 3600;

    /**
     * Checks if the signature is valid for the given private key
     * @param  string           $signature      Signature string
     * @param  SignableRequest  $request        Current HTTP request
     * @param  string           $private_key    Private key
     * @return bool                             True if valid, false otherwise
     */
    public static function validate_signature($signature, SignableRequest $request, $private_key)
    {
        $info = static::unpack_signature_information($signature);
        $expected_hmac = static::get_hmac($request, $info->time, $info->nonce, $info->signature_algorithm, $private_key);

        if (abs($info->time - time()) > static::$validity_window) {
            return false;
        }


        return $expected_hmac === $info->hmac;
    }

    /**
     * Gets the nonce associated with the signature
     * @param  string $signature Signature
     * @return string            Nonce
     */
    public static function get_nonce($signature)
    {
        return static::unpack_signature_information($signature)->nonce;
    }



    /* # Utility Functions */

    /**
     * Packs information into a signature
     * @param  string $signature_algorithm Hash algorithm (e.g. sha512)
     * @param  string $time                Timestamp
     * @param  string $nonce               Nonce
     * @param  string $hmac                Calculated HMAC
     * @return string                      Signature
     */
    private static function pack_signature_information($signature_algorithm, $time, $nonce, $hmac)
    {
        return base64_encode(implode(self::signature_separator, array($signature_algorithm, gmdate('c', $time), $nonce, $hmac)));
    }

    /**
     * Gets the parts included in the signature request
     * @param  string $signature Signature string
     * @return object            Information object containing [signature_algorithm, time, nonce, hmac]
     */
    private static function unpack_signature_information($signature)
    {
        $signature_string = base64_decode($signature);
        list($signature_algorithm, $time, $nonce, $hmac) = explode(self::signature_separator, $signature_string);

        return (object)array(
            'signature_algorithm' => $signature_algorithm,
            'time' => strtotime($time),
            'nonce' => $nonce,
            'hmac' => $hmac
        );
    }

    /**
     * Calculates an HMAC hash for the given components
     * @param  SignableRequest  $request                HTTP request to calculate the HMAC for
     * @param  string           $time                   Timestamp
     * @param  string           $nonce                  Nonce
     * @param  string           $signature_algorithm    Hash algorithm (e.g. sha512)
     * @param  string           $private_key            Private key
     * @return string                                   HMAC hash
     */
    private static function get_hmac(SignableRequest $request, $time, $nonce, $signature_algorithm, $private_key)
    {
        $hmac_string = implode("\n", array(
                                            // SignableRequest information:
                                            strtoupper($request->method), strtolower($request->host), $request->path,
                                            $request->query, $request->body,

                                            // Replay attack prevention:
                                            gmdate('c', $time), $nonce
                                ));

        // Calculate the HMAC
        return hash_hmac($signature_algorithm, $hmac_string, $private_key);
    }
}
