<?php
namespace app\controllers;

use app\models\Contract;
use app\traits\OrgFilterTrait;

class ContractController extends BaseRestController
{
    use OrgFilterTrait;
    
    public $modelClass = Contract::class;
}
