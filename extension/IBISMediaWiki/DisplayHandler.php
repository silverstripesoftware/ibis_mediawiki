<?php
require_once("YAMLHandler.php");
require_once("PageHandler.php");

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
		$main_header = '<h2>Statement <span class="editsection">%s</span> </h2>';
		$template_main = '<div class="ibis_title type_%s">%s</div><p>%s</p>';
		$template_edit_link ='[<a href="%s">edit</a>]';
		$template_parent = '<div class="ibis_parent">
		<strong>Topic(s) linking here</strong>
		<span class="ibis_parent_links">
		%s
		</span>
		</div>';
		$add_response_link = '<a href="'.$this->title->getEditURL().'" >Add a response</a>';
		$response_container = '<h2>Responses <span class="editsection">['.$add_response_link.']</span></h2><ul>%s</ul>';
		$template_response = '<li class="type_%s"><a href="'.$path.'/%s">%s</a> %s %s </li>';
		$remove_link_template = '[ <a href="'.$this->title->getLocalURL("action=response&op=remove&response=%s").'">remove</a> ]';
		$edit_response_link = '[ <a href="'.$path.'?title=%s&action=discussion&op=edit">edit</a> ]';
		//Edit discussion link
		$edit_link = $this->getEditDiscussionLink();
		if($edit_link){
			$main_header = sprintf($main_header,sprintf($template_edit_link,$edit_link));
		}
		//Parent Links
		$links = '';
		if(isset($this->ibis['parents']) && !empty($this->ibis['parents'])){
			$link = '<a href="'.$path.'/%s">%s</a>';
			$this->setArticleFactory();
			foreach($this->ibis['parents'] as $parent){
				$ibis = $this->GetContent($parent,True);
				$links .=sprintf($link,$parent,$ibis['title']);
			}
		}
		if(!$links){
			$links = "No topics";
		}
		$parents = sprintf($template_parent,$links);
		//Main HTML
		$desc = isset($this->ibis['desc'])?$this->ibis['desc']:'';
		$main = sprintf($template_main,$this->ibis['type'],$this->ibis['title'],$desc);
		//Response HTML
		$responses = '';
		if(isset($this->ibis['responses'])){
			$this->setArticleFactory();
			foreach($this->ibis['responses'] as $response){
				$node = $response['node'];
				$ibis = $this->GetContent($node,True);
				$type = $ibis['type'];
				$title = $ibis['title'];
				if($ibis['user']==$this->user->id or $this->user->isAdminUser){
					$remove = sprintf($remove_link_template,$node);
					$edit = sprintf($edit_response_link,$node);
				}
				else{
					$remove = $edit = "";
				}
				$responses .= sprintf($template_response,$type,$node,$title,$edit,$remove);
			}
		}
		if(!$responses){
			//$main = sprintf("%s\n<ul><lh>Responses : </lh>%s</ul>",$main,$responses);
			$responses = "<br />No responses. ".$add_response_link;
		}
		$responses_html = sprintf($response_container,$responses);
		$content = $parents.$main_header.$main.$responses_html;
		$html = sprintf($container,$content);
		return $html;
	}
}
?>
