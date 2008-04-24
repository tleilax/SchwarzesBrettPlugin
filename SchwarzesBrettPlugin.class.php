<?php
/**
* SchwarzesBrettPlugin.class.php
*
* Plugin zum Verwalten von Schwarzen Brettern (Angebote und Gesuche)
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
	var $zeit;

	/**
	 * Objekt der Templateklasse
	 *
	 * @var unknown_type
	 */
	private $template_factory;

	/**
	 * Konstruktor, erzeugt das Plugin.
	 *
	 */
	function __construct()
	{
		parent::AbstractStudIPSystemPlugin();
		$this->template_factory = new Flexi_TemplateFactory(dirname(__FILE__).'/templates');

		//plugin-icon
		$this->setPluginiconname('images/script.png');
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
	 * Überprüft, ob eine Anzeige bereits vorhanden ist
	 *
	 * @param string $titel
	 * @return boolean
	 */
	function is_duplicate($titel)
	{
		$db = new DB_Seminar();
		$db->queryf("SELECT artikel_id FROM sb_artikel WHERE user_id='%s' AND titel='%s' AND mkdate>(UNIX_TIMESTAMP()-60*60*24)",$GLOBALS['auth']->auth['uid'],$titel);
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
	 * Gibt alle Anzeigen zu einem Thema zurück
	 *
	 * @param string $thema_id
	 * @return array Anzeigen
	 */
	function get_artikel($thema_id)
	{
		$ret = array();
		$db = new DB_Seminar();
		$db->queryf("SELECT * FROM sb_artikel WHERE thema_id='%s' AND UNIX_TIMESTAMP() < (mkdate + %d) AND (visible=1 OR (visible=0 AND (user_id='%s' OR 'root'='%s'))) ORDER BY mkdate DESC",$thema_id,$this->zeit,$GLOBALS['auth']->auth["uid"],$GLOBALS['auth']->auth["perm"]);
		while ($db->next_record())
		{
			$a = new Artikel($db->f("artikel_id"));
			array_push($ret, $a);
		}
		return $ret;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $thema_id
	 * @return unknown
	 */
	function get_artikel_count_visible($thema_id)
	{
		$db = new DB_Seminar();
		$db->queryf("SELECT * FROM sb_artikel WHERE thema_id='%s' AND UNIX_TIMESTAMP() < (mkdate + %d) AND (visible=1 OR (visible=0 AND (user_id='%s' OR 'root'='%s'))) ORDER BY mkdate DESC",$thema_id,$this->zeit,$GLOBALS['auth']->auth["uid"],$GLOBALS['auth']->auth["perm"]);
		return $db->num_rows();
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $artikel_id
	 * @return unknown
	 */
	function get_artikel_lookups($artikel_id)
	{
		$db = new DB_Seminar();
		$db->queryf("SELECT * FROM sb_visits WHERE type='artikel' AND object_id='%s'",$artikel_id);
		return $db->num_rows();
	}

	/**
	 * Gibt eine Liste aller Themen aus der Datenbank zurück
	 *
	 * @return array Liste aller Themen
	 */
	private function get_themen()
	{
		$ret = array();
		$db = new DB_Seminar();
		$db->queryf("SELECT t.*, COUNT(a.artikel_id) count_artikel FROM sb_themen t LEFT JOIN sb_artikel a USING (thema_id) WHERE t.visible=1 OR (t.visible=0 AND (t.user_id='%s' OR 'root'='%s')) GROUP BY t.thema_id ORDER BY count_artikel DESC",$GLOBALS['auth']->auth["uid"],$GLOBALS['auth']->auth["perm"]);
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
	private function get_thema_perm($thema_id)
	{
		$db = new DB_Seminar();
		$db->queryf("SELECT perm FROM sb_themen WHERE thema_id='%s'",$thema_id);
		$db->next_record();
		return $db->f("perm");
	}

	/**
	 * Gibt die Anzahl aller Anzeigen zu einem Thema zurück
	 *
	 * @param string $thema_id
	 * @return int Anzahl aller Anzeigen zu einem Thema
	 */
	function get_artikel_count($thema_id)
	{
		$db = new DB_Seminar();
		$db->queryf("SELECT * FROM sb_artikel WHERE thema_id='%s'",$thema_id);
		return $db->num_rows();
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $obj_id
	 * @param unknown_type $type
	 */
	function visit($obj_id, $type)
	{
		$db = new DB_Seminar();
		$db->queryf("REPLACE INTO sb_visits SET object_id='%s', user_id='%s', type='%s', last_visitdate=UNIX_TIMESTAMP()",$obj_id,$GLOBALS['auth']->auth['uid'],$type);
	}

	/**
	 * Enter description here...
	 *
	 * @param string $obj_id
	 * @return datetime oder boolean
	 */
	function has_visited($obj_id)
	{
		$db = new DB_Seminar();
		$db->queryf("SELECT last_visitdate FROM sb_visits WHERE object_id='%s' AND user_id='%s'",$obj_id,$GLOBALS['auth']->auth['uid']);
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
	 * Gibt die Anzahl aller Anzeigen zurück
	 *
	 * @return int Anzahl aller Anzeigen
	 */
	function num_all_postings()
	{
		$db = new DB_Seminar();
		$db->query("SELECT * FROM sb_artikel");
		return $db->num_rows();
	}

	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	function new_items_since_last_visit()
	{
		$db = new DB_Seminar();
		$db->queryf("SELECT last_visitdate FROM sb_visits WHERE user_id='%s' AND object_id='root'",$GLOBALS['auth']->auth['uid']);
		if ($db->next_record())
		{
			$lv = $db->f("last_visitdate");
			$db->queryf("SELECT a.* FROM sb_artikel a, sb_themen t WHERE a.visible=1 AND t.thema_id=a.thema_id AND t.visible=1 AND a.mkdate>%d AND a.user_id!='%s' AND NOT EXISTS (SELECT object_id FROM sb_visits WHERE object_id=a.artikel_id AND type='artikel' AND user_id='%s')",$lv,$GLOBALS['auth']->auth['uid'],$GLOBALS['auth']->auth['uid']);
			return $db->num_rows();
		}
		return $this->num_all_postings();
	}

	/**
	 * Fügt ein Javascript für Ajax-Funktionen ein
	 *
	 */
	private function print_js_opener()
	{
		?>
		<script type="text/javascript" language="javascript">
		var show_content = new Array();
		function f(id, p, pr) {
			var pfeil = p;
			var pfeil_runter = pr;
			if (!show_content[id]) {
				new Ajax.Request('<?=$GLOBALS['ABSOLUTE_URI_STUDIP'].$this->getPluginpath()?>/ajaxDispatcher.php?ajax_cmd=visitObj&objid='+id, {
		                	method: 'post',
					onSuccess: function(transport) {
						pfeil = 'forumgrau';
						pfeil_runter = 'forumgraurunt';
		                	},
		       	        	onFailure: function(t) {alert('Error ' + t.status + ' -- ' + t.statusText); },
		               		on404: function(t) {alert('Error 404: location "' + t.statusText + '" was not found.'); }
		        	});
				$('content'+id).style.display='block';
				show_content[id]=true;
				$('indikator'+id).src='<?=$GLOBALS['ASSETS_URL']."/images/"?>'+pfeil_runter+'.gif';
				$('a'+id).style.fontWeight='bold';

			} else {
				$('content'+id).style.display='none';
				show_content[id]=false;
				$('indikator'+id).src='<?=$GLOBALS['ASSETS_URL']."/images/"?>'+pfeil+'.gif';
				$('a'+id).style.fontWeight='normal';
			}
		}
		</script>
		<?
	}

	/**
	 * Führt die Suche nach Anzeigen durch und zeigt die Ergebnisse an.
	 *
	 * @param String $search_text Suchwort
	 */
	private function search($search_text)
	{
		//Benutzereingaben abfangen (Wörter kürzer als 3 Zeichen)
		if(empty($search_text) || strlen($search_text) < 4)
		{
			StudIPTemplateEngine::showErrorMessage("Ihr Suchwort ist zu kurz, bitte versuchen Sie es erneut!");
			$this->show_themen();
			die;
		}

		//Datenbankabfrage
		$db = new DB_Seminar();
		$sql = sprintf("SELECT a.thema_id, a.artikel_id, a.titel, t.titel t_titel FROM sb_artikel AS a, sb_themen AS t WHERE
				t.thema_id=a.thema_id AND (UPPER(a.titel) LIKE '%s' OR UPPER(a.beschreibung) LIKE '%s') AND UNIX_TIMESTAMP() < (a.mkdate + %d)
				AND (a.visible=1 OR (a.visible=0 AND (a.user_id='%s' OR 'root'='%s'))) ORDER BY t.titel, a.titel
			","%".strtoupper($search_text)."%","%".strtoupper($search_text)."%",$this->zeit,$GLOBALS['auth']->auth["uid"],$GLOBALS['auth']->auth["perm"]);
		$db->query($sql);

		// keine Ergebnisse vorhanden
		if($db->num_rows()==0)
		{
			StudIPTemplateEngine::showErrorMessage("Es wurden keine Ergebnisse gefunden. Bitte versuchen Sie es mit einem anderen Wort erneut.");
			$this->show_themen();
		}
		//Ergebnisse anzeigen
		else
		{
			$a_open = trim($_REQUEST['a_open']);

			$pfeil = ($this->has_visited($a->getArtikelId()) ? "forumgrau" : "forumrot");
			$pfeil_runter = ($this->has_visited($a->getArtikelId()) ? "forumgraurunt" : "forumrotrunt");

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
				array_push($thema['artikel'], $a);
			}
			array_push($results, $thema);

			//Ausgabe erzeugen
			$this->print_js_opener();
			$template = $this->template_factory->open('search_results');
			$template->set_attribute('zeit', $this->zeit);
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
	private function show_themen()
	{
		$open = trim($_REQUEST['open']);
		$a_open = trim($_REQUEST['a_open']);

		$themen = $this->get_themen();

		$this->print_js_opener();
		$template = $this->template_factory->open('show_themen');
		$template->set_attribute('zeit', $this->zeit);
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
			//TODO: überarbeiten:
			$tmp_count = array(0=>0, 1=>0, 2=>0);
			$tmp_themen = array(0=>array(), 1=>array(), 2=>array());
			foreach ($themen as $tt)
			{
				$anzahl = $tt->getArtikelCount();
				foreach ($tmp_count as $c_key=>$c_val)
				{
					$tmp_count[$c_key] += $anzahl;
					array_push($tmp_themen[$c_key], $tt);
					break;
				}
				asort($tmp_count,SORT_NUMERIC);
			}
			asort($tmp_count,SORT_NUMERIC);

			//3 Spalten
			if(count($themen)%3 == 0 || count($themen) > 6)
			{
				echo "3 Spalten";
			}
			// 2 Spalten
			elseif(count($themen)%2 == 0)
			{
				echo "2 Spalten";
			}
			else
			{
				echo "eine Spalte";
			}

			//TODO: Template...
			echo "<TABLE BORDER=\"0\" STYLE=\"width:100%; text-align:center;\">\n";
			echo "  <TR>\n";
			foreach ($tmp_themen as $tt)
			{
				echo "  <TD VALIGN=\"TOP\" STYLE=\"width:33%; max-width:33%; min-width:33%;\">\n";
				foreach ($tt as $t) {
					echo "<DIV STYLE=\"".($this->user_agent['PPC'] ? "display:block; ":"")."width:94%; max-width:94%; min-width:94%; background-color:#FBFBF5; border:1px solid #808080; padding:10px 10px 10px 10px; text-align:left; margin:5px; float:left;\">\n";
					echo "    <DIV STYLE=\"float:left; font-weight:bold;\">".htmlReady($t->getTitel())."</DIV>\n";
					if ($GLOBALS['perm']->have_perm("root")) {
						echo "    <DIV STYLE=\"float:right;\">\n";
						if ($t->getVisible() == 0) echo "<IMG SRC=\"".$this->getPluginpath()."/images/exclamation.png\" ALT=\"".dgettext('sb',"nicht sichtbar")."\" TITLE=\"".dgettext('sb',"nicht sichtbar")."\">\n";
						echo "      <A HREF=\"".PluginEngine::getLink($this,array("modus"=>"show_add_thema_form","thema_id"=>$t->getThemaId()))."\"><IMG SRC=\"".$this->getPluginpath()."/images/table_edit.png\" BORDER=\"0\" ALT=\"".dgettext('sb',"bearbeiten")."\" TITLE=\"".dgettext('sb',"bearbeiten")."\"></A>\n";
						echo "      <A HREF=\"".PluginEngine::getLink($this,array("modus"=>"delete_thema","thema_id"=>$t->getThemaId()))."\" onClick=\"return confirm('".dgettext('sb',"Soll das Thema mit allen Einträgen wirklich gelöscht werden?")."');\"><IMG SRC=\"".$this->getPluginpath()."/images/cross.png\" BORDER=\"0\" ALT=\"".dgettext('sb',"löschen")."\" TITLE=\"".dgettext('sb',"löschen")."\"></A>\n";
						echo "    </DIV>\n";
					}
					echo "  <DIV STYLE=\"clear:both;\"></DIV>\n";
					echo "  <DIV STYLE=\"border-bottom:1px solid #808080; margin-bottom:10px;\">\n";
					echo "    </DIV>\n";
					if ($t->getBeschreibung())
						echo "  <DIV STYLE=\"font-size:x-small;\">".htmlReady($t->getBeschreibung())."</DIV>\n";
					$artikel = $this->get_artikel($t->getThemaId());
					if (count($artikel) == 0)
					{
						echo "<DIV STYLE=\"font-size:smaller; margin-bottom:10px; padding-left:5px;\">".dgettext('sb',"Zur Zeit sind keine Anzeigen vorhanden!")."</DIV>\n";
					}
					else
					{
						foreach ($artikel as $a)
						{
							$this->show_artikel($a);
						}
					}
					if ($GLOBALS['perm']->have_perm($t->getPerm()))
					{
						echo "<DIV STYLE=\"clear:both;\"></DIV><DIV STYLE=\"margin-top:10px; text-align:center;\"><A STYLE=\"font-size:smaller;\" HREF=\"".PluginEngine::getLink($this,array("modus"=>"show_add_artikel_form", "thema_id"=>$thema_id))."\">".dgettext('sb',"Neue Anzeige anlegen")."</A></DIV>\n";
					}
					echo "</DIV>\n";
				}
				echo "  </TD>\n";
			}
			echo "  </TR>\n";
			echo "</TABLE>\n";
		}
		//Adminfunktionen anzeigen
		if ($GLOBALS['perm']->have_perm("root"))
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
	function show_artikel($a)
	{

		$a_open = trim($_REQUEST['a_open']);
		$t = new Thema($a->getThemaId());

		$pfeil = ($this->has_visited($a->getArtikelId()) ? "forumgrau" : "forumrot");
		$pfeil_runter = ($this->has_visited($a->getArtikelId()) ? "forumgraurunt" : "forumrotrunt");

?>
<script type="text/javascript" language="javascript">
show_content['<?=$a->getArtikelId()?>'] = false;
</script>
<?


		echo "<DIV STYLE=\"font-size:smaller; padding-left:5px;\">\n";
		echo "  <DIV>\n";
		echo "    <DIV STYLE=\"float:left; width:80%; max-width:80%;\">\n";
		echo "      <A ID=\"a".$a->getArtikelId()."\" STYLE=\"font-weight:normal;\" \n";
		echo "        HREF=\"".PluginEngine::getLink($this,array("a_open"=>($a_open==$a->getArtikelId()?"":$a->getArtikelId())))."\" ";
		if ($this->user_agent['PC']) echo "        onClick=\"f('".$a->getArtikelId()."','".$pfeil."','".$pfeil_runter."'); return false;\"";
		echo ">\n";
		echo "      <IMG ID=\"indikator".$a->getArtikelId()."\" SRC=\"".$GLOBALS['ASSETS_URL']."/images/".($a_open==$a->getArtikelId()?$pfeil_runter:$pfeil).".gif\" BORDER=\"0\">\n";
		echo htmlReady($a->getTitel())."</A> <SPAN STYLE=\"font-size:smaller;\">[".$this->get_artikel_lookups($a->getArtikelId())."]</SPAN>\n";
		if ($a->getVisible() == 0) echo "<IMG SRC=\"".$this->getPluginpath()."/images/exclamation.png\" ALT=\"".dgettext('sb',"nicht sichtbar")."\" TITLE=\"".dgettext('sb',"nicht sichtbar")."\">\n";
		echo "    </DIV>\n";
		echo "    <DIV STYLE=\"float:right; font-size:smaller; width:18%; max-width:18%; padding-top:5px;\">".date("d.m.Y",$a->getMkdate())."</DIV>\n";
		echo "  </DIV>\n";
		if ($this->user_agent['PC'] || trim($_REQUEST['a_open'])==$a->getArtikelId())
		{
			echo "  <DIV STYLE=\"clear:both;\"></DIV>\n";
			echo "  <DIV ID=\"content".$a->getArtikelId()."\" STYLE=\"display:".($a_open==$a->getArtikelId()?"block":"none").";\">\n";
			echo "    <DIV STYLE=\"float:left; font-size:smaller; padding-bottom:10px; padding-top:10px;\">".dgettext('sb',"von")." <A HREF=\"about.php?username=".get_username($a->getUserId())."\">".get_fullname($a->getUserId())."</A></DIV>";
			echo "    <DIV STYLE=\"float:right; font-size:smaller; width:20%; max-width:20%; padding-top:5px; color:red;\">".dgettext('sb',"bis")." ".date("d.m.Y",$a->getMkdate()+$this->zeit)."</DIV>";
			echo "    <DIV STYLE=\"clear:both;\"></DIV>\n";
			echo "    <DIV>".formatReady($a->getBeschreibung())."</DIV>\n";
			echo "    <DIV STYLE=\"text-align:center; margin;10px; padding:10px;\">\n";
			if ($a->getUserId() != $GLOBALS['auth']->auth['uid'])
				echo "      <A HREF=\"sms_send.php?rec_uname=".get_username($a->getUserId())."&messagesubject=".rawurlencode($a->getTitel())."&message=".rawurlencode('[quote] '.$a->getBeschreibung().' [/quote]')."\">".makeButton("antworten","img")."</A>\n";
			if ($a->getUserId() == $GLOBALS['auth']->auth['uid'] || $GLOBALS['perm']->have_perm("root")) {
				echo "      <A HREF=\"".PluginEngine::getLink($this,array("modus"=>"show_add_artikel_form", "thema_id"=>$t->getThemaId(), "artikel_id"=>$a->getArtikelId()))."\">".makeButton("bearbeiten","img")."</A>\n";
				echo "      <A HREF=\"".PluginEngine::getLink($this,array("modus"=>"delete_artikel", "thema_id"=>$t->getThemaId(), "artikel_id"=>$a->getArtikelId()))."\">".makeButton("loeschen","img")."</A>\n";
			}
			echo "    </DIV>\n";
			echo "  </DIV>\n";
		}

		echo "</DIV>\n";
	}

	/**
	 * Hauptfunktion, dient in diesem Plugin als Frontcontroller und steuert die Ausgaben
	 *
	 */
	public function show()
	{
		$db = new DB_Seminar();
		$open = trim($_REQUEST['open']);
		$a_open = trim($_REQUEST['a_open']);
		if ($a_open) $this->visit($a_open,"artikel");

		$this->visit("root","thema");
		$modus = trim($_REQUEST['modus']);

		if ($modus)
		{
			// Nur Root-Funktionen
			if ($GLOBALS['perm']->have_perm("root"))
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
			if ($modus == "add_artikel" && $GLOBALS['perm']->have_perm($this->get_thema_perm($open)))
			{
				if ((!$this->is_duplicate($_REQUEST['titel']) && !isset($_REQUEST['artikel_id'])) || isset($_REQUEST['artikel_id']))
				{
					$a = new Artikel($_REQUEST['artikel_id']);
					$a->setTitel($_REQUEST['titel']);
					$a->setBeschreibung($_REQUEST['beschreibung']);
					$a->setThemaId($open);
					$a->setVisible(($_REQUEST['visible'] ? $_REQUEST['visible'] : 0));
					$a->save();
					StudIPTemplateEngine::showSuccessMessage("Die Anzeige wurde erfolgreich gespeichert.");
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
				$t = new Thema($_REQUEST['thema_id']);
				$template = $this->template_factory->open('edit_artikel');
				$template->set_attribute('t', $t);
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
				if ($a->getUserId() == $GLOBALS['auth']->auth['uid'] || $GLOBALS['perm']->have_perm("root"))
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
			if ($modus == "delete_artikel_really")
			{
				$a = new Artikel($_REQUEST['artikel_id']);
				if ($a->getUserId() == $GLOBALS['auth']->auth['uid'] || $GLOBALS['perm']->have_perm("root"))
				{
					if ($a->getUserId() != $GLOBALS['auth']->auth['uid'] && $GLOBALS['perm']->have_perm("root"))
					{
						$messaging=new messaging;
                        $msg = sprintf(dgettext('sb',"Die Anzeige \"%s\" wurde von der Administration geloescht."),$a->getTitel());
                        $messaging->insert_message($msg, get_username($a->getUserId()), "____%system%____", FALSE, FALSE, 1, FALSE, dgettext('sb',"Anzeige geloescht!"));
                    }
					$a->delete();
					StudIPTemplateEngine::showSuccessMessage("Die Anzeige wurde erfolgreich gelöscht.");
				}
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
			$this->show_themen();
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
