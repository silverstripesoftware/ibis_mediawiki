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

class YAMLHanderTest extends PHPUnit_Framework_TestCase {
	function test_yaml_to_array_should_return_associative_array() {
		$yaml = "---
title: sample issue
type: issue";
		$array = YAMLHandler::YAMLToArray($yaml);
		$this->assertEquals(2, count($array));
		$this->assertEquals('sample issue',$array['title']);
		$this->assertEquals('issue',$array['type']);
	}
	
	function test_array_to_yaml_should_return_yaml_text(){
		$array['title'] = 'sample issue';
		$array['type'] = 'issue';
		$yaml = YAMLHandler::ArrayToYAML($array);
		/*$this->assertEquals('---
title: sample issue
type: issue',$yaml);*/
	}
}

?>
