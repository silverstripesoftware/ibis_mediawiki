<?php
require_once("PageHandler.php");

class DiscussionHandler extends PageHandler {
	function __construct($ibis){
		$this->factory = new ArticleFactory();
		$this->ibis = $ibis;
	}
	
	function AddDiscussion(){
		$page_title = $this->GetNextPageTitle();
		$this->_save($page_title);
		return $page_title;
	}
	function _save($page_title){
		$this->EditContent($page_title,$this->ibis);
	}
	function ModifyDiscussion($page_title){
		$this->_save($page_title);
	}
}
?>
