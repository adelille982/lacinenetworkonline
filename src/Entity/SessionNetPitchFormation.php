<?php

namespace App\Entity;

use App\Repository\SessionNetPitchFormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use LogicException;

#[ORM\Entity(repositoryClass: SessionNetPitchFormationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class SessionNetPitchFormation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $imgSessionNetPitchFormation = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    private ?string $maxNumberRegistrationSessionNetPitchFormation = null;

    #[ORM\Column]
    private ?bool $remoteSession = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startDateSessionNetPitchFormation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endDateSessionNetPitchFormation = null;

    #[ORM\ManyToOne(inversedBy: 'sessionNetPitchFormations')]
    private ?NetPitchFormation $netPitchFormation = null;

    /**
     * @var Collection<int, RegistrationNetPitchFormation>
     */
    #[ORM\OneToMany(targetEntity: RegistrationNetPitchFormation::class, mappedBy: 'sessionNetPitchFormation')]
    private Collection $registrationNetPitchFormations;

    /**
     * @var Collection<int, Speaker>
     */
    #[ORM\ManyToMany(targetEntity: Speaker::class, mappedBy: 'sessionNetPitchFormation')]
    private Collection $speakers;

    #[ORM\ManyToOne(inversedBy: 'sessionNetPitchFormations')]
    private ?Location $location = null;

    #[ORM\Column]
    private ?bool $draft = null;

    #[ORM\OneToOne(mappedBy: 'sessionNetPitchFormation', cascade: ['persist', 'remove'])]
    private ?ArchivedSessionNetPitchFormation $archivedSessionNetPitchFormation = null;

    public function __construct()
    {
        $this->registrationNetPitchFormations = new ArrayCollection();
        $this->speakers = new ArrayCollection();
    }

    #[PrePersist]
    #[PreUpdate]
    public function validateSpeakersCount(): void
    {
        if (count($this->getSpeakers()) < 4) {
            throw new LogicException('Une session doit avoir au moins 4 intervenants associés.');
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImgSessionNetPitchFormation(): ?string
    {
        return $this->imgSessionNetPitchFormation;
    }

    public function setImgSessionNetPitchFormation(string $imgSessionNetPitchFormation): static
    {
        $this->imgSessionNetPitchFormation = $imgSessionNetPitchFormation;

        return $this;
    }

    public function getMaxNumberRegistrationSessionNetPitchFormation(): ?string
    {
        return $this->maxNumberRegistrationSessionNetPitchFormation;
    }

    public function setMaxNumberRegistrationSessionNetPitchFormation(string $maxNumberRegistrationSessionNetPitchFormation): static
    {
        $this->maxNumberRegistrationSessionNetPitchFormation = $maxNumberRegistrationSessionNetPitchFormation;

        return $this;
    }

    public function isRemoteSession(): ?bool
    {
        return $this->remoteSession;
    }

    public function setRemoteSession(bool $remoteSession): static
    {
        $this->remoteSession = $remoteSession;

        return $this;
    }

    public function getStartDateSessionNetPitchFormation(): ?\DateTimeInterface
    {
        return $this->startDateSessionNetPitchFormation;
    }

    public function setStartDateSessionNetPitchFormation(\DateTimeInterface $startDateSessionNetPitchFormation): static
    {
        $this->startDateSessionNetPitchFormation = $startDateSessionNetPitchFormation;

        return $this;
    }

    public function getEndDateSessionNetPitchFormation(): ?\DateTimeInterface
    {
        return $this->endDateSessionNetPitchFormation;
    }

    public function setEndDateSessionNetPitchFormation(\DateTimeInterface $endDateSessionNetPitchFormation): static
    {
        $this->endDateSessionNetPitchFormation = $endDateSessionNetPitchFormation;

        return $this;
    }

    public function getNetPitchFormation(): ?NetPitchFormation
    {
        return $this->netPitchFormation;
    }

    public function setNetPitchFormation(?NetPitchFormation $netPitchFormation): static
    {
        $this->netPitchFormation = $netPitchFormation;

        return $this;
    }

    /**
     * @return Collection<int, RegistrationNetPitchFormation>
     */
    public function getRegistrationNetPitchFormations(): Collection
    {
        return $this->registrationNetPitchFormations;
    }

    public function addRegistrationNetPitchFormation(RegistrationNetPitchFormation $registrationNetPitchFormation): static
    {
        if (!$this->registrationNetPitchFormations->contains($registrationNetPitchFormation)) {
            $this->registrationNetPitchFormations->add($registrationNetPitchFormation);
            $registrationNetPitchFormation->setSessionNetPitchFormation($this);
        }

        return $this;
    }

    public function removeRegistrationNetPitchFormation(RegistrationNetPitchFormation $registrationNetPitchFormation): static
    {
        if ($this->registrationNetPitchFormations->removeElement($registrationNetPitchFormation)) {
            if ($registrationNetPitchFormation->getSessionNetPitchFormation() === $this) {
                $registrationNetPitchFormation->setSessionNetPitchFormation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Speaker>
     */
    public function getSpeakers(): Collection
    {
        return $this->speakers;
    }

    public function addSpeaker(Speaker $speaker): static
    {
        if (!$this->speakers->contains($speaker)) {
            $this->speakers->add($speaker);
            $speaker->addSessionNetPitchFormation($this);
        }

        return $this;
    }

    public function removeSpeaker(Speaker $speaker): static
    {
        if ($this->speakers->removeElement($speaker)) {
            $speaker->removeSessionNetPitchFormation($this);
        }

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        if ($location !== null && $location->getTypeLocation() !== 'Formation') {
            throw new \InvalidArgumentException("Seuls les lieux de type 'Formation' peuvent être associés à une session de formation.");
        }

        $this->location = $location;

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

    public function getArchivedSessionNetPitchFormation(): ?ArchivedSessionNetPitchFormation
    {
        return $this->archivedSessionNetPitchFormation;
    }

    public function setArchivedSessionNetPitchFormation(?ArchivedSessionNetPitchFormation $archivedSessionNetPitchFormation): static
    {
        if ($archivedSessionNetPitchFormation === null && $this->archivedSessionNetPitchFormation !== null) {
            $this->archivedSessionNetPitchFormation->setSessionNetPitchFormation(null);
        }

        if ($archivedSessionNetPitchFormation !== null && $archivedSessionNetPitchFormation->getSessionNetPitchFormation() !== $this) {
            $archivedSessionNetPitchFormation->setSessionNetPitchFormation($this);
        }

        $this->archivedSessionNetPitchFormation = $archivedSessionNetPitchFormation;

        return $this;
    }

    public function __toString(): string
    {
        return
            ' Du ' . $this->startDateSessionNetPitchFormation?->format('d/m/Y')
            . ' au ' . $this->endDateSessionNetPitchFormation?->format('d/m/Y')
            . ' - ' . $this->netPitchFormation?->getTitleNetPitchFormation();
    }

    public function getValidatedRegistrations(): array
    {
        return $this->registrationNetPitchFormations
            ->filter(fn($r) => $r->getStatutRegistration() === 'Validé')
            ->toArray();
    }
}
