<?php

namespace minimvc\tests\units;

use minimvc;

require_once __DIR__ . '/Test.php';

class Controller extends Test
{
    public function testGetBaseUrl()
    {
        $_SERVER = array(
            'SCRIPT_NAME' => '/folder/index.php',
            'SERVER_NAME' => 'server_name.com',
        );
        $this->string((new \mock\minimvc\Controller)->getBaseUrl())
            ->isEqualTo('http://server_name.com/folder/')
        ;

        $_SERVER = array(
            'SCRIPT_NAME' => '/index.php',
            'SERVER_NAME' => 'server_name.com',
        );
        $this->string((new \mock\minimvc\Controller)->getBaseUrl())
            ->isEqualTo('http://server_name.com/')
        ;

        $_SERVER = array(
            'SCRIPT_NAME' => '/index.php',
            'SERVER_NAME' => 'server_name.com',
            'HTTPS' => 'on',
        );
        $this->string((new \mock\minimvc\Controller)->getBaseUrl())
            ->isEqualTo('https://server_name.com/')
        ;

        $_SERVER = array(
            'SCRIPT_NAME' => '/some/other/folder/index.php',
            'SERVER_NAME' => 'www.server_name.com',
            'HTTPS' => 'on',
        );
        $this->string((new \mock\minimvc\Controller)->getBaseUrl())
            ->isEqualTo('https://www.server_name.com/some/other/folder/')
        ;
    }

    public function testGetWords()
    {
        $mock = new \mock\minimvc\Controller;
        $_SERVER = array(
            'REQUEST_URI' => "/first/second/third",
            'SCRIPT_NAME' => "/index.php",
        );        
        $this->array($mock->getWords())->isEqualTo(array('second','third'));

        $mock = new \mock\minimvc\Controller;
        $_SERVER = array(
            'REQUEST_URI' => "/first/second/third/",
            'SCRIPT_NAME' => "/index.php",
        );
        $this->array($mock->getWords())->isEqualTo(array('second','third'));

        $mock = new \mock\minimvc\Controller;
        $_SERVER = array(
            'REQUEST_URI' => "/base/first/second/third/",
            'SCRIPT_NAME' => "/base/index.php",
        );
        $this->array($mock->getWords())->isEqualTo(array('second','third'));

        $mock = new \mock\minimvc\Controller;
        $_SERVER = array(
            'REQUEST_URI' => "/base/first/",
            'SCRIPT_NAME' => "/base/index.php",
        );
        $this->array($mock->getWords())->isEqualTo(array());

        $mock = new \mock\minimvc\Controller;
        $_SERVER = array(
            'REQUEST_URI' => "/base/first",
            'SCRIPT_NAME' => "/base/index.php",
        );
        $this->array($mock->getWords())->isEqualTo(array());

        $mock = new \mock\minimvc\Controller;
        $_SERVER = array(
            'REQUEST_URI' => "/base/",
            'SCRIPT_NAME' => "/base/index.php",
        );
        $this->array($mock->getWords())->isEqualTo(array());

        $mock = new \mock\minimvc\Controller;
        $_SERVER = array(
            'REQUEST_URI' => "/base/?param=123",
            'SCRIPT_NAME' => "/base/index.php",
        );
        $this->array($mock->getWords())->isEqualTo(array());

        $mock = new \mock\minimvc\Controller;
        $_SERVER = array(
            'REQUEST_URI' => "/base/first/?param=123",
            'SCRIPT_NAME' => "/base/index.php",
        );
        $this->array($mock->getWords())->isEqualTo(array());

        $mock = new \mock\minimvc\Controller;
        $_SERVER = array(
            'REQUEST_URI' => "/first/second?param=123&pother=bla",
            'SCRIPT_NAME' => "/index.php",
        );
        $this->array($mock->getWords())->isEqualTo(array('second'));
    }
    
    public function testGetAction() {
        $mock = new \mock\minimvc\Controller;
        $_SERVER = array(
            'REQUEST_URI' => "/first/second?param=123&pother=bla",
            'SCRIPT_NAME' => "/index.php",
        );
        $this->string($mock->getAction())->isEqualTo('first');

        $mock = new \mock\minimvc\Controller;
        $_SERVER = array(
            'REQUEST_URI' => "/base/",
            'SCRIPT_NAME' => "/base/index.php",
        );
        $this->string($mock->getAction())->isEqualTo('index');

        $mock = new \mock\minimvc\Controller;
        $_SERVER = array(
            'REQUEST_URI' => "/base/action",
            'SCRIPT_NAME' => "/base/index.php",
        );
        $this->string($mock->getAction())->isEqualTo('action');

        $mock = new \mock\minimvc\Controller;
        $_SERVER = array(
            'REQUEST_URI' => "/base/action/",
            'SCRIPT_NAME' => "/base/index.php",
        );
        $mock->setAction('otherAction');
        $this->string($mock->getAction())->isEqualTo('otherAction');

        $mock = new \mock\minimvc\Controller;
        $_SERVER = array(
            'REQUEST_URI' => "/base/action/",
            'SCRIPT_NAME' => "/base/index.php",
        );
        $this->string($mock->getAction())->isEqualTo('action');
        $mock->setAction('otherAction');
        $this->string($mock->getAction())->isEqualTo('otherAction');
    }

}
