<?php

namespace App\Http\Controllers;

use Lcobucci\JWT\Builder;
use Illuminate\Http\Request;
use Lcobucci\JWT\Signer;

class AuthController extends Controller
{
    /**
     * Check user credentials, if they are valid, give token to him
     *
     * @param Request $request
     */
    public function login(Request $request) {

        die("dssd");
        // IMPORTANT: on each login we will re-generate token

        // check if username matches given password

        // if yes, generate token for the user, which will be valid for 1 hour - after that token will be invalid and user will need to log in again

        // return generated token
    }


    public function revalidate() {

    }

    public function getToken(Request $request) {

//        $time = time();
//        $token = (new Builder())->issuedBy('http://example.com') // Configures the issuer (iss claim)
//       // ->permittedFor('http://example.org') // Configures the audience (aud claim)
//        //->identifiedBy(getenv('JWT_SECRET'), true) // Configures the id (jti claim), replicating as a header item
//        ->issuedAt($time) // Configures the time that the token was issue (iat claim)
//        ->canOnlyBeUsedAfter($time + 60) // Configures the time that the token can be used (nbf claim)
//        ->expiresAt($time + 3600) // Configures the expiration time of the token (exp claim)
//        //->withClaim('uid', 1) // Configures a new claim, called "uid"
//           // ->sign(getenv('JWT_SECRET'))
//        ->getToken(); // Retrieves the generated token


// get the local secret key
        $secret = getenv('SECRET');

        // Create the token header
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);

        // Create the token payload
        $payload = json_encode([
            'user_id' => 1,
            'role' => 'admin',
            'exp' => 1593828222
        ]);

        // Encode Header
        $base64UrlHeader = $this->base64UrlEncode($header);

// Encode Payload
        $base64UrlPayload = $this->base64UrlEncode($payload);

        // Create Signature Hash
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, getenv('JWT_SECRET'), true);

// Encode Signature to Base64Url String
        $base64UrlSignature = $this->base64UrlEncode($signature);
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;


        return $jwt;
//        return response()->json([
//            $token
//        ]);

//        $token->getHeaders(); // Retrieves the token headers
//        $token->getClaims(); // Retrieves the token claims
//
//        echo $token->getHeader('jti'); // will print "4f1g23a12aa"
//        echo $token->getClaim('iss'); // will print "http://example.com"
//        echo $token->getClaim('uid'); // will print "1"
//        echo $token; // The string representation of the object is a JWT string (pretty easy, right?)

    }

    private function base64UrlEncode($text)
    {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($text)
        );
    }

}
