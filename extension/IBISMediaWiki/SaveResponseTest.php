<?php
require_once 'PHPUnit/Framework.php';
require_once 'PageHandler.php';

class DummyPageHandler {

	function GetContent($node, $transform_content) {
		return array("title"=>"New Issue", "type"=>"issue");
	}

	function AddPage($response) {
		$this->called = "AddPage";
		return "IBIS_16";
	}
	
	function EditContent($node, $response) {
		$this->called = "EditContent";
	}
}

class SaveResponsesTest extends PHPUnit_Framework_TestCase {
	function test_save_response_should_create_page_if_node_empty() {
		$page_handler = new DummyPageHandler();
		$response = fnIBISSaveResponse('issue', 'Sample Issue', '', '1', $page_handler);
		$this->assertEquals("AddPage", $page_handler->called);
		$this->assertEquals('issue', $response["type"]);
		$this->assertEquals('Sample Issue', $response["title"]);
		$this->assertEquals('1', $response["user"]);
	}
	
	function test_save_response_should_set_node_field_when_adding_new_node() {
		$page_handler = new DummyPageHandler();
		$response = fnIBISSaveResponse('issue', 'Sample Issue', '', '1', $page_handler);
		$this->assertEquals("IBIS_16", $response["node"]);
	}
	
	function test_save_response_should_edit_page_if_node_not_empty() {
		$page_handler = new DummyPageHandler();
		$response = fnIBISSaveResponse('issue', 'Sample Issue', 'IBIS_15', '1', $page_handler);
		$this->assertEquals("EditContent", $page_handler->called);
		$this->assertEquals('issue', $response["type"]);
		$this->assertEquals('Sample Issue', $response["title"]);
		$this->assertEquals('IBIS_15', $response["node"]);
		$this->assertEquals('1', $response["user"]);
	}
}

?>