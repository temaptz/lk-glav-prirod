<?php
namespace app\controllers;

use app\models\ClientRequirement;
use app\traits\OrgFilterTrait;

class ClientRequirementController extends BaseRestController
{
    use OrgFilterTrait;
    
    public $modelClass = ClientRequirement::class;
}
