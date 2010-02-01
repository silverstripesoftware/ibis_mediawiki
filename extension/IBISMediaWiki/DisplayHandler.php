<?php
require_once("YAMLHandler.php");
require_once("PageHandler.php");
include_once('C:/wamp/bin/php/php5.3.0/PEAR/FirePHPCore/fb.php');

class DisplayHandler extends PageHandler{
	function __construct($title,$user=""){
		$this->title = $title;
		$article = new Article($title);
		$this->content = $article->getContent();
		$this->user = $user;
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
	function getEditDiscussionLink(){
		if (($this->ibis['user'] == $this->user->id) or $this->user->isAdminUser){
			return $this->title->getLocalURL("action=discussion&op=edit");
		}
		return False;
	}
	function getPageHTML($path){
		$container ='<div class="ibis_conversation">%s</div>';
		$main_container = '<div class="ibis_main type_%s">%s</div>';
		$template_title = '<span class="ibis_title">%s</span>';
		$template_edit_link ='[<a href="%s">edit</a>]';
		$template_parent = '<div class="ibis_parent">
		<span class="ibis_parent_text"> 
		Topic(s) linking here : 
		</span>
		<span class="ibis_parent_links">
		%s
		</span>
		</div>';
		$add_response_link = ' <a href="'.$this->title->getEditURL().'" >add</a> ';
		$response_container = '<ul><lh>Responses : </lh>['.$add_response_link.']%s</ul>';
		$template_response = '<li class="type_%s"><a href="'.$path.'/%s">%s</a> %s </li>';
		$remove_link_template = '[ <a href="#">remove</a> ]';
		//Edit discussion link
		$edit_link = $this->getEditDiscussionLink();
		if($edit_link){
			$template_title .= sprintf($template_edit_link,$edit_link);
		}
		$template_main = sprintf($main_container,'%s',$template_title);
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
				if($ibis['user']==$this->user->id){
					$remove = $remove_link_template;
				}
				else{
					$remove = "";
				}
				$responses .= sprintf($template_response,$type,$node,$title,$remove);
			}
		}
		if(!$responses){
			//$main = sprintf("%s\n<ul><lh>Responses : </lh>%s</ul>",$main,$responses);
			$responses = "<br />No responses added, ".$add_response_link." one now";
		}
		$responses_html = sprintf($response_container,$responses);
		$content = $parents.$main.$responses_html;
		$html = sprintf($container,$content);
		return $html;
	}
}
?>
