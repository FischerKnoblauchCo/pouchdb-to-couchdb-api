<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Mews\Purifier\Purifier;

class EncryptionController extends Controller
{

    private $keysToEncrypt = ['last_name'];
    private $client;
    private $authentication;
    private $purifier;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client(['headers' => ['Content-Type' => 'application/json']]);
        $this->authentication = config('app.couchdb-auth');
    }

    public function encryptUserCreation(Request $request) {

        $userCreateData = $request->all()['doc'];

        $dataToSend = [];

        foreach($userCreateData as $key => $item) {

            $key = strip_tags(clean($key));

            if (in_array($key, $this->keysToEncrypt)) {

                $dataToSend[$key] = Crypt::encrypt(strip_tags(clean($item)));

            } else {

                if ($key != '_rev') {

                    if (is_array($item)) {
                        $dataToSend[$key] = $item;
                    } else {
                        $dataToSend[$key] = strip_tags(clean($item));
                    }

                }

            }

        }

        $response = $this->client->request('POST', 'http://' . $this->authentication . '@127.0.0.1:5984/users_pouch', [
            'body' => json_encode($dataToSend)
        ]);

        return response()->json([
            'data' => $response
        ]);

    }

    public function getDocument(Request $request) {

        $documentId = $request->get('document_id');

        $response = $this->client->request('GET', 'http://' . $this->authentication . '@127.0.0.1:5984/users_pouch/' . $documentId);

        $dataToHandle = json_decode($response->getBody()->getContents());

        foreach($dataToHandle as $key => $item) {

            $key = strip_tags(clean($key));

            if (in_array($key, $this->keysToEncrypt)) {
                $dataToHandle->$key = Crypt::decrypt(strip_tags(clean($item)));
            } else {

                if (is_array($item)) {
                    $dataToHandle->$key = $item;
                } else {
                    $dataToHandle->$key = strip_tags(clean($item));
                }

            }
        }

        return response()->json([
            $dataToHandle
        ]);
    }
}
