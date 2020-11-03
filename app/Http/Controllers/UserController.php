<?php

namespace App\Http\Controllers;

use App\Http\UserService;
use App\Models\ReturnStatuses;
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
    private $userService;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client(['headers' => ['Content-Type' => 'application/json']]);
        $this->authentication = config('app.couchdb-auth');
        $this->userService = new UserService();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createUser(Request $request) {

        $userCreateData = $request->all(); //['doc'];
        $userCreateData['username'] = $userCreateData['name'];

        array_walk_recursive($userCreateData, [$this->userService, 'encryptOrHashUserData']);

        $response = $this->userService->createUser(null, $userCreateData, null);

        return response()->json([
            'data' => $response
        ], ReturnStatuses::_200);

    }


    /**
     * @param Request $request
     * @param $doc_id
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteUser(Request $request, $doc_id) {

        //$docId = $request->get('doc_id');
        $revId = $request->get('rev');

        // TODO delete user by its id and rev
        $response = $this->userService->deleteUser($doc_id, $revId); //$this->client->request('DELETE', 'http://' . $this->authentication . '@127.0.0.1:5984/users_pouch/' . $doc_id . '?rev=' . $revId);

        return response()->json([
            $response
        ], ReturnStatuses::_200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUser(Request $request) {

        $documentId = $request->get('document_id');

        $response = $this->userService->getUser($documentId);

        $dataToHandle = json_decode($response->getBody()->getContents());

        array_walk_recursive($dataToHandle, [$this->userService, 'decryptUserData']);

        return response()->json([
            $dataToHandle
        ], ReturnStatuses::_200);
    }

    /**
     * Get al users with docs
     */
    public function getUsers() { // _all_docs?include_docs=true

        $response = $this->userService->getUsers();

        $responseData = json_decode($response->getBody()->getContents());

        $dataToHandle = $this->userService->decryptUsersData($responseData->rows);

        $responseData->rows = $dataToHandle;

        return response()->json([
            $responseData
        ], ReturnStatuses::_200);

    }
}
