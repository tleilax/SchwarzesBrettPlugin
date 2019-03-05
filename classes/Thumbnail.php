<?php
namespace SchwarzesBrett;

use Exception;
use FileRef;
use GlobIterator;
use StudIPPlugin;
use PluginEngine;

class Thumbnail
{
    const MAX_SIZE = 128;
    const ID_PREFIX = 'sb-';
    const MAX_LIFETIME = 604800; // 7 * 24 * 60 * 60; // One week

    private static $plugin = null;

    public static function setPlugin(StudIPPlugin $plugin)
    {
        self::$plugin = $plugin;
    }

    public static function create(FileRef $ref)
    {
        return new self($ref);
    }

    public static function getStoragePath()
    {
        $path = $GLOBALS['UPLOAD_PATH'] . '/thumbnails';
        if ((!file_exists($path) && !mkdir($path)) || !is_dir($path)) {
            throw new Exception('Unable to access thumbnails storage path');
        }
        return $path;
    }

    public static function gc()
    {
        $pattern = sprintf(
            '%s/%s*.jpg',
            self::getStoragePath(),
            self::ID_PREFIX
        );

        $iterator = new GlobIterator($pattern);

        foreach ($iterator as $item) {
            if ($item->getMTime() < time() - self::MAX_LIFETIME) {
                @unlink($item->getPathname());
            }
        }
    }

    protected $ref;
    protected $title;

    protected $width = null;
    protected $height = null;
    protected $image_size = null;

    public function __construct(FileRef $ref)
    {
        $this->ref   = $ref;
        $this->title = $ref->description;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function getWidth()
    {
        return $this->calculateWidthAndHeight()[0];
    }

    public function getHeight()
    {
        return $this->calculateWidthAndHeight()[1];
    }

    private function calculateWidthAndHeight()
    {
        if ($this->width === null || $this->height === null) {
            if ($this->image_size === null) {
                $this->image_size = getimagesize($this->ref->file->getPath());
            }

            if ($this->width === null) {
                $this->width = min($this->image_size[0], self::MAX_SIZE);
            }

            $this->height = $this->image_size[1] * $this->width / $this->image_size[0];

            if ($this->height > self::MAX_SIZE) {
                $this->width = $this->getWidth() * self::MAX_SIZE / $this->height;
                $this->height = self::MAX_SIZE;
            }
        }

        return [$this->width, $this->height];
    }

    public function canResize()
    {
        return extension_loaded('gd');
    }

    public function getId()
    {
        return self::ID_PREFIX . md5(json_encode([
            $this->ref->id,
            $this->width,
            $this->height,
        ]));
    }

    public function getFilename()
    {
        return self::getStoragePath() . '/' . $this->getId() . '.jpg';
    }

    public function exists()
    {
        return file_exists($this->getFilename());
    }

    public function render()
    {
        if (!$this->exists()) {
            $filepath = $this->ref->file->getPath();
            if (!file_exists($filepath) || !is_readable($filepath)) {
                throw new Exception('Cannot read file contents');
            }

            $blob = file_get_contents($this->ref->file->getPath());

            if (!$this->canResize()) {
                return $blob;
            }

            $image = @imagecreatefromstring($blob);
            if ($image === false) {
                return $blob;
            }

            $original_width  = imagesx($image);
            $original_height = imagesy($image);

            $width = $this->getWidth();
            $height = $this->getHeight();

            if ($original_width <= $width && $original_height <= $height) {
                return $this->renderAsString($image);
            }

            $thumbnail = imagecreatetruecolor($width, $height);
            imagecopyresampled(
                $thumbnail, $image,
                0, 0,
                0, 0,
                $width, $height,
                $original_width, $original_height
            );
            imagedestroy($image);

            file_put_contents(
                $this->getFilename(),
                $this->renderAsString($thumbnail)
            );
        }

        return file_get_contents($this->getFilename());
    }

    public function getURL()
    {
        return PluginEngine::getLink(self::$plugin, [], "files/thumbnail/{$this->ref->id}");
    }

    public function getImageTag($link = false, $dimensions = true)
    {
        if ($dimensions) {
            $result = sprintf(
                '<img src="%s" width="%u" height="%u" class="lazy">',
                $this->getURL(),
                $this->getWidth(),
                $this->getHeight()
            );
        } else {
            $result = sprintf(
                '<img src="%s" class="lazy">',
                $this->getURL()
            );
        }

        if ($link) {
            if ($link === true) {
                $link = $this->ref->getDownloadURL();
            }
            $result = sprintf(
                '<a href="%s" target="_blank" data-lightbox="sb" data-title="%s">%s</a>',
                $link,
                htmlReady($this->title),
                $result
            );
        }

        return $result;
    }

    private function renderAsString($image, $destroy = true)
    {
        ob_start();
        imagejpeg($image);
        $result = ob_get_clean();

        if ($destroy) {
            imagedestroy($image);
        }

        return $result;
    }
}
