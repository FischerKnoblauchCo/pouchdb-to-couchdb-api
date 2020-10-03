<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class EncryptionController extends Controller
{

    private $keysToEncrypt = ['last_name'];
    private $client;
    private $authentication;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client(['headers' => ['Content-Type' => 'application/json']]);
        $this->authentication = config('app.couchdb-auth');
    }

    public function encryptUserCreation(Request $request) {

        $userCreateData = $request->all()['doc'];

        $dataToSend = [];

        foreach($userCreateData as $key => $item) {

            if (in_array($key, $this->keysToEncrypt)) {

                $dataToSend[$key] = Crypt::encrypt($item);

            } else {

                if ($key != '_rev') {
                    $dataToSend[$key] = $item;
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
            if (in_array($key, $this->keysToEncrypt)) {
                $dataToHandle->$key = Crypt::decrypt($item);
            } else {
                $dataToHandle->$key = $item;
            }
        }

        return response()->json([
            $dataToHandle
        ]);
    }
}
