<?php
namespace minimvc;

abstract class LangController extends Controller {
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
        return ($this->_autorisedLangs !== null);
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
                
                if (file_exists($viewFile)) {
                    return $viewFile;
                } else {
                    return false;
                }
            }
        } else {
            return parent::getViewFile();
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
}
