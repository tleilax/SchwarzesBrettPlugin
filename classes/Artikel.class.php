<?php
/**
* Artikel.class.php
*
* Eine Klasse für die Anzeigen des schwarzen Brettes. In diesem Plugin Artikel genannt.
*
* @author		Jan Kulmann <jankul@zmml.uni-bremen.de>
* @author		Michael Riehemann <michael.riehemann@uni-oldenburg.de>
* @package 		IBIT_SchwarzesBrettPlugin
* @copyright	2008-2009 IBIT und ZMML
* @version 		1.6.2
*/

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
    public function __construct($id=false)
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
    		$artikel = DBManager::get()->query("SELECT sb_artikel.*,sb_themen.titel as themen_titel FROM sb_artikel LEFT JOIN sb_themen USING(thema_id) WHERE artikel_id='{$id}'")->fetch(PDO::FETCH_ASSOC);
    		if (!empty($artikel))
    		{
                $this->titel = $artikel['titel'];
                $this->beschreibung = $artikel['beschreibung'];
                $this->user_id = $artikel['user_id'];
                $this->visible = $artikel['visible'];
                $this->thema_id = $artikel['thema_id'];
                $this->artikel_id = $artikel['artikel_id'];
    			$this->mkdatum = $artikel['mkdate'];
    			$this->thema_titel = $artikel['themen_titel'];
    		}
    	}
    }

    /**
     * Speichert neue und/oder bearbeite Artikel in die Datenbank
     *
     */
    public function save()
    {
    	if ($this->thema_id != "" && $this->titel != "")
    	{
    		//vorhanden Artikel updaten
    		if ($this->artikel_id != "") {
    			DBManager::get()->exec("UPDATE sb_artikel SET titel='{$this->titel}', beschreibung='{$this->beschreibung}', visible='{$this->visible}', thema_id='{$this->thema_id}' WHERE artikel_id='{$this->artikel_id}'");
    		}
    		//Neuen Artikel speichern
    		else {
    			$id = md5(uniqid(time()));
    			DBManager::get()->exec("INSERT INTO sb_artikel (artikel_id, thema_id, titel, user_id, mkdate, beschreibung, visible) VALUES ('{$id}','{$this->thema_id}','{$this->titel}','{$GLOBALS['auth']->auth['uid']}',UNIX_TIMESTAMP(),'{$this->beschreibung}', {$this->visible})");
    		}
    	}
    }

    /**
     * Löscht einen Artikel aus der Datenbank
     *
     */
    public function delete()
    {
    	if (!empty($this->artikel_id))
    	{
    		DBManager::get()->exec("DELETE FROM sb_artikel WHERE artikel_id='{$this->artikel_id}'");
    		DBManager::get()->exec("DELETE FROM sb_visits WHERE object_id='{$this->artikel_id}'");
    	}
    }

    function setTitel($s)
    {
    	$this->titel = trim($s);
    }

    function setBeschreibung($s)
    {
    	$this->beschreibung = trim($s);
    }

    function setUserId($s)
    {
    	$this->user_id = $s;
    }

    function setVisible($s)
    {
    	$this->visible = $s;
    }

    function setThemaId($s)
    {
    	$this->thema_id = $s;
    }

    function setArtikelId($s)
    {
    	$this->artikel_id = $s;
    }

    function getTitel()
    {
    	return $this->titel;
    }

    function getBeschreibung()
    {
    	return $this->beschreibung;
    }

    function getUserId()
    {
    	return $this->user_id;
    }

    function getVisible()
    {
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
