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

require_once 'PHPUnit/Framework.php';
require_once("TabsHandler.php");
require_once("DummyClasses.php");
class TabsHandlerTest extends PHPUnit_Framework_TestCase {
	function get_tabs(){
		$tabs = array(
			"edit" => array(
					'text' => "Edit",
					'href' => "#",
			),
			"viewsource" => array(
					'text' => "View Source",
					'href' => "#",
			),
		);
		return $tabs;
	}
	function get_ibis(){
		$ibis = array();
		$ibis['title'] = "sample title";
		$ibis['type'] = "issue";
		$ibis['user'] = "1";
		
		return $ibis;
	}
	function setup_tabs($user){
		$tabs = $this->get_tabs();
		$user = new User($user);
		$this->tabs_handler = new TabsHandler($tabs,$user,new Title());
		$this->tabs_handler->ibis = $this->get_ibis();
	}
	function test_remove_edit_tab_should_remove_edit_item(){
		$this->setup_tabs(1);
		$this->assertEquals(true,isset($this->tabs_handler->tabs['edit']));
		$this->assertEquals(true,isset($this->tabs_handler->tabs['viewsource']));
		$this->tabs_handler->RemoveEditTab();
		$this->assertEquals(false,isset($this->tabs_handler->tabs['edit']));
		$this->assertEquals(false,isset($this->tabs_handler->tabs['viewsource']));
	}
	function test_isIBISNode_should_return_true_if_title_starts_with_IBIS_and_ends_with_number(){
		$this->setup_tabs(1);
		$this->tabs_handler->t_title->title = "IBIS 123";
		$this->assertEquals(true,$this->tabs_handler->isIBISNode());
		$this->tabs_handler->t_title->title = "Non ibis node";
		$this->assertEquals(false,$this->tabs_handler->isIBISNode());
	}
	function test_removeTab_should_remove_the_passed_key_from_tabs_array(){
		$this->setup_tabs(1);
		$this->tabs_handler->removeTab('edit');
		$this->assertEquals(false,isset($this->tabs_handler->tabs['edit']));
	}
	function test_changeTabName_should_change_the_text_value_for_passed_key(){
		$this->setup_tabs(1);
		$this->assertEquals("Edit",$this->tabs_handler->tabs['edit']['text']);
		$this->tabs_handler->changeTabName("edit","New Name");
		$this->assertEquals("New Name",$this->tabs_handler->tabs['edit']['text']);
	}
	function test_addEditDiscussionTabIfApplicable_should_add_edit_tab_if_both_created_and_current_users_same(){
		$this->setup_tabs(1);
		$this->tabs_handler->addEditDiscussionTabIfApplicable();
		$this->assertEquals(true,isset($this->tabs_handler->tabs['edit_discussion']));		
	}
	function test_addEditDiscussionTabIfApplicable_should__not_add_edit_tab_if_both_created_and_current_users_different(){
		$this->setup_tabs(2);
		$this->tabs_handler->addEditDiscussionTabIfApplicable();
		$this->assertEquals(false,isset($this->tabs_handler->tabs['edit_discussion']));
	}
	function test_addNewTab_should_add_new_tab_with_passed_key_and_op(){
		$this->setup_tabs(2);
		$this->assertEquals(false,isset($this->tabs_handler->tabs['new_discussion']));
		$this->tabs_handler->addNewTab('new_discussion','New Discussion','new');
		$this->assertEquals(true,isset($this->tabs_handler->tabs['new_discussion']));
		$this->assertEquals('New Discussion',$this->tabs_handler->tabs['new_discussion']['text']);
	}
}
?>
