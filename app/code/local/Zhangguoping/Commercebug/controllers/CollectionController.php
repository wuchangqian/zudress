<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/

class Zhangguoping_Commercebug_CollectionController extends Mage_Core_Controller_Front_Action
{	
    public function testAction()
    {
        echo(
        (string) 
        Mage::getModel('catalog/product')
        ->getCollection()
        ->addFieldToFilter('sku',array(array('like'=>'a%'),array('like'=>'b%')))
        ->getSelect()
        );
    }
}