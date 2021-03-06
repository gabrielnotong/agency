<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Attachment;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class ImageCacheSubscriber implements EventSubscriber
{
    private CacheManager $cacheManager;
    private UploaderHelper $uploaderHelper;

    public function __construct(CacheManager $cacheManager, UploaderHelper $uploaderHelper)
    {
        $this->cacheManager = $cacheManager;
        $this->uploaderHelper = $uploaderHelper;
    }

    public function getSubscribedEvents(): array
    {
        return [
            'preRemove',
            'preUpdate',
        ];
    }

    public function preRemove(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Attachment) {
            return;
        }
        $this->cacheManager->remove($this->uploaderHelper->asset($entity, 'imageFile'));
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Attachment) {
            return;
        }

        if (!$entity->getImageFile() instanceof UploadedFile) {
            return;
        }

        $this->cacheManager->remove($this->uploaderHelper->asset($entity, 'imageFile'));
    }

}