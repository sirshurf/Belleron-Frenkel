<?php
class Bf_Application_Module_Bootstrap extends Zend_Application_Module_Bootstrap
{
    /**
     * Init config settings and resoure for this module
     * 
     */
    protected function _initModuleConfig ()
    {
        // load ini file
        $arrClassNameParts = explode('_', get_called_class());
        $strModulePath = Zend_Controller_Front::getInstance()->getModuleDirectory(
        strtolower($arrClassNameParts[0]));
        if (is_readable($strModulePath . '/configs/module.ini')) {
            $objOptions = new Zend_Config_Ini(
            $strModulePath . '/configs/module.ini', APPLICATION_ENV);
            $options = $objOptions->toArray();
            if (! empty($options['config'])) {
                if (is_array($options['config'])) {
                    $_options = array();
                    foreach ($options['config'] as $tmp) {
                        $_options = $this->mergeOptions($_options, 
                        $this->_loadConfig(
                        dirname(__FILE__) . '/configs/' . $tmp));
                    }
                    $options = $this->mergeOptions($_options, $options);
                } else {
                    $options = $this->mergeOptions(
                    $this->_loadConfig($options['config']), $options);
                }
            }
            // Set this bootstrap options
            $this->getApplication()->setOptions($options);
        }
    }
    /**
     * Load configuration file of options
     *
     * @param  string $file
     * @throws Zend_Application_Exception When invalid configuration file is provided
     * @return array
     */
    protected function _loadConfig ($file)
    {
        $environment = $this->getEnvironment();
        $suffix = pathinfo($file, PATHINFO_EXTENSION);
        $suffix = ($suffix === 'dist') ? pathinfo(basename($file, ".$suffix"), 
        PATHINFO_EXTENSION) : $suffix;
        switch (strtolower($suffix)) {
            case 'ini':
                $config = new Zend_Config_Ini($file, $environment);
                break;
            case 'xml':
                $config = new Zend_Config_Xml($file, $environment);
                break;
            case 'json':
                $config = new Zend_Config_Json($file, $environment);
                break;
            case 'yaml':
                $config = new Zend_Config_Yaml($file, $environment);
                break;
            case 'php':
            case 'inc':
                $config = include $file;
                if (! is_array($config)) {
                    throw new Zend_Application_Exception(
                    'Invalid configuration file provided; PHP file does not return array value');
                }
                return $config;
                break;
            default:
                throw new Zend_Application_Exception(
                'Invalid configuration file provided; unknown config type');
        }
        return $config->toArray();
    }
}