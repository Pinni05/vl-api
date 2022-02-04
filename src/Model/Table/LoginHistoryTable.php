<?php
namespace App\Model\Table;


use Cake\ORM\Table;

class LoginHistoryTable extends Table
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
        $this->setTable('login_history');

    }
    public function addLoginHistory($username, $loginStatus){
        $query = $this->query();
        $query->insert(['username', 'loginstatus','logintimestamp'])
            ->values([
                'username' => $username,
                'loginstatus' => $loginStatus,
                'logintimestamp' => new \DateTime(),
            ])
            ->execute();
        /*$loginHistory = $this->newEmptyEntity();
        $loginHistory->username = $username;
        $loginHistory->loginstatus = $loginStatus;;
        $loginHistory->logintimestamp =  new \DateTime();


        $this->save($loginHistory);*/

        return true;
    }

}
