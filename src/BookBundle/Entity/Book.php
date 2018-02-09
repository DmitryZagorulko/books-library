<?php

namespace BookBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Book
 *
 * @ORM\Table(name="book")
 * @ORM\Entity(repositoryClass="BookBundle\Repository\BookRepository")
 * @Serializer\ExclusionPolicy("none")
 */
class Book
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\ReadOnly()
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=40)
     *
     * @Serializer\Groups({"edit"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=40)
     *
     * @Serializer\Groups({"edit"})
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(name="cover", type="string", length=255, nullable=true)
     *
     * @Assert\Image(
     *      mimeTypes = {"image/png", "image/jpeg"},
     *      mimeTypesMessage = "Please upload a png/jpeg"
     * )
     *
     * @Serializer\ReadOnly()
     * @Serializer\Accessor(getter="getPathCover",setter="setCover")
     */
    private $cover;

    /**
     * @var string
     *
     * @ORM\Column(name="file", type="string", length=255, nullable=true)
     *
     * @Assert\File(maxSize = "5M")
     *
     * @Serializer\ReadOnly()
     * @Serializer\Accessor(getter="getPathFile",setter="setFile")
     * @Serializer\Expose(if="object.getAllowDownload() === true")
     */
    private $file;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="read_it", type="date")
     *
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @Serializer\Groups({"edit"})
     */
    private $readIt;

    /**
     * @var bool
     *
     * @ORM\Column(name="allow_download", type="boolean")
     *
     * @Serializer\Groups({"edit"})
     *
     */
    private $allowDownload;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Book
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set author
     *
     * @param string $author
     *
     * @return Book
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set cover
     *
     * @param string $cover
     *
     * @return Book
     */
    public function setCover($cover)
    {
        $this->cover = $cover;

        return $this;
    }

    /**
     * Get cover
     *
     * @return string
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * Get path cover
     *
     * @return string
     */
    public function getPathCover()
    {
        return $this->cover ? "/uploads/covers/" . $this->cover : null;
    }

    /**
     * Clear cover
     *
     * @return Book
     */
    public function clearCover()
    {
        $this->cover = null;

        return $this;
    }
    /**
     * Set file
     *
     * @param string $file
     *
     * @return Book
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get path file
     *
     * @return string
     */
    public function getPathFile()
    {
        return $this->file ? "/uploads/files/" . $this->file : null;
    }

    /**
     * Clear file
     *
     * @return Book
     */
    public function clearFile()
    {
        $this->file = null;

        return $this;
    }

    /**
     * Set readIt
     *
     * @param \DateTime $readIt
     *
     * @return Book
     */
    public function setReadIt($readIt)
    {
        $this->readIt = $readIt;

        return $this;
    }

    /**
     * Get readIt
     *
     * @return \DateTime
     */
    public function getReadIt()
    {
        return $this->readIt;
    }

    /**
     * Set allowDownload
     *
     * @param boolean $allowDownload
     *
     * @return Book
     */
    public function setAllowDownload($allowDownload)
    {
        $this->allowDownload = $allowDownload;

        return $this;
    }

    /**
     * Get allowDownload
     *
     * @return bool
     */
    public function getAllowDownload()
    {
        return $this->allowDownload;
    }
}
