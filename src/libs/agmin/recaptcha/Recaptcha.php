<?php

/**
 * Recaptcha Google
 *
 * https://developers.google.com/recaptcha/docs/verify
 * https://www.geeksforgeeks.org/google-recaptcha-integration-in-php/
 */

namespace Recaptcha;

class Recaptcha
{
    /**
     * The reCAPTCHA server URL's
     */
    private static $RECAPTCHA_VERIFY_SERVER = "https://www.google.com/recaptcha/api/siteverify";


    /**
     * Calls an HTTP POST function to verify if the user's guess was correct
     * @param string $privkey
     * @param string $remoteip
     * @param string $response
     */
    public static function recaptchaCheckAnswer($privkey, $remoteip, $response)
    {

        $data = array(
                'secret' => $privkey,
                'remoteip' => $remoteip,
                'response' => $response
        );

        $data_string = http_build_query($data);

        // Making request to verify captcha
        $response = file_get_contents(self::$RECAPTCHA_VERIFY_SERVER . '?' . $data_string);

        // that json
        $response = json_decode($response);

        return $response;
    }
}
