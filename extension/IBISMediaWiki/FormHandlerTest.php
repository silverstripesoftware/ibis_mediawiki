<?php
require_once 'PHPUnit/Framework.php';
require_once 'FormHandler.php';

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
		$form = new FormHandler();
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
		$form = new FormHandler();
		$html_output = $form->get_field_html();
		//
		$this->cases_for_new_response_form($html_output);
	}
	
	function test_get_edit_form_should_render_edit_form_with_responses(){
		$form = new FormHandler();
		$input = array("type"=>"issue", "title"=>"sample node", "node"=>"IBIS_15");
		$responses_html = $form->get_field_html($input);
		$edit_form_html = $form->get_edit_form($responses_html);
		//Check if the form tag exists
		$this->case_for_form_tag_exists($edit_form_html);
		//Check if responses html exists
		$this->case_for_response_form_exists($edit_form_html);
	}
	
	function test_get_edit_form_should_have_a_new_response_form_at_the_bottom(){
		$form = new FormHandler();
		$edit_form_html = $form->get_edit_form('');
		//Check if the form tag exists
		$this->case_for_form_tag_exists($edit_form_html);
		//Check if responses html exists
		$this->case_for_response_form_exists($edit_form_html);
		//Check if new response form exists at the bottom
		$this->cases_for_new_response_form($edit_form_html);
	}
}

?>