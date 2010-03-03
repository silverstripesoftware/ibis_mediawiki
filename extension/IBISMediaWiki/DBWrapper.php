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


class DBWrapper {
	function __construct(){
		global $wgDBprefix;
		$this->db_prefix = $wgDBprefix;
		$this->dbr = &wfGetDB( DB_SLAVE );
	}
	function get_next_page_title(){
		$q = "select max(page_id) as max_page_id from ".$this->db_prefix."page";
		$res = $this->dbr->query($q);
		$row = $this->dbr->fetchObject($res);
		$next_id = ((int)$row->max_page_id)+1;
		
		return "IBIS_".$next_id;
	}
	function get_ibis_conversation_index($page){
		$q = "SELECT page_title FROM ".$this->db_prefix."page WHERE 
page_id IN (
SELECT rev_page FROM ".$this->db_prefix."revision 
WHERE rev_id IN (SELECT old_id FROM ".$this->db_prefix."text WHERE old_text REGEXP 'type: topic')
) 
AND 
page_title REGEXP 'IBIS_[0-9]*$' 
ORDER BY page_id desc";
		
		$res = $this->dbr->query($q);
		
		$page->setArticleFactory();
		$index = array();
		while($row = $this->dbr->fetchObject($res)){
			$ibis = $page->GetContent($row->page_title,True);
			$index[] = array(
				"page" => $row->page_title,
				"title" => $ibis['title'],
			);			
		}
		return $index;
	}
}
?>
