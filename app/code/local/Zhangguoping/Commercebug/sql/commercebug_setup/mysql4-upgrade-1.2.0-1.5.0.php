<?php
    /**
    * Copyright © Pulsestorm LLC: All rights reserved
    */
	$installer = $this;
	$shim = $this->getShim();
	$o = $shim->getModel('admin/session');
	// $o = Mage::getModel('admin/session');
	if(is_object($o) && method_exists($o, 'refreshAcl') && is_callable(array($o,'refreshAcl')))
	{
	    $o->refreshAcl();
	}