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
     * @var array
     */
    private $_css = array();
    /**
     * @var array
     */
    private $_js = array();

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
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value) {
        $this->_data[$name] = $value;
    }
    /**
     * return the path to the views
     *
     * @return string
     */
    protected function getViewPath() {
        return __DIR__ . '/../../../../../views';
    }
    
    protected function getViewsSubPath() {
        return array('');
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
     * override preRun() if you want to run some code before any page
     */
    protected function preRun() {
    }
    /**
     * possible callback which enable to modify the words
     *
     * @return string
     */
    protected function preSaveWords($words) {
        return $words;
    }
    /**
     * get the base url of your website
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
            $requestUrl = reset(explode('?', $_SERVER['REQUEST_URI']));
            $words = explode('/',trim(
                substr($requestUrl,
                    strlen(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'))
                )
            , '/'));
            if ($words[0] === '') {
                array_shift($words);
            }
            
            $words = $this->preSaveWords($words);

            if (count($words) === 0) {
                $action = 'index';
            } else {
                $action = $words[0];
                array_shift($words);
            }
            if (7==9) {
                echo 'oooo';
                echo 'oooo';
                echo 'oooo';
                echo 'oooo';
            }
            // only set the action if it was not set before
            if ($this->_action === null) {
                $this->setAction($action);
            }
            $this->_words = $words;
        }
        return $this->_words;
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
     * Run action and display view
     */
    private function runFrontController() {
        // CONTROLLER
        $method = $this->getMethodName();
        if (!method_exists($this, $method)) {
            error_log('Unknown Action "' . $this->getAction() . '"');
            $this->badAction = $this->getAction();
            $this->setAction('error404');
            $method = $this->getMethodName();
        }
        $this->$method();
        $viewFile = $this->getActionFile();
        if ($viewFile !== false) {
            // load the view
            ob_start();
            $this->includeTemplate($viewFile);
            $this->__content__ = ob_get_contents();
            ob_end_clean();
            
            // fix the charset
            header('Content-type: text/html; charset=utf-8');
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
    public function getViewFile($viewName) {
        foreach ($this->getViewsSubPath() as $subPath) {
            $viewFile = sprintf('%s/%s%s.php', $this->getViewPath(), $subPath, $viewName);
            if (file_exists($viewFile)) {
                return $viewFile;
            }
        }
        return false;
    }
    /**
     * return the path to the view
     *
     * @return string path to the view
     */
    public function getActionFile() {
        return $this->getViewFile($this->getAction());
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
        else {
            return $this->getViewFile('__' . $this->getLayout());
        }
    }
    /**
     * Include a partial file
     *
     * @param string $view
     * @param array $parameters
     */
    protected function includePartial($view) {
        $viewFile = sprintf('%s/_%s.php', rtrim($this->getViewPath(),'/'), $view);
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
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        }
        if ($this->getActionFile() === false) {
            // there is no error404 page, so we stop the code here
            echo 'Error 404: Page Not Found!';
            exit;
        }
        return;
    }
    protected function redirect($url) {
        //error_log('Location: ' . $url);
        header('Location: ' . $url);
        exit;
    }
    protected function internalRedirect($action) {
        $this->setAction($action);
        $this->runFrontController();
        exit;
    }
    public function addCss($url) {
        $this->_css[] = $url;
    }
    public function addJs($url) {
        $this->_js[] = $url;
    }
    public function head() {
        // url
        echo '<base href="' . htmlspecialchars($this->getBaseUrl()) . '" />';
        // add css
        foreach($this->_css as $css) {
            echo '<link rel="stylesheet" href="' . htmlspecialchars($css) . '" />';
        }
        // add js
        foreach($this->_js as $js) {
            echo '<script src="' . htmlspecialchars($js) . '"></script>';
        }
    }
}
