<form name="search_form" method="post" action="<?=$link_search?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<td class="topic"><b>Allgemeine Suche nach Anzeigen:</b></td>
	</tr>
</table>
<div class="steel1" style="padding:5px;">Nach Anzeigen suchen:
	<input type="text" style="width:200px;" name="search_text" value="<?=htmlready($_REQUEST['search_text'])?>" />
	<?=makebutton("suchen","input", "nach Anzeigen suchen", "submit")?>
	<a href="<?=$link_back?>"><?=makebutton("zuruecksetzen","img", "zurücksetzen")?></a>
</div>
</form>
<br/>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<td class="topic"><b>Ergebnisse:</b></td>
	</tr>
</table>
<? foreach ($results as $result): ?>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<th><?=$result['thema_titel']?></th>
	</tr>
</table>
<table border="0" cellpadding="5" cellspacing="0" width="100%">
	<? foreach ($result['artikel'] as $a): ?>
	<tr>
		<td class="steel1">
		<script type="text/javascript" language="javascript">
			show_content['<?=$a->getArtikelId()?>'] = false;
		</script>

		<?=$a->getTitel()?>



<? /*
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
		if (trim($_REQUEST['a_open'])==$a->getArtikelId())
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
			if ($a->getUserId() == $GLOBALS['auth']->auth['uid'] || $GLOBALS['perm']->have_perm("root"))
			{
				echo "      <A HREF=\"".PluginEngine::getLink($this,array("modus"=>"show_add_artikel_form", "thema_id"=>$t->getThemaId(), "artikel_id"=>$a->getArtikelId()))."\">".makeButton("bearbeiten","img")."</A>\n";
				echo "      <A HREF=\"".PluginEngine::getLink($this,array("modus"=>"delete_artikel", "thema_id"=>$t->getThemaId(), "artikel_id"=>$a->getArtikelId()))."\">".makeButton("loeschen","img")."</A>\n";
			}

		}
*/ ?>



		</td>
	</tr>
	<? endforeach; ?>
</table>
<? endforeach; ?>

<pre>
<? print_r($results); ?>
</pre>