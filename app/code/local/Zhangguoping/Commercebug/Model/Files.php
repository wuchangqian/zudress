<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/
class Zhangguoping_Commercebug_Model_Files
{
    public function capture()
    {
        $collector  = new Zhangguoping_Commercebug_Model_Collectorfiles; 
        $o = new stdClass();
        $o->information = get_included_files();
        
        // $o->information = str_replace(Mage::getBaseDir(), '', $o->information);
        
        //selfishly do this in a few steps so symlinked module folders are also removes
        //and not placed into the "other" folder
        $o->information = preg_replace('%^.+?/app%','app', $o->information);
        $o->information = str_replace(Mage::getBaseDir().'/','',$o->information);

        $o->otherInformation = new stdClass();
        $o->otherInformation->baseDir = Mage::getBaseDir();
        $collector->collectInformation($o);
    }
}