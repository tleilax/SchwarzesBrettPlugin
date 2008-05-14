<?php
/**
 * SchwarzesBrettPlugin.class.php (SystemPlugin)
 *
 * Plugin zum Verwalten von Schwarzen Brettern (Angebote und Gesuche)
 * Diese Datei enthält die Hauptklasse des Plugins
 * Dieses Plugin basiert auf PHP5 und steht unter der GPL.
 * @author		Jan Kulmann <jankul@zmml.uni-bremen.de>
 * @author		Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @package 	ZMML_SchwarzesBrettPlugin
 * @copyright	2008 IBIT und ZMML
 * @version 	1.2.4
 */

// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+

// Imports
require_once("lib/functions.php");
require_once("lib/messaging.inc.php");
require_once("classes/Artikel.class.php");
require_once("classes/Thema.class.php");
require_once('vendor/flexi/flexi.php');

//debug
//error_reporting( E_ALL );


/**
 * SchwarzesBrettPlugin Hauptklasse
 *
 */
class SchwarzesBrettPlugin extends AbstractStudIPSystemPlugin
{
	/**
	 * Laufzeit der Einträge in Sekunden
	 *
	 * @var int
	 */
	public $zeit;

	/**
	 * Objekt der Templateklasse
	 *
	 * @var unknown_type
	 */
	private $template_factory;

	/**
	 * aktueller Benutzer
	 *
	 * @var user Objekt
	 */
	public $user;

	/**
	 * aktuelle Benutzerrechte
	 *
	 * @var permission Objekt
	 */
	public $permission;

	/**
	 * Konstruktor, erzeugt das Plugin.
	 *
	 */
	function __construct()
	{
		parent::AbstractStudIPSystemPlugin();
		$this->template_factory = new Flexi_TemplateFactory(dirname(__FILE__).'/templates');
		$this->user = $this->getUser();
		$this->permission = $this->user->getPermission();

		//plugin-icon
		$this->setPluginiconname('images/paste_plain.png');
		//$this->setPluginiconname('images/header_pinn1.gif'); //für safiredesign

		// Navigationsreiter erzeugen
		$nav = new PluginNavigation();
		$nav->setDisplayname(_('Schwarzes Brett'));
		$this->setNavigation($nav);

		// Holt die Laufzeit aus der Config. Default: 30Tage
		$this->zeit = get_config('BULLETIN_BOARD_DURATION') * 24 * 60 * 60;
	}

	/**
	 * Setzt den Pfad zum Plugin.
	 * @param string $path Pfad zum Plugin
	 */
	public function setPluginpath($path)
	{
		parent::setPluginpath($path);
		$this->buildMenu();
	}

	/**
	 * Erstellt das Menü des Plugins.
	 * @uses PluginNavigation
	 */
	public function buildMenu()
	{
		/*$new_postings = $this->new_items_since_last_visit();
		$all = $this->num_all_postings();
		$p = sprintf(dgettext('sb',"%d"),$all);
		if ($new_postings) $p .= sprintf(dgettext('sb',"/%d"),$new_postings);*/
		$tab = new PluginNavigation();
		$tab->setDisplayname(_('Schwarzes Brett'));
		$this->setNavigation( $tab );
	}

	/**
	 * Liefert den Namen des Plugins zurück.
	 * @return string Der Name des Plugins
	 */
	public function getPluginname()
	{
		return _('SchwarzesBrettPlugin');
	}

	/**
	 * Gibt alle Anzeigen zu einem Thema zurück
	 *
	 * @param string $thema_id
	 * @return array Anzeigen
	 */
	private function getArtikel($thema_id)
	{
		$ret = array();
		$db = new DB_Seminar();
		$db->queryf("SELECT * FROM sb_artikel WHERE thema_id='%s' AND UNIX_TIMESTAMP() < (mkdate + %d) AND (visible=1 OR (visible=0 AND (user_id='%s' OR 'root'='%s'))) ORDER BY mkdate DESC",$thema_id,$this->zeit,$this->user->getUserid(),$this->permission->perm->get_perm($this->user->getUserid()));
		$ret = array();
		while ($db->next_record())
		{
			$a = new Artikel($db->f("artikel_id"));
			array_push($ret, $a);
		}
		return $ret;
	}

	/**
	 * Gibt die Anzahl Besucher eines Artikels zurück.
	 *
	 * @param string $artikel_id
	 * @return int Anzahl Besucher
	 */
	private function getArtikelLookups($artikel_id)
	{
		$db = new DB_Seminar();
		$db->queryf("SELECT COUNT(*) AS count FROM sb_visits WHERE type='artikel' AND object_id='%s'",$artikel_id);
		$db->next_record();
		return $db->f('count');
	}

	/**
	 * Gibt eine Liste aller Themen aus der Datenbank zurück, die sichtbar sind
	 * oder in denen der Benutzer bereits einen Artikel erstellt hat.
	 *
	 * @return array Liste aller Themen
	 */
	private function getThemen()
	{
		$ret = array();
		$db = new DB_Seminar();
		$db->queryf("SELECT t.*, COUNT(a.artikel_id) count_artikel FROM sb_themen t LEFT JOIN sb_artikel a USING (thema_id) WHERE t.visible=1 OR t.user_id='%s' OR 'perm'='%s' GROUP BY t.thema_id ORDER BY t.titel",$this->user->getUserid(),$this->permission->perm->get_perm($this->user->getUserid()));
		$ret = array();
		while ($db->next_record())
		{
			$t = new ThemaExt($db->f("thema_id"));
			$t->setArtikelCount($db->f("count_artikel"));
			array_push($ret, $t);
		}
		return $ret;
	}

	/**
	 * Gibt die Benutzerrechte eines Themas zurück
	 *
	 * @param string $thema_id
	 * @return string $permission
	 */
	private function getThemaPermission($thema_id)
	{
		$db = new DB_Seminar();
		$db->queryf("SELECT perm FROM sb_themen WHERE thema_id='%s'",$thema_id);
		$db->next_record();
		return $db->f("perm");
	}

	/**
	 * Überprüft, ob eine Anzeige bereits vorhanden ist, dabei werden
	 * Titel, UserID und Datum verglichen.
	 *
	 * @param string $titel
	 * @return boolean
	 */
	private function isDuplicate($titel)
	{
		$db = new DB_Seminar();
		$db->queryf("SELECT artikel_id FROM sb_artikel WHERE user_id='%s' AND titel='%s' AND mkdate>(UNIX_TIMESTAMP()-(60*60*24))",$this->user->getUserid(),$titel);
		if ($db->num_rows()>0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Überprüft, ob der Benutzer dieses Objekt (Thema oder Artikel) bereits angesehen hat.
	 *
	 * @param string $obj_id
	 * @return datetime oder boolean
	 */
	private function hasVisited($obj_id)
	{
		$db = new DB_Seminar();
		$db->queryf("SELECT last_visitdate FROM sb_visits WHERE object_id='%s' AND user_id='%s'",$obj_id,$this->user->getUserid());
		if ($db->next_record())
		{
			return $db->f("last_visitdate");
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Führt die Suche nach Anzeigen durch und zeigt die Ergebnisse an.
	 *
	 * @param String $search_text Suchwort
	 */
	private function search($search_text)
	{
		//Benutzereingaben abfangen (Wörter kürzer als 3 Zeichen)
		if(empty($search_text) || strlen($search_text) < 3)
		{
			StudIPTemplateEngine::showErrorMessage("Ihr Suchwort ist zu kurz, bitte versuchen Sie es erneut!");
			$this->showThemen();
			die;
		}

		//Datenbankabfrage
		$db = new DB_Seminar();
		$sql = sprintf("SELECT a.thema_id, a.artikel_id, a.titel, t.titel t_titel FROM sb_artikel AS a, sb_themen AS t WHERE
				t.thema_id=a.thema_id AND (UPPER(a.titel) LIKE '%s' OR UPPER(a.beschreibung) LIKE '%s') AND UNIX_TIMESTAMP() < (a.mkdate + %d)
				AND (a.visible=1 OR (a.visible=0 AND (a.user_id='%s' OR 'root'='%s'))) ORDER BY t.titel, a.titel
			","%".strtoupper($search_text)."%","%".strtoupper($search_text)."%",$this->zeit,$this->user->getUserid(),$this->permission->perm->get_perm($this->user->getUserid()));
		$db->query($sql);

		// keine Ergebnisse vorhanden
		if($db->num_rows()==0)
		{
			StudIPTemplateEngine::showErrorMessage("Es wurden keine Ergebnisse gefunden. Bitte versuchen Sie es mit einem anderen Wort erneut.");
			$this->showThemen();
		}
		//Ergebnisse anzeigen
		else
		{
			$results = array();
			$thema = array();
			while ($db->next_record())
			{
				$a = new Artikel($db->f("artikel_id"));
				if(empty($thema['thema_id']))
				{
					$thema['thema_id'] = $db->f("thema_id");
					$thema['thema_titel'] = htmlReady($db->f("t_titel"));
					$thema['artikel'] = array();

				}
				elseif($db->f("thema_id") != $thema['thema_id'])
				{
					array_push($results, $thema);
					unset($thema);
					$thema['thema_id'] = $db->f("thema_id");
					$thema['thema_titel'] = htmlReady($db->f("t_titel"));
					$thema['artikel'] = array();
				}
				else
				{

				}
				array_push($thema['artikel'], $this->showArtikel($a));
			}
			array_push($results, $thema);

			//Ausgabe erzeugen
			$this->showAjaxScript();
			$template = $this->template_factory->open('search_results');

			//Adminfunktionen anzeigen
			if ($this->permission->hasRootPermission())
			{
				$template->set_attribute('rootlink', PluginEngine::getLink($this,array("modus"=>"show_add_thema_form")));
				$template->set_attribute('rootaccess', TRUE);
			}

			$template->set_attribute('zeit', $this->zeit);
			$template->set_attribute('pluginpfad', $this->getPluginpath());
			$template->set_attribute('link_search', PluginEngine::getLink($this,array("modus"=>"show_search_results")));
			$template->set_attribute('link_back', PluginEngine::getLink($this,array()));
			$template->set_attribute('results', $results);
			echo $template->render();
		}
	}

	/**
	 * Fügt ein Javascript für Ajax-Funktionen ein
	 *
	 */
	private function showAjaxScript()
	{
?>
<script type="text/javascript" language="javascript">
		function showArtikel(id)
		{
			$('content_'+id).style.display='block';
			$('headline_'+id).style.display='none';
			new Ajax.Request('<?=$GLOBALS['ABSOLUTE_URI_STUDIP']?><?=$this->getPluginpath()?>/ajaxDispatcher.php?objid='+id, {method: 'post'});
		}
		function closeArtikel(id)
		{
			$('content_'+id).style.display='none';
			$('headline_'+id).style.display='block';
		}
		function toogleThema(id)
		{
			$('list_'+id).toggle();
		}
		</script>
<?
	}

	/**
	 * Zeigt alle Themen und Anzeigen an
	 *
	 */
	private function showThemen()
	{
		$themen = $this->getThemen();

		$this->showAjaxScript();
		$template = $this->template_factory->open('show_themen');
		$template->set_attribute('zeit', $this->zeit);
		$template->set_attribute('pluginpfad', $this->getPluginpath());
		$template->set_attribute('link_edit', PluginEngine::getLink($this,array("modus"=>"show_add_thema_form")));
		$template->set_attribute('link_artikel', PluginEngine::getLink($this,array("modus"=>"show_add_artikel_form")));
		$template->set_attribute('link_delete', PluginEngine::getLink($this,array("modus"=>"delete_thema")));
		$template->set_attribute('link_search', PluginEngine::getLink($this,array("modus"=>"show_search_results")));
		$template->set_attribute('link_back', PluginEngine::getLink($this,array()));

		//Keine themen vorhanden
		if (count($themen) == 0)
		{
			$template->set_attribute('keinethemen', TRUE);
		}
		//themen anzeigen
		else
		{
			//Anzahl Themen pro Spalte berechnen
			if(count($themen) > 6) //3 Spalten
			{
				$template->set_attribute('themen_rows', (count($themen)%3==0)? count($themen)/3 : (count($themen)/3)+1);
			}
			elseif(count($themen) > 2) //2 Spalten
			{
				$template->set_attribute('themen_rows', 2);
			}
			else //1 Spalte
			{
				$template->set_attribute('themen_rows', 1);
			}

			$results = array();
			$thema = array();
			foreach ($themen as $tt)
			{
				$thema['thema'] = $tt;
				if($this->permission->perm->have_perm($tt->getPerm(), $this->user->getUserid()) ||  $this->permission->hasRootPermission())
				{
					$thema['permission'] = true;
				}
				$thema['artikel'] = array();
				$artikel = $this->getArtikel($tt->getThemaId());
				foreach($artikel as $a)
				{
					array_push($thema['artikel'], $this->showArtikel($a));
				}

				$thema['countArtikel'] = count($artikel);
				array_push($results, $thema);
			}
			$template->set_attribute('results', $results);
		}
		//Adminfunktionen anzeigen
		if ($this->permission->hasRootPermission())
		{
			$template->set_attribute('rootlink', PluginEngine::getLink($this,array("modus"=>"show_add_thema_form")));
			$template->set_attribute('rootaccess', TRUE);
		}
		echo $template->render();
	}

	/**
	 * Zeigt eine Anzeige an
	 *
	 * @param Object $a eine Anzeige
	 */
	private function showArtikel($a)
	{
		$template = $this->template_factory->open('show_artikel');
		$template->set_attribute('zeit', $this->zeit);
		$template->set_attribute('a', $a);
		$template->set_attribute('anzahl', $this->getArtikelLookups($a->getArtikelId()));
		$template->set_attribute('pluginpfad', $this->getPluginpath());
		$template->set_attribute('pfeil', ($this->hasVisited($a->getArtikelId()) ? "forumgrau" : "forumrot"));
		$template->set_attribute('pfeil_runter', ($this->hasVisited($a->getArtikelId()) ? "forumgraurunt" : "forumrotrunt"));
		//benutzer und root extrafunktionen anzeigen
		if($a->getUserId() == $this->user->getuserid() || $this->permission->hasRootPermission())
		{
			$template->set_attribute('access', true);
			$template->set_attribute('link_delete', PluginEngine::getLink($this,array("modus"=>"delete_artikel", "thema_id"=>$a->getThemaId(), "artikel_id"=>$a->getArtikelId())));
			$template->set_attribute('link_edit', PluginEngine::getLink($this,array("modus"=>"show_add_artikel_form", "thema_id"=>$a->getThemaId(), "artikel_id"=>$a->getArtikelId())));
		}
		// oder einen antwortbutton
		if($a->getUserId() != $this->user->getuserid())
		{
			$template->set_attribute('antwort', true);
		}
		$template->set_attribute('link_search', PluginEngine::getLink($this,array("modus"=>"show_search_results")));
		$template->set_attribute('link_back', PluginEngine::getLink($this,array()));
		return $template->render();
	}

	/**
	 * Hauptfunktion, dient in diesem Plugin als Frontcontroller und steuert die Ausgaben
	 *
	 */
	public function show()
	{
		// Login prüfen
		if (!isset($_SESSION['auth']) or !is_object($_SESSION['auth']) or !isset($_SESSION['auth']->auth) or !isset($_SESSION['auth']->auth['uid']) or ($_SESSION['auth']->auth['uid']=='nobody'))
		{
			StudIPTemplateEngine::showErrorMessage('Sie müssen eingeloggt sein, um dieses Plugin aufrufen zu dürfen');
			return;
		}

		$modus = trim($_REQUEST['modus']);
		if ($modus)
		{
			// Nur Root-Funktionen
			if ($this->permission->hasRootPermission())
			{
				// Thema speichern
				if ($modus == "save_thema")
				{
					$t = new Thema($_REQUEST['thema_id']);
					$t->setTitel($_REQUEST['titel']);
					$t->setBeschreibung($_REQUEST['beschreibung']);
					$t->setPerm($_REQUEST['perm']);
					$t->setVisible(($_REQUEST['visible'] ? $_REQUEST['visible'] : 0));
					$t->save();
					StudIPTemplateEngine::showSuccessMessage("Das Thema wurde erfolgreich gespeichert.");
					unset($modus);
				}
				// Thema anlegen oder bearbeiten
				if ($modus == "show_add_thema_form")
				{
					$t = new Thema($_REQUEST['thema_id']);
					$template = $this->template_factory->open('edit_thema');
					$template->set_attribute('t', $t);
					$template->set_attribute('link', PluginEngine::getLink($this,array()));
					echo $template->render();
				}
				// Thema löschen Sicherheitsabfrage
				if ($modus == "delete_thema")
				{
					$t = new Thema($_REQUEST['thema_id']);
					$yes = '<a href="'.PluginEngine::getLink($this,array("modus"=>"delete_thema_really", "thema_id"=>$t->getThemaId())).'">'.makeButton("ja","img").'</a>';
					$no = '<a href="'.PluginEngine::getLink($this,array()).'">'.makeButton("nein","img").'</a>';
					StudIPTemplateEngine::showInfoMessage(sprintf("Soll das Thema <b>\"%s\"</b> wirklich gelöscht werden?<br/>%s %s",$t->getTitel(), $yes, $no));
					unset($modus);
				}
				//Thema löschen
				if ($modus == "delete_thema_really")
				{
					$t = new Thema($_REQUEST['thema_id']);
					$t->delete();
					StudIPTemplateEngine::showSuccessMessage("Das Thema wurde erfolgreich gelöscht.");
					unset($modus);
				}
			}
			//Anzeige speichern
			if ($modus == "add_artikel" && $this->permission->perm->have_perm($this->getThemaPermission($_REQUEST['thema_id'])))
			{
				if ((!$this->isDuplicate($_REQUEST['titel']) && empty($_REQUEST['artikel_id'])) || !empty($_REQUEST['artikel_id']))
				{
					$a = new Artikel($_REQUEST['artikel_id']);
					$a->setTitel($_REQUEST['titel']);
					$a->setBeschreibung($_REQUEST['beschreibung']);
					$a->setThemaId($_REQUEST['thema_id']);
					$a->setVisible(($_REQUEST['visible'] ? $_REQUEST['visible'] : 0));
					$a->save();
					StudIPTemplateEngine::showSuccessMessage("Die Anzeige wurde erfolgreich gespeichert.");
				}
				else
				{
					StudIPTemplateEngine::showErrorMessage("Sie haben nicht die erforderlichen Rechte eine Anzeige zu erstellen bzw. Sie haben bereits einen Artikel mit diesem Titel erstellt.");
				}
				unset($modus);
			}
			//Anzeige erstellen/bearbeiten
			if ($modus == "show_add_artikel_form")
			{
				$a = new Artikel($_REQUEST['artikel_id']);
				if (!$artikel_id)
				{
					$a->setThemaId($thema_id);
				}
				$template = $this->template_factory->open('edit_artikel');
				$template->set_attribute('thema_id', $_REQUEST['thema_id']);
				$template->set_attribute('themen', $this->getThemen());
				$template->set_attribute('a', $a);
				$template->set_attribute('zeit', $this->zeit);
				$template->set_attribute('link', PluginEngine::getLink($this,array()));
				$template->set_attribute('link_thema', PluginEngine::getLink($this,array("open"=>$thema_id)));
				echo $template->render();

			}
			//Anzeige löschen Sicherheitsabfrage
			if ($modus == "delete_artikel")
			{
				$a = new Artikel($_REQUEST['artikel_id']);
				if ($a->getUserId() == $this->user->getUserid() || $this->permission->hasRootPermission())
				{
					$autor_name = '<a href="about.php?username='.get_username($a->getUserId()).'">'.get_fullname($a->getUserId()).'</a>';
					$yes = '<a href="'.PluginEngine::getLink($this,array("modus"=>"delete_artikel_really", "artikel_id"=>$a->getArtikelId())).'">'.makeButton("ja","img").'</a>';
					$no = '<a href="'.PluginEngine::getLink($this,array()).'">'.makeButton("nein","img").'</a>';
					StudIPTemplateEngine::showInfoMessage(sprintf("Soll die Anzeige <b>\"%s\"</b> von %s wirklich gelöscht werden?<br/>%s %s",$a->getTitel(), $autor_name, $yes, $no));
				}
				else
				{
					StudIPTemplateEngine::showErrorMessage("Sie haben nicht die erforderlichen Rechte diese Anzeige zu löschen.");
				}
				unset($modus);
			}
			//Artikel löschen
			if ($modus == "delete_artikel_really")
			{
				$a = new Artikel($_REQUEST['artikel_id']);
				//Root löscht Artikel eines Benutzers, also diesen benachrichtigen.
				if ($a->getUserId() != $this->user->getUserid() && $this->permission->hasRootPermission())
				{
					$messaging=new messaging;
					$msg = sprintf("Die Anzeige \"%s\" wurde von der Administration gelöscht.",$a->getTitel());
					$messaging->insert_message($msg, get_username($a->getUserId()), "____%system%____", FALSE, FALSE, 1, FALSE, "Anzeige gelöscht!");
				}
				$a->delete();
				StudIPTemplateEngine::showSuccessMessage("Die Anzeige wurde erfolgreich gelöscht.");
				unset($modus);
			}
			//Suchergebnisse abfragen und anzeigen, falls vorhanden
			if ($modus == "show_search_results")
			{
				$this->search(trim($_REQUEST['search_text']));
			}
		}
		// Standardansicht, wenn kein modus ausgewählt ist.
		if(!$modus)
		{
			$this->showThemen();
		}

	}

	/**
	 * Kompatiblität zu Pluginschnittstellenänderungen
	 * Ruft die show(); auf.
	 *
	 * @return show();
	 */
	public function actionShow()
	{
		return $this->show();
	}

}
