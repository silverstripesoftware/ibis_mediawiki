<?php
require_once("PageHandler.php");
require_once("HTMLCleaner.php");

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
			$page->LoadCurrentPage();
			if($this->canUserEdit($page->ibis['user'])){
				$this->outTitle = "Edit discussion : ".$page->ibis['title'];
				$this->ibis = $page->ibis;
				if(isset($page->ibis['responses'])){
					$key = $this->title->getDBkey()."_responses";
					$_SESSION[$key] = serialize($page->ibis['responses']);
				}
				if(isset($page->ibis['parents'])){
					$key = $this->title->getDBkey()."_parents";
					$_SESSION[$key] =  serialize($page->ibis['parents']);
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
	function RenderDiscussionForm($wikipath){
		$success = $this->_loadRender();
		if($success){
			$form = new FormHandler($this->user,$this->ibis,$wikipath);
			$html =  $form->get_discussion_form();
			$this->outHTML = $html;
		}
	}
	function _loadSave($title,$type,$desc,$user){
		$this->ibis = array();
		$response_key = $this->title->getDBkey()."_responses";
		if(isset($_SESSION[$response_key])){
			$this->ibis['responses'] = unserialize($_SESSION[$response_key]);
			unset($_SESSION[$response_key]);
		}
		$parent_key = $this->title->getDBkey()."_parents";
		if(isset($_SESSION[$parent_key])){
			$this->ibis['parents'] = unserialize($_SESSION[$parent_key]);
			unset($_SESSION[$parent_key]);
		}
		
		//Html Cleaner
		$html_cleaner = new HTMLCleaner();
		$title = $html_cleaner->clean_data($title);
		$desc = $html_cleaner->clean_data($desc);
		$this->ibis['title'] = $title;
		$this->ibis['type'] = $type;
		$this->ibis['user'] = $user;
		$this->ibis['desc'] = $desc;
		
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
	function SaveDiscussionForm($title,$type,$desc,$user){
		$this->_loadSave($title,$type,$desc,$user);
		if($this->op=="new"){
			$gen_title = $this->AddDiscussion();
			$titleObj = Title::newFromText($gen_title);
			return $titleObj->getFullURL();
		}
		elseif($this->op=="edit"){
			$this->ModifyDiscussion();
			return $this->title->getFullUrl();
		}
	}
}
?>
