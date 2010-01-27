<?php
require_once("PageHandler.php");
require_once('C:/wamp/bin/php/php5.3.0/PEAR/FirePHPCore/fb.php');

class DiscussionHandler extends PageHandler {
	function __construct($title,$user,$op){
		$this->factory = new ArticleFactory();
		$this->title = $title;
		$this->user = $user;
		$this->op = $op;
	}	
	function canUserEdit($owner){
		if (($owner == $this->user->id) or $this->user->isAdminUser){
			return True;
		}
		return False;
	}
	function _loadRender(){
		if($this->op=="new"){
			$this->outTitle = "Add new discussion";
			$this->ibis = array();
			return True;
		}
		elseif($this->op=="edit"){
			$page = new PageHandler($this->title->getText(),$this->user);
			$page->LoadCurrentPage(False);
			if($this->canUserEdit($page->ibis['user'])){
				$this->outTitle = "Edit discussion : ".$this->title->getText();
				$this->ibis = $page->ibis;
				if(isset($page->ibis['responses'])){
					$_SESSION[$this->title->getDBkey()] = serialize($page->ibis['responses']);
				}
				return True;
			}
			else{
				$this->outTitle = "Warning!";
				$this->outHTML = '<strong style="color:red">Please do not try to edit other user discussion. You can still add responses to it.</strong>';
			}
		}
		return False;
	}	
	function RenderDiscussionForm(){
		$success = $this->_loadRender();
		if($success){
			$form = new FormHandler($this->user,$this->ibis);
			$html =  $form->get_discussion_form();
			$this->outHTML = $html;
		}
	}
	function _loadSave($title,$type,$user){
		$this->ibis = array();
		if(isset($_SESSION[$this->title->getDBkey()])){
			$this->ibis['responses'] = unserialize($_SESSION[$this->title->getDBkey()]);
			unset($_SESSION[$this->title->getDBkey()]);
		}
		$this->ibis['title'] = $title;
		$this->ibis['type'] = $type;
		$this->ibis['user'] = $user;
		
	}
	function AddDiscussion(){
		$title = $this->GetNextPageTitle();
		$this->_save($title);
		return $title;
	}
	function _save($page_title){
		$this->EditContent($page_title,$this->ibis);
	}
	function ModifyDiscussion(){
		$this->_save($this->title->getText());
	}
	function SaveDiscussionForm($title,$type,$user){
		$this->_loadSave($title,$type,$user);
		if($this->op=="new"){
			$title = $this->AddDiscussion();
			$titleObj = Title::newFromText($title);
			return $titleObj->getFullURL;
		}
		elseif($this->op=="edit"){
			$this->ModifyDiscussion();
			return $this->title->getFullUrl();
		}
	}
}
?>
