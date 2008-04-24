<?php
/**
* Thema.class.php
*
* In dieser Datei sind 2 Klassen: Thema und ThemaExt.
* Eine Klasse für die Kategorien des schwarzen Brettes. In diesem Plugin Thema genannt.
* Dazu noch eine Erweiterungsklasse
*
* @author		Jan Kulmann <jankul@zmml.uni-bremen.de>
* @author		Michael Riehemann <michael.riehemann@uni-oldenburg.de>
* @package 		ZMML_SchwarzesBrettPlugin
* @copyright	2008 IBIT und ZMML
* @version 		1.0.2
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
 * Klasse für Themen-Objekte
 *
 */
class Thema
{

	/**
	 * Titel des Themas
	 *
	 * @var string
	 */
	var $titel;

	/**
	 * Beschreibung des Themas
	 *
	 * @var string
	 */
	var $beschreibung;

	/**
	 * Die ID des Erstellers
	 *
	 * @var string
	 */
	var $user_id;

	/**
	 * Sichtbarkeitsstatus für andere Benutzer
	 *
	 * @var boolean
	 */
	var $visible;

	/**
	 * Die ID des Themas
	 *
	 * @var unknown_type
	 */
	var $thema_id;

	/**
	 * Benutzerrechte für dieses Thema
	 *
	 * @var string
	 */
	var $perm;

	/**
	 * Erstellungsdatum
	 *
	 * @var unknown_type
	 */
	var $mkdatum;

	/**
	 * Konstruktor, erstellt ein Objekt der Klasse Thema
	 *
	 * @param string $id
	 * @return Thema
	 */
	public function __construct($id=FALSE)
	{
		if (!$id)
		{
			$this->titel = "";
			$this->beschreibung = "";
			$this->user_id = "";
			$this->visible = 0;
			$this->thema_id = "";
			$this->perm = "autor";
			$this->mkdatum = 0;
		}
		else
		{
			$db = new DB_Seminar();
			$db->queryf("SELECT * FROM sb_themen WHERE thema_id='%s'",$id);
			if ($db->next_record())
			{
				$this->titel = $db->f("titel");
                $this->beschreibung = $db->f("beschreibung");
                $this->user_id = $db->f("user_id");
    	        $this->visible = $db->f("visible");
                $this->perm = $db->f("perm");
                $this->thema_id = $db->f("thema_id");
				$this->mkdatum = $db->f("mkdate");
			}
		}
	}

	/**
	 * Speichert ein Thema in die Datenbank. Entweder neu angelegt oder bearbeitet.
	 *
	 */
	function save()
	{
		$db = new DB_Seminar();
		if ($this->titel != "")
		{
			if ($this->thema_id != "")
			{
				$db->queryf("UPDATE sb_themen SET titel='%s', beschreibung='%s', visible='%s', perm='%s' WHERE thema_id='%s'",$this->titel, $this->beschreibung, $this->visible, $this->perm, $this->thema_id);

			}
			else
			{
				$id = md5(uniqid(time()));
				$db->queryf("INSERT INTO sb_themen (thema_id, titel, user_id, mkdate, beschreibung, visible, perm) VALUES ('%s','%s','%s',UNIX_TIMESTAMP(),'%s', %d, '%s')",$id,$this->titel,$GLOBALS['auth']->auth['uid'],$this->beschreibung,$this->visible, $this->perm);
			}
		}
	}

	/**
	 * Löscht ein thema aus der Datenbank
	 *
	 */
	function delete()
	{
		if ($this->thema_id)
		{
			$db = new DB_Seminar();
			$db->queryf("DELETE FROM sb_visits WHERE object_id='%s'",$this->thema_id);
			$db->queryf("DELETE FROM sb_artikel WHERE thema_id='%s'",$this->thema_id);
			$db->queryf("DELETE FROM sb_themen WHERE thema_id='%s'",$this->thema_id);
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

	function setPerm($s) {
		$this->perm = $s;
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
	 * Gibt die Id des Themas zurück
	 *
	 * @return string
	 */
	function getThemaId()
	{
		return $this->thema_id;
	}

	function getPerm() {
		return $this->perm;
	}

	function getMkdate() {
		return $this->mkdatum;
	}

}

/**
 * Erweiterung für die Klasse Thema
 *
 */
class ThemaExt extends Thema
{

	/**
	 * Anzahl an Artikel
	 *
	 * @var unknown_type
	 */
	var $artikel_count;

	/**
	 * Konstruktor
	 *
	 * @param string $id
	 */
	public function __construct($id=FALSE)
	{
		parent::__construct($id);
		$this->artikel_count = 0;
	}

	function setArtikelCount($c)
	{
		$this->artikel_count = $c;
	}

	function getArtikelCount()
	{
		return $this->artikel_count;
	}
}

?>
