<?php
class LogoutController extends Zend_Controller_Action {
	function init(){
		include('./application/init.php');
	}

	function indexAction(){
		Zend_Auth::getInstance()->clearIdentity();
		Zend_Session::forgetMe();
		setcookie("userId", "", time() + 3600*24*3);  /* expire in 3 days */
		setcookie("secretHash", "", time() + 3600*24*3);  /* expire in 3 days */				
		$this->_redirect('/');
	}
}