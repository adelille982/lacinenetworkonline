<?php

namespace App\Entity;

use App\Repository\CategoryAnnouncementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryAnnouncementRepository::class)]
class CategoryAnnouncement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $imgCategoryAnnouncement = null;

    #[ORM\Column(length: 255)]
    private ?string $nameCategoryAnnouncement = null;

    #[ORM\Column(length: 255)]
    private ?string $colorCategoryAnnouncement = null;

    /**
     * @var Collection<int, SubCategoryAnnouncement>
     */
    #[ORM\OneToMany(targetEntity: SubCategoryAnnouncement::class, mappedBy: 'categoryAnnouncement')]
    private Collection $subCategoryAnnouncement;

    public function __construct()
    {
        $this->subCategoryAnnouncement = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImgCategoryAnnouncement(): ?string
    {
        return $this->imgCategoryAnnouncement;
    }

    public function setImgCategoryAnnouncement(string $imgCategoryAnnouncement): static
    {
        $this->imgCategoryAnnouncement = $imgCategoryAnnouncement;

        return $this;
    }

    public function getNameCategoryAnnouncement(): ?string
    {
        return $this->nameCategoryAnnouncement;
    }

    public function setNameCategoryAnnouncement(string $nameCategoryAnnouncement): static
    {
        $this->nameCategoryAnnouncement = $nameCategoryAnnouncement;

        return $this;
    }

    public function getColorCategoryAnnouncement(): ?string
    {
        return $this->colorCategoryAnnouncement;
    }

    public function setColorCategoryAnnouncement(string $colorCategoryAnnouncement): static
    {
        $this->colorCategoryAnnouncement = $colorCategoryAnnouncement;

        return $this;
    }

    /**
     * @return Collection<int, SubCategoryAnnouncement>
     */
    public function getSubCategoryAnnouncement(): Collection
    {
        return $this->subCategoryAnnouncement;
    }

    public function addSubCategoryAnnouncement(SubCategoryAnnouncement $subCategoryAnnouncement): static
    {
        if (!$this->subCategoryAnnouncement->contains($subCategoryAnnouncement)) {
            $this->subCategoryAnnouncement->add($subCategoryAnnouncement);
            $subCategoryAnnouncement->setCategoryAnnouncement($this);
        }

        return $this;
    }

    public function removeSubCategoryAnnouncement(SubCategoryAnnouncement $subCategoryAnnouncement): static
    {
        if ($this->subCategoryAnnouncement->removeElement($subCategoryAnnouncement)) {
            if ($subCategoryAnnouncement->getCategoryAnnouncement() === $this) {
                $subCategoryAnnouncement->setCategoryAnnouncement(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nameCategoryAnnouncement ?? 'Catégorie';
    }
}
