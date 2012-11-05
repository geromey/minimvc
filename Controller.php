<?php

    abstract class MiniMVC_Controller {

        /**
         * @var string
         */
        private $_layout = 'default';

        /**
         * @var array
         */
        protected $_data = array();

        /**
         * Magic method to get template data
         *
         * @param string $name
         *
         * @return mixed
         */
        public function __get($name) {
            return isset($this->_data[$name]) ? $this->_data[$name] : null;
        }

        /**
         * Magic method to check if template data exists
         *
         * @param string $name
         *
         * @return boolean
         */
        public function __isset($name) {
            return isset($this->_data[$name]);
        }

        /**
         * Magic Method to set template data
         *
         * @param string$name
         *
         * @param mixed $value
         */
        public function __set($name, $value) {
            $this->_data[$name] = $value;
        }
        
        abstract protected function getViewPath();

        /**
         * @param string $layout
         *
         * @return MiniMVC_Controller
         */
        protected function setLayout($layout) {
            $this->_layout = $layout;

            return $this;
        }

        /**
         * @return MiniMVC_Controller
         */
        public function noLayout() {
            return $this->setLayout(null);
        }

        /**
         * @return string
         */
        protected function getLayout() {
            return $this->_layout;
        }
        
        /**
         * return the default lang.
        
     * This function most be overriden when using multi languages
         *
         */
        protected function getDefaultLang() {
            return null;
        }

        /**
         * Set the autorised langs
         * This function most be overriden when using multi languages
         *
         * @param array $langs 
         */
        protected function getAutorisedLangs() {
        	return null;
        }
        
        /**
         * Override preRun() if you want to run some code before any page
         */
        protected function preRun() {}


        protected function getBaseUrl() {
            $url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';
            
            $this->baseUrl = 'http://' . $_SERVER['SERVER_NAME'] . $url;
        }
        
        protected function getWords() {
            $url = ;
            
            $shortUrl = trim(substr($_SERVER['REQUEST_URI'], strlen(rtrim(dirname($_SERVER['SCRIPT_NAME']),'/'))),'/');
            
            $words = explode('/',$shortUrl);
            
            if ('' == $words[0]) {
                array_shift($words);
            }
        }

        /**
         * Run Controller
         *
         * @throws RuntimeException
         */
        public function run() {
        
            $baseUrl = $this->getBaseUrl();

            $words =$this->getWords();

            if ($this->useLang()) {
                $this->lang = '';
                
                if (count($words) == 0) {
                    $this->redirect($baseUrl . $this->getDefaultLang() . '/');
                }
                $this->lang = $words[0];
                array_shift($words);
                
                if (!in_array($this->lang, $this->getAutorisedLangs())) {
                    $this->redirect($baseUrl . $this->getDefaultLang() . '/');
                }
            }
            
            if (count($words) == 0) {
            	  $this->action = 'index';
            }
            else {
                $this->action = $words[0];
                array_shift($words);
            }

            $this->words = $words;

            // fix the charset
            header('Content-type: text/html; charset=utf-8');

            $this->preRun();

            $this->runFrontController();
        }


        protected function useLang() {
            return ($this->_autorisedLangs !== null);
        }

        /**
         * Run action and display view
         *
         * @param $action
         */
        private function runFrontController() {
            // CONTROLLER
            $method = 'do' . ucfirst($this->action);

            if(!method_exists($this, $method)) {
                error_log('Unknown Action "' . $this->action . '"');

                $this->badAction = $this->action;

                $action = 'error404';
                $method = 'doError404';
            }

            $this->$method();

            // VIEW
            
            if ($this->useLang()) {
                $viewFile = sprintf(
                    '%s/%s/%s.php',
                    $this->getViewPath(),
                    $this->lang,
                    $this->action
                );
            }
            else {
                $viewFile = sprintf(
                    '%s/%s.php',
                    $this->getViewPath(),
                    $this->action
                );
            }

            ob_start();

            if (!$this->includeTemplate($viewFile, $this->_data) && $this->lang != $this->getDefaultLang()) {
            	  $viewFile = sprintf(
		                '%s/%s/%s%s.php',
		                $this->_viewPath,
		                $this->getDefaultLang(),
		                $this->action,
		                ucfirst($viewType)
		            );

		            $this->includeTemplate($viewFile, $this->_data);
            }

            $__content__ = ob_get_contents();
            ob_end_clean();

            // LAYOUT
            $layout = $this->getLayout();

            if($layout === null) {
                echo $__content__;
            }
            else {
                $layoutFile = sprintf(
                    '%s/__%s.php',
                    $this->_viewPath,
                    $layout
                );

                $this->noLayout();

                // add content in data
                $this->__content__ = $__content__;

                $this->includeTemplate(
                    $layoutFile,
                    $this->_data
                );
            }
        }

        /**
         * Include a partial file
         *
         * @param string $view
         * @param array $parameters
         */
        protected function includePartial($view, array $parameters = array()) {
            $viewFile = sprintf(
                '%s/_%s.php',
                $this->_viewPath,
                $view
            );

            $this->includeTemplate($viewFile, $parameters);
        }

        /**
         * Include a template
         *
         * @param string $viewFile
         * @param array $parameters
         */
        private function includeTemplate($viewFile, array $parameters = array()) {
            if(file_exists($viewFile)) {
                extract($parameters);

                require $viewFile;
                return true;
            }
            else {
                error_log('Unknown view "' . $viewFile . '"');
                return false;
            }
        }

        /**
         * default method for error 404 action
         *
         * @return string
         */
        protected function doError404() {
            if (!headers_sent()) {
                header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
            }

            return '';
        }

        public function getUrl($action = null, $lang = null) {
        	if (null === $lang) {
        		$lang = $this->lang;
        	}
        	if (null === $action) {
        		$action = $this->action;
        	}

        	if ('index' == $action) {
        		$action = '';
        	}

        	return htmlspecialchars($lang . '/' . $action);
        }

        protected function url($action = null, $lang = null) {
        	echo $this->getUrl($action, $lang);
        }

        protected function getOut() {

        	$langId = 0;
        	$numArgs = func_num_args();
        	foreach ($this->_autorisedLangs as $id => $autorisedLang) {
        		if ($this->lang == $autorisedLang && $id < $numArgs) {
        			$langId = $id;
        			break;
        		}
        	}

        	return htmlspecialchars(func_get_arg($langId));
        }

        protected function out() {
        	$args = func_get_args();
            echo call_user_func_array(array($this, 'getOut'), $args);
        }
        
        private function redirect($url) {
            //error_log('Location: ' . $url);
            header('Location: ' . $url);
            exit;
        }
    }
