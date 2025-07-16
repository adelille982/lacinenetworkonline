<?php

namespace App\Entity;

use App\Repository\HeaderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HeaderRepository::class)]
class Header
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $pageTypeHeader = null;

    #[ORM\Column(length: 255)]
    private ?string $mainImageHeader = null;

    #[ORM\Column(length: 255)]
    private ?string $titleHeader = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sloganHeader = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titleSeoPage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $metaDescriptionPage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $seoKeyPage = null;

    #[ORM\Column]
    private ?bool $draft = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPageTypeHeader(): ?string
    {
        return $this->pageTypeHeader;
    }

    public function setPageTypeHeader(string $pageTypeHeader): static
    {
        $this->pageTypeHeader = $pageTypeHeader;

        return $this;
    }

    public function getMainImageHeader(): ?string
    {
        return $this->mainImageHeader;
    }

    public function setMainImageHeader(string $mainImageHeader): static
    {
        $this->mainImageHeader = $mainImageHeader;

        return $this;
    }

    public function getTitleHeader(): ?string
    {
        return $this->titleHeader;
    }

    public function setTitleHeader(string $titleHeader): static
    {
        $this->titleHeader = $titleHeader;

        return $this;
    }

    public function getSloganHeader(): ?string
    {
        return $this->sloganHeader;
    }

    public function setSloganHeader(?string $sloganHeader): static
    {
        $this->sloganHeader = $sloganHeader;

        return $this;
    }

    public function getTitleSeoPage(): ?string
    {
        return $this->titleSeoPage;
    }

    public function setTitleSeoPage(string $titleSeoPage): static
    {
        $this->titleSeoPage = $titleSeoPage;

        return $this;
    }

    public function getMetaDescriptionPage(): ?string
    {
        return $this->metaDescriptionPage;
    }

    public function setMetaDescriptionPage(?string $metaDescriptionPage): static
    {
        $this->metaDescriptionPage = $metaDescriptionPage;

        return $this;
    }

    public function getSeoKeyPage(): ?string
    {
        return $this->seoKeyPage;
    }

    public function setSeoKeyPage(?string $seoKeyPage): static
    {
        $this->seoKeyPage = $seoKeyPage;

        return $this;
    }

    public function isDraft(): ?bool
    {
        return $this->draft;
    }

    public function setDraft(bool $draft): static
    {
        $this->draft = $draft;

        return $this;
    }
}
