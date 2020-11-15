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
    //private $client;
    private $authentication;
    private $purifier;
    private $userService;

    public function __construct()
    {
        //$this->client = new \GuzzleHttp\Client(['headers' => ['Content-Type' => 'application/json']]);
        $this->authentication = config('app.couchdb-auth');
        $this->userService = new UserService();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createUser(Request $request) {

        $sessionToken = $request->cookie('session_token');

        $userCreateData = $request->all(); //['doc'];
        $userCreateData['_id'] = config('app.user_prefix') . $userCreateData['name'];
        unset($userCreateData['passwordrepeat']); // TODO check if confirmation password is right

        $response = $this->userService->createUser($userCreateData, $sessionToken);

        return response()->json([
            $response['data']
        ], $response['status']);

    }

    public function editUser(Request $request) {

        $sessionToken = $request->cookie('session_token');

        $userEditData = $request->all(); //['doc'];
//        array_walk_recursive($userEditData, [$this->userService, 'encryptOrHashUserData']);

        $response = $this->userService->editUser($userEditData, $sessionToken);

        return response()->json([
            $response['data']
        ], $response['status']);

    }

    /**
     * @param Request $request
     * @param $doc_id
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteUser(Request $request, $doc_id) {

        $sessionToken = $request->cookie('session_token');

        //$docId = $request->get('doc_id');
        $revId = $request->get('rev');

        // TODO delete user by its id and rev
        $response = $this->userService->deleteUser($doc_id, $revId, $sessionToken);

        return response()->json([
            $response['data']
        ], $response['status']);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUser(Request $request) {

        $sessionToken = $request->cookie('session_token');

        $documentId = $request->get('document_id');

        $response = $this->userService->getUser($documentId, $sessionToken);

//        $dataToHandle = json_decode($response->getBody()->getContents());
//
//        array_walk_recursive($dataToHandle, [$this->userService, 'decryptUserData']);

        return response()->json([
            $response['data']
        ], $response['status']);
    }

    /**
     * Get al users with docs
     */
    public function getUsers(Request $request) { // _all_docs?include_docs=true

        $sessionToken = $request->cookie('session_token');

        $response = $this->userService->getUsers($sessionToken);

        return response()->json([
            $response['data']
        ], $response['status']);

    }
}
