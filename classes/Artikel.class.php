<?php
/**
 * Artikel.class.php
 *
 * Eine Klasse f�r die Anzeigen des schwarzen Brettes. In diesem Plugin Artikel genannt.
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author      Jan Kulmann <jankul@zmml.uni-bremen.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @package     IBIT_SchwarzesBrettPlugin
 * @copyright   2008-2010 IBIT und ZMML
 * @version     2.2
 */

/**
 * Klasse f�r Anzeigen-Objekte
 *
 */
class Artikel
{

    private $titel        = '';
    private $beschreibung = '';
    private $user_id      = '';
    private $visible      = 1;
    private $publishable  = 1;
    private $thema_id     = '';
    private $thema_titel  = '';
    private $artikel_id   = '';
    private $mkdatum      = 0;

    /**
     * Konstruktor, erstellt Artikel-Objekte
     *
     */
    public function __construct($id = null)
    {
        if (empty($id)) {
            return;
        }

        //Artikel laden
        $query = "SELECT sb_artikel.*, sb_themen.titel AS themen_titel "
               . "FROM sb_artikel "
               . "LEFT JOIN sb_themen USING(thema_id) "
               . "WHERE artikel_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($id));
        $artikel = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$artikel) {
            return;
        }

        $this->titel        = $artikel['titel'];
        $this->beschreibung = $artikel['beschreibung'];
        $this->user_id      = $artikel['user_id'];
        $this->visible      = $artikel['visible'];
        $this->publishable  = $artikel['publishable'];
        $this->thema_id     = $artikel['thema_id'];
        $this->artikel_id   = $artikel['artikel_id'];
        $this->mkdatum      = $artikel['mkdate'];
        $this->thema_titel  = $artikel['themen_titel'];
    }

    /**
     * Speichert neue und/oder bearbeite Artikel in die Datenbank
     *
     */
    public function save()
    {
        if ($this->thema_id != "" && $this->titel != "") {
            //vorhanden Artikel updaten
            if ($this->artikel_id != "") {
                $query = "UPDATE sb_artikel "
                       . "SET titel= ?, beschreibung= ?, visible= ?, publishable= ?, thema_id= ? "
                       . "WHERE artikel_id = ?";
                DBManager::get()
                    ->prepare($query)
                    ->execute(array(
                        $this->titel, $this->beschreibung, $this->visible,
                        $this->publishable, $this->thema_id, $this->artikel_id
                    ));
            }
            //Neuen Artikel speichern
            else {
                $id = md5(uniqid(time()));

                $query = "INSERT INTO sb_artikel "
                       . " (artikel_id, thema_id, titel, user_id, mkdate, beschreibung, visible, publishable) "
                       . "VALUES (?, ?, ?, ?, UNIX_TIMESTAMP(), ?, ?, ?)";
                DBManager::get()
                    ->prepare($query)
                    ->execute(array(
                        $id, $this->thema_id, $this->titel,
                        $GLOBALS['auth']->auth['uid'], $this->beschreibung,
                        $this->visible, $this->publishable
                    ));
            }
        }
    }

    /**
     * L�scht einen Artikel aus der Datenbank
     *
     */
    public function delete()
    {
        if (!empty($this->artikel_id)) {
            $query = "DELETE FROM sb_artikel WHERE artikel_id = ?";
            DBManager::get()
                ->prepare($query)
                ->execute(array($this->artikel_id));

            $query = "DELETE FROM sb_visits WHERE object_id = ?";
            DBManager::get()
                ->prepare($query)
                ->execute(array($this->artikel_id));
        }
    }

    /**
     * Sendet eine Blame-Mail
     * @param $blameReason Grund des Meldens
     */
    public function blame($blameReason)
    {
        global $ABSOLUTE_URI_STUDIP;
        $url = $ABSOLUTE_URI_STUDIP;
        if (substr($ABSOLUTE_URI_STUDIP, -1) === '/')
            $url = substr($url, 0, -1);

        $mailContent = "Folgende Anzeige wurde gemeldet:\n\n";
        $mailContent.= "Titel: ".$this->getTitel()."\n\n";
        $mailContent.= "Text: ".$this->getBeschreibung()."\n\n";
        $mailContent.= "ID: ".$this->artikel_id."\n\n";
        $mailContent.= "Autor: ".get_fullname($this->getUserId()).' ('.get_username($this->getUserId()).')'."\n\n";
        $mailContent.= "Blamer: ".get_fullname($GLOBALS['auth']->auth['uid']).' ('.get_username($GLOBALS['auth']->auth['uid']).')'."\n\n";
        $mailContent.= "Grund: ".$blameReason."\n\n";
        $mailContent.= "Anzeige Loeschen: ".$url.PluginEngine::getURL('schwarzesbrettplugin', array("artikel_id"=>$this->getArtikelId()), 'deleteArtikel')."\n\n";
        $mailContent.= "Anzeige Bearbeiten: ".$url.PluginEngine::getURL('schwarzesbrettplugin', array("artikel_id"=>$this->getArtikelId()), 'editArtikel')."\n\n";
        $mailContent.= "Antworten: ".$url.URLHelper::getLink('sms_send.php',
                                                             array(
                                                                 'rec_uname' => get_username($this->getUserId()),
                                                                 'messagesubject' => rawurlencode($this->getTitel()),
                                                                 'message' => '[quote] '.$this->getBeschreibung().' [/quote]')
                                                             )."\n\n";
        $mail = new StudipMail();
        $mail->addRecipient(get_config('BULLETIN_BOARD_BLAME_RECIPIENTS'))
             ->setSubject('Anzeige wurde gemeldet')
             ->setBodyText($mailContent)
             ->send();
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

    function setPublishable($s)
    {
        $this->publishable = $s;
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

    function getPublishable()
    {
        return $this->publishable;
    }

    /**
     * Gibt die Themen-ID zur�ck
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


    /**
     * @param  int $since Article lifetime in seconds
     * @return int Number of expired entries
     **/
    static function countExpired($since)
    {
        $query = "SELECT COUNT(artikel_id) FROM sb_artikel WHERE UNIX_TIMESTAMP() > mkdate + ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($since));
        return $statement->fetchColumn();
    }

    /**
     * @param  int $since Article lifetime in seconds
     * @return array Ids of the the expired articles
     **/
    static function getExpired($since)
    {
        $query = "SELECT artikel_id FROM sb_artikel WHERE UNIX_TIMESTAMP() > mkdate + ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($since));
        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Returns whether there are any articles for the given user in the given
     * lifetime.
     *
     * @param string $user_id  Id of the user
     * @param int    $lifetime Lifetime in seconds until an article is expired
     * @return bool True if there is at least on article from the user
     */
    public static function hasOwn($user_id, $lifetime)
    {
        $query = "SELECT 1
                  FROM sb_artikel
                  WHERE user_id = :user_id AND UNIX_TIMESTAMP() < mkdate + :lifetime";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $user_id);
        $statement->bindValue(':lifetime', $lifetime);
        $statement->execute();

        return $statement->fetchColumn();
    }
}
