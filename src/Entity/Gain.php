<?php

namespace App\Entity;

use App\Repository\GainRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GainRepository::class)]
class Gain
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $imgGain = null;

    #[ORM\Column(length: 255)]
    private ?string $titleGain = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sloganGain = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkGain = null;

    /**
     * @var Collection<int, NetPitchFormation>
     */
    #[ORM\OneToMany(targetEntity: NetPitchFormation::class, mappedBy: 'gain')]
    private Collection $netPitchFormations;

    public function __construct()
    {
        $this->netPitchFormations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImgGain(): ?string
    {
        return $this->imgGain;
    }

    public function setImgGain(?string $imgGain): static
    {
        $this->imgGain = $imgGain;

        return $this;
    }

    public function getTitleGain(): ?string
    {
        return $this->titleGain;
    }

    public function setTitleGain(string $titleGain): static
    {
        $this->titleGain = $titleGain;

        return $this;
    }

    public function getSloganGain(): ?string
    {
        return $this->sloganGain;
    }

    public function setSloganGain(string $sloganGain): static
    {
        $this->sloganGain = $sloganGain;

        return $this;
    }

    public function getLinkGain(): ?string
    {
        return $this->linkGain;
    }

    public function setLinkGain(?string $linkGain): static
    {
        $this->linkGain = $linkGain;

        return $this;
    }

    /**
     * @return Collection<int, NetPitchFormation>
     */
    public function getNetPitchFormations(): Collection
    {
        return $this->netPitchFormations;
    }

    public function addNetPitchFormation(NetPitchFormation $netPitchFormation): static
    {
        if (!$this->netPitchFormations->contains($netPitchFormation)) {
            $this->netPitchFormations->add($netPitchFormation);
            $netPitchFormation->setGain($this);
        }

        return $this;
    }

    public function removeNetPitchFormation(NetPitchFormation $netPitchFormation): static
    {
        if ($this->netPitchFormations->removeElement($netPitchFormation)) {
            if ($netPitchFormation->getGain() === $this) {
                $netPitchFormation->setGain(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->titleGain ?? 'Titre du gain';
    }
}
