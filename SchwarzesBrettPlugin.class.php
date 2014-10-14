<?php
/**
 * SchwarzesBrettPlugin.class.php
 *
 * Plugin zum Verwalten von Schwarzen Brettern (Angebote und Gesuche)
 *
 * Diese Datei enth�lt die Hauptklasse des Plugins
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author      Jan Kulmann <jankul@zmml.uni-bremen.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @author      Daniel Kabel <daniel.kabel@me.com>
 * @package     IBIT_SchwarzesBrettPlugin
 * @copyright   2008-2014 IBIT und ZMML
 * @license     http://www.gnu.org/licenses/gpl.html GPL Licence 3
 * @version     2.5
 */

// IMPORTS
require_once 'bootstrap.inc.php';

/**
 * SchwarzesBrettPlugin Hauptklasse
 *
 */
class SchwarzesBrettPlugin extends StudIPPlugin implements SystemPlugin
{
    public $zeit, $announcements;
    private $template_factory, $layout, $layout_infobox;

    const THEMEN_CACHE_KEY = 'plugins/SchwarzesBrettPlugin/themen';
    const ARTIKEL_CACHE_KEY = 'plugins/SchwarzesBrettPlugin/artikel/';
    const ARTIKEL_PUBLISHABLE_CACHE_KEY = 'plugins/SchwarzesBrettPlugin/artikel/publishable';

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->template_factory = new Flexi_TemplateFactory(dirname(__FILE__).'/templates');
        $this->layout           =  $GLOBALS['template_factory']->open('layouts/base_without_infobox');
        $this->layout_infobox   =  $GLOBALS['template_factory']->open('layouts/base');

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

        //menu nur anzeigen, wenn eingeloggt
        if($GLOBALS['perm']->have_perm('user')) {
            //Navigation
            $nav = new AutoNavigation(_('Schwarzes Brett'), PluginEngine::getURL($this, array()));
			$nav->setImage( 'icons/28/lightblue/billboard.png',array('title' => _('Schwarzes Brett'), "@2x" => TRUE));

            // Sachen in den Header laden (bis 1.11)
            PageLayout::addScript($this->getPluginURL().'/js/schwarzesbrett.js');

            //navigation
            $user_nav =new AutoNavigation(_('Schwarzes Brett'), PluginEngine::getURL($this, array(), 'show'));
            $user_nav->addSubNavigation('show', new AutoNavigation(_('�bersicht'), PluginEngine::getURL($this, array(), 'show')));
            //wenn auf der blacklist, darf man keine artikel mehr erstellen
            if (!$this->isBlacklisted($GLOBALS['user']->id)) {
                if (Artikel::hasOwn($GLOBALS['user']->id, $this->zeit)) {
                    $user_nav->addSubNavigation('own', new AutoNavigation(_('Meine Anzeigen'), PLuginEngine::getURL($this, array(), 'ownArtikel')));
                }
                $user_nav->addSubNavigation('add', new AutoNavigation(_('Anzeige erstellen'), PluginEngine::getURL($this, array(), 'editArtikel')));
            }
            $nav->addSubNavigation('show', $user_nav);

            //zusatzpunkte f�r root
            if ($GLOBALS['perm']->have_perm('root')) {
                $this->root = true;
                $root_nav = new AutoNavigation(_('Administration'), PluginEngine::getURL($this, array(), 'settings'));
                $root_nav->addSubNavigation('settings', new AutoNavigation(_('Grundeinstellungen'), PluginEngine::getURL($this, array(), 'settings')));
                $root_nav->addSubNavigation('addBlock', new AutoNavigation(_('Neues Thema anlegen'), PluginEngine::getURL($this, array(), 'editThema')));
                $root_nav->addSubNavigation('blacklist', new AutoNavigation(_('Benutzer-Blacklist'), PluginEngine::getURL($this, array(), 'blacklist')));
                $root_nav->addSubNavigation('duplicates', new AutoNavigation(_('Doppelte Eintr�ge suchen'), PluginEngine::getURL($this, array(), 'searchDuplicates')));

                if (!$this->hasActiveCronjob()) {
                    $olds = Artikel::countExpired($this->zeit);
                    if ($olds > 0) {
                        $root_nav->addSubNavigation('delete', new AutoNavigation(_('Datenbank bereinigen ('.$olds.' alte Eintr�ge)'), PluginEngine::getURL($this, array(), 'deleteOldArtikel')));
                    }
                }
                $nav->addSubNavigation('root', $root_nav);
            }
            Navigation::addItem('/schwarzesbrettplugin', $nav);
        }
    }

    public function initialize()
    {
        PageLayout::setTitle(_('Schwarzes Brett'));
        $this->addStylesheet('assets/schwarzesbrett.css');
    }

    public function getPluginname()
    {
        return _('Schwarzes Brett');
    }

    /**
     * @return  Grafik, die in der Hauptnavigation angezeigt wird
     */
    private function hasNewArticles()
    {
        $query = "SELECT MAX(last_visitdate) FROM sb_visits WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($GLOBALS['user']->id));
        $last_visitdate = $statement->fetchColumn();

        $query = "SELECT COUNT(*) FROM sb_artikel WHERE mkdate > ? AND visible = 1";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($last_visitdate));
        $last_artikel = $statement->fetchColumn();

        return $last_artikel > 0 ? 'new' : '';
    }

    /**
     * Hauptfunktion, dient in diesem Plugin als Frontcontroller und steuert die Ausgaben
     *
     */
    public function show_action()
    {
        if ($GLOBALS['perm']->have_perm('user')) {
            //Suchergebnisse abfragen und anzeigen, falls vorhanden
            if (Request::get('modus') == "show_search_results") {
                $this->search();
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
            $a->setPublishable(Request::get('publishable', 0));

            //keine thema
            if(Request::get('thema_id') == 'nix') {
                PageLayout::postMessage(MessageBox::error("Bitte w�hlen Sie ein Thema aus, in dem die Anzeige angezeigt werden soll."));
            //doppelter eintrag
            } elseif($this->isDuplicate(Request::get('titel')) && !Request::get('artikel_id')) {
                PageLayout::postMessage(MessageBox::error("Sie haben bereits einen Artikel mit diesem Titel erstellt. Bitte beachten Sie die Nutzungshinweise!"));
            //speichern
            } elseif (Request::get('titel') && Request::get('beschreibung')) {
                $a->save();
                PageLayout::postMessage(MessageBox::success("Die Anzeige wurde erfolgreich gespeichert."));
                //nach dem ver�ndern der themen, muss auch der cache geleert werden
                StudipCacheFactory::getCache()->expire(self::ARTIKEL_CACHE_KEY.$a->getThemaId());
                StudipCacheFactory::getCache()->expire(self::ARTIKEL_PUBLISHABLE_CACHE_KEY.$a->getThemaId());
                StudipCacheFactory::getCache()->expire(self::ARTIKEL_PUBLISHABLE_CACHE_KEY.'all');
                StudipCacheFactory::getCache()->expire(self::THEMEN_CACHE_KEY);
                $this->show_action();
                return;
            //kein titel und beschreibung
            } else {
                PageLayout::postMessage(MessageBox::error("Bitte geben Sie einen Titel und eine Beschreibung an."));
            }
        //keine rechte
        } elseif(Request::submitted('speichern') && !$this->getThemaPermission(Request::get('thema_id'))) {
            PageLayout::postMessage(MessageBox::error("Sie haben nicht die erforderlichen Rechte eine Anzeige zu erstellen."));
        }

        //Ausgabe
        $template = $this->template_factory->open('edit_artikel');
        $template->set_layout($this->layout_infobox);
        $template->thema_id   = Request::get('thema_id');
        $template->themen     = $this->getThemen();
        $template->a          = $a;
        $template->zeit       = $this->zeit;
        $template->link       = PluginEngine::getURL($this, array(), 'show');
        $template->link_thema = PluginEngine::getURL($this, array(), 'editArtikel');

        //Infobox
        $template->infobox = array(
            'picture' => 'infobox/contract.jpg',
            'content' => array(array(
                "kategorie" => _("Information:"),
                "eintrag" => array(
                    array("icon" => 'icons/16/black/info.png',
                    "text" => 'Bitte stellen Sie Artikel nur in eine Kategorie ein!'),
                    array("icon" => 'icons/16/black/info.png',
                    "text" => 'Bitte entfernen Sie Anzeigen, sobald diese nicht mehr aktuell sind.'),
                    array("icon" => 'icons/16/black/info.png',
                    "text" => 'Unter der Beschreibung wird automatisch ein Link zu Ihrer Benutzerhomepage eingebunden.'),
                    array("icon" => 'icons/16/black/info.png',
                    "text" => 'Andere Nutzer k�nnen direkt �ber einen Button antworten. Diese Nachrichten erhalten Sie als Stud.IP interne Post!'),
                    array("icon" => 'icons/16/black/info.png',
                    "text" => 'Wird ein Gegenstand oder eine Dienstleistung gegen Bezahlung angeboten, sollte der Betrag genannt werden, um unn�tige Nachfragen zu vermeiden.'),
                    array("icon" => 'icons/16/black/info.png',
                    "text" => 'Kleinanzeigen, die der Werbung f�r ein Produkt, eine Dienstleistung oder ein Unternehmen dienen, sind nicht gestattet.'),
                    array("icon" => 'icons/16/black/info.png',
                    "text" => 'Es ist weiterhin nicht gestattet Suchtmittel, Waffen, Arzneimittel sowie indizierte Spiele oder Filme anzubieten.'),
                    array("icon" => 'icons/16/black/info.png',
                    "text" => 'Jede Anzeige, die gegen diese Nutzungsordnung verst��t, wird umgehend entfernt.')
                )
            ))
        );
        echo $template->render();
    }

    /**
     * Zeigt das Formular zum Erstellen oder Bearbeiten von Themen an.
     * Nur f�r root
     */
    public function editThema_action()
    {
        if ($GLOBALS['perm']->have_perm('root')) {
            $t = new Thema(Request::get('thema_id'));

            // Speichern
            if (Request::get('modus') == "save_thema") {
                if (Request::get('titel')) {
                    $t->setTitel(Request::get('titel'));
                    $t->setBeschreibung(Request::get('beschreibung'));
                    $t->setPerm(Request::get('thema_perm'));
                    $t->setVisible(Request::get('visible', 0));
                    $t->save();

                    PageLayout::postMessage(MessageBox::success("Das Thema wurde erfolgreich gespeichert."));
                    //nach dem ver�ndern der themen, muss auch der cache geleert werden
                    StudipCacheFactory::getCache()->expire(self::THEMEN_CACHE_KEY);
                    StudipCacheFactory::getCache()->expire(self::ARTIKEL_PUBLISHABLE_CACHE_KEY.$t->getThemaId());
                    StudipCacheFactory::getCache()->expire(self::ARTIKEL_PUBLISHABLE_CACHE_KEY.'all');
                    $this->showThemen();
                    return;
                } else {
                    PageLayout::postMessage(MessageBox::error("Bitte geben Sie einen Titel (Pflichtfeld) ein."));
                }
            }

            // Ausgabe
            $template = $this->template_factory->open('edit_thema');
            $template->set_layout($this->layout);
            $template->t         = $t;
            $template->link      = PluginEngine::getURL($this, array(), 'editThema');
            $template->link_exit = PluginEngine::getURL($this, array(), 'show');
            echo $template->render();
        }
    }

    /**
     * Man kann mit dieser Funktion alle veralteten Artikel aus der DB l�schen
     * Nur f�r Root
     *
     */
    public function deleteOldArtikel_action()
    {
        Navigation::removeItem('/schwarzesbrettplugin/root/delete');
        Navigation::activateItem('/schwarzesbrettplugin/show');
        if ($GLOBALS['perm']->have_perm('root')) {
            $artikel = Artikel::getExpired($this->zeit);

            if (count($artikel) > 0) {
                foreach ($artikel as $id) {
                    $a = new Artikel($id);
                    $a->delete();
                }

                PageLayout::postMessage(MessageBox::success("Es wurden erfolgreich <em>".count($artikel)."</em> Artikel aus der Datenbank gel�scht."));
            } else {
                PageLayout::postMessage(MessageBox::info("Es gibt keine Artikel in der Datenbank, die gel�scht werden k�nnen."));
            }
        }
        $this->showThemen();
    }

    /**
     * L�scht ein Thema mit all seinen Anzeigen inkl. Sicherheitsabfrage
     * Nur f�r Root
     */
    public function deleteThema_action()
    {
        Navigation::activateItem('/schwarzesbrettplugin/show');
        if ($GLOBALS['perm']->have_perm('root')) {
            //Thema l�schen Sicherheitsabfrage
            if (Request::get('modus') == "delete_thema_really") {
                $t = new Thema(Request::get('thema_id'));
                $t->delete();
                PageLayout::postMessage(MessageBox::success("Das Thema und alle dazugeh�rigen Anzeigen wurden erfolgreich gel�scht."));
                //nach dem ver�ndern der themen, muss auch der cache geleert werden
                StudipCacheFactory::getCache()->expire(self::THEMEN_CACHE_KEY);
                StudipCacheFactory::getCache()->expire(self::ARTIKEL_PUBLISHABLE_CACHE_KEY.'all');
            } else {
                $t = new Thema(Request::get('thema_id'));
                echo $this->createQuestion('Soll das Thema **'.$t->getTitel().'** wirklich gel�scht werden?', array("modus"=>"delete_thema_really", "thema_id"=>$t->getThemaId()), 'deleteThema');
            }
        }
        $this->showThemen();
    }

    /**
     *  L�scht eine Anzeige inkl. Sicherheitsabfrage
     */
    public function deleteArtikel_action()
    {
        Navigation::activateItem('/schwarzesbrettplugin/show');
        $a = new Artikel(Request::get('artikel_id'));

        //Artikel l�schen Sicherheitsabfrage
        if (Request::get('modus') == "delete_artikel_really") {
            //Root l�scht Artikel eines Benutzers, also diesen benachrichtigen.
            if ($a->getUserId() != $GLOBALS['user']->id && $GLOBALS['perm']->have_perm('root')) {
                $messaging = new messaging();
                $msg = sprintf(_("Die Anzeige \"%s\" wurde von den System-Administratoren gel�scht.\n\n Bitte beachten Sie die Nutzungsordnung zum Erstellen von Anzeigen (Mehrfaches Einstellen ist nicht erlaubt). Bei wiederholtem Versto� k�nnen Sie gesperrt werden."), $a->getTitel());
                $messaging->insert_message($msg, get_username($a->getUserId()), "____%system%____", FALSE, FALSE, 1, FALSE, "Schwarzes Brett: Anzeige gel�scht!");
            }
            $a->delete();
            PageLayout::postMessage(MessageBox::success("Die Anzeige wurde erfolgreich gel�scht."));
            //nach dem ver�ndern der themen, muss auch der cache geleert werden
            $cache = StudipCacheFactory::getCache();
            $cache->expire(self::ARTIKEL_CACHE_KEY.$a->getThemaId());
            $cache->expire(self::THEMEN_CACHE_KEY);
            $cache->expire(self::ARTIKEL_PUBLISHABLE_CACHE_KEY.$a->getThemaId());
            $cache->expire(self::ARTIKEL_PUBLISHABLE_CACHE_KEY.'all');
        } elseif ($a->getUserId() == $GLOBALS['user']->id || $GLOBALS['perm']->have_perm('root')) {
            echo $this->createQuestion('Soll die Anzeige **'.$a->getTitel().'** von %%'.get_fullname($a->getUserId()).'%% wirklich gel�scht werden?', array("modus"=>"delete_artikel_really", "artikel_id"=>$a->getArtikelId()), 'deleteArtikel');
        } else {
            PageLayout::postMessage(MessageBox::error("Sie haben nicht die Berechtigung diese Anzeige zu l�schen."));
        }
        $this->showThemen();
    }

    public function blacklist_action()
    {
        if ($GLOBALS['perm']->have_perm('root')) {
            $template = $this->template_factory->open('blacklist');
            $template->set_layout($this->layout);

            if (Request::get('action') == 'delete'){
                $db = DBManager::get()->prepare("DELETE FROM sb_blacklist WHERE user_id = ?");
                $db->execute(array(Request::option('user_id')));

                $template->message = MessageBox::success(_('Der Benutzer wurde erfolgreich von der Blacklist entfernt und kann nun wieder Anzeigen erstellen.'));
            } elseif (Request::get('action') == 'add' && Request::option('user_id')) {
                //datenbank
                $db = DBManager::get()->prepare("REPLACE INTO sb_blacklist SET user_id = ?, mkdate = UNIX_TIMESTAMP()");
                $db->execute(array(Request::option('user_id')));

                                //nachricht an den benutzer
                $messaging = new messaging();
                $msg = _("Aufgrund von wiederholten Verst��en gegen die Nutzungsordnung wurde Ihr Zugang zum Schwarzen Brett gesperrt. Sie k�nnen keine weiteren Anzeigen erstellen.\n\n Bei Fragen wenden Sie sich bitte an die Systemadministratoren.");
                $messaging->insert_message($msg, get_username(Request::option('user_id')), "____%system%____", FALSE, FALSE, 1, FALSE, "Schwarzes Brett: Sie wurden gesperrt.");

                $template->message = MessageBox::success(_('Der Benutzer wurde erfolgreich auf die Blacklist gesetzt.'));
            }

            $users = DBManager::get()
                   ->query("SELECT * FROM sb_blacklist")
                   ->fetchAll(PDO::FETCH_ASSOC);

            $template->users = $users;
            $template->link  = PluginEngine::getURL($this, array(), 'blacklist');
            echo $template->render();
        }
    }

    public function searchDuplicates_action()
    {
        $template = $this->template_factory->open('duplicates');
        $template->set_layout($this->layout);

        $results = DBManager::get()
                 ->query("SELECT user_id, count(user_id) FROM sb_artikel s GROUP BY user_id HAVING count(user_id) > 1")
                 ->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $i => $result) {
            $query = "SELECT a.*, t.titel AS thema "
                   . "FROM sb_artikel AS a "
                   . "LEFT JOIN sb_themen AS t USING(thema_id) "
                   . "WHERE a.user_id = ? "
                   . "ORDER BY a.mkdate DESC";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($result['user_id']));
            $results[$i]['artikel'] = $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        $template->results     = $results;
        $template->link        = PluginEngine::getURL($this, array(), 'show');
        $template->link_edit   = PluginEngine::getURL($this, array(), 'editArtikel');
        $template->link_delete = PluginEngine::getURL($this, array(), 'deleteArtikel');
        echo $template->render();
    }

    /**
     * Gibt alle Anzeigen zu einem Thema zur�ck
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

        if (!empty($ret)) {
            return $ret;
        }

        $ret = array();

        $query = "SELECT artikel_id "
               . "FROM sb_artikel "
               . "WHERE thema_id = ? AND UNIX_TIMESTAMP() < mkdate + ? "
               .   "AND (visible = 1 OR (user_id = ? OR 'root' = ?)) "
               . "ORDER BY mkdate DESC";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array(
            $thema_id, $this->zeit, $GLOBALS['user']->id,
            $GLOBALS['perm']->get_perm($GLOBALS['user']->id),
        ));
        $artikel_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        foreach ($artikel_ids as $artikel_id) {
            $ret[] = new Artikel($artikel_id);
        }

        $cache->write(self::ARTIKEL_CACHE_KEY.$thema_id, serialize($ret));

        return $ret;
    }

    /**
     * Gibt die Anzahl Anzeigen f�r ein Thema zur�ck
     *
     * @param md5 $thema_id
     * @return int
     */
    private function getArtikelCount($thema_id)
    {
        $query = "SELECT COUNT(*) "
               . "FROM sb_artikel "
               . "WHERE thema_id = ? AND UNIX_TIMESTAMP() < mkdate + ? "
               .   "AND (visible = 1 OR (user_id = ? OR 'root' = ?))";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array(
            $thema_id, $this->zeit, $GLOBALS['user']->id,
            $GLOBALS['perm']->get_perm($this->user->user_id),
        ));
        return $statement->fetchColumn();
    }

    /**
     * Gibt die Anzahl Besucher eines Artikels zur�ck.
     *
     * @param string $artikel_id
     * @return int Anzahl Besucher
     */
    private function getArtikelLookups($artikel_id)
    {
        $query = "SELECT COUNT(*) FROM sb_visits WHERE type = 'artikel' AND object_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($artikel_id));
        return $statement->fetchColumn();
    }

    /**
     * Gibt eine Liste aller Themen aus der Datenbank zur�ck, die sichtbar sind
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
                    ."WHERE t.visible=1 OR t.user_id='{$GLOBALS['user']->id}' "
                    ."OR 'perm'='{$GLOBALS['perm']->get_perm($GLOBALS['user']->id)}' "
                    ."GROUP BY t.thema_id ORDER BY t.titel")->fetchAll(PDO::FETCH_ASSOC);
            $ret = array();
            foreach ($themen as $thema) {
                $t = new Thema($thema['thema_id']);
                $t->setArtikelCount($this->getArtikelCount($thema['thema_id']));
                $ret[] = $t;
            }
            $cache->write(self::THEMEN_CACHE_KEY, serialize($ret), 3600);
        }
        return $ret;
    }

    /**
     * Gibt die Benutzerrechte eines Themas zur�ck
     *
     * @param string $thema_id
     * @return string $permission
     */
    private function getThemaPermission($thema_id)
    {
        if ($thema_id == 'nix') {
            return true;
        }

        $query = "SELECT perm FROM sb_themen WHERE thema_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($thema_id));
        $perm = $statement->fetchColumn();

        return $GLOBALS['perm']->have_perm($perm);
    }

    /**
     * �berpr�ft, ob eine Anzeige bereits vorhanden ist, dabei werden
     * Titel, UserID und Datum verglichen.
     *
     * @param string $titel
     * @return boolean
     */
    private function isDuplicate($titel)
    {
        $query = "SELECT count(artikel_id) "
               . "FROM sb_artikel "
               . "WHERE user_id = ? AND titel = ? AND mkdate > UNIX_TIMESTAMP() - 60 * 60 * 24";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($GLOBALS['user']->id, $titel));
        $check = $statement->fetch(PDO::FETCH_COLUMN);

        return $check > 1;
    }

    /**
     * �berpr�ft, ob der Benutzer dieses Objekt (Thema oder Artikel) bereits angesehen hat.
     *
     * @param string $obj_id
     * @return datetime oder boolean
     */
    private function hasVisited($obj_id)
    {
        $query = "SELECT last_visitdate "
               . "FROM sb_visits "
               . "WHERE object_id = ? AND user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($obj_id, $GLOBALS['user']->id));
        $last_visitdate = $statement->fetchColumn();

        return !empty($last_visitdate) ? $last_visitdate : false;
    }

    /**
     * F�hrt die Suche nach Anzeigen durch und zeigt die Ergebnisse an.
     *
     * @param String $search_text Suchwort
     */
    private function search()
    {
        if (Request::get('search_user') && $GLOBALS['perm']->get_perm($GLOBALS['user']->id) == 'root') {
            //Datenbankabfrage
            $user = Request::get('search_user');
            $query = "SELECT a.thema_id, a.artikel_id, a.titel, t.titel AS t_titel "
                   . "FROM sb_artikel AS a, sb_themen AS t "
                   . "WHERE t.thema_id = a.thema_id AND a.user_id = ? "
                   . "ORDER BY t.titel, a.titel";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($user));
        } else {
            $search_text = Request::get('search_text');
            //Benutzereingaben abfangen (W�rter k�rzer als 3 Zeichen)
            if ((empty($search_text) || strlen($search_text) < 3) && !Request::get('search_user'))
            {
                PageLayout::postMessage(MessageBox::error("Ihr Suchwort ist zu kurz, bitte versuchen Sie es erneut!"));
                $this->showThemen();
                return;
            }

            $query = "SELECT a.thema_id, a.artikel_id, a.titel, t.titel AS t_titel "
                   . "FROM sb_artikel AS a, sb_themen AS t "
                   . "WHERE t.thema_id = a.thema_id "
                   .   "AND (UPPER(a.titel) LIKE CONCAT('%', UPPER(?), '%') "
                   .     "OR UPPER(a.beschreibung) LIKE CONCAT('%', UPPER(?), '%')) "
                   .   "AND UNIX_TIMESTAMP() < a.mkdate + ? "
                   .   "AND (a.visible = 1 OR (a.user_id = ? OR 'root' = ?)) "
                   . "ORDER BY t.titel, a.titel";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array(
                $search_text, $search_text,
                $this->zeit, $GLOBALS['user']->id,
                $GLOBALS['perm']->get_perm($GLOBALS['user']->id),
            ));

        }

        $dbresults = $statement->fetchAll(PDO::FETCH_ASSOC);

        // keine Ergebnisse vorhanden
        if(count($dbresults) == 0) {
            PageLayout::postMessage(MessageBox::error("Es wurden f�r <em>" . htmlReady($search_text) . "</em> keine Ergebnisse gefunden."));
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
                $results[] = $thema;

                $thema = array();
                $thema['thema_id'] = $result['thema_id'];
                $thema['thema_titel'] = htmlReady($result['t_titel']);
                $thema['artikel'] = array();
            }
            $thema['artikel'][] = $this->showArtikel($a);
        }
        $results[] = $thema;

        //Ausgabe erzeugen
        $template = $this->template_factory->open('search_results');
        $template->set_layout($this->layout);
        $template->zeit        = $this->zeit;
        $template->pluginpfad  = $this->getPluginURL();
        $template->link_search = PluginEngine::getURL($this, array("modus"=>"show_search_results"));
        $template->link_back   = PluginEngine::getURL($this, array(), 'show');
        $template->results     = $results;
        echo $template->render();
    }

    public function ownArtikel_action()
    {
        $query = "SELECT a.thema_id, a.artikel_id, a.titel, t.titel AS t_titel
                  FROM sb_artikel AS a, sb_themen AS t
                  WHERE t.thema_id = a.thema_id AND a.user_id = :user_id AND a.mkdate + :lifetime >= UNIX_TIMESTAMP()
                  ORDER BY t.titel, a.titel";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $GLOBALS['user']->id);
        $statement->bindValue(':lifetime', $this->zeit);
        $statement->execute();

        $dbresults = $statement->fetchAll(PDO::FETCH_ASSOC);

        // keine Ergebnisse vorhanden
        if(count($dbresults) == 0) {
            PageLayout::postMessage(MessageBox::error(_('Sie haben momentan keine eigenen Anzeigen.')));
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
                $results[] = $thema;

                $thema = array();
                $thema['thema_id'] = $result['thema_id'];
                $thema['thema_titel'] = htmlReady($result['t_titel']);
                $thema['artikel'] = array();
            }
            $thema['artikel'][] = $this->showArtikel($a);
        }
        $results[] = $thema;

        //Ausgabe erzeugen
        $template = $this->template_factory->open('search_results');
        $template->set_layout($this->layout);
        $template->zeit        = $this->zeit;
        $template->pluginpfad  = $this->getPluginURL();
        $template->link_search = PluginEngine::getURL($this, array("modus"=>"show_search_results"));
        $template->link_back   = PluginEngine::getURL($this, array(), 'show');
        $template->results     = $results;
        echo $template->render();
    }

    /**
     * Zeigt alle Themen und Anzeigen an
     *
     */
    private function showThemen()
    {
        $themen = $this->getThemen();

        if ($this->isBlacklisted($GLOBALS['user']->id)) {
            PageLayout::postMessage(MessageBox::info(_('Sie wurden gesperrt und k�nnen daher keine Anzeigen erstellen. Bitte wenden Sie sich an den Systemadministrator.')));
        }

        $template = $this->template_factory->open('show_themen');
        $template->set_layout($this->layout);
        $template->zeit            = $this->zeit;
        $template->pluginpfad      = $this->getPluginURL();
        $template->link_edit       = PluginEngine::getURL($this, array(), 'editThema');
        $template->link_artikel    = PluginEngine::getURL($this, array(), 'editArtikel');
        $template->link_delete     = PluginEngine::getURL($this, array(), 'deleteThema');
        $template->link_rss        = PluginEngine::getURL($this, array(), 'rss');
        $template->link_search     = PluginEngine::getURL($this, array("modus" => "show_search_results"));
        $template->link_back       = PluginEngine::getURL($this, array());
        $template->link_visit      = PluginEngine::getURL($this, array(), 'visitAll');
        $template->last_visit_date = $this->last_visitdate;
        $template->root            = $this->root;
        $template->enableRss       = Config::get()->BULLETIN_BOARD_ENABLE_RSS;

	$template->newArticles = false;
        //Keine themen vorhanden
        if (count($themen) == 0) {
            $template->keinethemen = true;
        }
        //themen anzeigen
        else {
            //Anzahl Themen pro Spalte berechnen
            if(count($themen) > 6) { //3 Spalten
                $template->themen_rows = ceil(count($themen) / 3);
            } elseif(count($themen) > 2) { //2 Spalten
                $template->themen_rows = 2;
            } else { //1 Spalte
                $template->themen_rows = 1;
            }

            //
            $query = "SELECT MAX(IFNULL(sv.last_visitdate, 0)) < MAX(mkdate) "
                   . "FROM sb_artikel AS sa "
                   . "LEFT JOIN sb_visits AS sv ON (sv.object_id IN (sa.thema_id, sa.artikel_id)  AND sv.user_id = ?) "
                   . "WHERE sa.thema_id = ? AND UNIX_TIMESTAMP() < sa.mkdate + :expire";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':expire', $this->zeit);

            $results = array();
            $thema = array();
            foreach ($themen as $tt) {
                $thema['thema'] = $tt;
                if($GLOBALS['perm']->have_perm($tt->getPerm(), $GLOBALS['user']->id) ||  $GLOBALS['perm']->have_perm('root')) {
                    $thema['permission'] = true;
                }
                $thema['artikel'] = array();
                $thema['countArtikel'] = $tt->getArtikelCount();

                $statement->execute(array($GLOBALS['user']->id, $tt->getThemaId()));
                $thema['newArticles'] = $statement->fetchColumn();
                $statement->closeCursor();

                $template->newArticles = $template->newArticles || $thema['newArticles'];

                $results[] = $thema;
            }
            $template->results = $results;

            $newOnes = $this->getLastArtikel();
            if (count($newOnes) > 0) {
                foreach($newOnes as $a) {
                    $lastArtikel[] = $this->showArtikel($a, 'show_lastartikel');
                }
                $template->lastArtikel = $lastArtikel;
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
        $template->zeit         = $this->zeit;
        $template->a            = $a;
        $template->anzahl       = $this->getArtikelLookups($a->getArtikelId());
        $template->pluginpfad   = $this->getPluginURL();
        $template->pfeil        = $this->hasVisited($a->getArtikelId()) ? 'blue' : 'red';
        $template->pfeil_runter = "forumgraurunt";
        //benutzer und root extrafunktionen anzeigen
        if($a->getUserId() == $GLOBALS['user']->id || $GLOBALS['perm']->have_perm('root')) {
            $template->access      = true;
            $template->link_delete = PluginEngine::getURL($this, array("artikel_id"=>$a->getArtikelId()), 'deleteArtikel');
            $template->link_edit   = PluginEngine::getURL($this, array("thema_id"=>$a->getThemaId(), "artikel_id"=>$a->getArtikelId()), 'editArtikel');
        }
        // oder einen antwortbutton
        if($a->getUserId() != $GLOBALS['user']->id) {
            $template->antwort     = true;
            $template->enableBlame = get_config('BULLETIN_BOARD_ENABLE_BLAME');
            $template->link_blame  = PluginEngine::getURL($this, array("thema_id"=>$a->getThemaId(), "artikel_id"=>$a->getArtikelId()), 'blameArtikel');
        }
        $template->link_search = PluginEngine::getURL($this, array("modus"=>"show_search_results"));
        $template->link_back   = PluginEngine::getURL($this, array());
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
        $query = "SELECT artikel_id "
               . "FROM sb_artikel "
               . "WHERE UNIX_TIMESTAMP() < mkdate + ? AND visible = 1 "
               . "ORDER BY mkdate DESC "
               . "LIMIT " . (int) $this->announcements;
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($this->zeit));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

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
        $query = "SELECT 1 FROM sb_blacklist WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($user_id));
        return $statement->fetchColumn();
    }

    /**
     *
     */
    function ajaxDispatch_action()
    {
        if($GLOBALS['perm']->have_perm('user')) {
            $obj_id = Request::get('objid');
            $thema_id = Request::get('thema_id');
            //Artikel
            if ($obj_id){
                $query = "REPLACE INTO sb_visits "
                       . "SET object_id = ?, user_id = ?, type='artikel', "
                       .     "last_visitdate = UNIX_TIMESTAMP()";
                DBManager::get()
                    ->prepare($query)
                    ->execute(array($obj_id, $GLOBALS['user']->id));

                $a = new Artikel($obj_id);
                Header('Content-Type: text/html; charset=windows-1252');
                echo $this->showArtikel($a, 'artikel_content');
                //nach dem ver�ndern der themen, muss auch der cache geleert werden
                StudipCacheFactory::getCache()->expire(self::ARTIKEL_CACHE_KEY.$a->getThemaId());
            }
            //thema
            if($thema_id){
                $query = "REPLACE INTO sb_visits "
                       . "SET object_id = ?, user_id = ?, type='thema', "
                       .     "last_visitdate = UNIX_TIMESTAMP()";
                DBManager::get()
                    ->prepare($query)
                    ->execute(array($thema_id, $GLOBALS['user']->id));

                $tt = $thema['thema'] = new Thema($thema_id);
                if($GLOBALS['perm']->have_perm($tt->getPerm(), $GLOBALS['user']->id) ||  $GLOBALS['perm']->have_perm('root')) {
                    $thema['permission'] = true;
                }
                $thema['artikel'] = array();
                $artikel = $this->getArtikel($tt->getThemaId());
                foreach($artikel as $a) {
                    $thema['artikel'][] = $this->showArtikel($a);
                }
                $tt->setArtikelCount(count($artikel));
                $template = $this->template_factory->open('themen_artikel');
                $template->pluginpfad   = $this->getPluginURL();
                $template->link_artikel = PluginEngine::getURL($this, array(), 'editArtikel');
                $template->result       = $thema;
                if ($this->isBlacklisted($GLOBALS['user']->id)) {
                    $template->blacklisted = true;
                }
                Header('Content-Type: text/html; charset=windows-1252');
                echo $template->render();
            }
         }
    }

    /**
     * unsch�n aber erstmal duplikation der createQuestion mit anpassung f�r plugins.
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

    /**
     * Managed das Melden eines Angebots
     * Zeigt einen Modalen Dialog, wird dieser best�tigt wir das Angebot gemeldet
     */
    public function blameArtikel_action()
    {
        $artikel_id = Request::get('artikel_id');
        $thema_id = Request::get('thema_id');

        Navigation::activateItem('/schwarzesbrettplugin/show');
        $a = new Artikel(Request::get('artikel_id'));

        //Artikel melden Sicherheitsabfrage
        if (Request::get('modus') == "blame_artikel_really") {
            $a->blame(Request::get('blame_reason'));
            PageLayout::postMessage(MessageBox::success("Die Anzeige wurde gemeldet"));
        } else {
            $msg = $this->template_factory->open('blame_dialog');
            $msg->question = 'Soll die Anzeige "**'.$a->getTitel().'**" von %%'.get_fullname($a->getUserId()).'%% wirklich gemeldet werden?';
            $msg->approvalLink = PluginEngine::getURL($this, array("modus"=>"blame_artikel_really", "artikel_id"=>$a->getArtikelId()), 'blameArtikel');
            $msg->disapprovalLink = PluginEngine::getURL($this);
            PageLayout::addBodyElements($msg->render());
        }
        $this->showThemen();
    }

    /**
     * Zeigt die Einstellungsseite
     */
    public function settings_action()
    {
        if ($GLOBALS['perm']->have_perm('root')) {
            $template = $this->template_factory->open('settings');
            $template->set_layout($this->layout);
            if (Request::get('action') == 'save'){
                foreach ($this->getThemen() as $thema) {
                    StudipCacheFactory::getCache()->expire(self::ARTIKEL_CACHE_KEY.$thema->getThemaId());
                    StudipCacheFactory::getCache()->expire(self::ARTIKEL_PUBLISHABLE_CACHE_KEY.$thema->getThemaId());
                }
                StudipCacheFactory::getCache()->expire(self::ARTIKEL_PUBLISHABLE_CACHE_KEY.'all');
                write_config('BULLETIN_BOARD_ANNOUNCEMENTS', Request::get('announcements'));
                write_config('BULLETIN_BOARD_DURATION', Request::get('duration'));
                write_config('BULLETIN_BOARD_BLAME_RECIPIENTS', Request::get('blameRecipients'));
                write_config('BULLETIN_BOARD_ENABLE_BLAME', (int)Request::get('enableBlame'));
                write_config('BULLETIN_BOARD_ENABLE_RSS', (int)Request::get('enableRss'));
                $template->message = MessageBox::success(_('Einstellungen gespeichert.'));
            }
            $metaAnnouncements   = Config::getInstance()->getMetadata('BULLETIN_BOARD_ANNOUNCEMENTS');
            $metaDuration        = Config::getInstance()->getMetadata('BULLETIN_BOARD_DURATION');
            $metaBlameRecipients = Config::getInstance()->getMetadata('BULLETIN_BOARD_BLAME_RECIPIENTS');
            $metaEnableBlame     = Config::getInstance()->getMetadata('BULLETIN_BOARD_ENABLE_BLAME');
            $metaEnableRss       = Config::getInstance()->getMetadata('BULLETIN_BOARD_ENABLE_RSS');
            $template->descAnnouncements   = $metaAnnouncements['description'];
            $template->descDuration        = $metaDuration['description'];
            $template->descBlameRecipients = $metaBlameRecipients['description'];
            $template->descEnableBlame     = $metaEnableBlame['description'];
            $template->descEnableRss       = $metaEnableRss['description'];
            $template->announcements       = Config::get()->BULLETIN_BOARD_ANNOUNCEMENTS;
            $template->duration            = Config::get()->BULLETIN_BOARD_DURATION;
            $template->blameRecipients     = Config::get()->BULLETIN_BOARD_BLAME_RECIPIENTS;
            $template->enableBlame         = Config::get()->BULLETIN_BOARD_ENABLE_BLAME;
            $template->enableRss           = Config::get()->BULLETIN_BOARD_ENABLE_RSS;
            $template->link                = PluginEngine::getURL($this, array(), 'settings');
            echo $template->render();
        }
    }

    public function visitAll_action()
    {
        $query = "INSERT INTO sb_visits (object_id, user_id, type, last_visitdate)
                  SELECT thema_id, :user_id, 'thema', UNIX_TIMESTAMP() FROM sb_themen
                  ON DUPLICATE KEY UPDATE last_visitdate = UNIX_TIMESTAMP()";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $GLOBALS['user']->id);
        $statement->execute();

        PageLayout::postMessage(MessageBox::success(_('Alle Themen wurden als besucht markiert')));

        header('Location: ' . PluginEngine::getLink($this, array()));
        die;
    }

    /**
     * Zeigt den RSS Feed des Schwarzen Bretts
     */
    public function rss_action()
    {
        if (get_config('BULLETIN_BOARD_ENABLE_RSS') != 1)
            return;
        global $ABSOLUTE_URI_STUDIP;
        $studipUrl = $ABSOLUTE_URI_STUDIP;
        if (substr($ABSOLUTE_URI_STUDIP, -1) === '/')
            $studipUrl = substr($studipUrl, 0, -1);

        $themaId = Request::get('thema_id', false);
        $items = '';
        foreach ($this->getPublishableArtikel($themaId) as $article) {
            $template = $this->template_factory->open('rss_artikel');
            $template->studipUrl   = $studipUrl;
            $template->title       = $article->getTitel();
            $template->description = $article->getBeschreibung();
            $template->pubDate     = $article->getMkdate();
            $template->guid        = $article->getArtikelId();
            $items .= $template->render();
        }

        $thema = '';
        $description = '';
        if ($themaId != 'all') {
            $t = new Thema($themaId);
            $thema = ' - '.$t->getTitel();
            $description = $t->getBeschreibung();
        }
        header("Content-type: text/xml; charset=utf-8");
        $template = $this->template_factory->open('rss');
        $template->selfLink    = $studipUrl . PluginEngine::getURL($this, array('thema_id' => $themaId), 'rss');
        $template->studipUrl   = $studipUrl;
        $template->title       = 'Stud.IP Schwarzes Brett' . $thema;
        $template->description = $description;
        $template->items       = $items;
        echo $template->render();
    }

    /**
     * Gibt alle Anzeigen zur�ck, die ver�ffentlicht werden d�rfen
     *
     * @uses StudipCache
     *
     * @param string $thema_id
     * @return array Anzeigen
     */
    private function getPublishableArtikel($thema_id)
    {
        $cache = StudipCacheFactory::getCache();
        $ret = unserialize($cache->read(self::ARTIKEL_PUBLISHABLE_CACHE_KEY.$thema_id));

        if (!empty($ret)) {
            return $ret;
        }

        $ret = array();

        if ($thema_id != 'all') {
            $query = "SELECT artikel_id "
                   . "FROM sb_artikel "
                   . "WHERE thema_id = ? AND UNIX_TIMESTAMP() < mkdate + ? "
                   .   "AND visible = 1 AND publishable = 1 "
                   . "ORDER BY mkdate DESC";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array(
                $thema_id, $this->zeit
            ));
        } else {
            $query = "SELECT artikel_id "
                   . "FROM sb_artikel "
                   . "WHERE UNIX_TIMESTAMP() < mkdate + ? "
                   .   "AND visible = 1 AND publishable = 1 "
                   . "ORDER BY mkdate DESC";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array(
                $this->zeit
            ));
        }

        $artikel_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        foreach ($artikel_ids as $artikel_id) {
            $ret[] = new Artikel($artikel_id);
        }

        $cache->write(self::ARTIKEL_PUBLISHABLE_CACHE_KEY.$thema_id, serialize($ret));

        return $ret;
    }

    private function hasActiveCronjob()
    {
        return Config::get()->CRONJOBS_ENABLE
            && ($tasks = CronjobTask::findByClass('SchwarzesBrettCronjob'))
            && $tasks[0]->active
            && $tasks[0]->schedules->findOneBy('active', '1');
    }
}
