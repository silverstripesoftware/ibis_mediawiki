<?php
require_once("YAMLHandler.php");

function fnIBISSaveResponse($type,$title,$user,$desc,$page_handler){
	$response = $page = array();
	$page['title'] = $title;
	$page['type'] = $type;
	$page['user'] = $user;
	$page['desc'] = $desc;
	$page['parents'][]=$page_handler->title->getDBkey();
	$response['node'] = $page_handler->AddPage($page);
	$response['user'] = $user;
	return $response;
}

class ArticleFactory {
	function getArticle($title) {
		if(gettype($title)=='string'){
			$title = Title::newFromText($title);
		}
		return new Article($title);
	}
}

class PageHandler {
	function __construct($titleObj, $user){
		$this->factory = new ArticleFactory();
		$this->user = $user;
		$this->title = $titleObj;
	}
	
	function _getObject($page_title){
		return $this->factory->getArticle($page_title);
	}
	
	function LoadCurrentPage(){
		$this->ibis = $this->GetContent($this->title, True);
		if(!(isset($this->ibis['user']))){
			$this->ibis['user']='1';
		}
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
		$dbr = &wfGetDB( DB_MASTER );
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
	
	function removeParent($node){
		//Function that removes the specified values from given array
		function array_remove_value() {
			$args = func_get_args();
			$arr = $args[0];
			$values = array_slice($args,1);
			foreach($arr as $k=>$v) {
				if(in_array($v, $values))
					unset($arr[$k]);
			}
			return $arr;
		}		
		$parent = $this->title->getDBkey();
		$ibis = $this->GetContent($node,True);
		if(isset($ibis['parents'])){
			$ibis['parents'] = array_remove_value($ibis['parents'],$parent);
			if (!count($ibis['parents'])){
				unset($ibis['parents']);
			}
			$this->EditContent($node,$ibis);
		}
	}
}
?>
