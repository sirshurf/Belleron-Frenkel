<?php
class Bf_Plugin_Resource_Cron extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        $options = $this->getOptions();

        if (array_key_exists('pluginPaths', $options)) {
            $cron = new Bf_Service_Cron($options['pluginPaths']);
        } else {
            $cron = new Bf_Service_Cron(array(
                'Bf_Plugin_Cron' => realpath(APPLICATION_PATH . "/../library/Bf/Plugin/Cron"),
            ));
        }

        if (array_key_exists('actions', $options)) {
            foreach ($options['actions'] as $name => $args) {
                $cron->addAction($name, $args);
            }
        }

        return $cron;
    }
}