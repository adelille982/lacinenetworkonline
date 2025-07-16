<?php

namespace App\Entity;

use App\Repository\HeaderHomeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HeaderHomeRepository::class)]
class HeaderHome
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $videoHeaderHome = null;

    #[ORM\Column(length: 255)]
    private ?string $titleHeaderHome = null;

    #[ORM\Column(length: 255)]
    private ?string $sloganHeaderHome = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    private ?string $numberEdition = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    private ?string $numberParticipant = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    private ?string $numberMovieReceived = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    private ?string $numberMovieProjected = null;

    /**
     * @var Collection<int, HeaderHomeImg>
     */
    #[ORM\OneToMany(targetEntity: HeaderHomeImg::class, mappedBy: 'headerHome')]
    private Collection $headerHomeImgs;

    #[ORM\Column]
    private ?bool $draft = null;

    public function __construct()
    {
        $this->headerHomeImgs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVideoHeaderHome(): ?string
    {
        return $this->videoHeaderHome;
    }

    public function setVideoHeaderHome(string $videoHeaderHome): static
    {
        $this->videoHeaderHome = $videoHeaderHome;

        return $this;
    }

    public function getTitleHeaderHome(): ?string
    {
        return $this->titleHeaderHome;
    }

    public function setTitleHeaderHome(string $titleHeaderHome): static
    {
        $this->titleHeaderHome = $titleHeaderHome;

        return $this;
    }

    public function getSloganHeaderHome(): ?string
    {
        return $this->sloganHeaderHome;
    }

    public function setSloganHeaderHome(string $sloganHeaderHome): static
    {
        $this->sloganHeaderHome = $sloganHeaderHome;

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

    public function getNumberParticipant(): ?string
    {
        return $this->numberParticipant;
    }

    public function setNumberParticipant(string $numberParticipant): static
    {
        $this->numberParticipant = $numberParticipant;

        return $this;
    }

    public function getNumberMovieReceived(): ?string
    {
        return $this->numberMovieReceived;
    }

    public function setNumberMovieReceived(string $numberMovieReceived): static
    {
        $this->numberMovieReceived = $numberMovieReceived;

        return $this;
    }

    public function getNumberMovieProjected(): ?string
    {
        return $this->numberMovieProjected;
    }

    public function setNumberMovieProjected(string $numberMovieProjected): static
    {
        $this->numberMovieProjected = $numberMovieProjected;

        return $this;
    }

    /**
     * @return Collection<int, HeaderHomeImg>
     */
    public function getHeaderHomeImgs(): Collection
    {
        return $this->headerHomeImgs;
    }

    public function addHeaderHomeImg(HeaderHomeImg $headerHomeImg): static
    {
        if (!$this->headerHomeImgs->contains($headerHomeImg)) {
            $this->headerHomeImgs->add($headerHomeImg);
            $headerHomeImg->setHeaderHome($this);
        }

        return $this;
    }

    public function removeHeaderHomeImg(HeaderHomeImg $headerHomeImg): static
    {
        if ($this->headerHomeImgs->removeElement($headerHomeImg)) {
            if ($headerHomeImg->getHeaderHome() === $this) {
                $headerHomeImg->setHeaderHome(null);
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

    public function __toString(): string
    {
        $id = $this->id ?? 0;
        $title = $this->titleHeaderHome ?? 'Sans titre';
        $slogan = $this->sloganHeaderHome ?? '';

        return sprintf('N° %d - %s - %s', $id, $title, $slogan);
    }
}
