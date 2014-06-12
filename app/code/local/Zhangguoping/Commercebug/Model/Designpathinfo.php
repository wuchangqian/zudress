<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/
class Zhangguoping_Commercebug_Model_Designpathinfo extends Varien_Object
{
	
	public function _construct()
	{
		//var_dump(__METHOD__);
// 		$file = Mage::getDesign()->getLayoutFilename('foobazbar');
// 		exit("here: " . $file);
		
// 		$file = Mage::getDesign()->getTheme('');
// 		exit("here: " . $file);
		$custom = $this->_getCustomData();
		$data = (array(
		'package'					=> Mage::getDesign()->getPackageName(),
		'translations'				=> Mage::getDesign()->getLocaleBaseDir(array()),
		'templates'					=> Mage::getDesign()->getTemplateFilename(''),
		'skin'						=> Mage::getDesign()->getSkinBaseDir(array()),
		'layout'					=> Mage::getDesign()->getLayoutFilename(''),
		'default_theme'				=> Mage::getDesign()->getTheme(''),
		'custom_theme' 				=> $custom['theme'],
		'custom_package' 			=> $custom['package'],		
		));
		
		foreach($data as $key=>$value)
		{
			if(strpos($value,Mage::getBaseDir()) == 0) //first position, not false
			{
				$data[$key] = trim(
				str_replace(Mage::getBaseDir(),'',$value)
				,'/');
			}
		}
		$this->setData($data);
	}
	
	protected function _getCustomData()
	{
		$designChange = Mage::getSingleton('core/design')
		->loadChange(Mage::app()->getStore()->getStoreId());	
		
		$data = array(
		'theme'		=>'N/A',
		'package'	=>'N/A'
		);
		$change_data = $designChange->getData();

		if($change_data && array_key_exists('theme',$change_data))
		{
			$data['theme']   = $change_data['theme'];
			$data['package'] = $change_data['package'];
		}
		return $data;
	}
// 	public function getPackage()
// 	{
// 	}
// 	
// 	public function getTranslations()
// 	{
// 			
// 	}
// 	
// 	public function getTemplates()
// 	{
// 	}
// 	
// 	public function getSkin()
// 	{
// 	}
// 	
// 	public function getLayout()
// 	{
// 	}
// 	
// 	public function getDefault()
// 	{
// 	}
// 	
// 	public function getCustomDesign()
// 	{
// 	}

	public function research()
	{
			var_dump('##Mage::getDesign()->getTheme(\'\')');
			var_dump(Mage::getDesign()->getTheme(''));

			var_dump('##Default Theme');
			var_dump(Mage::getDesign()->getDefaultTheme());
			
			var_dump('##Templates');
			var_dump(Mage::getDesign()->getTemplateFilename(''));

			var_dump('##Layout');
			var_dump(Mage::getDesign()->getLayoutFilename(''));
			
			var_dump('###Package');
			var_dump(Mage::getDesign()->getPackageName());
			
			
			var_dump('##Skin');			
			var_dump(
			Mage::getDesign()->getSkinBaseDir(array())
			);						
			
			var_dump('##base skin');
			var_dump(Mage::getBaseDir('skin'));
			
			
			var_dump(
			Mage::getDesign()->getBaseDir(array())
			);		

			var_dump('Nothing for design? Probably dno\'t need');
			var_dump(Mage::getBaseDir('design'));
			
			
			
			var_dump('##Translations');
			var_dump(
			Mage::getDesign()->getLocaleBaseDir(array())
			);		
	
	}
}
