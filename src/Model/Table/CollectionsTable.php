<?php
namespace App\Model\Table;


use Cake\ORM\Table;

class CollectionsTable extends Table
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
        $this->setTable('collections');

    }

}
