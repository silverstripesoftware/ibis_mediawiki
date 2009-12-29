<?php
require_once("YAMLHandler.php");
class DisplayHandler {
	function __construct($title,$path){
		$article = new Article($title);
		$this->content = $article->getContent();
		$this->path = $path;
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
	function getPageHTML(){
		$template_main = '<div class="ibis_conversation">
		<h1 class="type_%s">%s</h1>';
		$template_response = '<li class="type_%s"><a href="'.$this->path.'/%s">%s</a></li>';
		//Main HTML
		$main = sprintf($template_main,$this->ibis['type'],$this->ibis['title']);
		//Response HTML
		$responses = '';
		if(isset($this->ibis['responses'])){
			foreach($this->ibis['responses'] as $response){
				$responses .= sprintf($template_response,$response['type'],$response['node'],$response['title']);
			}
			if($responses){
				$html = sprintf("%s\n<ul>%s</ul></div>",$main,$responses);
			}
		}
		else{
			$html = $main."</div>";
		}
		return $html;
	}
	
}
?>
