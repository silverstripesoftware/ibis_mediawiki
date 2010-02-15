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