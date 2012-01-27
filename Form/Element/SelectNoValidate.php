<?php
class Bf_Form_Element_SelectNoValidate extends Zend_Form_Element_Select
{
    public function isValid($value, $context = null){
        return TRUE;
    }
 
}