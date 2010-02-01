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

function fnAddCustomBlock($skin, $tpl){
	global $wgScript;
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
	if($action=="discussion"){
		$discussionHandler = new DiscussionHandler($current_title,$user,$op);
		if($wgRequest->wasPosted()){
			if($wgRequest->getCheck('save')){
				$title = $wgRequest->data['ibis_title'];
				$type = $wgRequest->data['type'];
				$user = $wgRequest->data['user'];
				$url = $discussionHandler->SaveDiscussionForm($title,$type,$user);
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
		$page->LoadCurrentPage(False);
		if(isset($page->ibis['responses'])){
			foreach($page->ibis['responses'] as $key=>$response){
				if($response['node']==$response_node && $response['user']==$user->id){
					unset($page->ibis['responses'][$key]);
					$page->SavePage();
					$wgOut->redirect($current_title->getFullURL());
					return False;
				}
			}
		}
	}
	return True;
}
function fnIBISTabsHandler(&$content_actions){
	global $wgTitle,$wgUser;
	$user = new UserHandler($wgUser);
	$tabs_handler = new TabsHandler($content_actions,$user,$wgTitle);
	if($user->isGuest){	
		$tabs_handler->RemoveEditTab();
		return True;
	}
	//Removing unwanted mediawiki tabs 
	//Discussion/Talk
	$tabs_handler->removeTab('talk');
	//Move
	$tabs_handler->removeTab('move');
	//Watch
	$tabs_handler->removeTab('watch');
	
	if($tabs_handler->isIBISNode()){
		//$display = new DisplayHandler($wgTitle,$user);
		$tabs_handler->removeTab('edit');
	}
	//$tabs_handler->addNewTab("new_discussion","New Dicussion","new");

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
		$display = new DisplayHandler($wgTitle);
		if(!$display->isConvertionApplicableForThisPage()){
			$wgOut->setPageTitle('Error');
			$wgOut->addHTML('<strong style="color:red">Sorry, You cannot add discussions this way. Use "New discussion" tab </strong>');
			return False;
		}
		//IBIS User Handler for current user
		$user = new UserHandler($wgUser);
		
		$type_map = array(
			'issue' => 'Issue',
			'position' => 'Position',
			'supporting_argument' => 'Supporting Argument',
			'opposing_argument' => 'Opposing Argument',
		);
		$wgOut->setPageTitle('Add/Edit responses to IBIS Node : '.$editpage->mTitle->getText());
		
		$content = $editpage->mArticle->getContent();		
		
		if ( $wgRequest->wasPosted() ) {		
			if($wgRequest->getCheck('save')){
				fnIBISSaveResponses($wgRequest,$editpage->mTitle,$user);
				$wgOut->redirect($editpage->mTitle->getFullUrl());
			}
			if($wgRequest->getCheck('cancel')){
				$wgOut->redirect($editpage->mTitle->getFullUrl());
			}
		}
		// Render Edit form
		$form_handler = new FormHandler($user,$content);
		$wgOut->addHTML	($form_handler->get_edit_form());
		
		return false;
	}
	else{
		return True;
	}
}
function fnIBISSaveResponses($request,$titleObj,$user){
	$types = $request->data['type'];
	$titles = $request->data['ibis_title'];
	$nodes = $request->data['node'];
	$users = $request->data['user'];
	
	$page_handler = new PageHandler($titleObj,$user);
	$page_handler->LoadCurrentPage();
	#$page_handler->initDB();
	for($i=0;$i<count($titles);$i++){
		$title = $titles[$i];
		$type = $types[$i];
		$node = $nodes[$i];
		$user = $users[$i];
		
		//Add the response only if its title is not empty
		if($title!=''){
			$response = fnIBISSaveResponse($type,$title,$node,$user,$page_handler);
			$page_handler->ibis['responses'][] = $response;
		}
		else{
			if($node!=''){
				$page_handler->removeParent($node);
			}
		}
	}	
	$page_handler->SavePage();
	return;
}
?>
