<?php
require_once("YAMLHandler.php");
class PageHandler {
	function __construct($current_page_title,$user){
		$this->user = $user;
		$this->title = $current_page_title;
		$content = $this->_getContent($this->title);
		$this->ibis = YAMLHandler::YAMLToArray($content);
		// Removing the existing responses of current user
		$this->_removeCurrentUserResponses();
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
		$title = Title::newFromText($page_title);
		$article = new Article($title);
		return $article;
	}
	
	function _getContent($title,$ibis=False){
		$article = $this->_getObject($title);
		$content = $article->getContent();
		if($ibis){
			return YAMLHandler::YAMLToArray($content);
		}
		return $content;
	}
	
	function _editContent($title,$content){
		if(gettype($content)=='array'){
			$content = YAMLHandler::ArrayToYAML($content);
		}
		$article = $this->_getObject($title);
		$article->doEdit($content,'');
		return true;
	}
	
	function _getNextPageTitle(){	
		$dbr = &wfGetDB( DB_SLAVE );
		$q = "select max(page_id) as max_page_id from page";
		$res = $dbr->query($q);
		$row = $dbr->fetchObject($res);
		$next_id = ((int)$row->max_page_id)+1;
		
		return "IBIS_".$next_id;
	}
	
	function _addPage($content){
		$page_title = $this->_getNextPageTitle();
		$this->_editContent($page_title,$content);
		return $page_title;
	}
	
	function AddResponse($title,$type,$node,$user){
		if($node==''){
			$response['title'] = $title;
			$response['type'] = $type;
			$response['node'] = $this->_addPage($response);
		}
		else{
			$response = $this->_getContent($node,True);
			$response['title'] = $title;
			$response['type'] = $type;
			$this->_editContent($node,$response);
			$response['node'] = $node;
		}
		$response['user'] = $user;
		
		$this->ibis['responses'][] = $response;
	}
	
	function SavePage(){
		$this->_editContent($this->title,$this->ibis);
	}
	
}
?>
