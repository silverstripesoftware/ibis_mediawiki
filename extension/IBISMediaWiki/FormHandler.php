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
	
	function fnGetTinyMCEScriptInclude(){
		return '<script type="text/javascript" src="'.$this->wikipath.'/ibis_includes/tiny_mce/tiny_mce.js"></script> 
<script type="text/javascript"> 
	tinyMCE.init({
		mode : "textareas",
		theme : "simple",
		skin : "o2k7",
	});
</script>';
	}

	function get_form($title='', $type='', $desc='', $user='') {
		if(!$user){
			$user = $this->user->id;
		}
		$smarty = new Smarty();
		$smarty->template_dir = './extensions/IBISMediaWiki/templates';
		$smarty->compile_dir = './extensions/IBISMediaWiki/templates_c';		
		$smarty->caching_dir = './extensions/IBISMediaWiki/cache';
		
		$smarty->assign('title', $title);
		$smarty->assign($type, 'selected');
		$smarty->assign('desc', $desc);
		$smarty->assign('user', $user);
		$smarty->assign('path', $this->wikipath);
		
		$form = $smarty->fetch('IBISFormTemplate.tpl');
		$tiny_mce_script = $this->fnGetTinyMCEScriptInclude();
		return $tiny_mce_script.$form;
	}
	
	function get_response_form() {
		return $this->get_form();
	}
	
	function get_discussion_form(){
		$title=isset($this->ibis['title'])?$this->fnEscapeQuotes($this->ibis['title']):"";
		$type=isset($this->ibis['type'])?$this->ibis['type']:"";
		$user=isset($this->ibis['user'])?$this->ibis['user']:$this->user->id;
		$desc=isset($this->ibis['desc'])?$this->ibis['desc']:"";
		return $this->get_form($title,$type,$desc,$user);
	}
}
?>
