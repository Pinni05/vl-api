<?php
namespace App\Model\Table;


use Cake\ORM\Table;

class MdlUsersTable extends Table
{

    /**
    * Initialize method
    *
    * @param array $config The configuration for the Table.
    * @return void
    */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('external_auth_active_mdl_users');

    }
    public function checkUser($username, $password){
        $validLogin = 0;
        $user = $this->find()->where([
            'username' => $username
        ])->first();
        if(password_verify($password, $user->PASSWORD)) {
            $validLogin = 1;
        }

        return $validLogin;
    }

}
