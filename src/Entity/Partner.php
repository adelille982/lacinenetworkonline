<?php

namespace App\Entity;

use App\Repository\PartnerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartnerRepository::class)]
class Partner
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $namePartner = null;

    #[ORM\Column(length: 255)]
    private ?string $logoPartner = null;

    #[ORM\Column(length: 2000, nullable: true)]
    private ?string $linkPartner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNamePartner(): ?string
    {
        return $this->namePartner;
    }

    public function setNamePartner(string $namePartner): static
    {
        $this->namePartner = $namePartner;

        return $this;
    }

    public function getLogoPartner(): ?string
    {
        return $this->logoPartner;
    }

    public function setLogoPartner(string $logoPartner): static
    {
        $this->logoPartner = $logoPartner;

        return $this;
    }

    public function getLinkPartner(): ?string
    {
        return $this->linkPartner;
    }

    public function setLinkPartner(?string $linkPartner): static
    {
        $this->linkPartner = $linkPartner;

        return $this;
    }
}
