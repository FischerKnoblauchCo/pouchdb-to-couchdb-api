<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Mews\Purifier\Purifier;

class UserController extends Controller
{

    private $keysToEncrypt = ['name'];
    private $keysToHash = ['password'];
    private $client;
    private $authentication;
    private $purifier;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client(['headers' => ['Content-Type' => 'application/json']]);
        $this->authentication = config('app.couchdb-auth');
    }

    public function createUser(Request $request) {

        //die(print_r($request->all()));
        $userCreateData = $request->all(); //['doc'];

        $dataToSend = [];

        foreach($userCreateData as $key => $item) {

            $key = strip_tags(clean($key));

            if (in_array($key, $this->keysToEncrypt)) {

                $dataToSend[$key] = Crypt::encrypt(strip_tags(clean($item)));

            } else if (in_array($key, $this->keysToHash)) {

                $dataToSend[$key] = Hash::make(strip_tags(clean($item)));

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

    public function deleteUser(Request $request, $doc_id) {

        //$docId = $request->get('doc_id');
        $revId = $request->get('rev');

        // TODO delete user by its id and rev
       // die($doc_id . ' - ' . $revId);
        $response = $this->client->request('DELETE', 'http://' . $this->authentication . '@127.0.0.1:5984/users_pouch/' . $doc_id . '?rev=' . $revId);

        return response()->json([
            $response
        ]);
    }

    public function getUser(Request $request) {

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

    /**
     * Get al users with docs
     */
    public function getUsers() { // _all_docs?include_docs=true

        $response = $this->client->request('GET', 'http://' . $this->authentication . '@127.0.0.1:5984/users_pouch/_all_docs?include_docs=true');

        $responseData = json_decode($response->getBody()->getContents());

        $dataToHandle = $responseData->rows;

        foreach ($dataToHandle as $item) {

            if (isset($item->doc->name)) {
                $item->doc->name = Crypt::decrypt($item->doc->name);
            }

            if (isset($item->doc->password)) {
                unset($item->doc->password);
            }
//            else {
//                unset($item); // filter
//            }

        }

        $responseData->rows = $dataToHandle;
        //die(print_r($responseData->rows));
        // decrypt content

        return response()->json([
            $responseData
        ]);

    }
}
