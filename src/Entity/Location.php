<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $typeLocation = null;

    #[ORM\Column(length: 255)]
    private ?string $streetLocation = null;

    #[ORM\Column(length: 255)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255)]
    private ?string $cityLocation = null;

    /**
     * @var Collection<int, Speaker>
     */
    #[ORM\ManyToMany(targetEntity: Speaker::class, inversedBy: 'locations')]
    private Collection $speaker;

    /**
     * @var Collection<int, SessionNetPitchFormation>
     */
    #[ORM\OneToMany(targetEntity: SessionNetPitchFormation::class, mappedBy: 'location')]
    private Collection $sessionNetPitchFormations;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'location')]
    private Collection $events;

    public function __construct()
    {
        $this->speaker = new ArrayCollection();
        $this->sessionNetPitchFormations = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeLocation(): ?string
    {
        return $this->typeLocation;
    }

    public function setTypeLocation(string $typeLocation): static
    {
        $this->typeLocation = $typeLocation;

        return $this;
    }

    public function getStreetLocation(): ?string
    {
        return $this->streetLocation;
    }

    public function setStreetLocation(string $streetLocation): static
    {
        $this->streetLocation = $streetLocation;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCityLocation(): ?string
    {
        return $this->cityLocation;
    }

    public function setCityLocation(string $cityLocation): static
    {
        $this->cityLocation = $cityLocation;

        return $this;
    }

    /**
     * @return Collection<int, Speaker>
     */
    public function getSpeaker(): Collection
    {
        return $this->speaker;
    }

    public function addSpeaker(Speaker $speaker): static
    {
        if (!$this->speaker->contains($speaker)) {
            $this->speaker->add($speaker);
        }

        return $this;
    }

    public function removeSpeaker(Speaker $speaker): static
    {
        $this->speaker->removeElement($speaker);

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
            $sessionNetPitchFormation->setLocation($this);
        }

        return $this;
    }

    public function removeSessionNetPitchFormation(SessionNetPitchFormation $sessionNetPitchFormation): static
    {
        if ($this->sessionNetPitchFormations->removeElement($sessionNetPitchFormation)) {
            if ($sessionNetPitchFormation->getLocation() === $this) {
                $sessionNetPitchFormation->setLocation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setLocation($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            if ($event->getLocation() === $this) {
                $event->setLocation(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->streetLocation . ' - ' . $this->cityLocation . ' - ' . $this->typeLocation;
    }
}
