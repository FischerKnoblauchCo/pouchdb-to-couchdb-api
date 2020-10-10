<?php


namespace App\Http;

use Carbon\Carbon;

/**
 * @Author: Mirza Oglecevac
 *
 * Class AuthService
 * @package App\Http
 */
class AuthService
{

    /**
     * @return string
     */
    public function getToken() {

        // create token header
        $header = $this->createTokenHeader();

        // create token payload
        $payload = $this->createTokenPayload(null);

        // create signature
        $signature = $this->createSignature($header, $payload);

        return $header . "." . $payload . "." . $signature;
    }

    /**
     * @param $header
     * @param $payload
     * @return mixed
     */
    private function createSignature($header, $payload) {

        $signature = hash_hmac('sha256', $header . "." . $payload, config('app.jwt_secret'), true);

        return $this->base64UrlEncode($signature);
    }

    /**
     * @return mixed
     */
    private function createTokenHeader() {

        $header = json_encode([
            'type' => 'JWT'
        ]);

        return $this->base64UrlEncode($header);
    }

    /**
     * @param $user
     * @return mixed
     */
    private function createTokenPayload($user) { // TODO set user data in payload

        $currentTime = Carbon::now();
        $expirationTime = $this->setTokenExpirationTime($currentTime);

        $payload = json_encode([
            //'user_id' => 1,
            //'role' => 'admin',
            'logged_in_time' => $currentTime,
            'expiration_time' => $expirationTime
        ]);

        return $this->base64UrlEncode($payload);
    }

    /**
     * @param $currentTime
     * @return mixed
     */
    private function setTokenExpirationTime($currentTime) {

        $tokenDurationInMinutes = config('app.jwt_token_duration');
        $expirationTime = $currentTime->addMinutes($tokenDurationInMinutes)->unix();

        return $expirationTime;
    }

    /**
     * @param $text
     * @return mixed
     */
    private function base64UrlEncode($text)
    {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($text)
        );
    }

    public function getBearerToken($headers)
    {
        $header = $headers->get('Authorization', '');
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return '';
    }

    public function checkIfTokenIsValid($token) {

        // split the token
        $tokenParts = explode('.', $token);
        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signatureProvided = $tokenParts[2];

        //die(print_r($payload));
        // check the expiration time
        $expiration = Carbon::createFromTimestamp(json_decode($payload)->expiration_time);
        $tokenExpired = (Carbon::now()->diffInSeconds($expiration, false) < 0);

        // build a signature based on the header and payload using the secret
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, config('app.jwt_secret'), true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        // verify it matches the signature provided in the token
        $signatureValid = ($base64UrlSignature === $signatureProvided);

        echo "Header:\n" . $header . "\n";
        echo "Payload:\n" . $payload . "\n";

        if ($tokenExpired) {
            echo "Token has expired.\n";
        } else {
            echo "Token has not expired yet.\n";
        }

        if ($signatureValid) {
            echo "The signature is valid.\n";
        } else {
            echo "The signature is NOT valid\n";
        }
    }

}
