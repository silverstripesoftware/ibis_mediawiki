<?php
require_once("PageHandler.php");

class DiscussionHandler extends PageHandler {
	function __construct($ibis_title, $type, $user){
		$this->factory = new ArticleFactory();
		$this->ibis_title = $ibis_title;
		$this->type = $type;
		$this->user = $user;
	}

	function SaveDiscussion(){
		$page_title = $this->GetNextPageTitle();
		$content = array("title"=>$this->ibis_title,"type"=>$this->type,"user"=>$this->user->id);
		$this->EditContent($page_title,$content);
		return $page_title;
	}
}
?>
