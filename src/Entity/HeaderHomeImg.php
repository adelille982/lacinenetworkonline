<?php

namespace App\Entity;

use App\Repository\HeaderHomeImgRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HeaderHomeImgRepository::class)]
class HeaderHomeImg
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $imageHeaderHome = null;

    #[ORM\ManyToOne(inversedBy: 'headerHomeImgs')]
    private ?HeaderHome $headerHome = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImageHeaderHome(): ?string
    {
        return $this->imageHeaderHome;
    }

    public function setImageHeaderHome(string $imageHeaderHome): static
    {
        $this->imageHeaderHome = $imageHeaderHome;

        return $this;
    }

    public function getHeaderHome(): ?HeaderHome
    {
        return $this->headerHome;
    }

    public function setHeaderHome(?HeaderHome $headerHome): static
    {
        $this->headerHome = $headerHome;

        return $this;
    }

    public function __toString(): string
    {
        return $this->id . ' - ' . $this->imageHeaderHome;
    }
}
