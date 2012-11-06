<?php

namespace minimvc\tests\units;

use minimvc;

require_once __DIR__ . '/Test.php';

class Controller extends Test
{
    public function testGetBaseUrl()
    {
        $mock = new \mock\minimvc\Controller;

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
            'HTTPS' => 'on',
        );
        $this->string($mock->getBaseUrl())->isEqualTo('https://server_name.com/')
        ;
        
        $_SERVER = array(
            'SCRIPT_NAME' => '/some/other/folder/index.php',
            'SERVER_NAME' => 'www.server_name.com',
            'HTTPS' => 'on',
        );
        $this->string($mock->getBaseUrl())->isEqualTo('https://www.server_name.com/some/other/folder/')
        ;
    }

    public function testGetWords()
    {
        $mock = new \mock\minimvc\Controller;

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
