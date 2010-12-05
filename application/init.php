<?php
		Zend_Loader::loadClass('User');			
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		if($this->view->user->id == ""){
			checkRememberMe();
			$this->view->user = Zend_Auth::getInstance()->getIdentity();
		} else {
			$user = new User();
			$this->view->user = $user->fetchRow($user->select()->where('id = ? ',$this->view->user->id));
		}
		Zend_Loader::loadClass('Zend_Session');
?>
