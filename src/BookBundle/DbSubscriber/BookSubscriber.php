<?php

namespace BookBundle\DbSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use BookBundle\Entity\Book;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class BookSubscriber implements EventSubscriber
{
    protected $path;

    public function __construct($path)
    {
        $this->path = $path . "/../web";
    }

    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'preUpdate',
            'preRemove'
        ];
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

            if (!empty($file)  && !file_exists($this->path . $entity->getPathFile())) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();

                $file->move(
                    $this->path . "/uploads/files/",
                    $fileName
                );

                $entity->setFile($fileName);
            }

            if (!empty($cover) && !file_exists($this->path . $entity->getPathCover())) {
                $coverName = md5(uniqid()) . '.' . $cover->guessExtension();

                $cover->move(
                    $this->path . "/uploads/covers/",
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
            if (!empty($entity->getFile())) {
                $filePath = $this->path . $entity->getPathFile();

                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            if (!empty($entity->getCover())) {
                $coverPath = $this->path . $entity->getPathCover();

                if (file_exists($coverPath)) {
                    unlink($coverPath);
                }
            }
        }

        return;
    }
}
