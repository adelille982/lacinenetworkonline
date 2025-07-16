<?php

namespace App\Entity;

use App\Repository\SubCategoryAnnouncementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubCategoryAnnouncementRepository::class)]
class SubCategoryAnnouncement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'subCategoryAnnouncement')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CategoryAnnouncement $categoryAnnouncement = null;

    #[ORM\Column(length: 255)]
    private ?string $nameSubCategory = null;

    /**
     * @var Collection<int, Announcement>
     */
    #[ORM\OneToMany(targetEntity: Announcement::class, mappedBy: 'subCategoryAnnouncement')]
    private Collection $announcement;

    public function __construct()
    {
        $this->announcement = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoryAnnouncement(): ?CategoryAnnouncement
    {
        return $this->categoryAnnouncement;
    }

    public function setCategoryAnnouncement(?CategoryAnnouncement $categoryAnnouncement): static
    {
        $this->categoryAnnouncement = $categoryAnnouncement;

        return $this;
    }

    public function getNameSubCategory(): ?string
    {
        return $this->nameSubCategory;
    }

    public function setNameSubCategory(string $nameSubCategory): static
    {
        $this->nameSubCategory = $nameSubCategory;

        return $this;
    }

    /**
     * @return Collection<int, Announcement>
     */
    public function getAnnouncement(): Collection
    {
        return $this->announcement;
    }

    public function addAnnouncement(Announcement $announcement): static
    {
        if (!$this->announcement->contains($announcement)) {
            $this->announcement->add($announcement);
            $announcement->setSubCategoryAnnouncement($this);
        }

        return $this;
    }

    public function removeAnnouncement(Announcement $announcement): static
    {
        if ($this->announcement->removeElement($announcement)) {
            if ($announcement->getSubCategoryAnnouncement() === $this) {
                $announcement->setSubCategoryAnnouncement(null);
            }
        }

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->getCategoryAnnouncement()?->getColorCategoryAnnouncement();
    }

    public function __toString(): string
    {
        return $this->nameSubCategory ?? 'Sous-catégorie';
    }
}
