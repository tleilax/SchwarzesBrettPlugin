<?
/**
* ajaxDispatcher.php
*
* @author		Jan Kulmann <jankul@zmml.uni-bremen.de>
* @author		Michael Riehemann <michael.riehemann@uni-oldenburg.de>
* @package 		ZMML_SchwarzesBrettPlugin
* @copyright	2008 IBIT und ZMML
* @version 		1.1
*/

// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

page_open(array('sess' => 'Seminar_Session', 'auth' => 'Seminar_Default_Auth', 'perm' => 'Seminar_Perm', 'user' => 'Seminar_User'));


if(!empty($_REQUEST['ajax_cmd']) && $_REQUEST['ajax_cmd'] == 'visitObj') 
{
	$obj_id = trim($_REQUEST['objid']);
	require_once 'SchwarzesBrettPlugin.class.php';
	SchwarzesBrettPlugin::visit($obj_id, "artikel");
	echo "visit erfolgreich";
	die;
}
else
{
	echo "fehler";
	die;
}
?>
