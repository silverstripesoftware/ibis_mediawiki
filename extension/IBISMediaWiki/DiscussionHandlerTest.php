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
require_once("DiscussionHandler.php");
require_once("DummyClasses.php");

class DiscussionHandlerTest extends PHPUnit_Framework_TestCase {
	function get_ibis(){
		$ibis['title'] = "sample title";
		$ibis['type'] = "issue";
		$ibis['user'] = "1";
		$ibis['responses']=array(array("node" => "IBIS_67"),array("node" => "IBIS_78"));
	}
	function setup_discussion($id,$op){
		$user = new User($id);
		$this->discussion = new DiscussionHandler(new Title(),$user,$op);
	}
	function test_canUserEdit_should_return_always_true_if_admin_user(){
		//Article owner 1 ; current user - 1
		//Admin user can edit all the nodes
		$this->setup_discussion(1,'new');
		$result = $this->discussion->canUserEdit(1);
		$this->assertEquals(true,$result);
		$result = $this->discussion->canUserEdit(2);
		$this->assertEquals(true,$result);
	}
	function test_canUserEdit_should_only_return_true_for_user_created_nodes(){
		//Article owner 1 ; current user - 2
		// Non admin users can edit only their nodes
		$this->setup_discussion(2,'new');
		$result = $this->discussion->canUserEdit(2);
		$this->assertEquals(true,$result);
		$result = $this->discussion->canUserEdit(1);
		$this->assertEquals(false,$result);
	}
	function test__loadRender_should_create_empty_ibis_if_op_is_new(){
		//Article owner 1 ; current user - 2
		$this->setup_discussion(2,'new');
		$this->discussion->_loadRender();
		$this->assertEquals(true,isset($this->discussion->ibis));
		$this->assertEquals(0,count($this->discussion->ibis));
		$this->assertEquals("Add new discussion",$this->discussion->outTitle);
	}
	function test__loadRender_should_create_ibis_with_session_data_of_ibis_responses_if_op_is_edit(){
		//Article owner 1 ; current user - 1
		$this->setup_discussion(1,'edit');
		$this->discussion->_loadRender();
		$this->assertEquals(true,isset($this->discussion->ibis));
		$this->assertEquals(true,count($this->discussion->ibis)>0);
	}
	function test__loadRender_should_display_warning_msg_when_non_owner_or_non_admin_user_edit(){
		//Article owner 1 ; current user - 2
		$this->setup_discussion(2,'edit');
		$this->discussion->_loadRender();
		$this->assertEquals("Warning!",$this->discussion->outTitle);
		$this->assertEquals('<strong style="color:red">Please do not try to edit other user discussion. You can still add responses to it.</strong>',$this->discussion->outHTML);
	}
	function test__loadSave_should_set_all_the_passed_values_in_ibis_array(){
		//Article owner 1 ; current user - 1
		$this->setup_discussion(1,'edit');
		$this->discussion->_loadSave('sample title','issue','2');
		$this->assertEquals("sample title",$this->discussion->ibis['title']);
		$this->assertEquals("issue",$this->discussion->ibis['type']);
		$this->assertEquals("2",$this->discussion->ibis['user']);
	}
}
?>
