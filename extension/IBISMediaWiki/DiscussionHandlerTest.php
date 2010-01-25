<?php
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
		//Admin user can edit all the nodes
		$this->setup_discussion(1,'new');
		$result = $this->discussion->canUserEdit(1);
		$this->assertEquals(true,$result);
		$result = $this->discussion->canUserEdit(2);
		$this->assertEquals(true,$result);
	}
	function test_canUserEdit_should_only_return_true_for_user_created_nodes(){
		// Non admin users can edit only their nodes
		$this->setup_discussion(2,'new');
		$result = $this->discussion->canUserEdit(2);
		$this->assertEquals(true,$result);
		$result = $this->discussion->canUserEdit(1);
		$this->assertEquals(false,$result);
	}
	function test__loadRender_should_create_empty_ibis_if_op_is_new(){
		$this->setup_discussion(2,'new');
		$this->discussion->_loadRender();
		$this->assertEquals(true,isset($this->discussion->ibis));
		$this->assertEquals(0,count($this->discussion->ibis));
		$this->assertEquals("Add new discussion",$this->discussion->outTitle);
	}
	function test__loadRender_should_create_ibis_with_session_data_of_ibis_responses(){
		$this->setup_discussion(1,'edit');
		$this->discussion->_loadRender();
		$this->assertEquals(true,isset($this->discussion->ibis));
		$this->assertEquals(true,count($this->discussion->ibis)>0);
	}
}
?>
