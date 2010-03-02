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

require_once 'UnittestIncludes.php';
require_once 'PageHandler.php';

class PageHandlerTest extends PHPUnit_Framework_TestCase {
	function get_page_handler($article_data,$user_id,$title="IBIS_15") {
		$this->article = new Article('');
		$this->article->data = $article_data;
		$user = new User($user_id);
		$title = new Title($title);
		$page_handler = new PageHandler($title, $user);
		$page_handler->factory = new TestArticleFactory($this->article);
		$page_handler->LoadCurrentPage();
		return $page_handler;
	}
	
	function test_GetContent_should_return_yaml_data_if_ibis_False() {
		global $article_data;
		$page_handler = $this->get_page_handler($article_data,1);
		$content = $page_handler->GetContent("IBIS_15");
		$this->assertEquals($article_data, $content);
	}
	
	function test_GetContent_should_transform_data_to_array_if_ibis_True() {
		global $article_data;
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
		global $article_data;
		$article_data = "sample content 1";
		$page_handler = $this->get_page_handler($article_data,1);
		$content = $page_handler->EditContent("IBIS_15", "sample content 2");
		$this->assertEquals("sample content 2", $this->article->data);
	}
	
	function test_EditContent_should_replace_article_content_even_when_content_in_array_form(){
		global $article_data;
		$page_handler = $this->get_page_handler($article_data,1);
		$content_array = YAMLHandler::YAMLToArray($article_data);
		$content_array['responses'][0]['title'] = 'Changed position';
		$content = $page_handler->EditContent("IBIS_15", $content_array);
		$changed_content = YAMLHandler::ArrayToYAML($content_array);
		$this->assertEquals($changed_content, $this->article->data);
	}	
	
	function test_removeParent_should_remove_current_node_from_parents_array(){
		global $article_data;
		$page_handler = $this->get_page_handler($article_data,1,"IBIS_135");
		$page_handler->removeParent("IBIS_135");
		$content = $page_handler->article->getContent();
		$content_array = YAMLHandler::YAMLToArray($content);
		$this->assertEquals(false, isset($content_array['parents']));
	}
	
}
?>
