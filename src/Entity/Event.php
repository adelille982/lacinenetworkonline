<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $shortFilmProposal = null;

    #[ORM\Column(length: 255)]
    private ?string $numberEdition = null;

    #[ORM\Column(length: 255)]
    private ?string $typeEvent = null;

    #[ORM\Column(length: 255)]
    private ?string $titleEvent = null;

    #[ORM\Column(length: 255)]
    private ?string $imgEvent = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEvent = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $textEvent = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $programEvent = null;

    /**
     * @var Collection<int, ShortFilm>
     */
    #[ORM\ManyToMany(targetEntity: ShortFilm::class, inversedBy: 'events')]
    private Collection $shortFilms;

    /**
     * @var Collection<int, Speaker>
     */
    #[ORM\ManyToMany(targetEntity: Speaker::class, inversedBy: 'events')]
    private Collection $speakers;

    /**
     * @var Collection<int, UserEvent>
     */
    #[ORM\OneToMany(targetEntity: UserEvent::class, mappedBy: 'event')]
    private Collection $userEvents;

    #[ORM\Column]
    private ?bool $free = null;

    /**
     * @var Collection<int, ArchivedEvent>
     */
    #[ORM\OneToMany(targetEntity: ArchivedEvent::class, mappedBy: 'event')]
    private Collection $archivedEvents;

    #[ORM\Column]
    private ?bool $draft = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $priceEvent = null;

    public function __construct()
    {
        $this->shortFilms = new ArrayCollection();
        $this->speakers = new ArrayCollection();
        $this->userEvents = new ArrayCollection();
        $this->archivedEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isShortFilmProposal(): ?bool
    {
        return $this->shortFilmProposal;
    }

    public function setShortFilmProposal(bool $shortFilmProposal): static
    {
        $this->shortFilmProposal = $shortFilmProposal;

        return $this;
    }

    public function getNumberEdition(): ?string
    {
        return $this->numberEdition;
    }

    public function setNumberEdition(string $numberEdition): static
    {
        $this->numberEdition = $numberEdition;

        return $this;
    }

    public function getTypeEvent(): ?string
    {
        return $this->typeEvent;
    }

    public function setTypeEvent(string $typeEvent): static
    {
        $this->typeEvent = $typeEvent;

        return $this;
    }

    public function getTitleEvent(): ?string
    {
        return $this->titleEvent;
    }

    public function setTitleEvent(string $titleEvent): static
    {
        $this->titleEvent = $titleEvent;

        return $this;
    }

    public function getImgEvent(): ?string
    {
        return $this->imgEvent;
    }

    public function setImgEvent(string $imgEvent): static
    {
        $this->imgEvent = $imgEvent;

        return $this;
    }

    public function getDateEvent(): ?\DateTimeInterface
    {
        return $this->dateEvent;
    }

    public function setDateEvent(\DateTimeInterface $dateEvent): static
    {
        $this->dateEvent = $dateEvent;

        return $this;
    }

    public function getTextEvent(): ?string
    {
        return $this->textEvent;
    }

    public function setTextEvent(string $textEvent): static
    {
        $this->textEvent = $textEvent;

        return $this;
    }

    public function getProgramEvent(): ?string
    {
        return $this->programEvent;
    }

    public function setProgramEvent(string $programEvent): static
    {
        $this->programEvent = $programEvent;

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
        $statut = $shortFilm->getStatutShortFilm();

        if (in_array($this->typeEvent, ['Net Pitch', 'Location Net Pitch']) && $statut !== 'À financer') {
            throw new \InvalidArgumentException("Un événement de type '{$this->typeEvent}' ne peut être associé qu'à des courts-métrages de statut 'À financer'.");
        }

        if (in_array($this->typeEvent, ['Network', 'Location Network']) && $statut !== 'Produit') {
            throw new \InvalidArgumentException("Un événement de type '{$this->typeEvent}' ne peut être associé qu'à des courts-métrages de statut 'Produit'.");
        }

        foreach ($shortFilm->getSpeakers() as $speaker) {
            if ($speaker->getStatutSpeaker() !== 'Validé') {
                throw new \InvalidArgumentException("Le court-métrage '{$shortFilm->getTitleShortFilm()}' contient un intervenant non validé.");
            }
        }

        if (!in_array($statut, ['Produit', 'À financer'])) {
            throw new \InvalidArgumentException("Un événement ne peut être associé qu'à des courts-métrages ayant le statut 'Produit' ou 'À financer'.");
        }

        if (!$this->shortFilms->contains($shortFilm)) {
            $this->shortFilms->add($shortFilm);
        }

        return $this;
    }

    public function removeShortFilm(ShortFilm $shortFilm): static
    {
        $this->shortFilms->removeElement($shortFilm);

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
        if (in_array($this->typeEvent, ['Network', 'Location Network'])) {
            throw new \InvalidArgumentException("Les événements de type '{$this->typeEvent}' ne peuvent pas avoir d'intervenants associés.");
        }

        if ($speaker->getTypeSpeaker() !== 'Jury' || $speaker->getStatutSpeaker() !== 'Validé') {
            throw new \InvalidArgumentException("L'intervenant doit être de type 'Jury' et avoir le statut 'Validé'.");
        }

        if (!$this->speakers->contains($speaker)) {
            $this->speakers->add($speaker);
        }

        return $this;
    }

    public function removeSpeaker(Speaker $speaker): static
    {
        if ($this->speakers->contains($speaker)) {
            $this->speakers->removeElement($speaker);
        }

        return $this;
    }

    /**
     * @return Collection<int, UserEvent>
     */
    public function getUserEvents(): Collection
    {
        return $this->userEvents;
    }

    public function addUserEvent(UserEvent $userEvent): static
    {
        if (!$this->userEvents->contains($userEvent)) {
            $this->userEvents->add($userEvent);
            $userEvent->setEvent($this);
        }

        return $this;
    }

    public function removeUserEvent(UserEvent $userEvent): static
    {
        if ($this->userEvents->removeElement($userEvent)) {
            if ($userEvent->getEvent() === $this) {
                $userEvent->setEvent(null);
            }
        }

        return $this;
    }

    public function isFree(): ?bool
    {
        return $this->free;
    }

    public function setFree(bool $free): static
    {
        $this->free = $free;

        return $this;
    }

    /**
     * @return Collection<int, ArchivedEvent>
     */
    public function getArchivedEvents(): Collection
    {
        return $this->archivedEvents;
    }

    public function addArchivedEvent(ArchivedEvent $archivedEvent): static
    {
        if (!$this->archivedEvents->contains($archivedEvent)) {
            $this->archivedEvents->add($archivedEvent);
            $archivedEvent->setEvent($this);
        }

        return $this;
    }

    public function removeArchivedEvent(ArchivedEvent $archivedEvent): static
    {
        if ($this->archivedEvents->removeElement($archivedEvent)) {
            if ($archivedEvent->getEvent() === $this) {
                $archivedEvent->setEvent(null);
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

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        if ($location && $location->getTypeLocation() !== 'Événement') {
            throw new \InvalidArgumentException("Seuls les lieux de type 'Événement' peuvent être associés à un événement.");
        }

        $this->location = $location;

        return $this;
    }

    public function getPriceEvent(): ?string
    {
        return $this->priceEvent;
    }

    public function setPriceEvent(?string $priceEvent): static
    {
        $this->priceEvent = $priceEvent;

        return $this;
    }

    public function getParticipantsList(): self
    {
        return $this;
    }

    public function getParticipantsAsHtmlList(): string
    {
        if ($this->getUserEvents()->isEmpty()) {
            return '<em>Aucune inscription à ce jour</em>';
        }

        $html = '<ul>';
        foreach ($this->getUserEvents() as $userEvent) {
            $user = $userEvent->getUser();
            if ($user) {
                $fullName = htmlspecialchars($user->getFirstnameUser() . ' ' . $user->getLastnameUser());
                $html .= sprintf(
                    '<li><a href="/admin/?crudControllerFqcn=App%%5CController%%5CAdmin%%5CUserCrudController&crudAction=detail&entityId=%d" target="_blank">%s</a></li>',
                    $user->getId(),
                    $fullName
                );
            }
        }
        $html .= '</ul>';

        return $html;
    }

    public function __toString(): string
    {
        $title = $this->getTitleEvent();
        $type = $this->getTypeEvent();
        $edition = $this->getNumberEdition();

        if ($title && $type && $edition) {
            return sprintf('Édition n°%s [%s] - %s', $edition, $type, $title);
        }

        return 'Événement incomplet';
    }
}
