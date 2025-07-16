<?php

namespace App\Entity;

use App\Repository\ArchivedSessionNetPitchFormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArchivedSessionNetPitchFormationRepository::class)]
class ArchivedSessionNetPitchFormation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'archivedSessionNetPitchFormation', cascade: ['persist', 'remove'])]
    private ?SessionNetPitchFormation $sessionNetPitchFormation = null;

    /**
     * @var Collection<int, RegistrationNetPitchFormation>
     */
    #[ORM\OneToMany(targetEntity: RegistrationNetPitchFormation::class, mappedBy: 'archivedSessionNetPitchFormation')]
    private Collection $registrationSessionNetPitchFormation;

    #[ORM\Column]
    private ?\DateTimeImmutable $archivedAt = null;

    public function __construct()
    {
        $this->registrationSessionNetPitchFormation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSessionNetPitchFormation(): ?SessionNetPitchFormation
    {
        return $this->sessionNetPitchFormation;
    }

    public function setSessionNetPitchFormation(?SessionNetPitchFormation $sessionNetPitchFormation): static
    {
        $this->sessionNetPitchFormation = $sessionNetPitchFormation;

        return $this;
    }

    /**
     * @return Collection<int, RegistrationNetPitchFormation>
     */
    public function getRegistrationSessionNetPitchFormation(): Collection
    {
        return $this->registrationSessionNetPitchFormation;
    }

    public function addRegistrationSessionNetPitchFormation(RegistrationNetPitchFormation $registrationSessionNetPitchFormation): static
    {
        if (!$this->registrationSessionNetPitchFormation->contains($registrationSessionNetPitchFormation)) {
            $this->registrationSessionNetPitchFormation->add($registrationSessionNetPitchFormation);
            $registrationSessionNetPitchFormation->setArchivedSessionNetPitchFormation($this);
        }

        return $this;
    }

    public function removeRegistrationSessionNetPitchFormation(RegistrationNetPitchFormation $registrationSessionNetPitchFormation): static
    {
        if ($this->registrationSessionNetPitchFormation->removeElement($registrationSessionNetPitchFormation)) {
            if ($registrationSessionNetPitchFormation->getArchivedSessionNetPitchFormation() === $this) {
                $registrationSessionNetPitchFormation->setArchivedSessionNetPitchFormation(null);
            }
        }

        return $this;
    }

    public function getArchivedAt(): ?\DateTimeImmutable
    {
        return $this->archivedAt;
    }

    public function setArchivedAt(\DateTimeImmutable $archivedAt): static
    {
        $this->archivedAt = $archivedAt;

        return $this;
    }

    public function getValidatedRegistrations(): array
    {
        return $this->registrationSessionNetPitchFormation
            ->filter(fn($r) => $r->getStatutRegistration() === 'Validé')
            ->toArray();
    }

    public function getSessionSpeakers(): array
    {
        return $this->sessionNetPitchFormation?->getSpeakers()->toArray() ?? [];
    }
}
