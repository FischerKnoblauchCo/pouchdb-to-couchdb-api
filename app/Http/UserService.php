<?php


namespace App\Http;


use App\Models\MutationLists;
use App\Models\ReturnStatuses;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{

    use DbConnection;

    //private $authentication;
    private $client;
    private $dbUrl;

    public function __construct()
    {

        $this->client = new \GuzzleHttp\Client([
            'headers' => [
                'Content-Type' => 'application/json',
                'Cookie' => ''
            ]
        ]);
        $this->dbUrl = $this->getDatabaseLink();

        //$this->authentication = config('app.couchdb-auth');
    }

    public function encryptOrHashUserData(&$value, $key) {

        if (!is_numeric($key)) {
            if (in_array($key, MutationLists::USER_ENCRYPT_LIST)) {
                $value = Crypt::encrypt($value);
            } else if(in_array($key, MutationLists::USER_HASH_LIST)) {
                $value = Hash::make($value);
            }
        }

    }

    public function decryptUserData(&$value, $key) {

        if (in_array($key, MutationLists::USER_ENCRYPT_LIST)) {

            try {
                $value = Crypt::decrypt($value);
            } catch (\Exception $e) {

            }

        }

    }

    public function decryptUsersData($users) {

        foreach($users as $user) {
            array_walk_recursive($user->doc, [$this, 'decryptUserData']);
        }

        return $users;
    }

    /**
     * @param $userCreateData
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createUser($userCreateData, $sessionToken) {

        try {

            array_walk_recursive($userCreateData, [$this, 'encryptOrHashUserData']);

            $response = $this->client->request('POST', $this->dbUrl . '/' . config('app.users_table'), [
                'body' => json_encode($userCreateData),
                'headers' => [
                    'Cookie' => $sessionToken
                ]
            ]);

            return [
                'data' => json_decode($response->getBody()->getContents()),
                'status' => 200
            ];

        } catch (GuzzleException $e) {

            return [
                'data' => [],
                'status' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }

    }

    public function editUser($userEditData, $sessionToken) {

        try {

            $id = $userEditData['_id'];
            unset($userEditData['_id']);

            array_walk_recursive($userEditData, [$this, 'encryptOrHashUserData']);

            $response = $this->client->request('PUT', $this->dbUrl . '/' . config('app.users_table') .'/' . $id, [
                'body' => json_encode($userEditData),
                'headers' => [
                    'Cookie' => $sessionToken
                ]
            ]);

            return [
                'data' => json_decode($response->getBody()->getContents()),
                'status' => 200
            ];

        } catch (GuzzleException $e) {

            return [
                'data' => [],
                'status' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }

    }

    /**
     * @param $documentId
     * @param $sessionToken
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUser($documentId, $sessionToken) {

        try {

            $response = $this->client->request('GET', $this->dbUrl . '/' . config('app.users_table') . '/' . $documentId, [
                'headers' => [
                    'Cookie' => $sessionToken
                ]
            ]);

            $dataToHandle = json_decode($response->getBody()->getContents());

            $dataToHandle = $this->decryptUsersData($dataToHandle);

            return [
                'data' => $dataToHandle,
                'status' => 200
            ];

        } catch (GuzzleException $e) {

            return [
                'data' => [],
                'status' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }


    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUsers($sessionToken) {

        try {

            $response = $this->client->request('GET', $this->dbUrl . '/' . config('app.users_table') . '/_all_docs?include_docs=true', [
                'headers' => [
                    'Cookie' => $sessionToken
                ]
            ]);

            $dataToHandle = json_decode($response->getBody()->getContents());
            $dataToHandle = $this->decryptUsersData($dataToHandle->rows);
            $dataToHandle['rows'] = $dataToHandle;

            return [
                'data' => $dataToHandle,
                'status' => 200
            ];

        } catch (GuzzleException $e) {

            return [
                'data' => [],
                'status' => $e->getCode(),
                'message' => $e->getMessage()
            ];

        }

    }

    public function deleteUser($doc_id, $revId, $sessionToken) {

        try {

            $response = $this->client->request('DELETE', $this->dbUrl . '/' . config('app.users_table') . '/' . $doc_id . '?rev=' . $revId, [
                'headers' => [
                    'Cookie' => $sessionToken
                ]
            ]);

            return [
                'data' => json_decode($response->getBody()->getContents()),
                'status' => 200
            ];

        } catch (GuzzleException $e) {

            return [
                'data' => [],
                'status' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }

    }

}