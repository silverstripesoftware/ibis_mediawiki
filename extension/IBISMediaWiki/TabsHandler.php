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

class TabsHandler{
	function __construct(&$tabs,$title){	
		$this->tabs = &$tabs;
		$this->title = $title;
	}
	function RemoveEditTab(){
		if(isset($this->tabs['edit'])){
			$this->removeTab('edit');
		}
		if(isset($this->tabs['viewsource'])){
			$this->removeTab('viewsource');
		}
	}		
	function isIBISNode(){
		if (preg_match("/^IBIS\s\d+$/",$this->title->getText())){
			return True;
		}
		return False;
	}	
	function removeTab($key){
		unset($this->tabs[$key]);
	}
	function addNewDiscussionTab()
	{
		$this->tabs["new_discussion"] = Array(
			'text' => "New Discussion",
			'href' => $this->title->getLocalURL("action=discussion&op=new"),
		);
	}
	
}
?>
