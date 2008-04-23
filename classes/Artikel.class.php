<?php

class Artikel {

	var $titel;
	var $beschreibung;
	var $user_id;
	var $visible;
	var $thema_id;
	var $artikel_id;
	var $mkdatum;

	function Artikel($id=FALSE) {
		if (!$id) {
			$this->titel = "";
			$this->beschreibung = "";
			$this->user_id = "";
			$this->visible = 1;
			$this->thema_id = "";
			$this->artikel_id = "";
			$this->mkdatum = 0;
		} else {
			$db = new DB_Seminar();
			$db->queryf("SELECT * FROM sb_artikel WHERE artikel_id='%s'",$id);
			if ($db->next_record()) {
				$this->titel = $db->f("titel");
	                        $this->beschreibung = $db->f("beschreibung");
        	                $this->user_id = $db->f("user_id");
                	        $this->visible = $db->f("visible");
	                        $this->thema_id = $db->f("thema_id");
        	                $this->artikel_id = $db->f("artikel_id");
				$this->mkdatum = $db->f("mkdate");
			}
		}
	}

	function save() {
		$db = new DB_Seminar();
		if ($this->thema_id != "" && $this->titel != "") {
			if ($this->artikel_id != "")
				$db->queryf("UPDATE sb_artikel SET titel='%s', beschreibung='%s', visible='%s' WHERE artikel_id='%s'",$this->titel, $this->beschreibung, $this->visible, $this->artikel_id);
			else {
				$id = md5(uniqid(time()));
				$db->queryf("INSERT INTO sb_artikel (artikel_id, thema_id, titel, user_id, mkdate, beschreibung, visible) VALUES ('%s','%s','%s','%s',UNIX_TIMESTAMP(),'%s', %d)",$id,$this->thema_id,$this->titel,$GLOBALS['auth']->auth['uid'],$this->beschreibung,$this->visible);
			}
		}
	}

	function delete() {
		if ($this->artikel_id) {
			$db = new DB_Seminar();
			$db->queryf("DELETE FROM sb_artikel WHERE artikel_id='%s'",$this->artikel_id);
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

	function getThemaId() {
		return $this->thema_id;
	}

	function getArtikelId() {
		return $this->artikel_id;
	}

	function getMkdate() {
		return $this->mkdatum;
	}
}

?>
