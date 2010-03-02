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
require_once 'PHPUnit/Framework.php';
require_once 'FormHandler.php';

class FormHanderTest extends PHPUnit_Framework_TestCase {
	function test_get_discussion_form_should_render_form_with_type_radio_buttons_when_its_ibis_has_parents() {
		$user = new User(1);
		$form = new FormHandler($user,'','/wiki/');
		$form->ibis = array("type"=>"position", "title"=>"sample node", "user"=>"1","desc"=>"sample desc","parents"=>"asdf");
		$html_output = $form->get_discussion_form();
		//Check if only the given type is selected
		$this->assertEquals(1, preg_match('/value="issue" checked/', $html_output));
		$this->assertEquals(1, preg_match('/value="position" checked/', $html_output));
		$this->assertEquals(0, preg_match('/value="supporting_argument" checked/', $html_output));
		$this->assertEquals(0, preg_match('/value="opposing_argument" checked/', $html_output));
	}
	
	function test_get_discussion_form_should_render_form_with_hidden_input_when_its_ibis_has_no_parents() {
		$user = new User(1);
		$form = new FormHandler($user,'','/wiki/');
		$form->ibis = array("type"=>"position", "title"=>"sample node", "user"=>"1","desc"=>"sample desc");
		$html_output = $form->get_discussion_form();
		//Check if hidden input element with value topic is exists
		$this->assertEquals(1, preg_match('/<input type="hidden" value="topic" name="type" \/>/', $html_output));
	}
	
	function test_get_discussion_form_should_render_should_substitute_correct_value_in_other_form_elements(){
	//
		$user = new User(1);
		$form = new FormHandler($user,'','/wiki/');
		$form->ibis = array("type"=>"position", "title"=>"sample node", "user"=>"1","desc"=>"sample desc");
		$html_output = $form->get_discussion_form();
		//Check if the title input box exists
		$this->assertEquals(1, preg_match('/<input type="text" name="ibis_title" class="ibis_title" value="sample node"\/>/', $html_output));
		//Check if the desc exists
		$this->assertEquals(1, preg_match('<textarea.*?sample desc.*?textarea>', $html_output));
		$this->assertEquals(1, preg_match('/<input type="hidden" name="user" value="1" \/>/', $html_output));
		
	}
}

?>