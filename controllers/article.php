<?php
use SchwarzesBrett\Article;
use SchwarzesBrett\Category;
use SchwarzesBrett\User;

class ArticleController extends SchwarzesBrett\Controller
{
    public function view_action($id)
    {
        Navigation::activateItem('/schwarzesbrettplugin/show');

        $needle = Request::get('needle');
        if ($needle) {
            Article::markup($needle);
        }

        $this->article = Article::find($id);
        if (!$this->article) {
            $this->set_status(404);
            $this->render_nothing();
            return;
        }

        $this->article->visit();

        PageLayout::setTitle("{$this->article->titel} ({$this->article->category->titel})");
    }

    public function own_action()
    {
        try {
            Navigation::activateItem('/schwarzesbrettplugin/show/own');
        } catch (Exception $e) {
            $this->redirect('category/view');
            return;
        }

        PageLayout::setTitle($this->_('Meine Anzeigen'));

        $articles = User::get()->articles;
        $this->categories = Article::groupByCategory($articles);
    }

    public function user_action()
    {
        Navigation::activateItem('/schwarzesbrettplugin/show/all');

        $username   = Request::get('username');
        $this->user = User::findByUsername($username);

        PageLayout::setTitle(sprintf(
            $this->_('Alle Anzeigen von %s'),
            $this->user->getFullname()
        ));

        $articles = $this->user->articles->toArray();
        if ($this->user->id !== $GLOBALS['user']->id && !$this->is_admin) {
            $articles = array_filter($articles, function ($article) {
                return $article->visible;
            });
        }
        $this->categories = Article::groupByCategory($this->user->articles);
    }

    public function create_action($category_id = null)
    {
        PageLayout::setTitle($this->_('Anzeige erstellen'));

        $this->article           = new Article();
        $this->article->thema_id = $category_id;
        $this->categories        = $this->getCategories();

        $this->render_action('edit');
    }

    public function edit_action($id = null)
    {
        PageLayout::setTitle($this->_('Anzeige bearbeiten'));

        $this->id         = $id;
        $this->article    = Article::find($id);
        $this->categories = $this->getCategories();

        if (!$this->article->mayEdit()) {
            throw new AccessDeniedException($this->_('Sie dürfen diese Anzeige nicht bearbeiten.'));
        }
    }

    public function store_action($id = null)
    {
        if (Request::isPost() && $this->checkTicket()) {
            $article = $id
                     ? Article::find($id)
                     : new Article();

            if (!$article->mayEdit()) {
                throw new AccessDeniedException($this->_('Sie dürfen diese Anzeige nicht bearbeiten.'));
            }

            $duration = max(1, min(Config::get()->BULLETIN_BOARD_DURATION, Request::int('duration')));

            $article->thema_id     = Request::option('thema_id');
            $article->titel        = Request::get('titel');
            $article->beschreibung = transformBeforeSave(Request::get('beschreibung'));
            $article->visible      = Request::int('visible', 0);
            $article->publishable  = Request::int('publishable', 1);
            $article->user_id      = $article->user_id ?: $GLOBALS['user']->id;
            $article->duration     = $duration;
            $article->expires      = strtotime("+{$duration} days 23:59:59", $article->mkdate ?: time());
            $article->store();

            if (!Cateory::find($article->thema_id)) {
                throw new Exception('Article is not valid');
            }

            if ($GLOBALS['perm']->have_perm('root') && Request::int('reset')) {
                $article->resetCreation();
            }

            $config_words = Config::get()->BULLETIN_BOARD_BAD_WORDS;
            $needles    = array_filter(explode(',', $config_words));
            if (!empty($needles)) {
                $regexp = '/' . implode('|', $needles) . '/i';

                $haystack = "{$article->titel}###{$article->beschreibung}";
                if (preg_match_all($regexp, $haystack, $matches)) {
                    $bad_words = array_unique($matches[0]);

                    $url = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
                    $template = $this->get_template_factory()->open('article/mail-bad-words.php');
                    $template->_          = function ($s) { return $this->_($s); };
                    $template->controller = $this;
                    $template->article    = $article;
                    $template->bad_words  = $bad_words;
                    $mailbody = $template->render();
                    URLHelper::setBaseURL($url);

                    $mail = new StudipMail();
                    $mail->addRecipient(Config::get()->BULLETIN_BOARD_BLAME_RECIPIENTS)
                         ->setSubject($this->_('Anzeige enthält unzulässige Begriffe'))
                         ->setBodyText($mailbody)
                         ->setBodyHtml(formatReady($mailbody))
                         ->send();
                }
            }

            if ($this->hasUploadedImages()) {
                $errors = [];

                foreach ($this->getUploadedImages() as $image) {
                    if (isset($image['id'])) {
                        $ref = FileRef::find($image['id']);
                        $ref->description = $image['title'] ?: '';
                        $ref->store();

                        $thru = $article->addImage($ref, $image['position']);
                    } else {
                        $folder = $this->getFolder();
                        $error  = $folder->validateUpload($image, $GLOBALS['user']->id);
                        if ($error) {
                            $errors[] = $error;
                            continue;
                        }

                        $ref = $folder->createFile($image);
                        if ($ref) {
                            $article->addImage($ref);
                        } else {
                            $errors[] = sprintf(
                                $this->_('Datei %s konnte nicht erstellt werden'),
                                $file['name']
                            );
                        }

                    }
                }

                if ($errors) {
                    PageLayout::postError(
                        $this->_('Beim Hochladen der Dateien sind Fehler aufgetreten:'),
                        $errors
                    );
                }
            }

            if (isset($_POST['img'])) {
                $remove = [];
                foreach ($_POST['img'] as $id => $data) {
                    $image = $article->images->findOneBy('image_id', $id);
                    if (!$image) {
                        continue;
                    }

                    if (!empty($data['delete'])) {
                        $remove[] = $image->image;
                        continue;
                    }

                    $image->position = (int) $data['position'];
                    $image->store();

                    $image->image->description = trim($data['title']);
                    $image->image->store();
                }

                foreach ($remove as $img) {
                    $article->removeImage($img);
                }
            }

            $message = $id === null
                     ? $this->_('Die Anzeige wurde erstellt.')
                     : $this->_('Die Anzeige wurde gespeichert.');
            PageLayout::postSuccess($message);
        }

        $this->redirect(Request::get('return_to') ?: $this->url_for("category/view/{$article->thema_id}#sb-article-{$article->id}"));
    }

    private function getCategories()
    {
        return array_filter(
            Category::findByVisible(1, 'ORDER BY titel ASC'),
            function ($category) {
                return User::get()->mayPostTo($category);
            }
        );
    }

    public function delete_action($id)
    {
        if ($article = Article::find($id)) {
            if (!$article->mayEdit()) {
                throw new AccessDeniedException($this->_('Sie dürfen diese Anzeige nicht löschen.'));
            }
            $article->delete();

            PageLayout::postSuccess($this->_('Die Anzeige wurde gelöscht.'));
        }

        $this->redirect(Request::get('return_to') ?: $this->url_for('category'));
    }

    public function blame_action($id)
    {
        if (!$this->blame_enabled) {
            throw new Exception($this->_('Die Funktionen zum Anzeigen melden sind nicht aktiviert.'));
        }

        $this->article = Article::find($id);

        if (Request::isPost()) {
            $reason = Request::get('reason');

            $url = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
            $template = $this->get_template_factory()->open('article/mail-blame.php');
            $template->_          = function ($s) { return $this->_($s); };
            $template->controller = $this;
            $template->article    = $this->article;
            $template->reason     = $reason;
            $mailbody = $template->render();
            URLHelper::setBaseURL($url);

            $mail = new StudipMail();
            $mail->addRecipient(Config::get()->BULLETIN_BOARD_BLAME_RECIPIENTS)
                 ->setSubject("{$this->_('Anzeige wurde gemeldet')}: {$article->titel}")
                 ->setReplyToEmail($GLOBALS['user']->email)
                 ->setBodyText($mailbody)
                 ->setBodyHtml(formatReady($mailbody))
                 ->send();

            PageLayout::postInfo($this->_('Die Anzeige wurde den Administratoren gemeldet.'));

            $this->redirect("category/{$this->article->category->id}");
            return;
        }

        PageLayout::setTitle(sprintf(
            $this->_('Anzeige "%s" von %s melden'),
            $this->article->titel,
            $this->article->user->getFullname()
        ));
    }

    public function reply_action($article_id)
    {
        $article = Article::find($article_id);
        $quoted  = quotes_encode(
            mb_strlen($article->beschreibung) > 1003
                ? mb_substr($article->beschreibung, 0, 1000) . '...' :
                $article->beschreibung,
            $article->user->getFullname()
        );

        $this->redirect(URLHelper::getURL('dispatch.php/messages/write', [
            'rec_uname'       => $article->user->username,
            'default_subject' => "Re: {$article->titel}",
            'default_body'    => $quoted,
        ]));
    }

    public function purge_action()
    {
        $GLOBALS['perm']->check('root');

        $articles = Article::findBySQL('expires < UNIX_TIMESTAMP()');
        foreach ($articles as $article) {
            $article->delete();
        }

        $message = count($articles) > 0
                 ? MessageBox::success(sprintf($this->_('Es wurden %u Anzeigen aus der Datenbank gelöscht.'), count($articles)))
                 : MessageBox::info($this->_('Es gibt keine Artikel in der Datenbank, die gelöscht werden können.'));

        PageLayout::postMessage($message);

        $this->redirect('admin/settings');
    }

    private function hasUploadedImages()
    {
        if (!empty($_POST['new'])) {
            return true;
        }

        return !empty($_FILES['images']['name'])
            && is_array($_FILES['images']['name'])
            && count(array_filter($_FILES['images']['name'])) > 0;
    }

    private function getUploadedImages()
    {
        if (!$this->hasUploadedImages()) {
            return [];
        }

        $images = [];

        if (!empty($_FILES['images']['name']) && count(array_filter($_FILES['images']['name'])) > 0) {
            $count  = count($_FILES['images']['name']);
            $fields = ['name', 'type', 'tmp_name', 'error', 'size'];
            for ($i = 0; $i < $count; $i += 1) {
                $result = array_combine($fields, array_map(function ($field) use ($i) {
                    return $_FILES['images'][$field][$i];
                }, $fields));
                $result['tmp_path'] = $result['tmp_name'];

                $images[] = $result;
            }
        }

        if (!isset($_POST['new'])) {
            return $images;
        }

        foreach ($_POST['new'] as $id => $data) {
            if (!empty($data['delete'])) {
                continue;
            }

            $data['id'] = $id;

            $images[] = $data;
        }

        return $images;
    }
}
