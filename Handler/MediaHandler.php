<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 23.6.15.
 * Time: 09.52
 */

namespace ED\BlogBundle\Handler;

use Application\Sonata\MediaBundle\Entity\Media;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Session\Session;

class MediaHandler
{
    protected $session;

    function __construct(Session $session)
    {
        $this->session = $session;
    }


    /**
     * @param  string $originalFile - Full file location on the disc
     * @return string
     */
    public function generateNewFileName($originalFile)
    {
        $newFileName = sha1(date("d.m.Y H:i:s"));
        $locationArray = explode("/", $originalFile);
        $fileName = $locationArray[ count($locationArray) - 1 ];
        $nameParts = explode(".", $fileName);

        if(count($nameParts) >= 2)
        {
            //keep original Extension
            $newFileName .= '.' . $nameParts[ count($nameParts) - 1 ];
        }

        $locationArray[ count($locationArray) - 1 ] = $newFileName;

        return implode('/', $locationArray);
    }

    /**
     * @param string $originalFile - Full file location on the disc
     */
    public function getFileName($originalFile)
    {
        $locationArray = explode("/", $originalFile);
        $fileName = $locationArray[ count($locationArray) - 1 ];

        return $fileName;
    }

    /**
     * Keep temparary records of file media edit history
     *
     * @param $media
     * @param $providerReference
     * @param $width
     * @param $height
     */
    public function addToEditHistory($media, $providerReference, $width, $height)
    {
        $key = 'media_edit_history_' . $media->getId();
        $history = $this->session->get($key, null);

        if(!$history)
        {
            $history = array();
        }
        else
        {
            $history = unserialize($history);
        }

        $history[] = array(
            'providerReference' => $providerReference,
            'width' => $width,
            'height' => $height
        );

        $this->session->set($key, serialize( $history ));
    }

    public function generateMediaFromFile($file)
    {
        $media = new Media();

        $media->setBinaryContent(new File($file));
        $media->setContext('default');
        $media->setProviderName('sonata.media.provider.image');
        $media->setName($this->getFileName($file));
        $media->setEnabled(true);

        return $media;
    }
}