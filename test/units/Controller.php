<?php

namespace test\units;

use \mageekguy\atoum;
//use \vendor\project;

include_once dirname(__FILE__) . '/BaseUnitTest.php';

class MiniMVC_Controller extends BaseUnitTest
{
    public function testGetBaseUrl()
    {
        $_SERVER = array(
            'SCRIPT_NAME' => '/folder/index.php',
            'SERVER_NAME' => 'server_name.com',
        );

        $this->string(\MiniMVC_Controller::getBaseUrl())->isEqualTo('http://server_name.com/folder/')
        ;

        $_SERVER = array(
            'SCRIPT_NAME' => '/index.php',
            'SERVER_NAME' => 'server_name.com',
        );

        $this->string(\MiniMVC_Controller::getBaseUrl())->isEqualTo('http://server_name.com/')
        ;

        $_SERVER = array(
            'SCRIPT_NAME' => '/index.php',
            'SERVER_NAME' => 'server_name.com',
            'HTTPS' => 'on'
        );

        $this->string(\MiniMVC_Controller::getBaseUrl())->isEqualTo('https://server_name.com/')
        ;

        
        $_SERVER = array(
            'SCRIPT_NAME' => '/some/other/folder/index.php',
            'SERVER_NAME' => 'www.server_name.com',
            'HTTPS' => 'on'
        );

        $this->string(\MiniMVC_Controller::getBaseUrl())->isEqualTo('https://www.server_name.com/some/other/folder/')
        ;
    }
}
