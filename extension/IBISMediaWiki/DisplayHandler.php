<?php
require_once("YAMLHandler.php");
require_once("PageHandler.php");
include_once('C:/wamp/bin/php/php5.3.0/PEAR/FirePHPCore/fb.php');

class DisplayHandler extends PageHandler{
	function __construct($title){
		$article = new Article($title);
		$this->content = $article->getContent();
	}
	function setArticleFactory(){
		$this->factory = new ArticleFactory();
	}
	function isConvertionApplicableForThisPage(){
		$array = YAMLHandler::YAMLToArray($this->content);
		if(isset($array['title']) && isset($array['type'])){
			$this->ibis = $array;
			return True;
		}
		else{
			return False;
		}
	}
	function getPageHTML($path){
		$container ='<div class="ibis_conversation">%s</div>';
		$template_main = '<h1 class="type_%s">%s</h1>';
		$template_parent = '<div class="ibis_parent">
		<span class="ibis_parent_text"> 
		Topic(s) linking here : 
		</span>
		<span class="ibis_parent_links">
		%s
		</span>
		</div>';
		$template_response = '<li class="type_%s"><a href="'.$path.'/%s">%s</a></li>';
		//Parent Links
		$links = '';
		if(isset($this->ibis['parents'])){
			$link = '<a href="'.$path.'/%s">%s</a>';
			$this->setArticleFactory();
			foreach($this->ibis['parents'] as $parent){
				$ibis = $this->GetContent($parent,True);
				$links .=sprintf($link,$parent,$ibis['title']);
			}
		}
		if(!$links){
			$links = "None of the topics linking here.";
		}
		$parents = sprintf($template_parent,$links);
		//Main HTML
		$main = sprintf($template_main,$this->ibis['type'],$this->ibis['title']);
		//Response HTML
		$responses = '';
		if(isset($this->ibis['responses'])){
			$this->setArticleFactory();
			foreach($this->ibis['responses'] as $response){
				$node = $response['node'];
				$ibis = $this->GetContent($node,True);
				$type = $ibis['type'];
				$title = $ibis['title'];
				$responses .= sprintf($template_response,$type,$node,$title);
			}
			if($responses){
				$main = sprintf("%s\n<ul>%s</ul>",$main,$responses);
			}
		}
		$content = $parents.$main;
		$html = sprintf($container,$content);
		return $html;
	}
}
?>
