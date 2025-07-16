<?php

namespace App\Entity;

use App\Repository\SpeakerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SpeakerRepository::class)]
class Speaker
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailSpeaker = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telSpeaker = null;

    #[ORM\Column(length: 255)]
    private ?string $statutSpeaker = null;

    #[ORM\Column(length: 255)]
    private ?string $typeSpeaker = null;

    #[ORM\Column(length: 255)]
    private ?string $roleSpeaker = null;

    #[ORM\Column(length: 255)]
    private ?string $pictureSpeaker = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imgPopUpSpeaker = null;

    #[ORM\Column(length: 255)]
    private ?string $firstNameSpeaker = null;

    #[ORM\Column(length: 255)]
    private ?string $lastNameSpeaker = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $biographySpeaker = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pictureCompanySpeaker = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $search = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $newsSpeaker = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $instagramSpeaker = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $facebookSpeaker = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imgSpeakerProposal1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imgSpeakerProposal2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imgSpeakerProposal3 = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'speakers')]
    private Collection $events;

    /**
     * @var Collection<int, ShortFilm>
     */
    #[ORM\ManyToMany(targetEntity: ShortFilm::class, mappedBy: 'speakers')]
    private Collection $shortFilms;

    /**
     * @var Collection<int, SessionNetPitchFormation>
     */
    #[ORM\ManyToMany(targetEntity: SessionNetPitchFormation::class, inversedBy: 'speakers')]
    private Collection $sessionNetPitchFormation;

    /**
     * @var Collection<int, Location>
     */
    #[ORM\ManyToMany(targetEntity: Location::class, mappedBy: 'speaker')]
    private Collection $locations;

    #[ORM\Column]
    private ?bool $draft = null;

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->shortFilms = new ArrayCollection();
        $this->sessionNetPitchFormation = new ArrayCollection();
        $this->locations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmailSpeaker(): ?string
    {
        return $this->emailSpeaker;
    }

    public function setEmailSpeaker(?string $emailSpeaker): static
    {
        $this->emailSpeaker = $emailSpeaker;

        return $this;
    }

    public function getTelSpeaker(): ?string
    {
        return $this->telSpeaker;
    }

    public function setTelSpeaker(?string $telSpeaker): static
    {
        $this->telSpeaker = $telSpeaker;

        return $this;
    }

    public function getStatutSpeaker(): ?string
    {
        return $this->statutSpeaker;
    }

    public function setStatutSpeaker(string $statutSpeaker): static
    {
        $this->statutSpeaker = $statutSpeaker;

        return $this;
    }

    public function getTypeSpeaker(): ?string
    {
        return $this->typeSpeaker;
    }

    public function setTypeSpeaker(string $typeSpeaker): static
    {
        $this->typeSpeaker = $typeSpeaker;

        return $this;
    }

    public function getRoleSpeaker(): ?string
    {
        return $this->roleSpeaker;
    }

    public function setRoleSpeaker(string $roleSpeaker): static
    {
        $this->roleSpeaker = $roleSpeaker;

        return $this;
    }

    public function getPictureSpeaker(): ?string
    {
        return $this->pictureSpeaker;
    }

    public function setPictureSpeaker(string $pictureSpeaker): static
    {
        $this->pictureSpeaker = $pictureSpeaker;

        return $this;
    }

    public function getImgPopUpSpeaker(): ?string
    {
        return $this->imgPopUpSpeaker;
    }

    public function setImgPopUpSpeaker(string $imgPopUpSpeaker): static
    {
        $this->imgPopUpSpeaker = $imgPopUpSpeaker;

        return $this;
    }

    public function getFirstNameSpeaker(): ?string
    {
        return $this->firstNameSpeaker;
    }

    public function setFirstNameSpeaker(string $firstNameSpeaker): static
    {
        $this->firstNameSpeaker = $firstNameSpeaker;

        return $this;
    }

    public function getLastNameSpeaker(): ?string
    {
        return $this->lastNameSpeaker;
    }

    public function setLastNameSpeaker(string $lastNameSpeaker): static
    {
        $this->lastNameSpeaker = $lastNameSpeaker;

        return $this;
    }

    public function getBiographySpeaker(): ?string
    {
        return $this->biographySpeaker;
    }

    public function setBiographySpeaker(string $biographySpeaker): static
    {
        $this->biographySpeaker = $biographySpeaker;

        return $this;
    }

    public function getPictureCompanySpeaker(): ?string
    {
        return $this->pictureCompanySpeaker;
    }

    public function setPictureCompanySpeaker(?string $pictureCompanySpeaker): static
    {
        $this->pictureCompanySpeaker = $pictureCompanySpeaker;

        return $this;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): static
    {
        $this->search = $search;

        return $this;
    }

    public function getNewsSpeaker(): ?string
    {
        return $this->newsSpeaker;
    }

    public function setNewsSpeaker(?string $newsSpeaker): static
    {
        $this->newsSpeaker = $newsSpeaker;

        return $this;
    }

    public function getInstagramSpeaker(): ?string
    {
        return $this->instagramSpeaker;
    }

    public function setInstagramSpeaker(?string $instagramSpeaker): static
    {
        $this->instagramSpeaker = $instagramSpeaker;

        return $this;
    }

    public function getFacebookSpeaker(): ?string
    {
        return $this->facebookSpeaker;
    }

    public function setFacebookSpeaker(?string $facebookSpeaker): static
    {
        $this->facebookSpeaker = $facebookSpeaker;

        return $this;
    }

    public function getImgSpeakerProposal1(): ?string
    {
        return $this->imgSpeakerProposal1;
    }

    public function setImgSpeakerProposal1(?string $imgSpeakerProposal1): static
    {
        $this->imgSpeakerProposal1 = $imgSpeakerProposal1;

        return $this;
    }

    public function getImgSpeakerProposal2(): ?string
    {
        return $this->imgSpeakerProposal2;
    }

    public function setImgSpeakerProposal2(?string $imgSpeakerProposal2): static
    {
        $this->imgSpeakerProposal2 = $imgSpeakerProposal2;

        return $this;
    }

    public function getImgSpeakerProposal3(): ?string
    {
        return $this->imgSpeakerProposal3;
    }

    public function setImgSpeakerProposal3(?string $imgSpeakerProposal3): static
    {
        $this->imgSpeakerProposal3 = $imgSpeakerProposal3;

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
            $event->addSpeaker($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            $event->removeSpeaker($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, ShortFilm>
     */
    public function getShortFilms(): Collection
    {
        return $this->shortFilms;
    }

    public function addShortFilm(ShortFilm $shortFilm): static
    {
        if (!$this->shortFilms->contains($shortFilm)) {
            $this->shortFilms->add($shortFilm);
            $shortFilm->addSpeaker($this);
        }

        return $this;
    }

    public function removeShortFilm(ShortFilm $shortFilm): static
    {
        if ($this->shortFilms->removeElement($shortFilm)) {
            $shortFilm->removeSpeaker($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, SessionNetPitchFormation>
     */
    public function getSessionNetPitchFormation(): Collection
    {
        return $this->sessionNetPitchFormation;
    }

    public function addSessionNetPitchFormation(SessionNetPitchFormation $sessionNetPitchFormation): static
    {
        if (!$this->sessionNetPitchFormation->contains($sessionNetPitchFormation)) {
            $this->sessionNetPitchFormation->add($sessionNetPitchFormation);
        }

        return $this;
    }

    public function removeSessionNetPitchFormation(SessionNetPitchFormation $sessionNetPitchFormation): static
    {
        $this->sessionNetPitchFormation->removeElement($sessionNetPitchFormation);

        return $this;
    }

    /**
     * @return Collection<int, Location>
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(Location $location): static
    {
        if (!$this->locations->contains($location)) {
            $this->locations->add($location);
            $location->addSpeaker($this);
        }

        return $this;
    }

    public function removeLocation(Location $location): static
    {
        if ($this->locations->removeElement($location)) {
            $location->removeSpeaker($this);
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

    public function getLocationsLinks(): string
    {
        $links = [];
        foreach ($this->locations as $location) {
            $label = htmlspecialchars((string) $location); // Utilise __toString()
            $id = $location->getId();
            $links[] = sprintf('<a href="/admin/location/%d/show" target="_blank">%s</a>', $id, $label);
        }
        return implode('<br>', $links);
    }

    public function __toString(): string
    {
        return sprintf('[%s] %s %s', $this->typeSpeaker, $this->firstNameSpeaker, $this->lastNameSpeaker);
    }
}
