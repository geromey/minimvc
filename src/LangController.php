<?php
namespace minimvc;

abstract class LangController extends Controller {
    
    /**
     * @var string
     */
    private $_translate = null;
    
    /**
     * return the default lang.
     * This function most be overriden when using multi languages
     */
    abstract protected function getDefaultLang();
    /**
     * Set the autorised langs
     * This function most be overriden when using multi languages
     *
     * @param array $langs
     */
    abstract protected function getAutorisedLangs();
    
    abstract protected function getTranslatePath();

    protected function getViewsSubPath() {
        $subPath = array('');
        if ($this->useLang()) {
            $subPath[] = $this->getDefaultLang() . '/';
            if ($this->lang !== $this->getDefaultLang()) {
                $subPath[] = $this->lang . '/';
            }
        }
        return $subPath;
    }
    /**
     * get the words that compose the URL
     *
     * @return string
     */
    public function preSaveWords($words) {
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
        return $words;
    }

    /**
     * Run Controller
     */
    public function run() {
        // we most call this function because it will potentially redirect 
        $this->getWords();
        parent::run();
    }

    /**
     * Say if the controller is using languages
     * @return boolean
     */
    protected function useLang() {
        return ($this->getAutorisedLangs() !== null);
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

    /**
     * Overide this function if you are using a specific translation function
     * @param string $name
     * @return boolean
     */
    protected function _($string) {
        return htmlspecialchars($string);
    }

    protected function out($string) {
        $args = func_get_args();
        echo $this->_($string);
    }

}
