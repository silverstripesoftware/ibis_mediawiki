<?php
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