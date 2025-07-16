<?php

namespace App\Entity;

use App\Repository\NetPitchFormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NetPitchFormationRepository::class)]
class NetPitchFormation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $slugNetPitchformation = null;

    #[ORM\Column(length: 255)]
    private ?string $titleNetPitchFormation = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    private ?string $maxNumberNetPitchFormation = null;

    #[ORM\Column(length: 255)]
    private ?string $durationNetPitchFormation = null;

    #[ORM\Column(length: 255)]
    private ?string $fundingNetPitchFormation = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $shortDescriptionNetPitchFormation = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $longDescriptionNetPitchFormation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdfNetPitchFormation = null;

    #[ORM\ManyToOne(inversedBy: 'netPitchFormations')]
    private ?Gain $gain = null;

    /**
     * @var Collection<int, SessionNetPitchFormation>
     */
    #[ORM\OneToMany(targetEntity: SessionNetPitchFormation::class, mappedBy: 'netPitchFormation')]
    private Collection $sessionNetPitchFormations;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $programDescription = null;

    #[ORM\Column(length: 255)]
    private ?string $metaDescriptionNetPitchFormation = null;

    #[ORM\Column(length: 255)]
    private ?string $seoKeyNetPitchFormation = null;

    #[ORM\Column]
    private ?bool $draft = null;

    public function __construct()
    {
        $this->sessionNetPitchFormations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlugNetPitchformation(): ?string
    {
        return $this->slugNetPitchformation;
    }

    public function setSlugNetPitchformation(string $slugNetPitchformation): static
    {
        $this->slugNetPitchformation = $slugNetPitchformation;

        return $this;
    }

    public function getTitleNetPitchFormation(): ?string
    {
        return $this->titleNetPitchFormation;
    }

    public function setTitleNetPitchFormation(string $titleNetPitchFormation): static
    {
        $this->titleNetPitchFormation = $titleNetPitchFormation;

        return $this;
    }

    public function getMaxNumberNetPitchFormation(): ?string
    {
        return $this->maxNumberNetPitchFormation;
    }

    public function setMaxNumberNetPitchFormation(string $maxNumberNetPitchFormation): static
    {
        $this->maxNumberNetPitchFormation = $maxNumberNetPitchFormation;

        return $this;
    }

    public function getDurationNetPitchFormation(): ?string
    {
        return $this->durationNetPitchFormation;
    }

    public function setDurationNetPitchFormation(string $durationNetPitchFormation): static
    {
        $this->durationNetPitchFormation = $durationNetPitchFormation;

        return $this;
    }

    public function getFundingNetPitchFormation(): ?string
    {
        return $this->fundingNetPitchFormation;
    }

    public function setFundingNetPitchFormation(string $fundingNetPitchFormation): static
    {
        $this->fundingNetPitchFormation = $fundingNetPitchFormation;

        return $this;
    }

    public function getShortDescriptionNetPitchFormation(): ?string
    {
        return $this->shortDescriptionNetPitchFormation;
    }

    public function setShortDescriptionNetPitchFormation(string $shortDescriptionNetPitchFormation): static
    {
        $this->shortDescriptionNetPitchFormation = $shortDescriptionNetPitchFormation;

        return $this;
    }

    public function getLongDescriptionNetPitchFormation(): ?string
    {
        return $this->longDescriptionNetPitchFormation;
    }

    public function setLongDescriptionNetPitchFormation(string $longDescriptionNetPitchFormation): static
    {
        $this->longDescriptionNetPitchFormation = $longDescriptionNetPitchFormation;

        return $this;
    }

    public function getPdfNetPitchFormation(): ?string
    {
        return $this->pdfNetPitchFormation;
    }

    public function setPdfNetPitchFormation(?string $pdfNetPitchFormation): static
    {
        $this->pdfNetPitchFormation = $pdfNetPitchFormation;

        return $this;
    }

    public function getGain(): ?Gain
    {
        return $this->gain;
    }

    public function setGain(?Gain $gain): static
    {
        $this->gain = $gain;

        return $this;
    }

    /**
     * @return Collection<int, SessionNetPitchFormation>
     */
    public function getSessionNetPitchFormations(): Collection
    {
        return $this->sessionNetPitchFormations;
    }

    public function addSessionNetPitchFormation(SessionNetPitchFormation $sessionNetPitchFormation): static
    {
        if (!$this->sessionNetPitchFormations->contains($sessionNetPitchFormation)) {
            $this->sessionNetPitchFormations->add($sessionNetPitchFormation);
            $sessionNetPitchFormation->setNetPitchFormation($this);
        }

        return $this;
    }

    public function removeSessionNetPitchFormation(SessionNetPitchFormation $sessionNetPitchFormation): static
    {
        if ($this->sessionNetPitchFormations->removeElement($sessionNetPitchFormation)) {
            if ($sessionNetPitchFormation->getNetPitchFormation() === $this) {
                $sessionNetPitchFormation->setNetPitchFormation(null);
            }
        }

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

    public function getProgramDescription(): ?string
    {
        return $this->programDescription;
    }

    public function setProgramDescription(?string $programDescription): static
    {
        $this->programDescription = $programDescription;

        return $this;
    }

    public function getMetaDescriptionNetPitchFormation(): ?string
    {
        return $this->metaDescriptionNetPitchFormation;
    }

    public function setMetaDescriptionNetPitchFormation(string $metaDescriptionNetPitchFormation): static
    {
        $this->metaDescriptionNetPitchFormation = $metaDescriptionNetPitchFormation;

        return $this;
    }

    public function getSeoKeyNetPitchFormation(): ?string
    {
        return $this->seoKeyNetPitchFormation;
    }

    public function setSeoKeyNetPitchFormation(string $seoKeyNetPitchFormation): static
    {
        $this->seoKeyNetPitchFormation = $seoKeyNetPitchFormation;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%s', $this->titleNetPitchFormation);
    }
}
