<?
/**
* ajaxDispatcher.php
*
* @author               Jan Kulmann <jankul@zmml.uni-bremen.de>
*/

// +---------------------------------------------------------------------------+
// Copyright (C) 2007-2008 Jan Kulmann <jankul@zmml.uni-bremen.de>
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

if (!array_key_exists('ajax_cmd', $_REQUEST)) die;


switch($_REQUEST['ajax_cmd']) {
	case 'visitObj':
		$obj_id = trim($_REQUEST['objid']);
		if (!$obj_id) break;
		require_once dirname(__FILE__) . '/SchwarzesBrettPlugin.class.php';
		SchwarzesBrettPlugin::visit($obj_id, "artikel");
		echo "success";
		break;
	default: ;
}
?>
