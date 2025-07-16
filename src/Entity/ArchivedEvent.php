<?php

namespace App\Entity;

use App\Repository\ArchivedEventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArchivedEventRepository::class)]
class ArchivedEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'archivedEvents')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Event $event = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $archivedAt = null;

    #[ORM\OneToOne(inversedBy: 'archivedEvent', cascade: ['persist', 'remove'])]
    private ?BackToImage $backToImage = null;

    /**
     * @var Collection<int, Commentary>
     */
    #[ORM\OneToMany(targetEntity: Commentary::class, mappedBy: 'archivedEvent')]
    private Collection $commentaries;

    #[ORM\Column]
    private ?bool $draft = null;

    public function __construct()
    {
        $this->archivedAt = new \DateTime();
        $this->commentaries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getArchivedAt(): ?\DateTimeInterface
    {
        return $this->archivedAt;
    }

    public function setArchivedAt(\DateTimeInterface $archivedAt): static
    {
        $this->archivedAt = $archivedAt;

        return $this;
    }

    public function getBackToImage(): ?BackToImage
    {
        return $this->backToImage;
    }

    public function setBackToImage(?BackToImage $backToImage): static
    {
        $this->backToImage = $backToImage;

        return $this;
    }

    /**
     * @return Collection<int, Commentary>
     */
    public function getCommentaries(): Collection
    {
        return $this->commentaries;
    }

    public function addCommentary(Commentary $commentary): static
    {
        if (!$this->commentaries->contains($commentary)) {
            $this->commentaries->add($commentary);
            $commentary->setArchivedEvent($this);
        }

        return $this;
    }

    public function removeCommentary(Commentary $commentary): static
    {
        if ($this->commentaries->removeElement($commentary)) {
            if ($commentary->getArchivedEvent() === $this) {
                $commentary->setArchivedEvent(null);
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

    public function getShortFilmsList(): string
    {
        return $this->getShortFilmsAsHtmlList();
    }

    public function getSpeakersList(): string
    {
        return $this->getSpeakersAsHtmlList();
    }

    public function getParticipantsList(): ?self
    {
        return $this;
    }

    public function getShortFilmsAsHtmlList(): string
    {
        $films = $this->getEvent()?->getShortFilms() ?? [];

        if (empty($films)) {
            return '<em>Aucun court-métrage associé</em>';
        }

        $html = '<ul>';
        foreach ($films as $film) {
            $html .= sprintf(
                '<li><a href="/admin/?crudControllerFqcn=App%%5CController%%5CAdmin%%5CTotalShortFilmCrudController&crudAction=detail&entityId=%d" target="_blank">%s</a></li>',
                $film->getId(),
                htmlspecialchars($film->getTitleShortFilm())
            );
        }
        $html .= '</ul>';

        return $html;
    }

    public function getSpeakersAsHtmlList(): string
    {
        $speakers = $this->getEvent()?->getSpeakers() ?? [];

        if (empty($speakers)) {
            return '<em>Aucun jury associé</em>';
        }

        $html = '<ul>';
        foreach ($speakers as $speaker) {
            $controllerMap = [
                'Externe' => 'ProposalCrudController',
                'Stagiaire' => 'InternCrudController',
                'Réalisateur' => 'ProducerCrudController',
                'Formateur' => 'TrainerCrudController',
                'Jury' => 'JuryCrudController',
                'Entreprise' => 'CompanySpeakerCrudController',
            ];

            $controller = $controllerMap[$speaker->getTypeSpeaker()] ?? null;
            if ($controller) {
                $url = sprintf(
                    '/admin/?crudControllerFqcn=App%%5CController%%5CAdmin%%5C%s&crudAction=detail&entityId=%d',
                    $controller,
                    $speaker->getId()
                );
                $html .= sprintf('<li><a href="%s" target="_blank">%s</a></li>', $url, htmlspecialchars((string) $speaker));
            } else {
                $html .= sprintf('<li>%s</li>', htmlspecialchars((string) $speaker));
            }
        }
        $html .= '</ul>';

        return $html;
    }

    public function getParticipantsAsHtmlList(): string
    {
        $userEvents = $this->getEvent()?->getUserEvents() ?? [];

        if (count($userEvents) === 0) {
            return '<em>Aucune inscription n\'avait été souscrite</em>';
        }

        $html = '<ul>';
        foreach ($userEvents as $userEvent) {
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
        if (!$this->event) {
            return 'Événement archivé (incomplet)';
        }

        return sprintf(
            'Édition n° %s - Type: [%s] - %s - Événement réalisé le : %s - Archivé le %s',
            $this->event->getNumberEdition(),
            $this->event->getTypeEvent(),
            $this->event->getTitleEvent(),
            $this->event->getDateEvent()->format('d/m/Y'),
            $this->archivedAt->format('d/m/Y'),
        );
    }
}
