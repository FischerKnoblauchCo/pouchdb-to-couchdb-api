<?php


namespace App\Http;


use App\Models\MutationLists;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{

    //private $authentication;
    private $client;
    private $dbUrl;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client(['headers' => ['Content-Type' => 'application/json']]);
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
     * @param $currentUser
     * @param $userCreateData
     * @param $dbClient
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createUser($currentUser, $userCreateData, $dbClient) {

        $response = $this->client->request('POST', $this->dbUrl . '/' . config('app.users_table'), [
            'body' => json_encode($userCreateData)
        ]);

        return $response;
    }

    public function editUser($currentUser, $userEditData, $dbClient) {

        $id = $userEditData['_id'];
        unset($userEditData['_id']);

        $response = $this->client->request('PUT', $this->dbUrl . '/' . config('app.users_table') .'/' . $id, [
            'body' => json_encode($userEditData)
        ]);

        return $response;
    }

    /**
     * @param $documentId
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUser($documentId) {

        $response = $this->client->request('GET', $this->dbUrl . '/' . config('app.users_table') . '/' . $documentId);

        return $response;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUsers() {

        $response = $this->client->request('GET', $this->dbUrl . '/' . config('app.users_table') . '/_all_docs?include_docs=true');

        return $response;
    }

    public function deleteUser($doc_id, $revId) {

        $response = $this->client->request('DELETE', $this->dbUrl . '/' . config('app.users_table') . '/' . $doc_id . '?rev=' . $revId);

        return $response;
    }

    private function getDatabaseLink() {

        $schema = config('app.url_schema') . '://';
        $authentication = config('app.couchdb-auth');
        $dbIpAddress = '@' . config('database.connections.couchdb.host');
        $dbPort = ':' . config('database.connections.couchdb.port');

        return $schema . $authentication . $dbIpAddress . $dbPort;
    }

}