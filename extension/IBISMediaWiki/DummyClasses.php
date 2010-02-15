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