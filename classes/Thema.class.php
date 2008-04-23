<?php

class Thema {

	var $titel;
	var $beschreibung;
	var $user_id;
	var $visible;
	var $thema_id;
	var $perm;
	var $mkdatum;

	function Thema($id=FALSE) {
		if (!$id) {
			$this->titel = "";
			$this->beschreibung = "";
			$this->user_id = "";
			$this->visible = 0;
			$this->thema_id = "";
			$this->perm = "autor";
			$this->mkdatum = 0;
		} else {
			$db = new DB_Seminar();
			$db->queryf("SELECT * FROM sb_themen WHERE thema_id='%s'",$id);
			if ($db->next_record()) {
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

	function save() {
		$db = new DB_Seminar();
		if ($this->titel != "") {
			if ($this->thema_id != "")
				$db->queryf("UPDATE sb_themen SET titel='%s', beschreibung='%s', visible='%s', perm='%s' WHERE thema_id='%s'",$this->titel, $this->beschreibung, $this->visible, $this->perm, $this->thema_id);
			else {
				$id = md5(uniqid(time()));
				$db->queryf("INSERT INTO sb_themen (thema_id, titel, user_id, mkdate, beschreibung, visible, perm) VALUES ('%s','%s','%s',UNIX_TIMESTAMP(),'%s', %d, '%s')",$id,$this->titel,$GLOBALS['auth']->auth['uid'],$this->beschreibung,$this->visible, $this->perm);
			}
		}
	}

	function delete() {
		if ($this->thema_id) {
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

	function getThemaId() {
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
 * ?!?
 *
 */
class ThemaExt extends Thema
{

	var $artikel_count;

	function ThemaExt($id=FALSE)
	{
		parent::Thema($id);
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
