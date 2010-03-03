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

require_once("PageHandler.php");

class DisplayHandler extends PageHandler{
	function __construct($title="",$user=""){
		$this->title = $title;
		if($title){
			$this->article = new Article($title);
			$this->content = $this->article->getContent();
		}
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
		if(!$this->user->isGuest){
			if (($this->ibis['user'] == $this->user->id) or $this->user->isAdminUser){
				return $this->title->getLocalURL("action=discussion&op=edit");
			}
		}
		return False;
	}
	function getPageHTML($path){
		$this->setArticleFactory();
		
		$smarty = new Smarty();
		if( defined( 'MEDIAWIKI' ) ) {
			$smarty->template_dir = './extensions/IBISMediaWiki/templates';
			$smarty->compile_dir = './extensions/IBISMediaWiki/templates_c';		
			$smarty->caching_dir = './extensions/IBISMediaWiki/cache';
		}
		$smarty->assign('base_path', $path);
		$smarty->assign('title', $this->title->getDBkey());
		$edit_link = $this->getEditDiscussionLink();
		$smarty->assign('edit_link', $edit_link);
		$smarty->assign('add_response_link', $this->title->getEditURL());
	
		$smarty->assign('type', $this->ibis['type']);
		$smarty->assign('ibis_title', $this->ibis['title']);
		$desc = isset($this->ibis['desc'])?$this->ibis['desc']:'';
		$smarty->assign('desc', $desc);
		
		/////////////
		$user = User::newFromId((int)$this->ibis['user']);
		$smarty->assign('author', $user->getName());
		$author = $this->article->getLastNAuthors(1);
		$smarty->assign('last_author', $author[0]);
		$timestamp = $this->article->getTimestamp();
		$year = substr($timestamp, 0, 4);
		$month = substr($timestamp, 4, 2);
		$day = substr($timestamp, 6, 2);
		$hour = substr($timestamp, 8, 2);
		$minute = substr($timestamp, 10, 2);
		$second = substr($timestamp, 12, 2);
		$date = date('M d, Y H:i:s', mktime($hour, $minute, $second, $month, $day, $year));
		$smarty->assign('timestamp',$date);
		/////////		
		
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
					if(isset($response['user'])){
						$user = $response['user'];
					}
					else{
						$user = $ibis['user'];
					}
					if(( $user== $this->user->id) || $this->user->isAdminUser){
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
	
	function getIBISIndex($path){
		$smarty = new Smarty();
		if( defined( 'MEDIAWIKI' ) ) {
			$smarty->template_dir = './extensions/IBISMediaWiki/templates';
			$smarty->compile_dir = './extensions/IBISMediaWiki/templates_c';		
			$smarty->caching_dir = './extensions/IBISMediaWiki/cache';
		}
		$db = new DBWrapper();
		$index = $db->get_ibis_conversation_index($this);
		$smarty->assign('index', $index);
		$smarty->assign('base_path',$path);
		return $smarty->fetch('IBISIndexTemplate.tpl');
	}
}
?>
