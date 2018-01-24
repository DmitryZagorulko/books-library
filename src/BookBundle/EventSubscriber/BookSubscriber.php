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
        $this->upload($args);
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->clearCache();
        $this->upload($args);
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $this->clearCache();
        $this->remove($args);
    }

    public function clearCache()
    {
        $cache = new FilesystemAdapter;

        if ($cache->getItem('books.all')->isHit()) {
            $cache->clear();
        }

        return;
    }

    public function upload(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Book) {
            $file = $entity->getFile();
            $cover = $entity->getCover();

            if (!empty($file)) {
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $file->move(
                    $this->getFilePath(),
                    $fileName
                );
                $entity->setFile($fileName);
            }

            if (!empty($cover)) {
                $coverName = md5(uniqid()).'.'.$cover->guessExtension();
                $cover->move(
                    $this->getCoverPath(),
                    $coverName
                );
                $entity->setCover($coverName);
            }
        }

        return;
    }

    public function remove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Book) {
            if ($file = $entity->getFile()) {
                unlink($this->getFilePath().$file);
            }

            if ($cover = $entity->getCover()) {
                unlink($this->getCoverPath().$cover);
            }
        }

        return;
    }

    protected function getFilePath()
    {
        return __DIR__.'/../../../web/uploads/covers/';
    }

    protected function getCoverPath()
    {
        return __DIR__.'/../../../web/uploads/files/';
    }
}
