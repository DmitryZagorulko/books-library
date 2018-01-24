<?php

namespace BookBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use BookBundle\Entity\Book;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class BookSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate',
            'preRemove'
        );
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->clearCache();
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->clearCache();
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $this->clearCache();
    }

    public function clearCache()
    {
        $cache = new FilesystemAdapter;

        if ($cache->getItem('books.all')->isHit()) {
            $cache->clear();
        }
    }
}
