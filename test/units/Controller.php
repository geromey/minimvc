<?php

namespace tests\units;


include dirname(__FILE__) . '/../../Controller.php';


use \mageekguy\atoum;
//use \vendor\project;

class MiniMVC_Controller extends atoum\test
{
    public function testGetBaseUrl()
    {
        $_SERVER['SCRIPT_NAME'] = '/folder/index.php';
        $_SERVER['SERVER_NAME'] = 'server_name';

        $this->string(\MiniMVC_Controller::getBaseUrl())->isEqualTo('http://server_name/folder/')
        ;
    }
}
