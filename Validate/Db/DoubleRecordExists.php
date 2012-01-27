<?php 
 
class Bf_Validate_Db_DoubleRecordExists extends Zend_Validate_Db_RecordExists
{
    
    /**
     * Original token against which to validate
     * @var string
     */
    protected $_tokenString;
    protected $_token;
    
    public function __construct($options = null)
    {
        parent::__construct($options);
        
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (is_array($options) && array_key_exists('token', $options)) {
            if (array_key_exists('strict', $options)) {
                $this->setStrict($options['strict']);
            }

            $this->setToken($options['token']);
        } else if (null !== $options) {
            $this->setToken($options);
        }
    }
    
    /**
     * Retrieve token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * Set token against which to compare
     *
     * @param  mixed $token
     * @return Zend_Validate_Identical
     */
    public function setToken($token)
    {
        $this->_tokenString = (string) $token;
        $this->_token       = $token;
        return $this;
    }
    
    public function isValid($value, $context = null)
    {        
         if (($context !== null) && isset($context) && array_key_exists($this->getToken(), $context)) {
            $token = $context[$this->getToken()];
        } else {
            $token = $this->getToken();
        }

        if ($token === null) {
            $this->_error(self::MISSING_TOKEN);
            return false;
        }
        
        
        $select = $this->getSelect();
        $select->where($this->getToken()." = ?",$token);        
        
        return parent::isValid($value);
    }
}
