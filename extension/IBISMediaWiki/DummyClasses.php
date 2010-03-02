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
		$this->isAdminUser = $id==1?True:False;
		$this->isGuest = $id==0?True:False;
	}
	function newFromId($id){
		return new User($id);
	}
	function getName(){
		return "User".$this->id;
	}
}

class Title {
	function __construct($title = "IBIS 123"){
		$this->title=$title;
	}
	function newFromText($page_title) {
	}
	function getText(){
		return $this->title;
	}
	function getLocalURL($v=""){
		return $v;
	}
	function getDBkey(){
		return $this->title;
	}
	function getEditURL(){
		return "title=".$this->title."&action=edit";
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
	
	function getLastNAuthors($n){
		return array("test");
	}
	
	function getTimestamp(){
		return 20100301081607;
	}
}

$article_data = "
---
title: Sample Issue
type: issue
user: 2
desc: '<p>test sdtes df dsf &nbsp;</p>'
parents: 
  - IBIS_135
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