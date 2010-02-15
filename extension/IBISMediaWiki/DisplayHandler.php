<?php
/*******************************************************************************
	Code contributed to the Bloomer Project
    Copyright (C) 2010 iMorph Inc.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 3 as 
	published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*******************************************************************************/

require_once("YAMLHandler.php");
require_once("PageHandler.php");
require_once("$IP/ibis_includes/smarty/Smarty.class.php");

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
		$this->setArticleFactory();
		
		$smarty = new Smarty();
		$smarty->template_dir = './extensions/IBISMediaWiki/templates';
		$smarty->compile_dir = './extensions/IBISMediaWiki/templates_c';		
		$smarty->caching_dir = './extensions/IBISMediaWiki/cache';
		
		$smarty->assign('base_path', $path);
		$smarty->assign('title', $this->title->getDBkey());
		$edit_link = $this->getEditDiscussionLink();
		$smarty->assign('edit_link', $edit_link);
		$smarty->assign('add_response_link', $this->title->getEditURL());
	
		$smarty->assign('type', $this->ibis['type']);
		$smarty->assign('ibis_title', $this->ibis['title']);
		$desc = isset($this->ibis['desc'])?$this->ibis['desc']:'';
		$smarty->assign('desc', $desc);
		
		$parents = array();
		if(isset($this->ibis['parents']) && !empty($this->ibis['parents'])){
			foreach($this->ibis['parents'] as $parent){
				$ibis = $this->GetContent($parent,True);
				$parents[]=array(
				"node"=>$parent,
				"text"=>$ibis['title'],
				"type"=>$ibis['type'],
				);
			}
		}
		$smarty->assign('parents', $parents);
		
		$smarty->assign('isGuestUser', $this->user->isGuest);
		
		$responses = array();
		if(isset($this->ibis['responses'])){
			foreach($this->ibis['responses'] as $response){
				$owner = False;
				$node = $response['node'];
				$ibis = $this->GetContent($node,True);
				$type = $ibis['type'];
				$title = $ibis['title'];
				if(!$this->user->isGuest){
					if(($ibis['user'] == $this->user->id) || $this->user->isAdminUser){
						$owner = True;
					}
				}
				$responses[]= array(
				'type'=>$type,
				'node'=>$node,
				'text'=>$title,
				'owner'=>$owner,
				);
			}
		}
		$smarty->assign('responses', $responses);
		return $smarty->fetch('IBISPageTemplate.tpl');
	}
}
?>
