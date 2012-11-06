<?php

namespace test\units;

use \mageekguy\atoum;
//use \vendor\project;

include_once dirname(__FILE__) . '/BaseUnitTest.php';

class MiniMVC_Controller extends BaseUnitTest
{
    public function testGetBaseUrl()
    {
    
        $mock = new \mock\MiniMVC_Controller;

        $_SERVER = array(
            'SCRIPT_NAME' => '/folder/index.php',
            'SERVER_NAME' => 'server_name.com',
        );

        $this->string($mock->getBaseUrl())->isEqualTo('http://server_name.com/folder/')
        ;

        $_SERVER = array(
            'SCRIPT_NAME' => '/index.php',
            'SERVER_NAME' => 'server_name.com',
        );

        $this->string($mock->getBaseUrl())->isEqualTo('http://server_name.com/')
        ;

        $_SERVER = array(
            'SCRIPT_NAME' => '/index.php',
            'SERVER_NAME' => 'server_name.com',
            'HTTPS' => 'on'
        );

        $this->string($mock->getBaseUrl())->isEqualTo('https://server_name.com/')
        ;

        
        $_SERVER = array(
            'SCRIPT_NAME' => '/some/other/folder/index.php',
            'SERVER_NAME' => 'www.server_name.com',
            'HTTPS' => 'on'
        );

        $this->string($mock->getBaseUrl())->isEqualTo('https://www.server_name.com/some/other/folder/')
        ;
    }
    public function testGetWords()
    {
        $mock = new \mock\MiniMVC_Controller;

        $_SERVER = array(
            'REQUEST_URI' => "/first/second/third",
            'SCRIPT_NAME' => "/index.php",
        );
        $this->array($mock->getWords())->isEqualTo(array('first','second','third'));

        $_SERVER = array(
            'REQUEST_URI' => "/first/second/third/",
            'SCRIPT_NAME' => "/index.php",
        );
        $this->array($mock->getWords())->isEqualTo(array('first','second','third'));

        $_SERVER = array(
            'REQUEST_URI' => "/base/first/second/third/",
            'SCRIPT_NAME' => "/base/index.php",
        );
        $this->array($mock->getWords())->isEqualTo(array('first','second','third'));
                
        $_SERVER = array(
            'REQUEST_URI' => "/base/first/",
            'SCRIPT_NAME' => "/base/index.php",
        );
        $this->array($mock->getWords())->isEqualTo(array('first'));

        $_SERVER = array(
            'REQUEST_URI' => "/base/first",
            'SCRIPT_NAME' => "/base/index.php",
        );
        $this->array($mock->getWords())->isEqualTo(array('first'));

        $_SERVER = array(
            'REQUEST_URI' => "/base/",
            'SCRIPT_NAME' => "/base/index.php",
        );
        $this->array($mock->getWords())->isEqualTo(array());
        
    }
}
