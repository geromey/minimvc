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
        $_SERVER['SERVER_NAME'] = 'server_name.com';

        $this->string(\MiniMVC_Controller::getBaseUrl())->isEqualTo('http://server_name.com/folder/')
        ;
        
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SERVER_NAME'] = 'server_name.com';

        $this->string(\MiniMVC_Controller::getBaseUrl())->isEqualTo('http://server_name.com/')
        ;
        
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SERVER_NAME'] = 'server_name.com';
        $_SERVER['HTTPS'] = 'on';

        $this->string(\MiniMVC_Controller::getBaseUrl())->isEqualTo('https://server_name.com/')
        ;
    }
}
