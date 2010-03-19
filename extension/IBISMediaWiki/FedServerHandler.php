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


class FedServerHandler {
	function __construct(){
		global $wgIBISFedServerBaseUrl,$wgIBISFedServerConversationUrl,$wgIBISFedServerNodeUrl,$wgIBISFedServerUser,$wgIBISFedServerPwd;
		$this->user = $wgIBISFedServerUser;
		$this->pwd = $wgIBISFedServerPwd;
		$this->base = $wgIBISFedServerBaseUrl;
		$this->conversation = $wgIBISFedServerConversationUrl;
		$this->node = $wgIBISFedServerNodeUrl;
	}
	function build_params($cargo){
		$params = array(
			'username' => $this->user,
			'password' => $this->pwd,
			'cargo' => $cargo,
		);
		return http_build_query($params);
	}
	function postit($url,$cargo){
		$params = $this->build_params($cargo);
		$opts = array(
			'http'=>array(
			'method'=>"POST",
			'content' => $params,
			)
		);
		$context = stream_context_create($opts);
		$response = file_get_contents($url, false, $context);
	}
	function add_conversation($id,$type,$title,$desc,$author,$url){
		$server_url = $this->base.$this->conversation;
		$data = array(
			'nodeId' => $id,
			'type' => $type,
			'label' => $title,
			'details' => $desc,
			'author' => $author,
			'nodeUrl' => $url,
		);
		$cargo = json_encode($data);
		$this->postit($server_url,$cargo);
	}
	function add_node($parent_id,$parent_type,$id,$type,$title,$desc,$author,$url){
		$server_url = $this->base.$this->node;
		$data = array(
			'parentNodeId' => $parent_id,
			'parentNodeType' => $parent_type,
			'nodeId' => $id,
			'type' => $type,
			'label' => $title,
			'details' => $desc,
			'author' => $author,
			'nodeUrl' => $url,
		);
		$cargo = json_encode($data);
		$this->postit($server_url,$cargo);
	}
}
?>
