<?php
/**
 * SchwarzesBrettPlugin.class.php
 *
 * Plugin zum Verwalten von Schwarzen Brettern (Angebote und Gesuche)
 *
 * Diese Datei enthält die Hauptklasse des Plugins
 *
 * @author      Jan Kulmann <jankul@zmml.uni-bremen.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @package     IBIT_SchwarzesBrettPlugin
 * @copyright   2008-2010 IBIT und ZMML
 * @license     http://www.gnu.org/licenses/gpl.html GPL Licence 3
 * @version     2.0.2
 */

// IMPORTS
require_once 'bootstrap.inc.php';

/**
 * SchwarzesBrettPlugin Hauptklasse
 *
 */
class SchwarzesBrettPlugin extends StudIPPlugin implements SystemPlugin
{
    public $zeit, $announcements, $user, $perm;
    private $template_factory, $layout, $layout_infobox;

    const THEMEN_CACHE_KEY = 'plugins/SchwarzesBrettPlugin/themen';
    const ARTIKEL_CACHE_KEY = 'plugins/SchwarzesBrettPlugin/artikel/';

    /**
     *
     */
    public function __construct()
    {
        global $user, $perm, $auth;
        parent::__construct();

        $this->user = $user;
        $this->perm = $perm;
        $this->template_factory = new Flexi_TemplateFactory(dirname(__FILE__).'/templates');
        $this->layout =  $GLOBALS['template_factory']->open('layouts/base_without_infobox');
        $this->layout_infobox =  $GLOBALS['template_factory']->open('layouts/base');

        // Holt die Laufzeit aus der Config. Default: 30Tage
        $this->zeit = (int)get_config('BULLETIN_BOARD_DURATION') * 24 * 60 * 60;
        // Holt Anzahl anzuzeigende neuste Anzeigen. Default: 20
        $this->announcements = (int)get_config('BULLETIN_BOARD_ANNOUNCEMENTS');

        //Icons nach Standort
        //Hannover
        if ($GLOBALS['STUDIP_INSTALLATION_ID'] == 'luh') {
            $image = $this->getPluginURL().'/images/billboard_luh.png';
        //Oldenburg
        } elseif ($GLOBALS['STUDIP_INSTALLATION_ID'] == 'uni-ol' || $GLOBALS['STUDIP_INSTALLATION_ID'] == 'uol') {
            $image = $this->getPluginURL().'/images/billboard_uol.png';
        //Standard
        } else {
             $image = $this->getPluginURL().'/images/billboard.png';
        }

        //Navigation
        $nav = new AutoNavigation(_('Schwarzes Brett'), PluginEngine::getURL($this, array()));
        $nav->setImage($image, array('title' => _('Schwarzes Brett'), 'class' => $this->hasNewArticles()));

        //menu nur anzeigen, wenn eingeloggt
        if($this->perm->have_perm('user')) {
            $user_nav =new AutoNavigation(_('Schwarzes Brett'), PluginEngine::getURL($this, array(), 'show'));
            $user_nav->addSubNavigation('show', new AutoNavigation(_('Übersicht'), PluginEngine::getURL($this, array(), 'show')));
            //wenn auf der blacklist, darf man keine artikel mehr erstellen
            if (!$this->isBlacklisted($this->user->id)) {
                $user_nav->addSubNavigation('add', new AutoNavigation(_('Anzeige erstellen'), PluginEngine::getURL($this, array(), 'editArtikel')));
            }
            $nav->addSubNavigation('show', $user_nav);

            //zusatzpunkte für root
            if ($this->perm->have_perm('root')) {
                $this->root = true;
                $root_nav = new AutoNavigation(_('Administration'), PluginEngine::getURL($this, array(), 'editThema'));
                $root_nav->addSubNavigation('addBlock', new AutoNavigation(_('Neues Thema anlegen'), PluginEngine::getURL($this, array(), 'editThema')));
                $olds = DBManager::get()->query("SELECT count(artikel_id) FROM sb_artikel WHERE UNIX_TIMESTAMP() > (mkdate + {$this->zeit})")->fetchColumn();
                if ($olds > 0) {
                    $root_nav->addSubNavigation('delete', new AutoNavigation(_('Datenbank bereinigen ('.$olds.' alte Einträge)'), PluginEngine::getURL($this, array(), 'deleteOldArtikel')));
                }
                $root_nav->addSubNavigation('blacklist', new AutoNavigation(_('Benutzer-Blacklist'), PluginEngine::getURL($this, array(), 'blacklist')));
                $root_nav->addSubNavigation('duplicates', new AutoNavigation(_('Doppelte Einträge suchen'), PluginEngine::getURL($this, array(), 'searchDuplicates')));
                $nav->addSubNavigation('root', $root_nav);
            }
            Navigation::addItem('/schwarzesbrettplugin', $nav);
        }

        // Sachen in den Header laden (bis 1.11)
        PageLayout::addScript($this->getPluginURL().'/js/schwarzesbrett.js');
    }

    /**
     * @return  Grafik, die in der Hauptnavigation angezeigt wird
     */
    private function hasNewArticles()
    {
        $last_visitdate = DBManager::get()->query("SELECT MAX(last_visitdate) FROM sb_visits WHERE user_id='{$this->user->id}'")->fetchColumn();
        $last_artikel = DBManager::get()->query("SELECT count(*) FROM sb_artikel WHERE mkdate > '{$last_visitdate}' AND visible = 1")->fetchColumn();
        if ($last_artikel > 0) {
            return 'new';
        } else {
            return '';
        }
    }

    /**
     * Hauptfunktion, dient in diesem Plugin als Frontcontroller und steuert die Ausgaben
     *
     */
    public function show_action()
    {
        if($this->perm->have_perm('user')) {
            //Suchergebnisse abfragen und anzeigen, falls vorhanden
            if (Request::get('modus') == "show_search_results") {
                $this->search(Request::get('search_text'));
                return;
            }
            $this->showThemen();
        }
    }


    /**
     * Zeigt die Seite zum Erstellen oder Bearbeiten von Artikeln
     */
    public function editArtikel_action()
    {
        //Daten holen
        $a = new Artikel(Request::get('artikel_id', false));

        //Speichern
        if (Request::submitted('speichern') && $this->getThemaPermission(Request::get('thema_id'))) {
            $a->setTitel(Request::get('titel'));
            $a->setBeschreibung(Request::get('beschreibung'));
            $a->setThemaId(Request::get('thema_id'));
            $a->setVisible(Request::get('visible', 0));

            //keine thema
            if(Request::get('thema_id') == 'nix') {
                $this->message = MessageBox::error("Bitte wählen Sie ein Thema aus, in dem die Anzeige angezeigt werden soll.");
            //doppelter eintrag
            } elseif($this->isDuplicate(Request::get('titel')) && !Request::get('artikel_id')) {
                $this->message = MessageBox::error("Sie haben bereits einen Artikel mit diesem Titel erstellt. Bitte beachten Sie die Nutzungshinweise!");
            //speichern
            } elseif (Request::get('titel') && Request::get('beschreibung')) {
                $a->save();
                $this->message =  MessageBox::success("Die Anzeige wurde erfolgreich gespeichert.");
                //nach dem verändern der themen, muss auch der cache geleert werden
                StudipCacheFactory::getCache()->expire(self::ARTIKEL_CACHE_KEY.$a->getThemaId());
                StudipCacheFactory::getCache()->expire(self::THEMEN_CACHE_KEY);
                $this->show_action();
                return;
            //kein titel und beschreibung
            } else {
                $this->message = MessageBox::error("Bitte geben Sie einen Titel und eine Beschreibung an.");
            }
        //keine rechte
        } elseif(Request::submitted('speichern') && !$this->getThemaPermission(Request::get('thema_id'))) {
            $this->message = MessageBox::error("Sie haben nicht die erforderlichen Rechte eine Anzeige zu erstellen.");
        }

        //Ausgabe
        $template = $this->template_factory->open('edit_artikel');
        $template->message = $this->message;
        $template->set_layout($this->layout_infobox);
        $template->set_attribute('thema_id', Request::get('thema_id'));
        $template->set_attribute('themen', $this->getThemen());
        $template->set_attribute('a', $a);
        $template->set_attribute('zeit', $this->zeit);
        $template->set_attribute('link', PluginEngine::getURL($this, array(), 'show'));
        $template->set_attribute('link_thema', PluginEngine::getURL($this, array(), 'editArtikel'));

        //Infobox
        $template->infobox = array(
            'picture' => 'infobox/contract.jpg',
            'content' => array(array(
                "kategorie" => _("Information:"),
                "eintrag" => array(
                    array("icon" => 'icons/16/black/info.png',
                    "text" => 'Jede Anzeige <b>sollte</b> einen <b>universitären Bezug</b> haben, alle anderen Anzeigen werden entfernt.'),
                    array("icon" => 'icons/16/black/info.png',
                    "text" => '<b>Bitte Artikel nur in <em>eine</em> Kategorie einstellen!</b>'),
                    array("icon" => 'icons/16/black/info.png',
                    "text" => 'Sobald eine Anzeige nicht mehr aktuell ist (z.b. in dem Fall, dass ein Buch verkauft oder eine Mitfahrgelegenheit gefunden wurde), sollte die Anzeige durch den Autor entfernt werden.'),
                    array("icon" => 'icons/16/black/info.png',
                    "text" => 'Unter der Beschreibung wird automatisch ein Link zu Ihrer Benutzerhomepage eingebunden. <br />Außerdem können andere Nutzer direkt über einen Button antworten. Diese Nachrichten erhalten Sie als Stud.IP interne Post!'),
                    #array("icon" => $pluginpfad. '/images/information.png',
                    #"text" => 'Bitte die Anzeigen in die dafür vorgesehenen Themen einstellen, damit dieses schwarze Brett so übersichtlich wie möglich bleibt.'),
                    array("icon" => 'icons/16/black/info.png',
                    "text" => 'Wird ein Gegenstand oder eine Dienstleistung gegen Bezahlung angeboten, sollte der Betrag genannt werden, um unnötige Nachfragen zu vermeiden.'),
                    array("icon" => 'icons/16/black/info.png',
                    "text" => 'Jede Anzeige, die gegen diese Nutzungsordnung verstößt, wird umgehend entfernt.')
                )
            ))
        );
        echo $template->render();
    }

    /**
     * Zeigt das Formular zum Erstellen oder Bearbeiten von Themen an.
     * Nur für root
     */
    public function editThema_action()
    {
        if ($this->perm->have_perm('root')) {
            $t = new Thema(Request::get('thema_id'));

            // Speichern
            if (Request::get('modus') == "save_thema") {
                if (Request::get('titel')) {
                    $t->setTitel(Request::get('titel'));
                    $t->setBeschreibung(Request::get('beschreibung'));
                    $t->setPerm(Request::get('thema_perm'));
                    $t->setVisible(Request::get('visible', 0));
                    $t->save();

                    $this->message = MessageBox::success("Das Thema wurde erfolgreich gespeichert.");
                    //nach dem verändern der themen, muss auch der cache geleert werden
                    StudipCacheFactory::getCache()->expire(self::THEMEN_CACHE_KEY);
                    $this->showThemen();
                    return;
                } else {
                    $this->message = MessageBox::error("Bitte geben Sie einen Titel (Pflichtfeld) ein.");
                }
            }

            // Ausgabe
            $template = $this->template_factory->open('edit_thema');
            $template->set_layout($this->layout);
            $template->message = $this->message;
            $template->set_attribute('t', $t);
            $template->set_attribute('link', PluginEngine::getURL($this, array(), 'editThema'));
            $template->set_attribute('link_exit', PluginEngine::getURL($this, array(), 'show'));
            echo $template->render();
        }
    }

    /**
     * Man kann mit dieser Funktion alle veralteten Artikel aus der DB löschen
     * Nur für Root
     *
     */
    public function deleteOldArtikel_action()
    {
        Navigation::removeItem('/schwarzesbrettplugin/root/delete');
        Navigation::activateItem('/schwarzesbrettplugin/show');
        if ($this->perm->have_perm('root')) {
            $artikel = DBManager::get()->query("SELECT artikel_id FROM sb_artikel WHERE UNIX_TIMESTAMP() > (mkdate + {$this->zeit})")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($artikel as $id) {
                $a = new Artikel($id);
                $a->delete();
            }
            if (count($artikel) > 0) {
                $this->message = MessageBox::success("Es wurden erfolgreich <em>".count($artikel)."</em> Artikel aus der Datenbank gelöscht.");
            } else {
                $this->message = MessageBox::info("Es gibt keine Artikel in der Datenbank, die gelöscht werden können.");
            }
        }
        $this->showThemen();
    }

    /**
     * Löscht ein Thema mit all seinen Anzeigen inkl. Sicherheitsabfrage
     * Nur für Root
     */
    public function deleteThema_action()
    {
        Navigation::activateItem('/schwarzesbrettplugin/show');
        if ($this->perm->have_perm('root')) {
            //Thema löschen Sicherheitsabfrage
            if (Request::get('modus') == "delete_thema_really") {
                $t = new Thema(Request::get('thema_id'));
                $t->delete();
                $this->message =  MessageBox::success("Das Thema und alle dazugehörigen Anzeigen wurden erfolgreich gelöscht.");
                //nach dem verändern der themen, muss auch der cache geleert werden
                StudipCacheFactory::getCache()->expire(self::THEMEN_CACHE_KEY);
            } else {
                $t = new Thema(Request::get('thema_id'));
                echo $this->createQuestion('Soll das Thema **'.$t->getTitel().'** wirklich gelöscht werden?', array("modus"=>"delete_thema_really", "thema_id"=>$t->getThemaId()), 'deleteThema');
            }
        }
        $this->showThemen();
    }

    /**
     *  Löscht eine Anzeige inkl. Sicherheitsabfrage
     */
    public function deleteArtikel_action()
    {
        Navigation::activateItem('/schwarzesbrettplugin/show');
        $a = new Artikel(Request::get('artikel_id'));

        //Artikel löschen Sicherheitsabfrage
        if (Request::get('modus') == "delete_artikel_really") {
            //Root löscht Artikel eines Benutzers, also diesen benachrichtigen.
            if ($a->getUserId() != $this->user->id && $this->perm->have_perm('root')) {
                $messaging = new messaging();
                $msg = sprintf(_("Die Anzeige \"%s\" wurde von der Administration gelöscht.\n\n Bitte beachten Sie die Nutzungsordnung zum Erstellen von Anzeigen. Bei Wiederholung können Sie gesperrt werden.", $a->getTitel()));
                $messaging->insert_message($msg, get_username($a->getUserId()), "____%system%____", FALSE, FALSE, 1, FALSE, "Schwarzes Brett: Anzeige gelöscht!");
            }
            $a->delete();
            $this->message = MessageBox::success("Die Anzeige wurde erfolgreich gelöscht.");
            //nach dem verändern der themen, muss auch der cache geleert werden
            StudipCacheFactory::getCache()->expire(self::ARTIKEL_CACHE_KEY.$a->getThemaId());
            StudipCacheFactory::getCache()->expire(self::THEMEN_CACHE_KEY);
        } elseif ($a->getUserId() == $this->user->id || $this->perm->have_perm('root')) {
            echo $this->createQuestion('Soll die Anzeige **'.$a->getTitel().'** von %%'.get_fullname($a->getUserId()).'%% wirklich gelöscht werden?', array("modus"=>"delete_artikel_really", "artikel_id"=>$a->getArtikelId()), 'deleteArtikel');
        } else {
            $this->message = MessageBox::error("Sie haben nicht die Berechtigung diese anzeige zu löschen.");
        }
        $this->showThemen();
    }

    public function blacklist_action()
    {
        if ($this->perm->have_perm('root')) {
            $template = $this->template_factory->open('blacklist');
            $template->set_layout($this->layout);

            if (Request::get('action') == 'delete'){
                $db = DBManager::get()->prepare("DELETE FROM sb_blacklist WHERE user_id = ?");
                $db->execute(array(Request::option('user_id')));
                $template->message = MessageBox::success(_('Der Benutzer wurde erfolgreich von der Blacklist entfernt und kann nun wieder Anzeigen erstellen.'));
            } elseif (Request::get('action') == 'add' && Request::option('user_id')) {
                //datenbank
                $db = DBManager::get()->prepare("REPLACE INTO sb_blacklist SET user_id =?, mkdate=UNIX_TIMESTAMP()");
                $db->execute(array(Request::option('user_id')));
                //nachricht an den benutzer
                $messaging = new messaging();
                $msg = _("Aufgrund von wiederholten Verstößen gegen die Nutzungsordnung wurde Ihr Zugang zum Schwarzen Brett gesperrt. Sie können keine weiteren Anzeigen erstellen.\n\n Bei weiteren Fragen wenden Sie sich bitte an die Systemadministratoren.");
                $messaging->insert_message($msg, get_username(Request::option('user_id')), "____%system%____", FALSE, FALSE, 1, FALSE, "Schwarzes Brett: Sie wurden gesperrt.");

                $template->message = MessageBox::success(_('Der Benutzer wurde erfolgreich auf die Blacklist gesetzt.'));
            }

            $template->set_attribute('users', DBManager::get()->query("SELECT * FROM sb_blacklist")->fetchAll(PDO::FETCH_ASSOC));
            $template->set_attribute('link', PluginEngine::getURL($this, array(), 'blacklist'));
            echo $template->render();
        }
    }

    public function searchDuplicates_action()
    {
        $template = $this->template_factory->open('duplicates');
        $template->set_layout($this->layout);

        $results = DBManager::get()->query("SELECT user_id, count(user_id) FROM sb_artikel s GROUP BY user_id HAVING count(user_id) > 1")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $i => $result) {
            $results[$i]['artikel'] = DBManager::get()->query("SELECT * FROM sb_artikel WHERE user_id = '{$result['user_id']}'")->fetchAll(PDO::FETCH_ASSOC);
        }
        $template->set_attribute('results', $results);
        $template->set_attribute('link', PluginEngine::getURL($this, array(), 'show'));
        $template->set_attribute('link_edit', PluginEngine::getURL($this, array(), 'editArtikel'));
        $template->set_attribute('link_delete', PluginEngine::getURL($this, array(), 'deleteArtikel'));
        echo $template->render();
    }

    /**
     * Gibt alle Anzeigen zu einem Thema zurück
     *
     * @uses StudipCache
     *
     * @param string $thema_id
     * @return array Anzeigen
     */
    private function getArtikel($thema_id)
    {
        $cache = StudipCacheFactory::getCache();
        $ret = unserialize($cache->read(self::ARTIKEL_CACHE_KEY.$thema_id));

        if (empty($ret)) {
            $ret = array();
            $artikel_ids = DBManager::get()->query("SELECT artikel_id FROM sb_artikel "
                ."WHERE thema_id='{$thema_id}' AND UNIX_TIMESTAMP() < (mkdate + {$this->zeit}) "
                ."AND (visible=1 OR (visible=0 AND (user_id='{$this->user->id}' "
                ."OR 'root'='{$this->perm->get_perm($this->user->id)}'))) "
                ."ORDER BY mkdate DESC")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($artikel_ids as $artikel_id) {
                $ret[] = new Artikel($artikel_id);
            }
            $cache->write(self::ARTIKEL_CACHE_KEY.$thema_id, serialize($ret));
        }
        return $ret;
    }

    /**
     * Gibt die Anzahl Anzeigen für ein Thema zurück
     *
     * @param md5 $thema_id
     * @return int
     */
    private function getArtikelCount($thema_id)
    {
        return DBManager::get()->query("SELECT count(*) FROM sb_artikel "
            ."WHERE thema_id='{$thema_id}' AND UNIX_TIMESTAMP() < (mkdate + {$this->zeit}) "
            ."AND (visible=1 OR (visible=0 AND (user_id='{$this->user->id}' "
            ."OR 'root'='{$this->perm->get_perm($this->user-userid)}'))) ")->fetchColumn();
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
     *
     * @uses StudipCache
     *
     * @return array Liste aller Themen
     */
    private function getThemen()
    {
        $cache = StudipCacheFactory::getCache();
        $ret = unserialize($cache->read(self::THEMEN_CACHE_KEY));

        if(empty($ret)) {
            $themen = DBManager::get()->query("SELECT t.thema_id, COUNT(a.thema_id) count_artikel "
                    ."FROM sb_themen t LEFT JOIN sb_artikel a USING (thema_id) "
                    ."WHERE t.visible=1 OR t.user_id='{$this->user->id}' "
                    ."OR 'perm'='{$this->perm->get_perm($this->user->id)}' "
                    ."GROUP BY t.thema_id ORDER BY t.titel")->fetchAll(PDO::FETCH_ASSOC);
            $ret = array();
            foreach ($themen as $thema) {
                $t = new Thema($thema['thema_id']);
                $t->setArtikelCount($this->getArtikelCount($thema['thema_id']));
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
        if ($thema_id != 'nix') {
            $perm = DBManager::get()->query("SELECT perm FROM sb_themen WHERE thema_id='{$thema_id}'")->fetch(PDO::FETCH_COLUMN);
            return $this->perm->have_perm($perm);
        } else {
            return true;
        }
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
        $db = DBManager::get()->prepare("SELECT count(artikel_id) FROM sb_artikel WHERE user_id=? AND titel=? AND mkdate > (UNIX_TIMESTAMP()-(60*60*24))");
        $db->execute(array($this->user->id, $titel));
        $check = $db->fetch(PDO::FETCH_COLUMN);

        if ($check > 0) {
            return true;
        } else {
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
        $last_visitdate = DBManager::get()->query("SELECT last_visitdate FROM sb_visits WHERE object_id='{$obj_id}' AND user_id='{$this->user->id}'")->fetch(PDO::FETCH_COLUMN);
        if (!empty($last_visitdate)) {
            return $last_visitdate;
        } else {
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
            $this->message = MessageBox::error("Ihr Suchwort ist zu kurz, bitte versuchen Sie es erneut!");
            $this->showThemen();
            return;
        }

        //Datenbankabfrage
        $sql = sprintf("SELECT a.thema_id, a.artikel_id, a.titel, t.titel t_titel FROM sb_artikel AS a, sb_themen AS t WHERE
                t.thema_id=a.thema_id AND (UPPER(a.titel) LIKE '%s' OR UPPER(a.beschreibung) LIKE '%s') AND UNIX_TIMESTAMP() < (a.mkdate + %d)
                AND (a.visible=1 OR (a.visible=0 AND (a.user_id='%s' OR 'root'='%s'))) ORDER BY t.titel, a.titel
            ","%".strtoupper($search_text)."%","%".strtoupper($search_text)."%", $this->zeit, $this->user->id, $this->perm->get_perm($this->user->id));
        $dbresults = DBManager::get()->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        // keine Ergebnisse vorhanden
        if(count($dbresults) == 0) {
            $this->message = MessageBox::error("Es wurden für <em>{$search_text}</em> keine Ergebnisse gefunden.");
            $this->showThemen();
            return;
        }

        //Ergebnisse anzeigen
        $results = array();
        $thema = array();
        foreach ($dbresults as $result) {
            $a = new Artikel($result['artikel_id']);
            if(empty($thema['thema_id'])) {
                $thema['thema_id'] = $result['thema_id'];
                $thema['thema_titel'] = htmlReady($result['t_titel']);
                $thema['artikel'] = array();

            } elseif($result['thema_id'] != $thema['thema_id']) {
                array_push($results, $thema);
                unset($thema);
                $thema['thema_id'] = $result['thema_id'];
                $thema['thema_titel'] = htmlReady($result['t_titel']);
                $thema['artikel'] = array();
            }
            array_push($thema['artikel'], $this->showArtikel($a));
        }
        array_push($results, $thema);

        //Ausgabe erzeugen
        $template = $this->template_factory->open('search_results');
        $template->set_layout($this->layout);
        $template->set_attribute('zeit', $this->zeit);
        $template->set_attribute('pluginpfad', $this->getPluginURL());
        $template->set_attribute('link_search', PluginEngine::getURL($this, array("modus"=>"show_search_results")));
        $template->set_attribute('link_back', PluginEngine::getURL($this, array(), 'show'));
        $template->set_attribute('results', $results);
        echo $template->render();
    }

    /**
     * Zeigt alle Themen und Anzeigen an
     *
     */
    private function showThemen()
    {
        $themen = $this->getThemen();

        if ($this->isBlacklisted($this->user->id)) {
            $this->message .= MessageBox::info(_('Sie wurden gesperrt und können daher keine Anzeigen erstellen. Bitte wenden Sie sich an den Systemadministrator.'));
        }

        $template = $this->template_factory->open('show_themen');
        $template->set_layout($this->layout);
        $template->message = $this->message;
        $template->set_attribute('zeit', $this->zeit);
        $template->set_attribute('pluginpfad', $this->getPluginURL());
        $template->set_attribute('link_edit', PluginEngine::getURL($this, array(), 'editThema'));
        $template->set_attribute('link_artikel', PluginEngine::getURL($this, array(), 'editArtikel'));
        $template->set_attribute('link_delete', PluginEngine::getURL($this, array(), 'deleteThema'));
        $template->set_attribute('link_search', PluginEngine::getURL($this, array("modus" => "show_search_results")));
        $template->set_attribute('link_back', PluginEngine::getURL($this, array()));
        $template->set_attribute('last_visit_date', $this->last_visitdate);
        $template->set_attribute('root', $this->root);

        //Keine themen vorhanden
        if (count($themen) == 0) {
            $template->set_attribute('keinethemen', TRUE);
        }
        //themen anzeigen
        else {
            //Anzahl Themen pro Spalte berechnen
            if(count($themen) > 6) { //3 Spalten
                $template->set_attribute('themen_rows', (count($themen)%3==0)? count($themen)/3 : (count($themen)/3)+1);
            } elseif(count($themen) > 2) { //2 Spalten
                $template->set_attribute('themen_rows', 2);
            } else { //1 Spalte
                $template->set_attribute('themen_rows', 1);
            }

            $results = array();
            $thema = array();
            foreach ($themen as $tt) {
                $thema['thema'] = $tt;
                if($this->perm->have_perm($tt->getPerm(), $this->user->id) ||  $this->perm->have_perm('root')) {
                    $thema['permission'] = true;
                }
                $thema['artikel'] = array();
                $thema['last_thema_user_date'] = DBManager::get()->query("SELECT MAX(sv.last_visitdate) FROM sb_visits AS sv LEFT JOIN sb_artikel AS sa ON sv.object_id = sa.artikel_id WHERE sv.user_id='{$this->user->id}' AND sa.thema_id = '{$tt->getThemaId()}'")->fetchColumn();
                $thema['countArtikel'] = $tt->getArtikelCount();
                array_push($results, $thema);
            }
            $template->set_attribute('results', $results);

            $newOnes = $this->getLastArtikel();
            if (count($newOnes) > 0) {
                foreach($newOnes as $a) {
                    $lastArtikel[] = $this->showArtikel($a, 'show_lastartikel');
                }
                $template->set_attribute('lastArtikel', $lastArtikel);
            }
        }
        echo $template->render();
    }

    /**
     * Zeigt eine Anzeige an (wird per ajax geholt)
     *
     * @param Object $a eine Anzeige
     * @param string $template
     */
    private function showArtikel($a, $template = 'show_artikel')
    {
        $template = $this->template_factory->open($template);
        $template->set_attribute('zeit', $this->zeit);
        $template->set_attribute('a', $a);
        $template->set_attribute('anzahl', $this->getArtikelLookups($a->getArtikelId()));
        $template->set_attribute('pluginpfad', $this->getPluginURL());
        $template->set_attribute('pfeil', ($this->hasVisited($a->getArtikelId()) ? "blue" : "red"));
        $template->set_attribute('pfeil_runter', "forumgraurunt");
        //benutzer und root extrafunktionen anzeigen
        if($a->getUserId() == $this->user->id || $this->perm->have_perm('root'))
        {
            $template->set_attribute('access', true);
            $template->set_attribute('link_delete', PluginEngine::getURL($this, array("artikel_id"=>$a->getArtikelId()), 'deleteArtikel'));
            $template->set_attribute('link_edit', PluginEngine::getURL($this, array("thema_id"=>$a->getThemaId(), "artikel_id"=>$a->getArtikelId()), 'editArtikel'));
        }
        // oder einen antwortbutton
        if($a->getUserId() != $this->user->id)
        {
            $template->set_attribute('antwort', true);
        }
        $template->set_attribute('link_search', PluginEngine::getURL($this, array("modus"=>"show_search_results")));
        $template->set_attribute('link_back', PluginEngine::getURL($this, array()));
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
        $result = DBManager::get()->query("SELECT artikel_id FROM sb_artikel "
                      ."WHERE UNIX_TIMESTAMP() < (mkdate + {$this->zeit}) "
                      ."AND visible=1 ORDER BY mkdate DESC "
                      ."LIMIT {$this->announcements}")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($result as $artikel_id) {
            $ret[] = new Artikel($artikel_id);
        }
        return $ret;
    }

    /**
     *
     * @param md5 $user_id
     */
    private function isBlacklisted($user_id)
    {
        return DBManager::get()->query("SELECT 1 FROM sb_blacklist WHERE user_id = '{$user_id}'")->fetchColumn();
    }

    /**
     *
     */
    function ajaxDispatch_action()
    {
        if($this->perm->have_perm('user')) {
            $obj_id = Request::get('objid');
            $thema_id = Request::get('thema_id');
            //Artikel
            if ($obj_id){
                $oid = DBManager::get()->quote($obj_id );
                $uid = DBManager::get()->quote($GLOBALS['user']->id);
                DBManager::get()->exec("REPLACE INTO sb_visits SET object_id=$oid, user_id=$uid, type='artikel', last_visitdate=UNIX_TIMESTAMP()");
                $a = new Artikel($obj_id);
                Header('Content-Type: text/html; charset=windows-1252');
                echo $this->showArtikel($a, 'artikel_content');
                //nach dem verändern der themen, muss auch der cache geleert werden
                StudipCacheFactory::getCache()->expire(self::ARTIKEL_CACHE_KEY.$a->getThemaId());
            }
            //thema
            if($thema_id){
                $tt = $thema['thema'] = new Thema($thema_id);
                if($this->perm->have_perm($tt->getPerm(), $this->user->id) ||  $this->perm->have_perm('root')) {
                    $thema['permission'] = true;
                }
                $thema['artikel'] = array();
                $artikel = $this->getArtikel($tt->getThemaId());
                foreach($artikel as $a)
                {
                    array_push($thema['artikel'], $this->showArtikel($a));
                }
                $tt->setArtikelCount(count($artikel));
                $template = $this->template_factory->open('themen_artikel');
                $template->set_attribute('pluginpfad', $this->getPluginURL());
                $template->set_attribute('link_artikel', PluginEngine::getURL($this, array(), 'editArtikel'));
                $template->set_attribute('result', $thema);
                if ($this->isBlacklisted($this->user->id)) {
                    $template->blacklisted = true;
                }
                Header('Content-Type: text/html; charset=windows-1252');
                echo $template->render();
            }
         }
    }

    /**
     * unschön aber erstmal duplikation der createQuestion mit anpassung für plugins.
     *
     * @param $question
     * @param $approvalCmd
     */
    function createQuestion($question, $approvalCmd, $link = 'show')
    {
        $msg = $GLOBALS['template_factory']->open('shared/question');
        $msg->question = $question;
        $msg->approvalLink = PluginEngine::getURL($this, $approvalCmd, $link);
        $msg->disapprovalLink = PluginEngine::getURL($this);
        echo $msg->render();
    }
}
