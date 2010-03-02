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
require_once("DisplayHandler.php");

class DisplayHandlerTest extends PHPUnit_Framework_TestCase {
	function get_display($article_data,$user_id,$title="IBIS_94"){
		$this->article = new Article('');
		$this->article->data = $article_data;
		$user = new User($user_id);
		$title = new Title($title);
		$display = new DisplayHandler($title,$user);
		$display->factory = new TestArticleFactory($this->article);
		$display->isConvertionApplicableForThisPage();
		return $display;
	}
	function test_getEditDiscussionLink_should_return_edit_link_if_user_is_owner(){	
		global $article_data;
		$display = $this->get_display($article_data,2);
		$link = $display->getEditDiscussionLink();
		$this->assertEquals(True,$link=="action=discussion&op=edit");
	}
	function test_getEditDiscussionLink_should_return_False_if_user_not_owner(){	
		global $article_data;
		$display = $this->get_display($article_data,3);
		$link = $display->getEditDiscussionLink();
		$this->assertEquals(True,$link==False);
	}
	function test_getEditDiscussionLink_should_return_edit_link_if_user_is_Admin(){	
		global $article_data;
		$display = $this->get_display($article_data,1);
		$link = $display->getEditDiscussionLink();
		$this->assertEquals(True,$link=="action=discussion&op=edit");
	}
	function test_getPageHTML_should_have_add_response_link_only_for_logged_in_users(){	
		global $article_data;
		$display = $this->get_display($article_data,3);
		$html = $display->getPageHTML("");
		$this->assertEquals(1, preg_match('/<a href="title=IBIS_94&action=edit" >Add a response<\/a>/', $html));
		$display = $this->get_display($article_data,0);
		$html = $display->getPageHTML("");
		$this->assertEquals(0, preg_match('/<a href="title=IBIS_94&action=edit" >Add a response<\/a>/', $html));
	}
	function test_getPageHTML_should_have_edit_remove_for_owners_and_admins(){
		global $article_data;
		// User - 1, Owner : first response - 1, second response - 2
		$display = $this->get_display($article_data,1);
		$html = $display->getPageHTML("");
		$this->assertEquals(1, preg_match('/<a href="\?title=IBIS_15&action=discussion&op=edit">edit<\/a>/', $html));
		$this->assertEquals(1, preg_match('/<a href="\?title=IBIS_94&action=response&op=remove&response=IBIS_15">remove<\/a>/', $html));
		$this->assertEquals(1, preg_match('/<a href="\?title=IBIS_31&action=discussion&op=edit">edit<\/a>/', $html));
		$this->assertEquals(1, preg_match('/<a href="\?title=IBIS_94&action=response&op=remove&response=IBIS_31">remove<\/a>/', $html));
		
		// User - 2, Owner : first response - 1, second response - 2
		$display = $this->get_display($article_data,2);
		$html = $display->getPageHTML("");
		$this->assertEquals(0, preg_match('/<a href="\?title=IBIS_15&action=discussion&op=edit">edit<\/a>/', $html));
		$this->assertEquals(0, preg_match('/<a href="\?title=IBIS_94&action=response&op=remove&response=IBIS_15">remove<\/a>/', $html));
		$this->assertEquals(1, preg_match('/<a href="\?title=IBIS_31&action=discussion&op=edit">edit<\/a>/', $html));
		$this->assertEquals(1, preg_match('/<a href="\?title=IBIS_94&action=response&op=remove&response=IBIS_31">remove<\/a>/', $html));
		
		
	}
	function test_getPageHTML_should_have_title_meta_desc_responses_for_all_users(){
		global $article_data;
		$display = $this->get_display($article_data,1);
		$html = $display->getPageHTML("");
		//print $html;
		// Parent
		$this->assertEquals(1, preg_match('/<a href="\/IBIS_135">Sample Issue<\/a>/', $html));
		//Title	
		$this->assertEquals(1, preg_match('/\s*<div class="ibis_title type_issue">(.|Sample Issue)(.|\s)*<\/div>/', $html));
		//Page meta
		$this->assertEquals(1, preg_match('/\s*<p class="ibis_meta"> Created by : User2, Last Edited by : test on Mar 01, 2010 08:16:07 <\/p>/', $html));
		//Description
		$this->assertEquals(1, preg_match('/\s*<div class="ibis_title type_issue">(.|<p>test sdtes df dsf &nbsp;<\/p>)(.|\s)*<\/div>/', $html));
		//Responses
		// The response title will be as same as current node title, since the article will always return the same data
		$this->assertEquals(1, preg_match('/<a href="\/IBIS_15">Sample Issue<\/a>/', $html));
		$this->assertEquals(1, preg_match('/<a href="\/IBIS_31">Sample Issue<\/a>/', $html));	
	}
}
?>
