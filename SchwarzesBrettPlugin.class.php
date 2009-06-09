<?php
/**
 * SchwarzesBrettPlugin.class.php
 *
 * Plugin zum Verwalten von Schwarzen Brettern (Angebote und Gesuche)
 *
 * Diese Datei enthält die Hauptklasse des Plugins
 *
 * PHP version 5
 *
 * @author		Jan Kulmann <jankul@zmml.uni-bremen.de>
 * @author		Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @package 	IBIT_SchwarzesBrettPlugin
 * @copyright 	2008-2009 IBIT und ZMML
 * @license 	http://www.gnu.org/licenses/gpl.html GPL Licence 3
 * @version 	1.6.1
 */

// IMPORTS
require_once 'bootstrap.inc.php';

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

	public $announcements;

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
	
	const THEMEN_CACHE_KEY = 'plugins/SchwarzesBrettPlugin/themen';

	/**
	 * Konstruktor, erzeugt das Plugin.
	 *
	 */
	function __construct()
	{
		parent::AbstractStudIPSystemPlugin();
		$this->setPluginIcon();

		$this->buildMenu();
		$this->template_factory = new Flexi_TemplateFactory(dirname(__FILE__).'/templates');
		$this->user = $this->getUser();
		$this->permission = $this->user->getPermission();

		// Holt die Laufzeit aus der Config. Default: 30Tage
		$this->zeit = get_config('BULLETIN_BOARD_DURATION') * 24 * 60 * 60;
		// Holt Anzahl anzuzeigende neuste Anzeigen. Default: 20
		$this->announcements = get_config('BULLETIN_BOARD_ANNOUNCEMENTS');

		$path = $GLOBALS['ABSOLUTE_URI_STUDIP'].str_replace($GLOBALS['ABSOLUTE_PATH_STUDIP'], '', dirname(__FILE__));
		$GLOBALS['_include_additional_header'] .= '<script src="'.$path.'/js/schwarzesbrett.js" type="text/javascript"></script>'."\n";

	}

	/**
	 * Setzt den Pfad zum Plugin.
	 * @param string $path Pfad zum Plugin
	 */
	public function setPluginpath($path)
	{
		parent::setPluginpath($path);
	}

	/**
	 * Erstellt das Menü des Plugins.
	 * @uses PluginNavigation
	 */
	public function buildMenu()
	{
		$tab = new PluginNavigation();
		$tab->setDisplayname(_('Schwarzes Brett'));
		$this->setNavigation($tab);
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
	 * Zeigt das Icon im header an (rot, wenn es neue gibt.
	 *
	 */
	private function setPluginIcon()
	{
		$this->setPluginiconname('images/paste_plain.png');
		
		/*$last_visitdate = DBManager::get()->query("SELECT MAX(last_visitdate) FROM sb_visits WHERE user_id='{$GLOBALS['auth']->auth['uid']}'")->fetch(PDO::FETCH_COLUMN);
		$last_artikel = DBManager::get()->query("SELECT count(*) FROM sb_artikel WHERE mkdate > '{$last_visitdate}' AND visible = 1")->fetch(PDO::FETCH_COLUMN);
		if ($last_artikel > 0)
		{
			#$this->setPluginiconname('images/paste_plain.png');
			$this->setPluginiconname('images/header_pinn2.gif');
		}
		else
		{
			#$this->setPluginiconname('images/paste_plain.png');
			$this->setPluginiconname('images/header_pinn1.gif');
		}*/
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
		$artikel_ids = DBManager::get()->query("SELECT artikel_id FROM sb_artikel WHERE thema_id='{$thema_id}' AND UNIX_TIMESTAMP() < (mkdate + {$this->zeit}) AND (visible=1 OR (visible=0 AND (user_id='{$this->user->getUserid()}' OR 'root'='{$this->permission->perm->get_perm($this->user->getUserid())}'))) ORDER BY mkdate DESC")->fetchAll(PDO::FETCH_COLUMN);
		foreach ($artikel_ids as $artikel_id)
		{
			$ret[] = new Artikel($artikel_id);
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
		return DBManager::get()->query("SELECT COUNT(*) FROM sb_visits WHERE type='artikel' AND object_id='{$artikel_id}'")->fetch(PDO::FETCH_COLUMN);
	}

	/**
	 * Gibt eine Liste aller Themen aus der Datenbank zurück, die sichtbar sind
	 * oder in denen der Benutzer bereits einen Artikel erstellt hat.
	 * Themen werden gechached
	 *
	 * @uses StudipCacheFactory
	 * @return array Liste aller Themen
	 */
	private function getThemen()
	{
		$cache = StudipCacheFactory::getCache();  	
		$ret = unserialize($cache->read(self::THEMEN_CACHE_KEY));
		
		if(empty($ret))
		{
			$themen = DBManager::get()->query("SELECT t.thema_id, COUNT(a.artikel_id) count_artikel FROM sb_themen t LEFT JOIN sb_artikel a USING (thema_id) WHERE t.visible=1 OR t.user_id='{$this->user->getUserid()}' OR 'perm'='{$this->permission->perm->get_perm($this->user->getUserid())}' GROUP BY t.thema_id ORDER BY t.titel")->fetchAll(PDO::FETCH_ASSOC);
			$ret = array();
			foreach ($themen as $thema)
			{
				$t = new Thema($thema['thema_id']);
				$t->setArtikelCount($thema['count_artikel']);
				array_push($ret, $t);
			}			
			$cache->write(self::THEMEN_CACHE_KEY, serialize($ret));
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
		return DBManager::get()->query("SELECT perm FROM sb_themen WHERE thema_id='{$thema_id}'")->fetch(PDO::FETCH_COLUMN);
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
		if (count(DBManager::get()->query("SELECT artikel_id FROM sb_artikel WHERE user_id='{$this->user->getUserid()}' AND titel='{$titel}' AND mkdate>(UNIX_TIMESTAMP()-(60*60*24))")->fetchAll(PDO::FETCH_ASSOC)) > 0)
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
		$last_visitdate = DBManager::get()->query("SELECT last_visitdate FROM sb_visits WHERE object_id='{$obj_id}' AND user_id='{$this->user->getUserid()}'")->fetch(PDO::FETCH_COLUMN);
		if (!empty($last_visitdate))
		{
			return $last_visitdate;
		}
		else
		{
			return false;
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
		$sql = sprintf("SELECT a.thema_id, a.artikel_id, a.titel, t.titel t_titel FROM sb_artikel AS a, sb_themen AS t WHERE
				t.thema_id=a.thema_id AND (UPPER(a.titel) LIKE '%s' OR UPPER(a.beschreibung) LIKE '%s') AND UNIX_TIMESTAMP() < (a.mkdate + %d)
				AND (a.visible=1 OR (a.visible=0 AND (a.user_id='%s' OR 'root'='%s'))) ORDER BY t.titel, a.titel
			","%".strtoupper($search_text)."%","%".strtoupper($search_text)."%",$this->zeit,$this->user->getUserid(),$this->permission->perm->get_perm($this->user->getUserid()));
		$results = DBManager::get()->query($sql)->fetchAll(PDO::FETCH_ASSOC);

		// keine Ergebnisse vorhanden
		if(count($results) == 0)
		{
			StudIPTemplateEngine::showErrorMessage("Es wurden für <em>{$search_text}</em> keine Ergebnisse gefunden.");
			$this->showThemen();
		}
		//Ergebnisse anzeigen
		else
		{
			$results = array();
			$thema = array();
			foreach ($results as $result)
			{
				$a = new Artikel($result['artikel_id']);
				if(empty($thema['thema_id']))
				{
					$thema['thema_id'] = $result['thema_id'];
					$thema['thema_titel'] = htmlReady($result['t_titel']);
					$thema['artikel'] = array();

				}
				elseif($result['thema_id'] != $thema['thema_id'])
				{
					array_push($results, $thema);
					unset($thema);
					$thema['thema_id'] = $result['thema_id'];
					$thema['thema_titel'] = htmlReady($result['t_titel']);
					$thema['artikel'] = array();
				}
				else
				{

				}
				array_push($thema['artikel'], $this->showArtikel($a));
			}
			array_push($results, $thema);

			//Ausgabe erzeugen
			$template = $this->template_factory->open('search_results');

			//Adminfunktionen anzeigen
			if ($this->permission->hasRootPermission())
			{
				$template->set_attribute('rootlink', PluginEngine::getLink($this,array("modus"=>"show_add_thema_form")));
				$template->set_attribute('rootaccess', TRUE);
			}

			$template->set_attribute('zeit', $this->zeit);
			$template->set_attribute('pluginpfad', $this->getPluginURL());
			$template->set_attribute('link_search', PluginEngine::getLink($this,array("modus"=>"show_search_results")));
			$template->set_attribute('link_back', PluginEngine::getLink($this,array()));
			$template->set_attribute('results', $results);
			echo $template->render();
		}
	}

	/**
	 * Zeigt alle Themen und Anzeigen an
	 *
	 */
	private function showThemen()
	{
		$themen = $this->getThemen();

		$template = $this->template_factory->open('show_themen');
		$template->set_attribute('zeit', $this->zeit);
		$template->set_attribute('pluginpfad', $this->getPluginURL());
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

			$newOnes = $this->getLastArtikel();
			if (count($newOnes) > 0)
			{
				foreach($newOnes as $a)
				{
					$lastArtikel[] = $this->showArtikel($a, 'show_lastartikel');
				}
				$template->set_attribute('lastArtikel', $lastArtikel);
			}
		}
		//Adminfunktionen anzeigen
		if ($this->permission->hasRootPermission())
		{
			$template->set_attribute('rootlinknew', PluginEngine::getLink($this,array("modus"=>"show_add_thema_form")));
			$template->set_attribute('rootlinkdelete', PluginEngine::getLink($this,array(), 'deleteOldArtikel'));
			$template->set_attribute('rootaccess', true);
		}
		echo $template->render();
	}

	/**
	 * Zeigt eine Anzeige an
	 *
	 * @param Object $a eine Anzeige
	 */
	private function showArtikel($a, $template='show_artikel')
	{
		$template = $this->template_factory->open($template);
		$template->set_attribute('zeit', $this->zeit);
		$template->set_attribute('a', $a);
		$template->set_attribute('anzahl', $this->getArtikelLookups($a->getArtikelId()));
		$template->set_attribute('pluginpfad', $this->getPluginURL());
		$template->set_attribute('pfeil', ($this->hasVisited($a->getArtikelId()) ? "forumgrau" : "forumrot"));
		$template->set_attribute('pfeil_runter', "forumgraurunt");
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
		$template->set_attribute('link_ajax', $this->getPluginURL().'/ajaxDispatcher.php');
		return $template->render();
	}

	/**
	 * Holt die 20 (default) aktuellsten Artikel aus der Datenbank
	 * Die Anzahl der Artikel wird in der globalen Konfiguration festgelegt
	 *
	 * @return array() Artikel
	 */
	private function getLastArtikel()
	{
		$result = DBManager::get()->query("SELECT artikel_id FROM sb_artikel WHERE UNIX_TIMESTAMP() < (mkdate + {$this->zeit}) AND visible=1 ORDER BY mkdate DESC LIMIT {$this->announcements}")->fetchAll(PDO::FETCH_COLUMN);
		foreach ($result as $artikel_id)
		{
			$ret[] = new Artikel($artikel_id);
		}
		return $ret;
	}

	private function editArtikel()
	{
		$a = new Artikel($_REQUEST['artikel_id']);
		if (!$_REQUEST['artikel_id'])
		{
			$a->setThemaId($_REQUEST['thema_id']);
		}
		$template = $this->template_factory->open('edit_artikel');
		$template->set_attribute('thema_id', $_REQUEST['thema_id']);
		$template->set_attribute('themen', $this->getThemen());
		$template->set_attribute('a', $a);
		$template->set_attribute('zeit', $this->zeit);
		$template->set_attribute('link', PluginEngine::getLink($this,array()));
		$template->set_attribute('link_thema', PluginEngine::getLink($this,array("open"=>$_REQUEST['thema_id'])));
		echo $template->render();
	}

	private function editThema()
	{
		$t = new Thema($_REQUEST['thema_id']);
		$template = $this->template_factory->open('edit_thema');
		$template->set_attribute('t', $t);
		$template->set_attribute('link', PluginEngine::getLink($this,array()));
		echo $template->render();
	}

	/**
	 * Hauptfunktion, dient in diesem Plugin als Frontcontroller und steuert die Ausgaben
	 *
	 */
	public function actionshow()
	{
		$modus = trim($_REQUEST['modus']);
		if ($modus)
		{
			// Nur Root-Funktionen
			if ($this->permission->hasRootPermission())
			{
				// Thema speichern
				if ($modus == "save_thema")
				{
					if (!empty($_REQUEST['titel']))
					{
						$t = new Thema($_REQUEST['thema_id']);
						$t->setTitel($_REQUEST['titel']);
						$t->setBeschreibung($_REQUEST['beschreibung']);
						$t->setPerm($_REQUEST['thema_perm']);
						$t->setVisible(($_REQUEST['visible'] ? $_REQUEST['visible'] : 0));
						$t->save();
						StudIPTemplateEngine::showSuccessMessage("Das Thema wurde erfolgreich gespeichert.");
						unset($modus);
					}
					else
					{
						StudIPTemplateEngine::showErrorMessage("Fehler! Bitte geben Sie einen Titel ein.");
						$this->editThema();
						exit;
					}
				}
				// Thema anlegen oder bearbeiten
				if ($modus == "show_add_thema_form")
				{
					$this->editThema();
				}
				// Thema löschen Sicherheitsabfrage
				if ($modus == "delete_thema")
				{
					$t = new Thema($_REQUEST['thema_id']);
					#echo createQuestion('Soll das Thema **'.$t->getTitel().'** wirklich gelöscht werden?', array("modus"=>"delete_thema_really", "thema_id"=>$t->getThemaId()));

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
					if (!empty($_REQUEST['titel']) && !empty($_REQUEST['beschreibung']))
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
						StudIPTemplateEngine::showErrorMessage("Fehler! Bitte geben Sie einen Titel und eine Beschreibung an.");
						$this->editArtikel();
						exit;
					}
				}
				elseif($this->isDuplicate($_REQUEST['titel']))
				{
					StudIPTemplateEngine::showErrorMessage("Sie haben bereits einen Artikel mit diesem Titel erstellt. Bitte beachten Sie die Nutzungshinweise!");

				}
				else
				{
					StudIPTemplateEngine::showErrorMessage("Sie haben nicht die erforderlichen Rechte eine Anzeige zu erstellen.");
				}
				unset($modus);
			}
			//Anzeige erstellen/bearbeiten
			if ($modus == "show_add_artikel_form")
			{
				$this->editArtikel();
			}
			//Anzeige löschen Sicherheitsabfrage
			if ($modus == "delete_artikel")
			{
				$a = new Artikel($_REQUEST['artikel_id']);
				if ($a->getUserId() == $this->user->getUserid() || $this->permission->hasRootPermission())
				{
					#echo createQuestion('Soll die Anzeige **'.$a->getTitel().'** von %%'.get_fullname($a->getUserId()).'%% wirklich gelöscht werden?', array("modus"=>"delete_artikel_really", "artikel_id"=>$a->getArtikelId()));
										
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
	 * Root kann mit dieser Funktion alle veralteten Artikel aus der DB löschen
	 *
	 */
	public function actiondeleteOldArtikel()
	{
		//TODO: pdo
		if ($this->permission->hasRootPermission())
		{
			$artikel = DBManager::get()->query("SELECT artikel_id FROM sb_artikel WHERE UNIX_TIMESTAMP() > (mkdate + {$this->zeit})")->fetchAll(PDO::FETCH_COLUMN);
			foreach ($artikel as $id)
			{
				$a = new Artikel($id);
				$a->delete();
			}
			StudIPTemplateEngine::showSuccessMessage("Es wurden erfolgreich <b>".count($artikel)."</b> Artikel aus der Datenbank gelöscht.");
		}
		else
		{
			StudIPTemplateEngine::showErrorMessage("Sie haben nicht die Berechtigung Artikel zu löschen.");
		}
		$this->showThemen();
	}
}
