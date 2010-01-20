<?php
require_once("YAMLHandler.php");

function fnIBISSaveResponse($type,$title,$node,$user,$page_handler){
	if($node==''){
		$response['title'] = $title;
		$response['type'] = $type;
		$response['node'] = $page_handler->AddPage($response);
	}
	else{
		$response = $page_handler->GetContent($node,True);
		$response['title'] = $title;
		$response['type'] = $type;
		$page_handler->EditContent($node,$response);
		$response['node'] = $node;
	}
	$response['user'] = $user;
	
	return $response;
}

class ArticleFactory {
	function getArticle($node) {
		$title = Title::newFromText($node);
		return new Article($title);
	}
}

class PageHandler {
	function __construct($current_page_title, $user){
		$this->factory = new ArticleFactory();
		$this->user = $user;
		$this->title = $current_page_title;
	}
	
	function _removeCurrentUserResponses(){
		$filtered_ibis = array();
		if($this->user->isAdminUser){
			unset($this->ibis['responses']);
		}
		else{
			foreach($this->ibis['responses'] as $response){
				if($response['user']!=$this->user->id){
					$filtered_ibis[] = $response;
				}
			}
			$this->ibis['responses'] = $filtered_ibis;
		}
	}
	
	function _getObject($page_title){
		return $this->factory->getArticle($page_title);
	}
	
	function LoadCurrentPage() {
		$this->ibis = $this->GetContent($this->title, True);
		// Removing the existing responses of current user
		$this->_removeCurrentUserResponses();
	}
	
	function GetContent($title,$ibis=False){
		$article = $this->_getObject($title);
		$content = $article->getContent();
		if($ibis){
			return YAMLHandler::YAMLToArray($content);
		}
		return $content;
	}
	
	function EditContent($title,$content){
		if(gettype($content)=='array'){
			$content = YAMLHandler::ArrayToYAML($content);
		}
		$article = $this->_getObject($title);
		$article->doEdit($content,'');
		return true;
	}
	
	function GetNextPageTitle(){	
		$dbr = &wfGetDB( DB_SLAVE );
		$q = "select max(page_id) as max_page_id from page";
		$res = $dbr->query($q);
		$row = $dbr->fetchObject($res);
		$next_id = ((int)$row->max_page_id)+1;
		
		return "IBIS_".$next_id;
	}
	
	function AddPage($content){
		$page_title = $this->GetNextPageTitle();
		$this->EditContent($page_title,$content);
		return $page_title;
	}
	
	function SavePage(){
		$this->EditContent($this->title,$this->ibis);
	}	
}
?>
