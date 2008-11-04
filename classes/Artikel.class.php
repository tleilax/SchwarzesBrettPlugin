<?php
/**
* Artikel.class.php
*
* Eine Klasse für die Anzeigen des schwarzen Brettes. In diesem Plugin Artikel genannt.
*
* @author		Jan Kulmann <jankul@zmml.uni-bremen.de>
* @author		Michael Riehemann <michael.riehemann@uni-oldenburg.de>
* @package 		ZMML_SchwarzesBrettPlugin
* @copyright	2008 IBIT und ZMML
* @version 		1.2.4
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

/**
 * Klasse für Anzeigen-Objekte
 *
 */
class Artikel
{

	private $titel;
	private $beschreibung;
	private $user_id;
	private $visible;
	private $thema_id;
	private $thema_titel;
	private $artikel_id;
	private $mkdatum;

	/**
	 * Konstruktor, erstellt Artikel-Objekte
	 *
	 */
	function __construct($id=FALSE)
	{
		if (!$id)
		{
			$this->titel = "";
			$this->beschreibung = "";
			$this->user_id = "";
			$this->visible = 1;
			$this->thema_id = "";
			$this->artikel_id = "";
			$this->mkdatum = 0;
		}
		else
		{
			$db = new DB_Seminar();
			$db->queryf("SELECT * FROM sb_artikel WHERE artikel_id='%s'",$id);
			if ($db->next_record())
			{
				$this->titel = $db->f("titel");
                $this->beschreibung = $db->f("beschreibung");
                $this->user_id = $db->f("user_id");
    	        $this->visible = $db->f("visible");
                $this->thema_id = $db->f("thema_id");
                $this->artikel_id = $db->f("artikel_id");
				$this->mkdatum = $db->f("mkdate");
			}
			$db->queryf("SELECT titel FROM sb_themen WHERE thema_id='%s'", $this->thema_id);
			if ($db->next_record())
			{
                $this->thema_titel = $db->f("titel");
			}
		}
	}

	/**
	 * Speichert neue und/oder bearbeite Artikel in die Datenbank
	 *
	 */
	function save()
	{
		$db = new DB_Seminar();
		if ($this->thema_id != "" && $this->titel != "")
		{
			//vorhanden Artikel updaten
			if ($this->artikel_id != "")
			{

				$db->queryf("UPDATE sb_artikel SET titel='%s', beschreibung='%s', visible='%s', thema_id='%s' WHERE artikel_id='%s'",$this->titel, $this->beschreibung, $this->visible, $this->thema_id, $this->artikel_id);
			}
			//Neuen Artikel speichern
			else
			{
				$id = md5(uniqid(time()));
				$db->queryf("INSERT INTO sb_artikel (artikel_id, thema_id, titel, user_id, mkdate, beschreibung, visible) VALUES ('%s','%s','%s','%s',UNIX_TIMESTAMP(),'%s', %d)",$id,$this->thema_id,$this->titel,$GLOBALS['auth']->auth['uid'],$this->beschreibung,$this->visible);
			}
		}
	}

	/**
	 * Löscht einen Artikel aus der Datenbank
	 *
	 */
	function delete()
	{
		if (!empty($this->artikel_id))
		{
			$db = new DB_Seminar();
			$db->queryf("DELETE FROM sb_artikel WHERE artikel_id='%s'",$this->artikel_id);
			$db->queryf("DELETE FROM sb_visits WHERE object_id='%s'",$this->artikel_id);
		}
	}

	function setTitel($s) {
		$this->titel = trim($s);
	}

	function setBeschreibung($s) {
		$this->beschreibung = trim($s);
	}

	function setUserId($s) {
		$this->user_id = $s;
	}

	function setVisible($s) {
		$this->visible = $s;
	}

	function setThemaId($s) {
		$this->thema_id = $s;
	}

	function setArtikelId($s) {
		$this->artikel_id = $s;
	}

	function getTitel() {
		return $this->titel;
	}

	function getBeschreibung() {
		return $this->beschreibung;
	}

	function getUserId() {
		return $this->user_id;
	}

	function getVisible() {
		return $this->visible;
	}

	/**
	 * Gibt die Themen-ID zurück
	 *
	 * @return string ThemaID
	 */
	function getThemaId()
	{
		return $this->thema_id;
	}

	function getThemaTitel()
	{
		return $this->thema_titel;
	}

	function getArtikelId() {
		return $this->artikel_id;
	}

	function getMkdate() {
		return $this->mkdatum;
	}
}

?>
