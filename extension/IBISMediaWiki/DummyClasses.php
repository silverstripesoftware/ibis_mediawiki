<?php
class User{
	function __construct($id){
		$this->id = $id;
		$this->isAdminUser = $id==1?true:false;
		$this->isGuest = $id==0?true:false;
	}
}

class Title {
	function __construct(){
		$this->title="IBIS 123";
	}
	function newFromText($page_title) {
	}
	function getText(){
		return $this->title;
	}
	function getLocalURL(){
		return "";
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
	function __construct($node) {
		global $article_data;
		$this->data = $article_data;
	}
	
	function getContent() {
		return $this->data;
	}
	
	function doEdit($data, $comment) {
		$this->data = $data;
	}
}

$article_data = "
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
?>