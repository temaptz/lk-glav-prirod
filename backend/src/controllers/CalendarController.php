<?php
namespace app\controllers;

use app\models\CalendarEvent;
use app\traits\OrgFilterTrait;

class CalendarController extends BaseRestController
{
    use OrgFilterTrait;
    
    public $modelClass = CalendarEvent::class;
}
