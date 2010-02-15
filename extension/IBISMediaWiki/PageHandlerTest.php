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
require_once 'YAMLHandler.php';
require_once 'PageHandler.php';
require_once("DummyClasses.php");


class PageHandlerTest extends PHPUnit_Framework_TestCase {
	function get_page_handler($article_data,$user_id) {
		$this->article = new Article('');
		$this->article->data = $article_data;
		$user = new User($user_id);
		$page_handler = new PageHandler("IBIS_15", $user);
		$page_handler->factory = new TestArticleFactory($this->article);
		$page_handler->LoadCurrentPage();
		return $page_handler;
	}
	
	function test_GetContent_should_return_yaml_data_if_ibis_False() {
		$page_handler = $this->get_page_handler($article_data,1);
		$content = $page_handler->GetContent("IBIS_15");
		$this->assertEquals($article_data, $content);
	}
	
	function test_GetContent_should_transform_data_to_array_if_ibis_True() {
		$page_handler = $this->get_page_handler($article_data,1);
		$content = $page_handler->GetContent("IBIS_15", True);
		$this->assertEquals("Sample Issue", $content["title"]);
		$this->assertEquals("issue", $content["type"]);
		$this->assertEquals("Sample Position", $content["responses"][0]["title"]);
		$this->assertEquals("position", $content["responses"][0]["type"]);
		$this->assertEquals("IBIS_15", $content["responses"][0]["node"]);
		$this->assertEquals("1", $content["responses"][0]["user"]);
	}

	function test_EditContent_should_replace_article_content() {
		$article_data = "sample content 1";
		$page_handler = $this->get_page_handler($article_data,1);
		$content = $page_handler->EditContent("IBIS_15", "sample content 2");
		$this->assertEquals("sample content 2", $this->article->data);
	}
	
	function test_EditContent_should_replace_article_content_even_when_content_in_array_form(){
				
		$page_handler = $this->get_page_handler($article_data,1);
		$content_array = YAMLHandler::YAMLToArray($article_data);
		$content_array['responses'][0]['title'] = 'Changed position';
		$content = $page_handler->EditContent("IBIS_15", $content_array);
		$changed_content = YAMLHandler::ArrayToYAML($content_array);
		$this->assertEquals($changed_content, $this->article->data);
	}	
	
	function test_LoadCurrentPage_should_remove_only_current_users_responses_if_not_admin(){
				
		$page_handler = $this->get_page_handler($article_data,2);
		$ibis_yaml = YAMLHandler::ArrayToYAML($page_handler->ibis);
		$this->assertEquals(0, preg_match('/Oppose/', $ibis_yaml));//user 2
		$this->assertEquals(0, preg_match('/IBIS_31/', $ibis_yaml));//user 2
		$this->assertEquals(1, preg_match('/Sample Position/', $ibis_yaml));//user 1
		$this->assertEquals(1, preg_match('/IBIS_15/', $ibis_yaml));//user 1
	}
	
	function test_LoadCurrentPage_should_remove_all_responses_if_admin(){
				
		$page_handler = $this->get_page_handler($article_data,1);
		$ibis_yaml = YAMLHandler::ArrayToYAML($page_handler->ibis);
		$this->assertEquals(0, preg_match('/Oppose/', $ibis_yaml));//user 2
		$this->assertEquals(0, preg_match('/IBIS_31/', $ibis_yaml));//user 2
		$this->assertEquals(0, preg_match('/Sample Position/', $ibis_yaml));//user 1
		$this->assertEquals(0, preg_match('/IBIS_15/', $ibis_yaml));//user 1
	}
}
?>
