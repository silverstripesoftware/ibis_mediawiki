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

if( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	die();
}
//Third pary libraries 
require_once('IBISIncludes.php');
//Our own handlers
require_once("DBWrapper.php");
require_once("YAMLHandler.php");
require_once('UserHandler.php');
require_once('FormHandler.php');
require_once('PageHandler.php');
require_once('DisplayHandler.php');
require_once('DiscussionHandler.php');
require_once('TabsHandler.php');
require_once('FedServerHandler.php');

$wgExtensionFunctions[] = 'fnIBISMediaWiki';
$wgExtensionCredits['other'][] = array(
  'name'       => 'IBIS_Mediawiki',
  'author'     => 'Karthik jayapal',
  'url'        => '',
  'description'=> 'IBIS conversation in Mediawiki',
);

function fnIBISMediaWiki()
{
	global $wgHooks;
	$wgHooks['SkinTemplateOutputPageBeforeExec'][] = 'fnIBISBlockHandler';
	$wgHooks['SkinTemplateContentActions'][] = 'fnIBISTabsHandler';
	$wgHooks['AlternateEdit'][] = 'fnIBISEdit';
	$wgHooks['OutputPageBeforeHTML'][] = 'fnIBISPageRenderer';
	$wgHooks['UnknownAction'][] = 'fnIBISActionHandler';
}

function fnSetErrorPage($out){
	$out->setPageTitle('Error');
	$out->addHTML('<strong style="color:red">Sorry, You dont have permission to perform this action </strong>');
}

function fnIBISBlockHandler($skin, $tpl){
	global $wgScript,$wgUser;
	/*$user = new UserHandler($wgUser);
	if($user->isGuest){	
		return True;
	}*/
	$sidebar = $tpl->data['sidebar'];
	$custom_sidebar = array();
	//Adding IBIS Index link in navigation sidebar block
	$ibis_index	= array(
		'text' => 'IBIS Conversation Index',
		'href' => $wgScript.'/IBIS_Index',
		'id' => 'n-ibis-index',
		'active' => '',
	);
	$sidebar['navigation'][] = $ibis_index;
	$tpl->set( 'sidebar', $sidebar );
	return true;
}

function fnIBISActionHandler($action, $article){	
	global $wgOut,$wgRequest,$wgUser,$wgScriptPath;
	$current_title = $article->getTitle();
	$user = new UserHandler($wgUser);
	$op = isset($wgRequest->data['op'])?$wgRequest->data['op']:'';
	if($user->isGuest){
		fnSetErrorPage($wgOut);
		return False;
	}
	if($action=="discussion"){
		$discussionHandler = new DiscussionHandler($current_title,$user,$op);
		if($wgRequest->wasPosted()){
			if($wgRequest->getCheck('save')){
				$title = $wgRequest->data['ibis_title'];
				$type = $wgRequest->data['type'];
				$user_id = $wgRequest->data['user'];
				$desc = $wgRequest->data['desc'];
				$fed_handler = new FedServerHandler();
				$url = $discussionHandler->SaveDiscussionForm($fed_handler,$title,$type,$desc,$user_id,$user->name);
				$wgOut->redirect($url);
			}
			if($wgRequest->getCheck('cancel')){
				$wgOut->redirect($current_title->getFullUrl());
			}
		}
		else{
			$discussionHandler->RenderDiscussionForm($wgScriptPath);
			$wgOut->setPageTitle($discussionHandler->outTitle);
			$wgOut->addHTML($discussionHandler->outHTML);
		}
		return false;
	}
	elseif($action=="response"&&$op=="remove"){
		$response_node = $wgRequest->data['response'];
		$page = new PageHandler($current_title,$user);
		$page->LoadCurrentPage();
		if(isset($page->ibis['responses'])){
			foreach($page->ibis['responses'] as $key=>$response){
				if($response['node']==$response_node && ($response['user']==$user->id || $user->isAdminUser)){
					unset($page->ibis['responses'][$key]);
					$page->SavePage();
					$page->removeParent($response_node);
					$wgOut->redirect($current_title->getFullURL());
					return False;
				}
			}
		}
	}
	return True;
}
function fnIBISTabsHandler(&$content_actions){
	global $wgUser,$wgTitle;
	
	$user = new UserHandler($wgUser);
	$tabs_handler = new TabsHandler($content_actions,$wgTitle);
	if ($tabs_handler->isIBISNode()){
		$tabs_handler->RemoveEditTab();
		//Removing unwanted mediawiki tabs 
		//Discussion/Talk
		$tabs_handler->removeTab('talk');
		
		//Non-Registered user will not have other two tabs
		if($user->isGuest){	
			return True;
		}
		//Move
		$tabs_handler->removeTab('move');
		//Watch
		$tabs_handler->removeTab('watch');	
	}
	if(!$user->isGuest){	
		$tabs_handler->addNewDiscussionTab();
	}
	return True;
}

function fnIBISPageRenderer( &$out, &$text ){
	global $wgTitle,$wgScript,$wgOut,$wgUser,$wgDBprefix ;
	$page_title = $wgTitle->getText();
	$path = $wgScript;
	if($page_title == "IBIS Index"){
		$wgOut->setPageTitle('IBIS Conversation Index');
		$display = new DisplayHandler();
		$text = $display->getIBISIndex($path);
	}
	elseif (preg_match("/^IBIS\s\d+$/",$page_title)){
		$title = $wgTitle;
		$user = new UserHandler($wgUser);
		$display = new DisplayHandler($title,$user);
		if($display->isConvertionApplicableForThisPage()){
			$text = $display->getPageHTML($path);
		}
		else{
			$wgOut->setPageTitle('Error');
			$text = '<strong style="color:red">Sorry, You cannot add discussions this way. Use "New discussion" tab </strong>';
		}
	}
	return true;
}

function fnIBISEdit( &$editpage)
{	
	global $wgOut,$wgRequest,$wgTitle,$wgUser,$wgScriptPath;
	
	if (preg_match("/^IBIS\s\d+$/",$wgTitle->getText())){
		//IBIS User Handler for current user
		$user = new UserHandler($wgUser);
		if($user->isGuest){
			fnSetErrorPage($wgOut);
			return False;
		}
		$display = new DisplayHandler($wgTitle);
		if(!$display->isConvertionApplicableForThisPage()){
			$wgOut->setPageTitle('Error');
			$wgOut->addHTML('<strong style="color:red">Sorry, You cannot add discussions this way. Use "New discussion" to add </strong>');
			return False;
		}
		
		$content = $editpage->mArticle->getContent();		
		
		if ( $wgRequest->wasPosted() ) {		
			if($wgRequest->getCheck('save')){
				fnIBISAddResponse($wgRequest,$editpage->mTitle,$user);
			}
			$wgOut->redirect($editpage->mTitle->getFullUrl());
		}
		// Render Edit form
		$form_handler = new FormHandler($user,$content,$wgScriptPath);
		$wgOut->setPageTitle('Add a response to the discussion : '.$form_handler->ibis['title']);
		$wgOut->addHTML	($form_handler->get_response_form());
		
		return false;
	}
	else{
		return True;
	}
}
function fnIBISAddResponse($request,$titleObj,$user){
	$type = $request->data['type'];
	$title = $request->data['ibis_title'];
	$user = $request->data['user'];
	$desc = $request->data['desc'];
	$page_handler = new PageHandler($titleObj,$user);
	$page_handler->LoadCurrentPage();
	if($title!=''){
		$fed_handler = new FedServerHandler();
		$response = fnIBISSaveResponse($fed_handler,$type,$title,$user,$desc,$page_handler);
		$page_handler->ibis['responses'][] = $response;
		$page_handler->SavePage();
	}
	return;
}
?>