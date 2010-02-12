<?php
require_once("$IP/ibis_includes/htmlcleaner/htmLawed.php");
class HTMLCleaner{
	function clean_data($data){
		if(get_magic_quotes_gpc()){
			$data  = stripslashes($data);
		}
		$config = array(
		'safe'=>1,
		'elements'=>'b,strong,u,i,em,ul,ol,p,span,div,br',
		);
		
		$data = html_entity_decode($data);
		$cleaned_data = htmLawed($data, $config);
		return $cleaned_data;
	}
}
?>
