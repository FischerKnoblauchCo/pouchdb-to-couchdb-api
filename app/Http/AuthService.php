<?php


namespace App\Http;

use App\Models\ReturnStatuses;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * @Author: Mirza Oglecevac
 *
 * Class AuthService
 * @package App\Http
 */
class AuthService
{
    use DbConnection;

    /**
     * Here we set access token to user cookie, which will user use for accessing other server resources
     *
     * @param $response
     * @param $token
     * @return mixed
     */
    public function setTokenInCookie($response, $token, $cookieType) {

        $response->headers->setCookie( // with this cookie user will send every other request, to identify himself
            new Cookie('access_token',
                $token,
                $this->getCookieExpiratonTime($cookieType),
                '/',
                null, // TODO this is client domain from where request is sent
                false,
                false,
                false,
                null
//                $token,
//                $this->getCookieExpiratonTime($cookieType),
//                '/',
//                null, // TODO this is client domain from where request is sent
//                false,
//                false,
//                false,
//                null
            )
        );

        return $response;
    }

    /**
     * @param $type
     * @return float|int
     */
    public function getCookieExpiratonTime($type) {

        switch ($type) {
            case 'LOGOUT':
                $time = 1;
                break;
            case 'LOGIN':
                $time = $this->getFreshTokenExpirationTime();
                break;
            default:
                $time = 1;
        }

        return $time;
    }

    /**
     * @return float|int
     */
    private function getFreshTokenExpirationTime() {

        $currentTimestamp = time();
        // TODO because we are in UTC 2 timezone, we need to add 2h to this (is seconds)
        $currentTimestamp = $currentTimestamp + 7200;

        return $currentTimestamp + $this->getTokenDurationInSeconds();
    }

    /**
     * After user login credentials are checked and alright, this function generates token form him, which will be used for all next requests, until user log outs or token expires
     *
     * @return string
     */
    public function getToken($user) {

        // create token header
        $header = $this->createTokenHeader();

        // create token payload
        $payload = $this->createTokenPayload($user);

        // create signature
        $signature = $this->createSignature($header, $payload);

        return $header . "." . $payload . "." . $signature;
    }

    /**
     * NOT USED - Used by JWT middleware to extract token from the incoming request
     *
     * @param $headers
     * @return false|string
     */
    public function getBearerToken($headers)
    {
        $header = $headers->get('Authorization', '');
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return '';
    }

    /**
     * Used by JWT middleware to check if incoming user token is valid (maybe expired or signature corrupted)
     *
     * @param $token
     * @return array
     */
    public function checkIfTokenIsValid($token) {

        // split the token
        $tokenParts = explode('.', $token);

        // check if token has 3 parts, if not, token is invalid for sure
        if (count($tokenParts) != 3) {
            return [
                'valid' => false,
                'response' => ReturnStatuses::BAD_TOKEN_FORMAT
            ];
        }

        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signatureProvided = $tokenParts[2];

        // check the expiration time
        $expiration = Carbon::createFromTimestamp(json_decode($payload)->exp);
        $tokenExpired = (Carbon::now()->diffInSeconds($expiration, false) < 0);

        if ($tokenExpired) {
            return [
                'valid' => false,
                'response' => ReturnStatuses::TOKEN_EXPIRED
            ];
        }

        // build a signature again, based on the header and payload using the secret, so we can compare it with the received one
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);
        $signature = hash_hmac(config('app.hashing_algorithm'), $base64UrlHeader . "." . $base64UrlPayload, config('app.jwt_secret'), true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        // verify it matches the signature provided in the token
        $signatureValid = ($base64UrlSignature === $signatureProvided);

        if (!$signatureValid) {
            return [
                'valid' => false,
                'response' => ReturnStatuses::INVALID_TOKEN_SIGNATURE
            ];
        }

        // if all checks are passed, user has valid token, so request can pass the middleware
        return [
            'valid' => true,
            'response' => $payload // we need payload to get user id, which we will use as a key for storing his token in cookies
        ];

    }


    /**
     * @param $header
     * @param $payload
     * @return mixed
     */
    private function createSignature($header, $payload) {

        $signature = hash_hmac(config('app.hashing_algorithm'), $header . "." . $payload, config('app.jwt_secret'), true);

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
            'iss' => config('app.app_url'),
            'iat' => $currentTime,
            'exp' => $expirationTime,
            'sub' => 2, // TODO temporary - we need real user id here
            //'role' => 'admin',
//            'logged_in_time' => $currentTime,
//            'expiration_time' => $expirationTime
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

    private function getTokenDurationInSeconds() {

        $durationInMinutes = config('app.jwt_token_duration');
        return $durationInMinutes * 60;
    }

    public function getUserCsrfToken($token) {

        $tokenParts = explode('.', $token);
        $payload = base64_decode($tokenParts[1]);

        $expires = json_decode($payload)->exp;
        $userId = json_decode($payload)->sub;

        $csrfTokenRaw = $expires . $userId;

        $encrypter = new \Illuminate\Encryption\Encrypter( config('app.csrf_secret'), config( 'app.cipher' ));
        $csrfToken = $encrypter->encrypt($csrfTokenRaw);


        return $csrfToken;
    }

    /**
     * @param $token
     * @param $csrfToken
     * @return array
     */
    public function checkUserCsrfToken($token, $csrfToken) {

        try {

            $tokenParts = explode('.', $token);
            $payload = base64_decode($tokenParts[1]);

            $expires = json_decode($payload)->exp;
            $userId = json_decode($payload)->sub;

            $csrfTokenRaw = $expires . $userId;

            $encrypter = new \Illuminate\Encryption\Encrypter( config('app.csrf_secret'), config( 'app.cipher' ));
            $decrypted = $encrypter->decrypt($csrfToken);

            // check if the encrypted string and the one we got from the JWT are the same
            //Log::info($csrfTokenRaw . ' - ' . $decrypted);
            if($csrfTokenRaw !== $decrypted) {

                // user has invalid CSRF token - reject the request
                return [
                    'valid' => false,
                    'response' => ReturnStatuses::INVALID_CSRF_TOKEN
                ];
            }

        } catch (\Exception $e) {

            return [
                'valid' => false,
                'response' => ReturnStatuses::INVALID_CSRF_TOKEN
            ];
        }

        return [
            'valid' => true
        ];

    }

    public function checkIfCredentialsAreValid($username, $password) {

        try {

            $response = $this->client->request('POST', $this->dbUrl . '/users_pouch/_find', [
                'body' => json_encode([
                    'selector' => [
                        'username' => [
                            '$eq' => $username
                        ]
                    ],
                    //'limit' => 1
                    //'use_index' => ['username-find']
                ])
            ]);

            $data = (array)json_decode($response->getBody()->getContents());
            $data = $data['docs'] ?? [];

            if (empty($data)) { // user with this username doesnt exist
                return false;
            }


            $user = $data[0];

            if (Hash::check($password, $user->password)) {
                return true;
            } else {
                return false;
            }

        } catch (\Exception $e) {
            return false;
        }

    }
}
