<?php

namespace App\Entity;

use App\Repository\ShortFilmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShortFilmRepository::class)]
class ShortFilm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $statutShortFilm = null;

    #[ORM\Column(length: 255)]
    private ?string $posterShortFilm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $posterPopUpShortFilm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $durationShortFilm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $genreShortFilm = null;

    #[ORM\Column(length: 255)]
    private ?string $titleShortFilm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $productionShortFilm = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $pitchShortFilm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imgProposal1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imgProposal2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imgProposal3 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imgProposal4 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imgProposal5 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkShortFilmProposal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkTrailerShortFilmProposal = null;

    #[ORM\Column(nullable: true)]
    private ?bool $formatDcp = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'shortFilms')]
    private Collection $events;

    /**
     * @var Collection<int, Speaker>
     */
    #[ORM\ManyToMany(targetEntity: Speaker::class, inversedBy: 'shortFilms')]
    private Collection $speakers;

    #[ORM\Column]
    private ?bool $draft = null;

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->speakers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatutShortFilm(): ?string
    {
        return $this->statutShortFilm;
    }

    public function setStatutShortFilm(string $statutShortFilm): static
    {
        $this->statutShortFilm = $statutShortFilm;

        return $this;
    }

    public function getPosterShortFilm(): ?string
    {
        return $this->posterShortFilm;
    }

    public function setPosterShortFilm(string $posterShortFilm): static
    {
        $this->posterShortFilm = $posterShortFilm;

        return $this;
    }

    public function getPosterPopUpShortFilm(): ?string
    {
        return $this->posterPopUpShortFilm;
    }

    public function setPosterPopUpShortFilm(?string $posterPopUpShortFilm): static
    {
        $this->posterPopUpShortFilm = $posterPopUpShortFilm;

        return $this;
    }

    public function getDurationShortFilm(): ?string
    {
        return $this->durationShortFilm;
    }

    public function setDurationShortFilm(string $durationShortFilm): static
    {
        $this->durationShortFilm = $durationShortFilm;

        return $this;
    }

    public function getGenreShortFilm(): ?string
    {
        return $this->genreShortFilm;
    }

    public function setGenreShortFilm(string $genreShortFilm): static
    {
        $this->genreShortFilm = $genreShortFilm;

        return $this;
    }

    public function getTitleShortFilm(): ?string
    {
        return $this->titleShortFilm;
    }

    public function setTitleShortFilm(string $titleShortFilm): static
    {
        $this->titleShortFilm = $titleShortFilm;

        return $this;
    }

    public function getProductionShortFilm(): ?string
    {
        return $this->productionShortFilm;
    }

    public function setProductionShortFilm(string $productionShortFilm): static
    {
        $this->productionShortFilm = $productionShortFilm;

        return $this;
    }

    public function getPitchShortFilm(): ?string
    {
        return $this->pitchShortFilm;
    }

    public function setPitchShortFilm(string $pitchShortFilm): static
    {
        $this->pitchShortFilm = $pitchShortFilm;

        return $this;
    }

    public function getImgProposal1(): ?string
    {
        return $this->imgProposal1;
    }

    public function setImgProposal1(?string $imgProposal1): static
    {
        $this->imgProposal1 = $imgProposal1;

        return $this;
    }

    public function getImgProposal2(): ?string
    {
        return $this->imgProposal2;
    }

    public function setImgProposal2(?string $imgProposal2): static
    {
        $this->imgProposal2 = $imgProposal2;

        return $this;
    }

    public function getImgProposal3(): ?string
    {
        return $this->imgProposal3;
    }

    public function setImgProposal3(?string $imgProposal3): static
    {
        $this->imgProposal3 = $imgProposal3;

        return $this;
    }

    public function getImgProposal4(): ?string
    {
        return $this->imgProposal4;
    }

    public function setImgProposal4(?string $imgProposal4): static
    {
        $this->imgProposal4 = $imgProposal4;

        return $this;
    }

    public function getImgProposal5(): ?string
    {
        return $this->imgProposal5;
    }

    public function setImgProposal5(?string $imgProposal5): static
    {
        $this->imgProposal5 = $imgProposal5;

        return $this;
    }

    public function getLinkShortFilmProposal(): ?string
    {
        return $this->linkShortFilmProposal;
    }

    public function setLinkShortFilmProposal(?string $linkShortFilmProposal): static
    {
        $this->linkShortFilmProposal = $linkShortFilmProposal;

        return $this;
    }

    public function getLinkTrailerShortFilmProposal(): ?string
    {
        return $this->linkTrailerShortFilmProposal;
    }

    public function setLinkTrailerShortFilmProposal(?string $linkTrailerShortFilmProposal): static
    {
        $this->linkTrailerShortFilmProposal = $linkTrailerShortFilmProposal;

        return $this;
    }

    public function isFormatDcp(): ?bool
    {
        return $this->formatDcp;
    }

    public function setFormatDcp(?bool $formatDcp): static
    {
        $this->formatDcp = $formatDcp;

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
            $event->addShortFilm($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            $event->removeShortFilm($this);
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
        if ($this->statutShortFilm === 'Produit') {
            if ($speaker->getTypeSpeaker() === 'Réalisateur' && $speaker->getStatutSpeaker() === 'Validé' && !$this->speakers->contains($speaker)) {
                $this->speakers->add($speaker);
            } else {
                throw new \InvalidArgumentException("L'intervenant doit être de type 'Réalisateur' et avoir le statut 'Validé' pour un court-métrage de type 'Produit'.");
            }
        } elseif ($this->statutShortFilm === 'À financer') {
            if (($speaker->getTypeSpeaker() === 'Réalisateur' || $speaker->getTypeSpeaker() === 'Stagiaire') && $speaker->getStatutSpeaker() === 'Validé' && !$this->speakers->contains($speaker)) {
                $this->speakers->add($speaker);
            } else {
                throw new \InvalidArgumentException("L'intervenant doit être de type 'Stagiaire' ou 'Réalisateur' et avoir le statut 'Validé' pour un court-métrage de type 'À financer'.");
            }
        } elseif ($this->statutShortFilm === 'Proposé') {
            if (($speaker->getTypeSpeaker() === 'Externe') && $speaker->getStatutSpeaker() === 'Proposé' && !$this->speakers->contains($speaker)) {
                $this->speakers->add($speaker);
            } else {
                throw new \InvalidArgumentException("L'intervenant doit être de type 'Externe' et avoir le statut 'Proposé' pour un court-métrage de type 'Proposé'.");
            }
        }

        return $this;
    }

    public function removeSpeaker(Speaker $speaker): static
    {
        $this->speakers->removeElement($speaker);

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

    public function __toString(): string
    {
        return $this->titleShortFilm . ' (' . $this->durationShortFilm . ')' . ' (' . $this->statutShortFilm . ')';
    }
}
