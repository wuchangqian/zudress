<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/
class Zhangguoping_Commercebug_Model_Configaccessclass extends Mage_Core_Model_Config_Data
{	
    public function save()
    {		
            
        if(!Mage::getModel($this->getValue()))
        {
            Mage::throwException(sprintf('Invalid Access Class: Could not instantiate a [%s]',$this->getValue()));
        }
        else
        {
            parent::save();
        }
    }
}