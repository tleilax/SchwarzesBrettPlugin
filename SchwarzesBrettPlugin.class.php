<?php
/**
* SchwarzesBrettPlugin.class.php
*
* Plugin zum Verwalten von Schwarzen Brettern
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


require_once ("lib/functions.php");
require_once ("lib/messaging.inc.php");
require_once ("classes/Artikel.class.php");
require_once ("classes/Thema.class.php");

class SchwarzesBrettPlugin extends AbstractStudIPSystemPlugin {

	var $zeit;
	var $user_agent;

	function SchwarzesBrettPlugin() {
		parent::AbstractStudIPSystemPlugin();
		bindtextdomain('sb',dirname(__FILE__).'/locale');
		if ($this->new_items_since_last_visit())
			// $this->setPluginiconname('images/script_red.png');
			$this->setPluginiconname('images/header_pinn2.gif');
		else
			// $this->setPluginiconname('images/script.png');
			$this->setPluginiconname('images/header_pinn1.gif');
		// Navigationsreiter erzeugen
		$nav = new PluginNavigation();
		$nav->setDisplayname(dgettext('sb',"Schwarzes Brett"));
		$this->setNavigation($nav);
		$this->zeit = get_config('BULLETIN_BOARD_DURATION') * 24 * 60 * 60; // 30 Tage Laufzeit
		$ua = $_SERVER['HTTP_USER_AGENT'];
		$this->user_agent = array('PC'=>0, 'PPC'=>0);
		if (strpos($ua, "PPC") !== false || strpos($ua, "Windows CE") !== false)
			$this->user_agent['PPC'] = 1;
		else 
			$this->user_agent['PC'] = 1;
	}

	/**
	 * Setzt den Pfad zum Plugin.
	 * @param string $path Pfad zum Plugin
	 */
	function setPluginpath($path)	{
		parent::setPluginpath($path);
		$this->buildMenu();
	}

	/**
	 * Erstellt das Menü des Plugins.
	 * @uses PluginNavigation
	 */
	function buildMenu() {
		/*$new_postings = $this->new_items_since_last_visit();
		$all = $this->num_all_postings();
		$p = sprintf(dgettext('sb',"%d"),$all);
		if ($new_postings) $p .= sprintf(dgettext('sb',"/%d"),$new_postings);*/
		$tab = new PluginNavigation();
		$tab->setDisplayname(dgettext('sb',"SchwarzesBrett"));
		$this->setNavigation( $tab );
	}

	/**
	 * Liefert den Namen des Plugins zurück.
	 * @return string Der Name des Plugins
	 */
	function getPluginname() {
		return dgettext('sb',"Schwarzes Brett Plugin");
	}


	function get_scriptaculous() {
                echo "<script src=\"".$this->getPluginpath()."/lib/scriptaculous/prototype.js\" type=\"text/javascript\"></script>\n";
                echo "<script src=\"".$this->getPluginpath()."/lib/scriptaculous/scriptaculous.js\" type=\"text/javascript\"></script>\n";
        }

	function edit_thema_form($thema_id=FALSE) {
		$t = new Thema($thema_id);

		echo "<CENTER>\n";
		echo "<FORM NAME=\"add\" METHOD=\"POST\" ACTION=\"".PluginEngine::getLink($this,array())."\">\n";
		echo "<DIV STYLE=\"text-align:left; width:600px; margin:5px; padding:5px; border-color:red; border-width:thin; border-style:solid;\">\n";
		echo "  <INPUT TYPE=\"hidden\" NAME=\"modus\" VALUE=\"save_thema\">\n";
		if ($thema_id)
			echo "  <INPUT TYPE=\"hidden\" NAME=\"thema_id\" VALUE=\"".$t->getThemaId()."\">\n";
		echo "  <TABLE BORDER=\"0\" WIDTH=\"590\">\n";
		echo "    <TR>\n";
		echo "      <TD><SPAN STYLE=\"font-size:smaller; font-weight:bold;\">".dgettext('sb',"Titel").":</SPAN></TD>\n";
		echo "      <TD><INPUT TYPE=\"text\" NAME=\"titel\" VALUE=\"".htmlReady($t->getTitel())."\" MAXLENGTH=\"255\" STYLE=\"width:500px;\"></TD>\n";
		echo "    </TR>\n";
		echo "    <TR>\n";
		echo "      <TD VALIGN=\"TOP\"><SPAN STYLE=\"font-size:smaller; font-weight:bold;\">".dgettext('sb',"Beschreibung").":</SPAN></TD>\n";
		echo "      <TD><TEXTAREA NAME=\"beschreibung\" STYLE=\"width:500px; height:150px;\">".htmlReady($t->getBeschreibung())."</TEXTAREA></TD>\n";
		echo "    </TR>\n";
		echo "    <TR>\n";
		echo "      <TD><SPAN STYLE=\"font-size:smaller; font-weight:bold;\">".dgettext('sb',"Berechtigung").":</SPAN></TD>\n";
		echo "      <TD>\n";
		$pe = array('autor','tutor','dozent','admin','root');
		echo "        <SELECT NAME=\"perm\" SIZE=\"1\" STYLE=\"width:70px;\">\n";
		foreach ($pe as $p)
			echo "          <OPTION VALUE=\"$p\" ".($t->getPerm()==$p?"SELECTED":"").">$p</OPTION>\n";
		echo "        </SELECT>\n";
		echo "      </TD>\n";
		echo "    </TR>\n";
		echo "    <TR>\n";
		echo "      <TD VALIGN=\"TOP\"><SPAN STYLE=\"font-size:smaller; font-weight:bold;\">".dgettext('sb',"Sichtbar").":</SPAN></TD>\n";
		echo "      <TD><INPUT TYPE=\"checkbox\" NAME=\"visible\" VALUE=\"1\" ".($t->getVisible()?"CHECKED":"")."></TD>\n";
		echo "    </TR>\n";
		echo "    <TR>\n";
		echo "      <TD COLSPAN=\"2\" ALIGN=\"CENTER\">\n";
		echo "        <DIV STYLE=\"width:100%; margin-bottom:10px;\"></DIV>";
		echo "        <INPUT TYPE=\"image\" ".makeButton("speichern","src")." />\n";
		echo "        <A HREF=\"".PluginEngine::getLink($this,array())."\"><IMG ".makeButton("abbrechen","src")." BORDER=\"0\"/></A>\n";
		echo "      </TD>\n";
		echo "    </TR>\n";
		echo "  </TABLE>\n";
		echo "</DIV>\n";
		echo "</FORM>\n";
		echo "</CENTER>\n";
	}

	function edit_artikel_form($thema_id, $artikel_id=FALSE) {
		$a = new Artikel($artikel_id);
		if (!$artikel_id)
			$a->setThemaId($thema_id);
		$t = new Thema($thema_id);

		echo "<CENTER>\n";
		echo "<FORM NAME=\"add\" METHOD=\"POST\" ACTION=\"".PluginEngine::getLink($this,array())."\">\n";
		echo "<DIV STYLE=\"text-align:left; width:600px; margin:5px; padding:5px; border-color:red; border-width:thin; border-style:solid;\">\n";
		echo "  <INPUT TYPE=\"hidden\" NAME=\"modus\" VALUE=\"add_artikel\">\n";
		echo "  <INPUT TYPE=\"hidden\" NAME=\"open\" VALUE=\"$thema_id\">\n";
		if ($artikel_id)
			echo "  <INPUT TYPE=\"hidden\" NAME=\"artikel_id\" VALUE=\"".$a->getArtikelId()."\">\n";
		echo "  <TABLE BORDER=\"0\" WIDTH=\"590\">\n";
		echo "    <TR>\n";
		echo "      <TD><SPAN STYLE=\"font-size:smaller; font-weight:bold;\">".dgettext('sb',"Thema").":</SPAN></TD>\n";
		echo "      <TD><SPAN STYLE=\"font-size:smaller; font-weight:bold; color:red;\">".htmlReady($t->getTitel())."</SPAN></TD>\n";
		echo "    </TR>\n";
		echo "    <TR>\n";
		echo "      <TD><SPAN STYLE=\"font-size:smaller; font-weight:bold;\">".dgettext('sb',"Titel").":</SPAN></TD>\n";
		echo "      <TD><INPUT TYPE=\"text\" NAME=\"titel\" VALUE=\"".htmlReady($a->getTitel())."\" MAXLENGTH=\"255\" STYLE=\"width:500px;\"></TD>\n";
		echo "    </TR>\n";
		echo "    <TR>\n";
		echo "      <TD VALIGN=\"TOP\"><SPAN STYLE=\"font-size:smaller; font-weight:bold;\">".dgettext('sb',"Beschreibung").":</SPAN></TD>\n";
		echo "      <TD><TEXTAREA NAME=\"beschreibung\" STYLE=\"width:500px; height:150px;\">".htmlReady($a->getBeschreibung())."</TEXTAREA></TD>\n";
		echo "    </TR>\n";
		echo "    <TR>\n";
		echo "      <TD VALIGN=\"TOP\"><SPAN STYLE=\"font-size:smaller; font-weight:bold;\">".dgettext('sb',"Sichtbar").":</SPAN></TD>\n";
		echo "      <TD><INPUT TYPE=\"checkbox\" NAME=\"visible\" VALUE=\"1\" ".($a->getVisible()?"CHECKED":"")."></TD>\n";
		echo "    </TR>\n";
		echo "    <TR>\n";
		echo "      <TD COLSPAN=\"2\" ALIGN=\"CENTER\">\n";
		echo "        <DIV STYLE=\"width:100%; color:red; font-size:smaller; margin-bottom:10px;\">".sprintf(dgettext('sb',"Laufzeit bis %s"),date("d.m.Y",($a->getMkdate()?$a->getMkdate():time())+$this->zeit))."</DIV>";
		echo "        <INPUT TYPE=\"image\" ".makeButton("speichern","src")." />\n";
		echo "        <A HREF=\"".PluginEngine::getLink($this,array("open"=>$thema_id))."\"><IMG ".makeButton("abbrechen","src")." BORDER=\"0\"/></A>\n";
		echo "    </TD>\n";
		echo "    </TR>\n";
		echo "  </TABLE>\n";
		echo "</DIV>\n";
		echo "</FORM>\n";
		echo "<DIV STYLE=\"text-align:left; width:600px; margin:5px; padding:5px; font-size:smaller;\">\n";
		echo "  <SPAN STYLE=\"font-weight:bold;\">".dgettext('sb',"Hinweise zur Anzeigenerstellung:")."</SPAN>\n";
		echo "    <UL>\n";
		echo "      <LI>".dgettext('sb',"Jede Anzeige <B>muss</B> einen <B>universit&auml;ren Bezug</B> haben, alle anderen Anzeigen werden entfernt.");
		echo "      <LI>".sprintf(dgettext('sb',"Eine Anzeige hat z.Z. eine Laufzeit von <B>%d Tagen</B>. Nach Ablauf dieser Frist wird die Anzeige automatisch nicht mehr angezeigt."),($this->zeit / (24 * 60 * 60)))."\n";
		echo "      <LI>".dgettext('sb',"Sobald eine Anzeige nicht mehr aktuell ist (z.B. in dem Fall, dass ein Buch verkauft oder eine Mitfahrgelegenheit gefunden wurde), sollte die Anzeige durch den Autor entfernt werden.")."\n";
		echo "      <LI>".dgettext('sb',"Bitte die Anzeigen in die dafr&uuml;r vorgesehenen Themen einstellen. Falsche thematische Zuordnungen werden entfernt.")."\n";
		echo "      <LI>".dgettext('sb',"Wird ein Gegenstand oder eine Dienstleistung gegen Bezahlung angeboten, sollte der Betrag genannt werden, um unn&ouml;tige Nachfragen zu vermeiden.")."\n";
		echo "      <LI>".dgettext('sb',"Jede Anzeige, die gegen die Nutzungsordnung verst&ouml;&szlig;t, wird umgehend entfernt.");
		echo "      <LI>".dgettext('sb',"Kommerzielle Anzeigen sind nicht erw&uuml;nscht und werden entfernt.");
		echo "    </UL>\n";
		echo "</DIV>\n";
		echo "</CENTER>\n";
	}

	function get_artikel($thema_id) {
		$ret = array();
		$db = new DB_Seminar();
		$db->queryf("SELECT * FROM sb_artikel WHERE thema_id='%s' AND UNIX_TIMESTAMP() < (mkdate + %d) AND (visible=1 OR (visible=0 AND (user_id='%s' OR 'root'='%s'))) ORDER BY mkdate DESC",$thema_id,$this->zeit,$GLOBALS['auth']->auth["uid"],$GLOBALS['auth']->auth["perm"]);
		while ($db->next_record()) {
			$a = new Artikel($db->f("artikel_id"));
			array_push($ret, $a);
		}
		return $ret;		
	}

	function is_duplicate($titel) {
		$db = new DB_Seminar();
		$db->queryf("SELECT artikel_id FROM sb_artikel WHERE user_id='%s' AND titel='%s' AND mkdate>(UNIX_TIMESTAMP()-60*60*24)",$GLOBALS['auth']->auth['uid'],$titel);
		if ($db->num_rows()>0)
			return TRUE;
		else
			return FALSE;
	}

	function get_artikel_count_visible($thema_id) {
		$db = new DB_Seminar();
		$db->queryf("SELECT * FROM sb_artikel WHERE thema_id='%s' AND UNIX_TIMESTAMP() < (mkdate + %d) AND (visible=1 OR (visible=0 AND (user_id='%s' OR 'root'='%s'))) ORDER BY mkdate DESC",$thema_id,$this->zeit,$GLOBALS['auth']->auth["uid"],$GLOBALS['auth']->auth["perm"]);
		return $db->num_rows();
	}

	function show_artikel_body($a, $t) {
		$a_open = trim($_REQUEST['a_open']);
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

	function show_artikel($a) {

		$a_open = trim($_REQUEST['a_open']);
		$t = new Thema($a->getThemaId());

		$pfeil = ($this->has_visited($a->getArtikelId()) ? "forumgrau" : "forumrot");
		$pfeil_runter = ($this->has_visited($a->getArtikelId()) ? "forumgraurunt" : "forumrotrunt");

		if ($this->user_agent['PC']) {
?>
<script type="text/javascript" language="javascript">
show_content['<?=$a->getArtikelId()?>'] = false;
</script>
<?
		}

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
			$this->show_artikel_body($a, $t, "none");
		echo "</DIV>\n";
	}

	function print_js_opener() {
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

	function list_artikel($thema_id) {
		$a_open = trim($_REQUEST['a_open']);
		$t = new Thema($thema_id);
		$artikel = $this->get_artikel($thema_id);
		if (count($artikel) == 0)
			echo "<DIV STYLE=\"font-size:smaller; margin-bottom:10px; padding-left:5px;\">".dgettext('sb',"Zur Zeit sind keine Anzeigen vorhanden!")."</DIV>\n";
		else 
			foreach ($artikel as $a) {
				$this->show_artikel($a);
			}
		if ($GLOBALS['perm']->have_perm($t->getPerm()))
			echo "<DIV STYLE=\"clear:both;\"></DIV><DIV STYLE=\"margin-top:10px; text-align:center;\"><A STYLE=\"font-size:smaller;\" HREF=\"".PluginEngine::getLink($this,array("modus"=>"show_add_artikel_form", "thema_id"=>$thema_id))."\">".dgettext('sb',"Neue Anzeige anlegen")."</A></DIV>\n";

	}

	function get_artikel_lookups($artikel_id) {
		$db = new DB_Seminar();
		$db->queryf("SELECT * FROM sb_visits WHERE type='artikel' AND object_id='%s'",$artikel_id);
		return $db->num_rows();
	}

	function get_themen() {
		$ret = array();
		$db = new DB_Seminar();
		$db->queryf("SELECT t.*, COUNT(a.artikel_id) count_artikel FROM sb_themen t LEFT JOIN sb_artikel a USING (thema_id) WHERE t.visible=1 OR (t.visible=0 AND (t.user_id='%s' OR 'root'='%s')) GROUP BY t.thema_id ORDER BY count_artikel DESC",$GLOBALS['auth']->auth["uid"],$GLOBALS['auth']->auth["perm"]);
		while ($db->next_record()) {
			$t = new ThemaExt($db->f("thema_id"));
			$t->setArtikelCount($db->f("count_artikel"));
			array_push($ret, $t);
		}
		return $ret;
	}

	function list_themen() {
		$open = trim($_REQUEST['open']);
		$a_open = trim($_REQUEST['a_open']);

		$counter = 0;

		if ($this->user_agent['PC'])
			$this->print_js_opener();

		echo "<CENTER>\n";
		$themen = $this->get_themen();

		if (count($themen) > 3) {
			$tmp_count = array(0=>0, 1=>0, 2=>0);
			$tmp_themen = array(0=>array(), 1=>array(), 2=>array());
			foreach ($themen as $tt) {
				$anzahl = $tt->getArtikelCount();
				foreach ($tmp_count as $c_key=>$c_val){
					$tmp_count[$c_key] += $anzahl;
					array_push($tmp_themen[$c_key], $tt);
					break;
				}
				asort($tmp_count,SORT_NUMERIC);
			}
			if ($this->user_agent['PC'])
				asort($tmp_count,SORT_NUMERIC);
			else
				sort($tmp_count,SORT_NUMERIC);
		}

		if (count($themen) == 0)
			echo "<SPAN STYLE=\"font-size:smaller;\">".dgettext('sb',"Zur Zeit sind keine Themengebiete vorhanden!")."</SPAN><BR><BR>\n";
		else {
			if ($this->user_agent['PC']) {
				echo "<TABLE BORDER=\"0\" STYLE=\"width:100%; text-align:center;\">\n";
				echo "  <TR>\n";
			}
			foreach ($tmp_themen as $tt) {
				if ($this->user_agent['PC']) echo "  <TD VALIGN=\"TOP\" STYLE=\"width:33%; max-width:33%; min-width:33%;\">\n";
				foreach ($tt as $t) {
					echo "<DIV STYLE=\"".($this->user_agent['PPC'] ? "display:block; ":"")."width:94%; max-width:94%; min-width:94%; background-color:#FBFBF5; border:1px solid #808080; padding:10px 10px 10px 10px; text-align:left; margin:5px; float:left;\">\n";
					echo "    <DIV STYLE=\"float:left; font-weight:bold;\">".htmlReady($t->getTitel())."</DIV>\n";
					if ($GLOBALS['perm']->have_perm("root")) {
						echo "    <DIV STYLE=\"float:right;\">\n";
						if ($t->getVisible() == 0) echo "<IMG SRC=\"".$this->getPluginpath()."/images/exclamation.png\" ALT=\"".dgettext('sb',"nicht sichtbar")."\" TITLE=\"".dgettext('sb',"nicht sichtbar")."\">\n";
						echo "      <A HREF=\"".PluginEngine::getLink($this,array("modus"=>"show_add_thema_form","thema_id"=>$t->getThemaId()))."\"><IMG SRC=\"".$this->getPluginpath()."/images/table_edit.png\" BORDER=\"0\" ALT=\"".dgettext('sb',"bearbeiten")."\" TITLE=\"".dgettext('sb',"bearbeiten")."\"></A>\n";
						echo "      <A HREF=\"".PluginEngine::getLink($this,array("modus"=>"delete_thema","thema_id"=>$t->getThemaId()))."\" onClick=\"return confirm('".dgettext('sb',"Soll das Thema mit allen Eintr&auml;gen wirklich gel&ouml;scht werden?")."');\"><IMG SRC=\"".$this->getPluginpath()."/images/cross.png\" BORDER=\"0\" ALT=\"".dgettext('sb',"l&ouml;schen")."\" TITLE=\"".dgettext('sb',"l&ouml;schen")."\"></A>\n";
						echo "    </DIV>\n";
					}
					echo "  <DIV STYLE=\"clear:both;\"></DIV>\n";
					echo "  <DIV STYLE=\"border-bottom:1px solid #808080; margin-bottom:10px;\">\n";
					echo "    </DIV>\n";
					if ($t->getBeschreibung())
						echo "  <DIV STYLE=\"font-size:x-small;\">".htmlReady($t->getBeschreibung())."</DIV>\n";
					$this->list_artikel($t->getThemaId());
					echo "</DIV>\n";
				}
				if ($this->user_agent['PC']) echo "  </TD>\n";
			}
			if ($this->user_agent['PC']) {
				echo "  </TR>\n";
				echo "</TABLE>\n";
			}
		}
		if ($GLOBALS['perm']->have_perm("root")) {
			echo "<DIV STYLE=\"clear:both; margin-top:10px; text-align:center;\">\n";
			echo "<A STYLE=\"font-size:smaller;\" HREF=\"".PluginEngine::getLink($this,array("modus"=>"show_add_thema_form"))."\"><IMG ".makeButton("neuanlegen","src")." BORDER=\"0\"></A>\n";
			echo "</DIV>\n";
		}
		echo "<BR>\n";
		echo "</CENTER>\n";
	}

	function get_thema_perm($thema_id) {
		$db = new DB_Seminar();
		$db->queryf("SELECT perm FROM sb_themen WHERE thema_id='%s'",$thema_id);
		$db->next_record();
		return $db->f("perm");
	}

	function get_artikel_count($thema_id) {
		$db = new DB_Seminar();
		$db->queryf("SELECT * FROM sb_artikel WHERE thema_id='%s'",$thema_id);
		return $db->num_rows();
	}

	function visit($obj_id, $type) {
		$db = new DB_Seminar();
		$db->queryf("REPLACE INTO sb_visits SET object_id='%s', user_id='%s', type='%s', last_visitdate=UNIX_TIMESTAMP()",$obj_id,$GLOBALS['auth']->auth['uid'],$type);
	}
	
	function has_visited($obj_id) {
		$db = new DB_Seminar();
		$db->queryf("SELECT last_visitdate FROM sb_visits WHERE object_id='%s' AND user_id='%s'",$obj_id,$GLOBALS['auth']->auth['uid']);
		if ($db->next_record())
			return $db->f("last_visitdate");
		else
			return FALSE;
	}

	function num_all_postings() {
		$db = new DB_Seminar();
		$db->query("SELECT * FROM sb_artikel");
		return $db->num_rows();
	}

	function new_items_since_last_visit() {
		$db = new DB_Seminar();
		$db->queryf("SELECT last_visitdate FROM sb_visits WHERE user_id='%s' AND object_id='root'",$GLOBALS['auth']->auth['uid']);
		if ($db->next_record()) {
			$lv = $db->f("last_visitdate");
			$db->queryf("SELECT a.* FROM sb_artikel a, sb_themen t WHERE a.visible=1 AND t.thema_id=a.thema_id AND t.visible=1 AND a.mkdate>%d AND a.user_id!='%s' AND NOT EXISTS (SELECT object_id FROM sb_visits WHERE object_id=a.artikel_id AND type='artikel' AND user_id='%s')",$lv,$GLOBALS['auth']->auth['uid'],$GLOBALS['auth']->auth['uid']);
			return $db->num_rows();
		}
		return $this->num_all_postings();;
	}

	function show_search_form() {
		echo "<CENTER>\n";
		echo "<DIV STYLE=\"text-align:center; width:500px;\">\n";
		echo "  <FORM NAME=\"search_form\" METHOD=\"POST\" ACTION=\"".PluginEngine::getLink($this,array("modus"=>"show_search_results"))."\">\n";
		echo "    <DIV STYLE=\"float:left;\">\n";
		echo "      <SPAN STYLE=\"font-weight:bold; font-size:smaller;\">".dgettext('sb',"Suche nach Anzeigen:")." </SPAN>\n";
		echo "      <INPUT TYPE=\"text\" STYLE=\"size:100px; maxlength:100;\" VALUE=\"".htmlReady($_REQUEST['search_text'])."\" NAME=\"search_text\">\n";
		echo "    </DIV>\n";
		echo "    <DIV STYLE=\"float:left;\">\n";
		echo "      &nbsp;<INPUT TYPE=\"image\" ".makeButton("suchen","src").">\n";
		echo "    </DIV>\n";
		echo "    <DIV STYLE=\"float:left;\">\n";
		echo "      &nbsp;<A HREF=\"".PluginEngine::getLink($this,array())."\">".makeButton("zuruecksetzen","img")."</A>\n";
		echo "    </DIV>\n";
		echo "    <DIV STYLE=\"clear:both;\"></DIV>\n";
		echo "  </FORM>\n";
		echo "  <SPAN STYLE=\"font-size:x-small;\">".sprintf(dgettext('sb',"Aktuelle Laufzeit: max. %d Tage"),($this->zeit / (24 * 60 * 60)));
		echo "</DIV>\n";
		echo "</CENTER>\n";
	}

	function do_search($search_text) {
		$db = new DB_Seminar();
		$sql = sprintf("SELECT a.thema_id, a.artikel_id, a.titel, t.titel t_titel FROM sb_artikel a, sb_themen t WHERE 
				t.thema_id=a.thema_id AND UPPER(a.titel) LIKE '%s' AND UNIX_TIMESTAMP() < (a.mkdate + %d) 
				AND (a.visible=1 OR (a.visible=0 AND (a.user_id='%s' OR 'root'='%s'))) ORDER BY t.titel, a.titel 
			","%".strtoupper($search_text)."%",$this->zeit,$GLOBALS['auth']->auth["uid"],$GLOBALS['auth']->auth["perm"]);
		//echo $sql;
		$db->query($sql);
		$thema_id = "";
	
		$this->print_js_opener();

		echo "<CENTER>\n";
		$this->show_search_form();
		echo "<DIV STYLE=\"width:33%;\">\n"; 
		while ($db->next_record()) {
			$a = new Artikel($db->f("artikel_id"));
			if ($thema_id != $db->f("thema_id") && $thema_id != "") 
				echo "</DIV>\n";
			if ($thema_id != $db->f("thema_id")) {
				echo "<DIV STYLE=\"display:block; width:94%; max-width:94%; min-width:94%; background-color:#FBFBF5; border:1px solid #808080; padding:10px 10px 10px 10px; text-align:left; margin:5px; float:left;\">\n";
				echo "    <DIV STYLE=\"float:left; font-weight:bold;\">".htmlReady($db->f("t_titel"))."</DIV>\n";
				echo "  <DIV STYLE=\"clear:both; display:block;\"></DIV>\n";
				echo "  <DIV STYLE=\"border-bottom:1px solid #808080; margin-bottom:10px;\">\n";
				echo "  </DIV>\n";
				$thema_id = $db->f("thema_id");
			}
			$this->show_artikel($a);
		}
		echo "</DIV>\n";
		echo "</CENTER>\n";
	}

	function parse_msg($txt) {
		echo "<TABLE BORDER=\"0\">\n";
		parse_msg($txt);
		echo "</TABLE>\n";
	}

	function show() {
		$db = new DB_Seminar();
		$open = trim($_REQUEST['open']);
		$a_open = trim($_REQUEST['a_open']);
		if ($a_open) $this->visit($a_open,"artikel");

		$this->visit("root","thema");

		$this->get_scriptaculous();

		$modus = trim($_REQUEST['modus']);

		if ($modus) {
			if ($GLOBALS['perm']->have_perm("root")) {
				if ($modus == "save_thema") {
					$t = new Thema($_REQUEST['thema_id']);
					$t->setTitel($_REQUEST['titel']);
					$t->setBeschreibung($_REQUEST['beschreibung']);
					$t->setPerm($_REQUEST['perm']);
					$t->setVisible(($_REQUEST['visible'] ? $_REQUEST['visible'] : 0));
					$t->save();
					$this->parse_msg("msg§".dgettext('sb',"Thema wurde gespeichert."));
					unset($modus);
				}
				if ($modus == "show_add_thema_form")
					$this->edit_thema_form($_REQUEST['thema_id']);
				if ($modus == "delete_thema") {
					$t = new Thema($_REQUEST['thema_id']);
					$yes = "<A HREF=\"".PluginEngine::getLink($this,array("modus"=>"delete_thema_really", "thema_id"=>$t->getThemaId()))."\">".makeButton("ja","img")."</A>";
					$no = "<A HREF=\"".PluginEngine::getLink($this,array())."\">".makeButton("nein","img")."</A>";
					$this->parse_msg(sprintf("info§".dgettext('sb',"Soll das Thema \"%s\" wirklich gel&ouml;scht werden?<BR>%s %s"),$t->getTitel(), $yes, $no));
					unset($modus);
				}
				if ($modus == "delete_thema_really") {
					$t = new Thema($_REQUEST['thema_id']);
					$t->delete();
					$this->parse_msg("msg§".dgettext('sb',"Thema wurde gel&ouml;scht."));
					unset($modus);
				}
			}
			if ($modus == "add_artikel" && $GLOBALS['perm']->have_perm($this->get_thema_perm($open))) {
				if ((!$this->is_duplicate($_REQUEST['titel']) && !isset($_REQUEST['artikel_id'])) || isset($_REQUEST['artikel_id'])) {
					$a = new Artikel($_REQUEST['artikel_id']);
					$a->setTitel($_REQUEST['titel']);
					$a->setBeschreibung($_REQUEST['beschreibung']);
					$a->setThemaId($open);
					$a->setVisible(($_REQUEST['visible'] ? $_REQUEST['visible'] : 0));
					$a->save();
					$this->parse_msg("msg§".dgettext('sb',"Anzeige wurde gespeichert."));
				}
				unset($modus);
			}
			if ($modus == "show_add_artikel_form")
				$this->edit_artikel_form($_REQUEST['thema_id'], $_REQUEST['artikel_id']);
			if ($modus == "delete_artikel") {
				$a = new Artikel($_REQUEST['artikel_id']);
				if ($a->getUserId() == $GLOBALS['auth']->auth['uid'] || $GLOBALS['perm']->have_perm("root")) {
					$autor_name = "<A HREF=\"about.php?username=".get_username($a->getUserId())."\">".get_fullname($a->getUserId())."</A>";
					$yes = "<A HREF=\"".PluginEngine::getLink($this,array("modus"=>"delete_artikel_really", "artikel_id"=>$a->getArtikelId()))."\">".makeButton("ja","img")."</A>";
					$no = "<A HREF=\"".PluginEngine::getLink($this,array())."\">".makeButton("nein","img")."</A>";
					$this->parse_msg(sprintf("info§".dgettext('sb',"Soll die Anzeige \"%s\" von %s wirklich gel&ouml;scht werden?<BR>%s %s"),$a->getTitel(), $autor_name, $yes, $no));
				}
				unset($modus);
			}
			if ($modus == "delete_artikel_really") {
				$a = new Artikel($_REQUEST['artikel_id']);
				if ($a->getUserId() == $GLOBALS['auth']->auth['uid'] || $GLOBALS['perm']->have_perm("root")) {
					if ($a->getUserId() != $GLOBALS['auth']->auth['uid'] && $GLOBALS['perm']->have_perm("root")) {
						$messaging=new messaging;
                                                $msg = sprintf(dgettext('sb',"Die Anzeige \"%s\" wurde von der Administration geloescht."),$a->getTitel());
                                                $messaging->insert_message($msg, get_username($a->getUserId()), "____%system%____", FALSE, FALSE, 1, FALSE, dgettext('sb',"Anzeige geloescht!"));
                                        }
					$this->parse_msg("msg§".dgettext('sb',"Anzeige gel&ouml;scht!"));
					$a->delete();
				}
				unset($modus);
			}
			if ($modus == "show_search_results") {
				$txt = trim($_REQUEST['search_text']);
				if ($txt)
					$this->do_search($txt);
				else
					unset($modus);
			}
		}
		if (!$modus) {
			$this->show_search_form();
			$this->list_themen();
		}
		
	}

	function actionShow() {
		return $this->show();
	}

}

class ThemaExt extends Thema {

	var $artikel_count;

	function ThemaExt($id=FALSE) {
		parent::Thema($id);
		$this->artikel_count = 0;
	}

	function setArtikelCount($c) {
		$this->artikel_count = $c;
	}

	function getArtikelCount() {
		return $this->artikel_count;
	}
}
