<?php
class FilesController extends SchwarzesBrett\Controller
{
    public function upload_action()
    {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
            $this->set_status(400);
            $this->render_text('');
            return;
        }

        $file = $_FILES['image'];
        if (StudipVersion::newerThan('4.5')) {
            $file = StandardFile::create($file);
        }

        $folder = $this->getFolder();
        $error  = $folder->validateUpload($file, $GLOBALS['user']->id);
        if ($error) {
            $this->set_status(500);
            $this->render_text("Error: {$error}");
            return;
        }

        if (StudipVersion::newerThan('4.5')) {
            $ref = $folder->addFile($file)->getFileRef();
        } else {
            $ref = $folder->createFile($file);
        }
        if (!$ref) {
            $this->set_status(500);
            $this->render_text(
                sprintf(
                    $this->_('Datei %s konnte nicht erstellt werden'),
                    $_FILES['image']['name']
                )
            );
            return;
        }

        $thumbnail = SchwarzesBrett\Thumbnail::create($ref);
        // HTTP/2 Server push the thumbnail, if supported
        $this->addHeader('Link', "<{$thumbnail->getURL()}>; rel=preload; as=image", false);

        $this->render_json([
            'id'  => $ref->id,
            'url' => $thumbnail->getURL(),
        ]);
    }

    public function thumbnail_action($ref_id, $width = null, $height = null)
    {
        SchwarzesBrett\Thumbnail::gc();

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $this->set_status(304);
            $this->addHeader('Cache-Control', 'public,max-age=' . 365 * 24 * 60 * 60);
            $this->addHeader('Pragma', 'public');

            $this->render_nothing();
            return;
        }

        $ref = FileRef::find($ref_id);
        if (!$ref) {
            $this->set_status(404);
            $this->render_nothing();
            return;
        }

        if (!$ref->isImage()) {
            $this->set_status(415);
            $this->render_nothing();
            return;
        }

        $public = false;
        $folder = $ref->folder;
        do {
            if ($folder->folder_type === 'PublicFolder') {
                $public = true;
                break;
            }
            $folder = $folder->parentfolder;
        } while ($folder);

        if (!$public) {
            $this->set_status(403);
            $this->render_nothing();
            return;
        }

        try {
            $thumbnail = SchwarzesBrett\Thumbnail::create($ref);
            $thumbnail->setWidth($width);
            $thumbnail->setHeight($height);
            $content = $thumbnail->render($width, $height);

            $this->set_content_type('image/jpeg');
            $this->addHeader('Content-Disposition', 'inline; ' . encode_header_parameter('filename', 'thumbnail.jpg'));
            $this->addHeader('Expires', $this->gmdate('+1 year'));
            $this->addHeader('Last-Modified', $this->gmdate());
            $this->addHeader('Cache-Control', 'public,max-age=' . 365 * 24 * 60 * 60);
            $this->addHeader('Pragma', 'public');

            $this->render_text($content);
        } catch (Exception $e) {
            $this->set_status(404);
            $this->render_nothing();
        }
    }

    protected function gmdate($offset = null)
    {
        $timestamp = time();
        if ($offset !== null) {
            $timestamp = strtotime($offset, $timestamp);
        }
        return gmdate('D, d M Y H:i:s', $timestamp) . ' GMT';
    }
}
