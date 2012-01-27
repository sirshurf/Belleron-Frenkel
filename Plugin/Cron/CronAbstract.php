<?php

abstract class Bf_Plugin_Cron_CronAbstract implements Bf_Plugin_Cron_CronInterface
{
    /**
     * 
     * Enter description here ...
     * @var int
     */
    private $intMinLockTime = 0;
    
    private $_options = array();
    private $appOptions = array();

    /**
     * @return int $intMinLockTime
     */
    public function getIntMinLockTime ()
    {
        return $this->intMinLockTime;
    }

    /**
     * @param int $intMinLockTime
     */
    public function setIntMinLockTime ($intMinLockTime)
    {
        $this->intMinLockTime = (int)$intMinLockTime;
        return $this;
    }

    /**
     * @return arrays $appOptions
     */
    public function getAppOptions ()
    {
        return $this->appOptions;
    }

    /**
     * @param array $appOptions
     */
    public function setAppOptions ($appOptions = array())
    {
        $this->appOptions = $appOptions;
    }

    public function __construct ($args = null)
    {
        if (! empty($args)) {
            if (! empty($args['minLockTime'])) {
                $this->setIntMinLockTime($args['minLockTime']);
                unset($args['minLockTime']);
            }
            $this->_options = (array) $args;
        }
    }

    public function lock ()
    {
        $pid = $this->isLocked();
        if ($pid) {
            throw new Bf_Plugin_Cron_Exception('This task is already locked.Pid: ' . $pid);
        }
        
        $pid = getmypid();
        if (! file_put_contents($this->_getLockFile(), $pid)) {
            throw new Bf_Plugin_Cron_Exception('A lock could not be obtained.');
        }
        
        touch($this->_getTouchFile());
        
        return $pid;
    }

    public function unlock ()
    {
        if (! file_exists($this->_getLockFile())) {
            throw new Bf_Plugin_Cron_Exception('This task is not locked.');
        }
        
        if (! unlink($this->_getLockFile())) {
            throw new Bf_Plugin_Cron_Exception('The lock could not be deleted.');
        }
        
        return true;
    }

    public function isLocked ()
    {
        if (! file_exists($this->_getLockFile())) {
            return false;
        }
        
        return file_get_contents($this->_getLockFile());
    }

    public function isStandBy ()
    {
        
        if (! file_exists($this->_getTouchFile())) {
            return false;
        }
        
        $intFileLastModified = filemtime($this->_getTouchFile());
        
        if ($intFileLastModified+$this->getIntMinLockTime() > time()){
            return TRUE;
        }
           
        return FALSE;
    }

    protected function _getLockFile ()
    {
        $fileName = 'cron.' . get_class($this) . '.lock';
        $lockFile = realpath(APPLICATION_PATH . '/../files/tmp/') . '/' . $fileName;
        return $lockFile;
    }

    protected function _getTouchFile ()
    {
        $fileName = 'cron.' . get_class($this) . '.touch';
        $lockFile = realpath(APPLICATION_PATH . '/../files/tmp/') . '/' . $fileName;
        return $lockFile;
    }

}