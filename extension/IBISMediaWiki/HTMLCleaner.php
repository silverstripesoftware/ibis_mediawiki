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

require_once("$IP/ibis_includes/htmlcleaner/htmLawed.php");
class HTMLCleaner{
	function clean_data($data){
		if(get_magic_quotes_gpc()){
			$data  = stripslashes($data);
		}
		$config = array(
		'safe'=>1,
		'elements'=>'a,b,strong,u,i,em,ul,ol,p,span,div,br,img',
		);
		
		$cleaned_data = htmLawed($data, $config);
		return $cleaned_data;
	}
}
?>
