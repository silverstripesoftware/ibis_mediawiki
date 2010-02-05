<?php

/*
* Author : Karthik Jayapal
* Started on : 24/12/2009	
* For : iMorph Inc.,
*/

if( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	die();
}

require_once('FormHandler.php');
require_once('PageHandler.php');
require_once('DisplayHandler.php');
require_once('UserHandler.php');
require_once('DiscussionHandler.php');
require_once('TabsHandler.php');

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
	$wgHooks['SkinTemplateOutputPageBeforeExec'][] = 'fnAddCustomBlock';
	$wgHooks['SkinTemplateContentActions'][] = 'fnIBISTabsHandler';
	$wgHooks['AlternateEdit'][] = 'fnIBISEdit';
	$wgHooks['OutputPageBeforeHTML'][] = 'fnIBISPageRenderer';
	$wgHooks['UnknownAction'][] = 'fnIBISActionHandler';
}

function fnSetErrorPage($out){
	$out->setPageTitle('Error');
	$out->addHTML('<strong style="color:red">Sorry, You dont have permission to perform this action </strong>');
}

function fnAddCustomBlock($skin, $tpl){
	global $wgScript,$wgUser;
	$user = new UserHandler($wgUser);
	if($user->isGuest){	
		return True;
	}
	$sidebar = $tpl->data['sidebar'];
	$custom_sidebar = array();
	//Adding a new block on top
	$custom_sidebar['IBIS ACTIONS'][]	= array(
		'text' => 'New Discussion',
		'href' => $wgScript.'?action=discussion&op=new',
		'id' => 'n-ibis-actions',
		'active' => '',
	);
	foreach($sidebar as $key=>$val){
		$custom_sidebar[$key] = $val;
	}
	$tpl->set( 'sidebar', $custom_sidebar );
	return true;
}

function fnIBISActionHandler($action, $article){
	global $wgOut,$wgRequest,$wgUser;
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
				$user = $wgRequest->data['user'];
				$desc = $wgRequest->data['desc'];
				$url = $discussionHandler->SaveDiscussionForm($title,$type,$desc,$user);
				$wgOut->redirect($url);
			}
			if($wgRequest->getCheck('cancel')){
				$wgOut->redirect($current_title->getFullUrl());
			}
		}
		else{
			$discussionHandler->RenderDiscussionForm();
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
	$tabs_handler = new TabsHandler($content_actions);
	if (preg_match("/^IBIS\s\d+$/",$wgTitle->getText())){
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
	return True;
}

function fnIBISPageRenderer( &$out, &$text ){
	global $wgTitle,$wgScript,$wgOut,$wgUser;
	if (preg_match("/^IBIS\s\d+$/",$wgTitle->getText())){
		$title = $wgTitle;
		$path = $wgScript;
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
	global $wgOut,$wgRequest,$wgTitle,$wgUser;
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
		$form_handler = new FormHandler($user,$content);
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
		$response = fnIBISSaveResponse($type,$title,$user,$desc,$page_handler);
		$page_handler->ibis['responses'][] = $response;
		$page_handler->SavePage();
	}
	return;
}
?>