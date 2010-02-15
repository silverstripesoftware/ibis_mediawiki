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
require_once 'FormHandler.php';
class User{
	function __construct($id){
		$this->id = $id;
		$this->isAdminUser = $id==1?true:false;
		$this->isGuest = $id==0?true:false;
	}
}

class FormHanderTest extends PHPUnit_Framework_TestCase {
	function cases_for_new_response_form($html_output){
		//Check if none of the type is selected
		$this->assertEquals(0, preg_match('/value="issue" selected/', $html_output));
		$this->assertEquals(0, preg_match('/value="position" selected/', $html_output));
		$this->assertEquals(0, preg_match('/value="supporting_argument" selected/', $html_output));
		$this->assertEquals(0, preg_match('/value="opposing_argument" selected/', $html_output));
		//Check if the responses type dropdown exists
		$this->assertEquals(1, preg_match('/<select name="type\[\]">(.|\s)*?<\/select>/', $html_output));
		//Check if the title input box exists without data
		$this->assertEquals(1, preg_match('/<input type="text" name="ibis_title\[\]" size="50" value=""\/>/', $html_output));
		//Check if the node info exists without data
		$this->assertEquals(1, preg_match('<input type="hidden" name="node\[\]" value="" />', $html_output));
	}
	function case_for_form_tag_exists($html_output){
		$this->assertEquals(1, preg_match('/<form action="" method="post">(.|\s)*?<\/form>/', $html_output));
	}
	function case_for_response_form_exists($html_output){
		$this->assertEquals(1, preg_match('/<select name="type\[\]">(.|\s)*?<br \/><br \/>/', $html_output));
	}
	
	function test_get_field_html_should_render_issue_field_with_prefilled_data_when_passing_response_array() {
		$user = new User(1);
		$form = new FormHandler($user,'');
		$input = array("type"=>"position", "title"=>"sample node", "node"=>"IBIS_15");
		$html_output = $form->get_field_html($input);
		//Check if only the given type is selected
		$this->assertEquals(0, preg_match('/value="issue" selected/', $html_output));
		$this->assertEquals(1, preg_match('/value="position" selected/', $html_output));
		$this->assertEquals(0, preg_match('/value="supporting_argument" selected/', $html_output));
		$this->assertEquals(0, preg_match('/value="opposing_argument" selected/', $html_output));
		//Check if the responses type dropdown exists
		$this->assertEquals(1, preg_match('/<select name="type\[\]">(.|\s)*?<\/select>/', $html_output));
		//Check if the title input box exists
		$this->assertEquals(1, preg_match('/<input type="text" name="ibis_title\[\]" size="50" value="sample node"\/>/', $html_output));
		//Check if the node info exists
		$this->assertEquals(1, preg_match('<input type="hidden" name="node\[\]" value="IBIS_15" />', $html_output));
	}
	
	function test_get_field_html_should_create_issue_field_without_any_data_when_passing_nothing(){
		$user = new User(1);
		$form = new FormHandler($user,'');
		$html_output = $form->get_field_html();
		$this->cases_for_new_response_form($html_output);
	}
	
	function test_get_edit_form_should_render_edit_form_with_responses(){
		$ibis['responses'][] = array("type"=>"issue", "title"=>"sample node", "node"=>"IBIS_15");
		$user = new User(1);
		$form = new FormHandler($user,$ibis);
		$edit_form_html = $form->get_edit_form();
		//Check if the form tag exists
		$this->case_for_form_tag_exists($edit_form_html);
		//Check if responses html exists
		$this->case_for_response_form_exists($edit_form_html);
	}
	
	function test_get_edit_form_should_have_a_new_response_form_at_the_bottom(){
		$user = new User(1);
		$form = new FormHandler($user,'');
		$edit_form_html = $form->get_edit_form();
		//Check if the form tag exists
		$this->case_for_form_tag_exists($edit_form_html);
		//Check if responses html exists
		$this->case_for_response_form_exists($edit_form_html);
		//Check if new response form exists at the bottom
		$this->cases_for_new_response_form($edit_form_html);
	}
	
	function test_get_edit_form_should_not_have_other_user_responses(){
		$ibis['responses'][] = array("type"=>"issue", "title"=>"sample node", "node"=>"IBIS_15","user"=>"1");
		$user = new User(2);
		$form = new FormHandler($user,$ibis);
		$edit_form_html = $form->get_edit_form();
		$this->assertEquals(0,preg_match('/value="sample node"/', $edit_form_html));
		$this->assertEquals(0,preg_match('/name="user\[\]" value="1"/', $edit_form_html));
		$this->assertEquals(0,preg_match('/name="node\[\]" value="IBIS_15"/', $edit_form_html));
	}
	function test_get_edit_form_should_have_user_own_responses(){
		$ibis['responses'][] = array("type"=>"issue", "title"=>"sample node", "node"=>"IBIS_15","user"=>"1");
		$user = new User(1);
		$form = new FormHandler($user,$ibis);
		$edit_form_html = $form->get_edit_form();
		$this->assertEquals(1,preg_match('/value="sample node"/', $edit_form_html));
		$this->assertEquals(1,preg_match('/name="user\[\]" value="1"/', $edit_form_html));
		$this->assertEquals(1,preg_match('/name="node\[\]" value="IBIS_15"/', $edit_form_html));
	}
}

?>