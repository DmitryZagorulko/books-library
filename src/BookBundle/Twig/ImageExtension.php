<?php

namespace BookBundle\Twig;

class ImageExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [new \Twig_SimpleFunction('getImage', [$this, 'imageSize'])];
    }

    public function imageSize($image, $width = 100, $height = 150)
    {
        return "src={$image} width={$width} height={$height}";
    }

    public function getName()
    {
        return 'getImage';
    }
}
