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
require_once("$IP/extensions/IBISMediaWiki/includes/smarty/Smarty.class.php");
class FormHandler extends PageHandler{
	function __construct($user,$ibis,$wikipath){
		$this->user = $user;
		if(gettype($ibis)=='array'){
			$this->ibis = $ibis;
		}
		else{
			$this->ibis = YAMLHandler::YAMLToArray($ibis);
		}
		$this->wikipath = $wikipath;
	}
	function fnEscapeQuotes($data){
		return preg_replace("/\"/","&quot;",$data);
	}
	function fnGetSelectedTypeMap(){
		return array(
			'%issue%' => '',
			'%position%' => '',
			'%supporting_argument%' => '',
			'%opposing_argument%' => '',
		);
	}

	function get_form($title='', $type='', $desc='', $user='',$op="response") {
		if(!$user){
			$user = $this->user->id;
		}
		$smarty = new Smarty();
		$smarty->template_dir = './extensions/IBISMediaWiki/templates';
		$smarty->compile_dir = './extensions/IBISMediaWiki/templates_c';		
		$smarty->caching_dir = './extensions/IBISMediaWiki/cache';
		
		$smarty->assign('title', $title);
		$smarty->assign($type, 'selected');
		$smarty->assign('desc', htmlentities($desc));
		$smarty->assign('user', $user);
		//$smarty->assign('path', $this->wikipath);
		
		if($op=="discussion"){
			if(!empty($this->ibis['parents']) && isset($this->ibis['parents'])){
				$isNew = False;
			}
			else{
				$isNew = True;
			}
			$smarty->assign('isNew', $isNew);
		}
		$form = $smarty->fetch('IBISFormTemplate.tpl');
		return $form;
	}
	
	function get_response_form() {
		return $this->get_form();
	}
	
	function get_discussion_form(){
		$title=isset($this->ibis['title'])?$this->fnEscapeQuotes($this->ibis['title']):"";
		$type=isset($this->ibis['type'])?$this->ibis['type']:"";
		$user=isset($this->ibis['user'])?$this->ibis['user']:$this->user->id;
		$desc=isset($this->ibis['desc'])?$this->ibis['desc']:"";
		
		$isNewDiscussion = $title==""?True:False;
		return $this->get_form($title,$type,$desc,$user,$op="discussion");
	}
}
?>
