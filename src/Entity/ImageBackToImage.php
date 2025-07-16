<?php

namespace App\Entity;

use App\Repository\ImageBackToImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImageBackToImageRepository::class)]
class ImageBackToImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $imgBackToImage = null;

    #[ORM\ManyToOne(inversedBy: 'imageBackToImages')]
    private ?BackToImage $backToImage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImgBackToImage(): ?string
    {
        return $this->imgBackToImage;
    }

    public function setImgBackToImage(string $imgBackToImage): static
    {
        $this->imgBackToImage = $imgBackToImage;

        return $this;
    }

    public function getBackToImage(): ?BackToImage
    {
        return $this->backToImage;
    }

    public function setBackToImage(?BackToImage $backToImage): static
    {
        $this->backToImage = $backToImage;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('N°%d - %s', $this->id ?? 0, $this->imgBackToImage ?? 'Image');
    }
}
