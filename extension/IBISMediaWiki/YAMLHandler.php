<?php
require_once('spyc.php');
class YAMLHandler {
	function YAMLToArray($yaml){
		return Spyc::YAMLLoad($yaml);
	}

	function ArrayToYAML($array){
		return Spyc::YAMLDump($array);
	}
}
?>
