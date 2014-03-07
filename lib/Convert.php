<?php
/**
 * Minerva_Convert_Invoice:: defines an API for implementing document export.
 *
 * $Horde: incubator/minerva/lib/Convert.php,v 1.64 2009/11/09 19:58:37 duck Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.php.
 *
 * @author  Duck <duck@obala.net>
 * @package Minerva
 */
class Minerva_Convert {

    /**
     * Handle for driver parameters
     *
     * @var params
     */
    protected $_params = array();

    /**
     * Mime Type we are generationg
     *
     * @var mime
     */
    protected $_mime = 'html';

    /**
     * Template we are currently using
     *
     * @var template
     */
    protected $_template = 'invoice';

    /**
     * Invoice default currency
     *
     * @var defaultCurrency
     */
    protected $_defaultCurrency = array();

    /**
     * Creator
     */
    public function __construct($template = null)
    {
        $this->_params = $GLOBALS['conf']['convert'];

        if (empty($this->_params['save_path'])) {
            $this->_params['save_path'] = Horde::getTempDir();
        }

        if (empty($this->_params['template_path'])) {
            $this->_params['template_path'] = MINERVA_TEMPLATES . '/convert/';
        }

        if ($template) {
            $this->_template = $template;
        }
    }

    /**
     * Return a view variable
     *
     * @param string $name Variable name to retrieve
     */
    public function __get($name)
    {
        return isset($this->$name) ? $this->$name : '';
    }

    /**
     * Attempts to return a concrete Minerva_Convert instance based on $driver.
     *
     * @param string $driver  The type of the concrete Minerva_Convert subclass
     *                        to return.  The class name is based on the
     *                        storage driver ($driver).  The code is
     *                        dynamically included.
     *
     * @return Minerva_Convert  The newly created concrete Minerva_Convert
     *                          instance, or PEAR_Error on an error.
     */
    public static function factory($driver = 'invoice')
    {
        $template = strtolower($driver);
        $driver = ucfirst($driver);
        if (!file_exists(dirname(__FILE__) . '/Convert/' . $driver . '.php')) {
            $template = strtolower($driver);
            $driver = 'Invoice';
        }

        $class = 'Minerva_Convert_' . $driver;

        if (class_exists($class)) {
            return new $class($template);
        } else {
            return PEAR::raiseError(sprintf("Convert driver \"%s\" do not exists.", $driver));
        }
    }

    /**
     * Save convtent
     */
    protected function _saveContent()
    {
        ob_start();
        require $this->_params['save_path'] . '/' . $this->_template . '.php';
        $content = ob_get_clean();

        if (Horde_Util::extensionExists('tidy')) {
            $tidy_config = array('wrap' => 0,
                                 'indent' => true,
                                 'indent-spaces' => 4,
                                 'tab-size' => 2,
                                 'output-xhtml' => true,
                                 'enclose-block-text' => true,
                                 'hide-comments' => true,
                                 'numeric-entities' => true);

            $tidy = tidy_parse_string($content, $tidy_config, 'utf8');
            $tidy->cleanRepair();
            $content = tidy_get_output($tidy);
        }

        $path = $this->_savePath();
        if (!file_put_contents($path, $content)) {
            return PEAR::raiseError(sprintf(_("Cannot save to file %s."), $path));
        }

        return $path;
    }

    /**
     * Path where to save document
     *
     * @return string path
     */
    protected function _savePath()
    {
        return $this->_params['save_path'] . '/' . $this->_template  . '/' .
               date('Ymd-') . uniqid() . '.' . $this->_mime;
    }

    /**
     * Returns the usable tempalte path
     *
     * @return string path
     */
    private function _getTemplatePath()
    {
        static $dir;

        if (!$dir) {
            if (file_exists($this->_params['template_path'] . '/' . $this->_template)) {
               $dir = $this->_params['template_path'] . '/' . $this->_template;
            } else {
                $dir = MINERVA_TEMPLATES . '/convert/' . $this->_template;
            }
        }

        return $dir;
    }

    /**
     * Returns the usable headers/footer path
     *
     * @return string path
     */
    private function _getHeaderFooterPath()
    {
        static $dir;

        if (!$dir) {
            if (file_exists($this->_params['template_path'] . '/' . $this->_template . '/header.php')) {
                $dir = $this->_params['template_path'] . '/' . $this->_template;
            } elseif (file_exists($this->_params['template_path'] . '/header.php')) {
                $dir = $this->_params['template_path'];
            } else {
                $dir = MINERVA_TEMPLATES . '/convert/';
            }
        }

        return $dir;
    }

    /**
     * Return file paths of the template files
     *
     * @return string template content
     */
    public function parts()
    {
        static $templates;

        $templates = array();
        if (file_exists($this->_getHeaderFooterPath() . '/header.php')) {
            $templates['header'] = $this->_getHeaderFooterPath() . '/header.php';
        } else {
            $path = $this->_getHeaderFooterPath() . '/header.php';
            return PEAR::raiseError(sprintf(_("Cannot read template file %s."), $path));
        }

        $templates['body'] = $this->_getTemplatePath() . '/' . $this->_template . '.php';

        if (file_exists($this->_getHeaderFooterPath() . '/footer.php')) {
            $templates['footer'] = $this->_getHeaderFooterPath() . '/footer.php';
        } else {
            $path = $this->_getHeaderFooterPath() . '/footer.php';
            return PEAR::raiseError(sprintf(_("Cannot read template file %s."), $path));
        }

        return $templates;
    }

    /**
     * Get template modification time
     *
     * @return unix time stamp of the template modification
     */
    private function _mTime()
    {
        $path = $this->_params['save_path'] . '/' . $this->_template . '.php';
        $parts = $this->parts();

        if (is_readable($path)) {
            $mTime = filemtime($path);
        } else {
            $mTime = 0;
        }

        foreach ($parts as $file) {
            if (!is_readable($file)) {
                return PEAR::raiseError(sprintf(_("Cannot read template file %s."), $file));
            }
            if (filemtime($file) > $mTime) {
                $mTime = filemtime($file);
            }
        }

        return $mTime;
    }

    /**
     * Get template modification time
     *
     * @return unix time stamp of the template modification
     */
    public function preview()
    {
        $parts = $this->parts();

        $this->company = Minerva::getCompany();
        $this->banks = Minerva::getBankAccounts();

        include $parts['header'];
        include $parts['body'];
        include $parts['footer'];
    }

    /**
     * Build tamplate cache file
     *
     * @return true of false if the template was build
     */
    private function _build()
    {
        static $build;

        if ($build) {
            return $build;
        }

        // Check if the template was already generated and
        // the mtime is lower then mtime of the template parts

        $t_mtime = $this->_mTime();
        if ($t_mtime instanceof PEAR_Error) {
            return $t_mtime;
        }

        $path = $this->_params['save_path'] . '/' . $this->_template . '.php';
        if (!file_exists($path) || $t_mtime > filemtime($path)) {

            // save_path exists?
            if (!file_exists($this->_params['save_path'])) {
                $dir = $this->_params['save_path'];
                if (!mkdir($dir)) {
                    return PEAR::raiseError(sprintf(_("Directory does not exist: %s"), $dir));
                }
            }

            // sub save_path of template exists?
            if (!file_exists($this->_params['save_path'] . '/' . $this->_template)) {
                $dir = $this->_params['save_path']. '/' . $this->_template;
                if (!mkdir($dir)) {
                    return PEAR::raiseError(sprintf(_("Directory does not exist: %s"), $dir));
                }
            }

            $content = '';
            $parts = $this->parts($this->_template);
            if (isset($parts['header'])) {
                $content = file_get_contents($parts['header']);
            }

            $content .= file_get_contents($parts['body']);

            if (isset($parts['footer'])) {
                $content .= file_get_contents($parts['footer']);
            }

            $build = file_put_contents($path, $content);
        }

        return $build;
    }

    /**
     * Expunge cached documents
     *
     * @param string    $template   Template to expunge
     */
    public function expungeCache($template)
    {
        if (is_null($template)) {
            return true;
        }

        $path = $this->_params['save_path'] . '/' . $template . '/';
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            unlink($path . $file);
        }

        unlink($this->_params['save_path'] . '/' . $template . '.php');

        return true;
    }

    /**
     * Get available templates to convert to.
     *
     * @return array Avaiable templates
     */
    public function getTemplates()
    {
        if (!is_dir($this->_params['template_path'])) {
            return PEAR::raiseError(sprintf(_("Template path %s don't exists."), $this->_params['template_path']));
        }

        if (!($dh = opendir($this->_params['template_path']))) {
            return PEAR::raiseError(sprintf(_("Cannot open dir %s."), $this->_params['template_path']));
        }

        $templates = array();
        while (($file = readdir($dh)) !== false) {
            if (!file_exists($this->_params['template_path'] . $file . '/info.php')) {
                    continue;
            }

            include $this->_params['template_path'] . $file . '/info.php';
            $templates[$file] = array('path' => $this->_params['template_path'] . $file,
                                      'name' => $template_name);
        }
        closedir($dh);

        return $templates;
    }

    /**
     * Load and set default values
     *
     * @return  $output The parsed template or PEAR_Error on failure
     */
    protected function _getTemplate()
    {
        // build invoice file
        $result = $this->_build();
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        // External files link
        $viewurl = Horde_Util::addParameter(Horde::applicationUrl('view.php', true), 'file', null);
        $this->view_url = $viewurl;

        // Add banks data
        $this->banks = Minerva::getBankAccounts();

        // Add company data
        $this->company = Minerva::getCompany();

        // Standard fields
        $this->today = Minerva::format_date($_SERVER['REQUEST_TIME'], false);
        $this->city = Minerva::getInvoicePlace();

        return true;
    }

    /**
     * Applies default formatting
     *
     * @param mixed  &$value Value to format
     * @param string $key    Index key in the array
     */
    protected function _formatOutputWalk(&$value, $key)
    {
        if (is_float($value)) {
            $value = Horde_Currencies::formatPrice($value, $this->_defaultCurrency);
        } elseif ($key == 'date') {
            $value = strftime($GLOBALS['prefs']->getValue('date_format'), strtotime($value));
            return;
        } elseif ($key == 'address' || $key == 'postal_address' || $key == 'name' || $key == 'comment') {
            $value = nl2br($value);
            return;
        }
    }

    /**
    * Convert html file to mime
    */
    protected function _convertToMime($file, $mime)
    {
        $driver = $GLOBALS['conf']['convert']['driver'];
        require_once dirname(__FILE__) . '/Convert/' . $driver . '.php';

        $class = 'Minerva_Convert_' . $driver;
        if (class_exists($class)) {
            $helper = new $class($this->_params);
        } else {
            return PEAR::raiseError(sprintf("Convert driver \"%s\" do not exists.", $driver));
        }

        if (method_exists($helper, $mime)) {
            $result = $helper->$mime($file);
        } else {
            $result = $helper->execConvert($file, $mime);
        }

        return $result;
    }

    /**
     * Set defaultCurrency from invoice currencies
     */
    protected function _setDefaultCurrency($currencies)
    {
        foreach ($currencies as $key => $value) {
            if ($value['exchange_rate'] == 1) {
                $this->_defaultCurrency = $value;
                return $value['currency_symbol'];
            }
        }
    }
}