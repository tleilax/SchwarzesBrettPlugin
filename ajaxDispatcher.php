<?
/**
* ajaxDispatcher.php
*
* @author		Jan Kulmann <jankul@zmml.uni-bremen.de>
* @author		Michael Riehemann <michael.riehemann@uni-oldenburg.de>
* @package 		ZMML_SchwarzesBrettPlugin
* @copyright	2009 IBIT und ZMML
* @version 		1.6.1
*/

page_open(array(
	'sess' => 'Seminar_Session', 
	'auth' => 'Seminar_Default_Auth', 
	'perm' => 'Seminar_Perm', 
	'user' => 'Seminar_User')
);

if(!empty($_REQUEST['objid']))
{
	$uid=$GLOBALS['auth']->auth['uid'];
	$oid=trim($_REQUEST['objid']);
	DBManager::get()->exec("REPLACE INTO sb_visits SET object_id='{$oid}', user_id='{$uid}', type='artikel', last_visitdate=UNIX_TIMESTAMP()");
}
die;
?>
