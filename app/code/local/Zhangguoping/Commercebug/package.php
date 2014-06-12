<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/

class Commercebug_Packager
{
    public $files = array();
    public $namespace;
    public function run()
    {
        //export
        $base = dirname(__FILE__);
        $temp_dir = $this->getTempDir();			
        shell_exec('svn export svn+ssh://gliff@gliff.org/usr/home/gliff/repository/MagentoDevbox/trunk/Commercebug '.$temp_dir. '/' . $this->namespace . '/Commercebug');

        //replace with customer namespace
        $this->doNamesapceReplacement($temp_dir. '/' . $this->namespace . '/Commercebug');
        
        //create base directory structure
        $this->doCreateBaseStructure($temp_dir);
        
        //move stuff where it belongs
        $this->doPutThingsInPlace($temp_dir);
        
        //create out module activation file
        $this->doCreateModuleActivationFile($temp_dir);
        //finally, create the archive and gzip it
        $path_tar = tempnam(sys_get_temp_dir(),'tmp').'.tar';
        shell_exec('tar -C '.$temp_dir.' -cvf ' . $path_tar . ' app skin ');
        
        rename($path_tar, '/Users/alanstorm/Desktop/testtar/'.time().'.tar');
        echo $path_tar, "\n";
    }
    
    public function doCreateModuleActivationFile($temp_dir)
    {
        $string = '<?xml version="1.0" encoding="UTF-8"?><config><modules><Zhangguoping_Commercebug><active>true</active><codePool>local</codePool></Zhangguoping_Commercebug></modules></config>';
        $string = str_replace('Zhangguoping_', $this->namespace . '_',$string);
        file_put_contents($temp_dir . '/app/etc/modules/'.$this->namespace.'_Commercebug.xml',
        $string
        );
    }
    
    public function doPutThingsInPlace($temp_dir)
    {
        rename($temp_dir . '/' . $this->namespace . '/Commercebug/skin/frontend/commercebug', 
        $temp_dir . '/skin/frontend/commercebug');		
        
        rename($temp_dir . '/' . $this->namespace, 
        $temp_dir . '/app/code/local/' . $this->namespace);
    }
    
    public function doCreateBaseStructure($temp_dir)
    {
        mkdir($temp_dir . '/app/code/local' ,0777,true);			
        mkdir($temp_dir . '/skin/frontend' ,0777,true);			
        mkdir($temp_dir . '/app/etc/modules' ,0777,true);				
    }
    
    public function doNamesapceReplacement($base_path)
    {
        foreach($this->files as $file)
        {
            $string = file_get_contents($base_path . '/' . $file);
            $string = str_replace('Zhangguoping_', $this->namespace . '_',$string);
            file_put_contents($base_path . '/' . $file, $string);
        }
    }
    
    public function getTempDir()
    {
        $name = tempnam(sys_get_temp_dir(),'tmp');
        unlink($name);
        $name = $name;			
        mkdir($name . '/' . $this->namespace,0777,true);
        return $name;
    }
}

$a 				= new Commercebug_Packager();
$a->namespace 	= 'Ozone';
$a->files 		= Array(
'Block/Html.php',
'controllers/AjaxController.php',
'controllers/IndexController.php',
'Helper/Classurilookup.php',
'Helper/Collector.php',
'Helper/Data.php',
'Helper/Log.php',
'Model/Collector.php',
'Model/Layout/Update.php',
'Model/Layoutviewer/Observer.php',
'Model/Observer.php',
'etc/config.xml',
);

$a->run();