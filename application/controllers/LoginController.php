<?php
class LoginController extends Zend_Controller_Action {
	function init(){
		include('./application/init.php');
	}

	function indexAction(){
		if ($this->_request->isPost() && $this->_getParam('login_email') != ""&& $this->_getParam('login_password') != "") {
			$loginEmail = $this->_request->getPost('login_email');
			$loginPassword =$this->_request->getPost('login_password');
			Zend_Auth::getInstance()->clearIdentity();
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			$f = new Zend_Filter_StripTags();
			$email = $f->filter($loginEmail);
			$password = $f->filter($loginPassword);
			Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
			$db = Zend_Registry::get('db');
			$authAdapter = new Zend_Auth_Adapter_DbTable($db);
			$authAdapter->setTableName('user');
			$authAdapter->setIdentityColumn('email');
			$authAdapter->setCredentialColumn('password');

			$authAdapter->setIdentity($email);
			$authAdapter->setCredential(md5($password));

			$auth = Zend_Auth::getInstance();
			$result = $auth->authenticate($authAdapter);
			if ($result->isValid()) {
				$data = $authAdapter->getResultRowObject(null,'password');
				$auth->getStorage()->write($data);
				$this->view->user = Zend_Auth::getInstance()->getIdentity();
				if($_POST["rememberMe"] != ""){
					setcookie("userId", $this->view->user->id, time()+3600*24*3);  /* expire in 3 days */
					setcookie("secretHash", md5("ThisisSalt".$this->view->user->id."wichlesssalt".$this->view->user->join_date."wichlesssalt".$this->view->user->email."doeqkjdsjkah231".$this->view->user->join_date."dsadsa"), time()+3600*24*3);  /* expire in 3 days */				
				}
				$maxSessionTime=60*60*24*30*6; // six months
				Zend_Session::rememberMe($maxSessionTime);
				$this->_forward('index','index');				
			}
		}
		$this->_redirect('/');
		
	}
}