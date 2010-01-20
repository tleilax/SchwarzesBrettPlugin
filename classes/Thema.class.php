<?php

/**
 * Thema.class.php
 *
 * Eine Klasse für die Kategorien des schwarzen Brettes.
 * In diesem Plugin Thema genannt.
 *
 * @author		Jan Kulmann <jankul@zmml.uni-bremen.de>
 * @author		Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @package 	IBIT_SchwarzesBrettPlugin
 * @copyright	2009 IBIT und ZMML
 * @version 	1.6.2
 */

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
	private $titel;

	/**
	 * Beschreibung des Themas
	 *
	 * @var string
	 */
	private $beschreibung;

	/**
	 * Die ID des Erstellers
	 *
	 * @var string
	 */
	private $user_id;

	/**
	 * Sichtbarkeitsstatus für andere Benutzer
	 *
	 * @var boolean
	 */
	private $visible;

	/**
	 * Die ID des Themas
	 *
	 * @var unknown_type
	 */
	private $thema_id;

	/**
	 * Benutzerrechte für dieses Thema
	 *
	 * @var string
	 */
	private $perm;

	/**
	 * Erstellungsdatum
	 *
	 * @var unknown_type
	 */
	private $mkdatum;

	private $artikel_count;

	/**
	 * Konstruktor, erstellt ein Objekt der Klasse Thema
	 *
	 * @param string $id
	 * @return Thema
	 */
	public function __construct($id = false)
	{
		if(!$id)
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
			$thema = DBManager::get()->query("SELECT * FROM sb_themen WHERE thema_id='{$id}'")->fetch(PDO::FETCH_ASSOC);
			if(!empty($thema))
			{
				$this->titel = $thema['titel'];
				$this->beschreibung = $thema['beschreibung'];
				$this->user_id = $thema['user_id'];
				$this->visible = $thema['visible'];
				$this->perm = $thema['perm'];
				$this->thema_id = $thema['thema_id'];
				$this->mkdatum = $thema['mkdate'];
			}
		}
		$this->artikel_count = 0;
	}

	/**
	 * Speichert ein Thema in die Datenbank. Entweder neu angelegt oder bearbeitet.
	 *
	 */
	public function save()
	{
		if($this->titel != "")
		{
			if($this->thema_id != "")
			{
				DBManager::get()->exec("UPDATE sb_themen SET titel='{$this->titel}', beschreibung='{$this->beschreibung}', visible='{$this->visible}', perm='{$this->perm}' WHERE thema_id='{$this->thema_id}'");

			} else
			{
				$id = md5(uniqid(time()));
				DBManager::get()->exec("INSERT INTO sb_themen (thema_id, titel, user_id, mkdate, beschreibung, visible, perm) VALUES ('{$id}','{$this->titel}','{$GLOBALS['auth']->auth['uid']}',UNIX_TIMESTAMP(),'{$this->beschreibung}', {$this->visible}, '{$this->perm}')");
			}
		}
	}

	/**
	 * Löscht ein thema aus der Datenbank
	 *
	 */
	public function delete()
	{
		if($this->thema_id)
		{
			DBManager::get()->exec("DELETE FROM sb_visits WHERE object_id='{$this->thema_id}'");
			DBManager::get()->exec("DELETE FROM sb_artikel WHERE thema_id='{$this->thema_id}'");
			DBManager::get()->exec("DELETE FROM sb_themen WHERE thema_id='{$this->thema_id}'");
		}
	}

	public function setTitel($s)
	{
		$this->titel = trim($s);
	}

	public function setBeschreibung($s)
	{
		$this->beschreibung = trim($s);
	}

	public function setUserId($s)
	{
		$this->user_id = $s;
	}

	public function setVisible($s)
	{
		$this->visible = $s;
	}

	public function setThemaId($s)
	{
		$this->thema_id = $s;
	}

	public function setPerm($s)
	{
		$this->perm = $s;
	}

	public function getTitel()
	{
		return $this->titel;
	}

	public function getBeschreibung()
	{
		return $this->beschreibung;
	}

	function getUserId()
	{
		return $this->user_id;
	}

	public function getVisible()
	{
		return $this->visible;
	}

	/**
	 * Gibt die Id des Themas zurück
	 *
	 * @return string
	 */
	public function getThemaId()
	{
		return $this->thema_id;
	}

	public function getPerm()
	{
		return $this->perm;
	}

	public function getMkdate()
	{
		return $this->mkdatum;
	}

	public function setArtikelCount($c)
	{
		$this->artikel_count = $c;
	}

	public function getArtikelCount()
	{
		return $this->artikel_count;
	}
}
?>
