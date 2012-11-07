<?php
namespace minimvc;

abstract class Controller {
    /**
     * @var string
     */
    private $_layout = 'default';
    /**
     * @var string
     */
    private $_baseUrl = null;
    /**
     * @var string
     */
    private $_action = null;
    /**
     * @var array
     */
    private $_words = null;
    /**
     * @var array
     */
    protected $_data = array();

    /**
     * Magic method to get template data
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        return isset($this->_data[$name]) ? $this->_data[$name] : null;
    }
    /**
     * Magic method to check if template data exists
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name) {
        return isset($this->_data[$name]);
    }
    /**
     * Magic Method to set template data
     *
     * @param string$name
     * @param mixed $value
     */
    public function __set($name, $value) {
        $this->_data[$name] = $value;
    }
    protected function getViewPath() {
        return __DIR__ . '/../../../../../views';
    }
    /**
     * @param string $layout
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
    protected function preRun() {
    }
    /**
     * get the base url of the website
     *
     * @return string
     */
    public function getBaseUrl() {
        if ($this->_baseUrl === null) {
            $this->_baseUrl = 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's': '') . '://'
                . $_SERVER['SERVER_NAME']
                . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';
        }
        return $this->_baseUrl;
    }
    /**
     * get the words that compose the URL
     *
     * @return string
     */
    public function getWords() {
        if ($this->_words === null) {
            $requestUrl = explode('?', $_SERVER['REQUEST_URI'])[0];
            $words = explode('/',trim(
                substr($requestUrl,
                    strlen(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'))
                )
            , '/'));
            if ($words[0] === '') {
                array_shift($words);
            }

            if ($this->useLang()) {
                $this->lang = '';
                if (count($words) === 0) {
                    $this->redirect($this->getBaseUrl() . $this->getDefaultLang() . '/');
                }
                $this->lang = $words[0];
                array_shift($words);
                if (!in_array($this->lang, $this->getAutorisedLangs())) {
                    $this->redirect($this->getBaseUrl() . $this->getDefaultLang() . '/');
                }
            }
            if (count($words) === 0) {
                $action = 'index';
            } else {
                $action = $words[0];
                array_shift($words);
            }
            // only set the action if it was not set before
            if ($this->_action === null) {
                $this->setAction($action);
            }
            $this->_words = $words;
        }
        return $words;
    }
    public function getAction() {
        if ($this->_action === null) {
            // the name of the action is determinated when calculating the words
            $this->getWords();
        }
        return $this->_action;
    }
    public function setAction($action) {
        $this->_action = $action;
    }
    /**
     * Run Controller
     */
    public function run() {
        // fix the charset
        header('Content-type: text/html; charset=utf-8');
        $this->preRun();
        $this->runFrontController();
    }
    /**
     * return the method name of a specific action
     * @return boolean
     */
    public function getMethodName() {
        return 'do' . ucfirst($this->getAction());
    }
    /**
     * Say if the controller is using languages
     * @return boolean
     */
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
        $method = $this->getMethodName();
        if (!method_exists($this, $method)) {
            error_log('Unknown Action "' . $this->action . '"');
            $this->badAction = $this->getAction();
            $this->setAction('error404');
            $method = $this->getMethodName();
        }
        $this->$method();
        $viewFile = $this->getViewFile();
        if ($viewFile !== false) {
            // load the view
            ob_start();
            $this->includeTemplate($viewFile);
            $this->__content__ = ob_get_contents();
            ob_end_clean();
            // load the layout
            $layoutFile = $this->getLayoutFile();
            if ($layoutFile === false) {
                // if there is no layout, simply echo the content
                echo $this->__content__;
            } else {
                $this->includeTemplate($layoutFile);
            }
        }
    }
    /**
     * return the path to the view
     *
     * @return string path to the view
     */
    public function getViewFile() {
        if ($this->useLang()) {
            $viewFile = sprintf('%s/%s/%s.php', rtrim($this->getViewPath(),'/'), $this->lang, $this->getAction());
            if (!file_exists($viewFile)) {
                // if the view does not exists in the selected language
                // then we use the default language view
                $viewFile = sprintf('%s/%s/%s%s.php', rtrim($this->getViewPath(),'/'), $this->getDefaultLang(), $this->getAction());
            }
        } else {
            $viewFile = sprintf('%s/%s.php', rtrim($this->getViewPath(),'/'), $this->getAction());
        }
        if (file_exists($viewFile)) {
            return $viewFile;
        } else {
            return false;
        }
    }
    /**
     * return the path to the view
     *
     * @return string path to the view
     */
    public function getLayoutFile() {
        $layout = $this->getLayout();
        if (null === $layout) {
            return false;
        }
        $layoutFile = sprintf('%s/__%s.php', rtrim($this->getViewPath(),'/'), $this->getLayout());
        if (file_exists($layoutFile)) {
            return $layoutFile;
        } else {
            return false;
        }
    }
    /**
     * Include a partial file
     *
     * @param string $view
     * @param array $parameters
     */
    protected function includePartial($view) {
        $viewFile = sprintf('%s/_%s.php', $this->_viewPath, $view);
        $this->includeTemplate($viewFile);
    }
    /**
     * Include a template
     *
     * @param string $viewFile
     * @param array $parameters
     */
    private function includeTemplate($viewFile) {
        extract($this->_data);
        require $viewFile;
    }
    /**
     * default method for error 404 action
     *
     * @return string
     */
    protected function doError404() {
        if (!headers_sent()) {
            header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
        }
        if ($this->getViewFile() === false) {
            // there is no error404 page, so we stop the code here
            echo 'Error 404: Page Not Found!';
            exit;
        }
        return '';
    }
    public function getUrl($action = null, $lang = null) {
        if (null === $lang) {
            $lang = $this->lang;
        }
        if (null === $action) {
            $action = $this->getAction();
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
        foreach ($this->getAutorisedLangs() as $id => $autorisedLang) {
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
