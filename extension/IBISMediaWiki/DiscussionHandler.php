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

class DiscussionHandler extends PageHandler {
	function __construct($title,$user,$op){
		$this->factory = new ArticleFactory();
		$this->title = $title;
		$this->user = $user;
		$this->op = $op;
	}	
	function canUserEdit($owner){
		if (($owner == $this->user->id) or $this->user->isAdminUser){
			return True;
		}
		return False;
	}
	
	function _loadRender(){
		if($this->op=="new"){
			$this->outTitle = "Add new discussion";
			$this->ibis = array();
			return True;
		}
		elseif($this->op=="edit"){
			$this->LoadCurrentPage();
			if($this->canUserEdit($this->ibis['user'])){
				$this->outTitle = "Edit discussion : ".$this->ibis['title'];
				return True;
			}
			else{
				$this->outTitle = "Warning!";
				$this->outHTML = '<strong style="color:red">Please do not try to edit other user discussion. You can still add responses to it.</strong>';
			}
		}
		return False;
	}	
	function RenderDiscussionForm($wikipath){
		$success = $this->_loadRender();
		if($success){
			$form = new FormHandler($this->user,$this->ibis,$wikipath);
			$html =  $form->get_discussion_form();
			$this->outHTML = $html;
		}
	}
	function _loadSave($title,$type,$desc,$user){
		//Html Cleaner
		if($this->op=="edit"){
		$this->LoadCurrentPage();
		}
		$html_cleaner = new HTMLCleaner();
		$title = $html_cleaner->clean_data($title);
		$desc = $html_cleaner->clean_data($desc);
		$this->ibis['title'] = $title;
		$this->ibis['type'] = $type;
		$this->ibis['user'] = $user;
		$this->ibis['desc'] = $desc;
		
	}
	function AddDiscussion(){
		$title = $this->GetNextPageTitle();
		$this->_save($title);
		return $title;
	}
	function _save($page_title){
		$this->EditContent($page_title,$this->ibis);
	}
	function ModifyDiscussion(){
		$this->_save($this->title->getText());
	}
	function SaveDiscussionForm($title,$type,$desc,$user){
		$this->_loadSave($title,$type,$desc,$user);
		if($this->op=="new"){
			$gen_title = $this->AddDiscussion();
			$titleObj = Title::newFromText($gen_title);
			return $titleObj->getFullURL();
		}
		elseif($this->op=="edit"){
			$this->ModifyDiscussion();
			return $this->title->getFullUrl();
		}
	}
}
?>
