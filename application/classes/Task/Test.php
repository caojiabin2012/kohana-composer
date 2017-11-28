<?php defined('SYSPATH') or die('No direct script access.');

class Task_Test extends Minion_Task
{

    protected $_options = array(
        'parm' => NULL,
    );
    protected function _execute(array $params)
    {
        var_dump($params);
    }
}
