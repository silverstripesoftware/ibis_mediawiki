<?php
require_once 'PHPUnit/Framework.php';
require_once 'YAMLHandler.php';
require_once 'PageHandler.php';

class User{
	function __construct($id){
		$this->id = $id;
		$this->isAdminUser = $id==1?true:false;
		$this->isGuest = $id==0?true:false;
	}
}

class Title {
	function newFromText($page_title) {
	}
}

class TestArticleFactory {
	function __construct($article) {
		$this->article = $article;
	}

	function getArticle($node) {
		return $this->article;
	}
}

class Article {
	function __construct($node, $data) {
		$this->data = $data;
	}
	
	function getContent() {
		return $this->data;
	}
	
	function doEdit($data, $comment) {
		$this->data = $data;
	}
}

class PageHandlerTest extends PHPUnit_Framework_TestCase {
	function get_article_data(){
		return "
---
title: Sample Issue
type: issue
responses: 
  - 
    title: Sample Position
    type: position
    node: IBIS_15
    user: 1
  - 
    title: Oppose
    type: opposing_argument
    node: IBIS_31
    user: 2

";
	}
	function get_page_handler($article_data,$user_id) {
		$this->article = new Article('', $article_data);
		$user = new User($user_id);
		$page_handler = new PageHandler("IBIS_15", $user);
		$page_handler->factory = new TestArticleFactory($this->article);
		$page_handler->LoadCurrentPage();
		return $page_handler;
	}
	
	function test_GetContent_should_return_yaml_data_if_ibis_False() {
		$article_data = $this->get_article_data();
		$page_handler = $this->get_page_handler($article_data,1);
		$content = $page_handler->GetContent("IBIS_15");
		$this->assertEquals($article_data, $content);
	}
	
	function test_GetContent_should_transform_data_to_array_if_ibis_True() {
		$article_data = $this->get_article_data();
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
		$article_data = $this->get_article_data();		
		$page_handler = $this->get_page_handler($article_data,1);
		$content_array = YAMLHandler::YAMLToArray($article_data);
		$content_array['responses'][0]['title'] = 'Changed position';
		$content = $page_handler->EditContent("IBIS_15", $content_array);
		$changed_content = YAMLHandler::ArrayToYAML($content_array);
		$this->assertEquals($changed_content, $this->article->data);
	}	
	
	function test_LoadCurrentPage_should_remove_only_current_users_responses_if_not_admin(){
		$article_data = $this->get_article_data();		
		$page_handler = $this->get_page_handler($article_data,2);
		$ibis_yaml = YAMLHandler::ArrayToYAML($page_handler->ibis);
		$this->assertEquals(0, preg_match('/Oppose/', $ibis_yaml));//user 2
		$this->assertEquals(0, preg_match('/IBIS_31/', $ibis_yaml));//user 2
		$this->assertEquals(1, preg_match('/Sample Position/', $ibis_yaml));//user 1
		$this->assertEquals(1, preg_match('/IBIS_15/', $ibis_yaml));//user 1
	}
	
	function test_LoadCurrentPage_should_remove_all_responses_if_admin(){
		$article_data = $this->get_article_data();		
		$page_handler = $this->get_page_handler($article_data,1);
		$ibis_yaml = YAMLHandler::ArrayToYAML($page_handler->ibis);
		$this->assertEquals(0, preg_match('/Oppose/', $ibis_yaml));//user 2
		$this->assertEquals(0, preg_match('/IBIS_31/', $ibis_yaml));//user 2
		$this->assertEquals(0, preg_match('/Sample Position/', $ibis_yaml));//user 1
		$this->assertEquals(0, preg_match('/IBIS_15/', $ibis_yaml));//user 1
	}
}
?>
