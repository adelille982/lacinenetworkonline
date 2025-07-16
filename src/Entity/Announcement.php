<?php

namespace App\Entity;

use App\Repository\AnnouncementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnnouncementRepository::class)]
class Announcement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $typeAnnouncement = null;

    #[ORM\ManyToOne(inversedBy: 'announcement')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SubCategoryAnnouncement $subCategoryAnnouncement = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $textAnnouncement = null;

    #[ORM\Column(length: 255)]
    private ?string $departmentAnnouncement = null;

    #[ORM\Column(length: 255)]
    private ?string $cityAnnouncement = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $availabilityAnnouncement = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $expiryAnnouncement = null;

    #[ORM\Column(length: 255)]
    private ?string $linkAnnouncement = null;

    #[ORM\Column]
    private ?bool $remuneration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAtAnnouncement = null;

    #[ORM\ManyToOne(inversedBy: 'announcements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeAnnouncement(): ?string
    {
        return $this->typeAnnouncement;
    }

    public function setTypeAnnouncement(string $typeAnnouncement): static
    {
        $this->typeAnnouncement = $typeAnnouncement;

        return $this;
    }

    public function getTextAnnouncement(): ?string
    {
        return $this->textAnnouncement;
    }

    public function setTextAnnouncement(string $textAnnouncement): static
    {
        $this->textAnnouncement = $textAnnouncement;

        return $this;
    }

    public function getDepartmentAnnouncement(): ?string
    {
        return $this->departmentAnnouncement;
    }

    public function setDepartmentAnnouncement(string $departmentAnnouncement): static
    {
        $this->departmentAnnouncement = $departmentAnnouncement;

        return $this;
    }

    public function getCityAnnouncement(): ?string
    {
        return $this->cityAnnouncement;
    }

    public function setCityAnnouncement(string $cityAnnouncement): static
    {
        $this->cityAnnouncement = $cityAnnouncement;

        return $this;
    }

    public function getAvailabilityAnnouncement(): ?\DateTimeInterface
    {
        return $this->availabilityAnnouncement;
    }

    public function setAvailabilityAnnouncement(\DateTimeInterface $availabilityAnnouncement): static
    {
        $this->availabilityAnnouncement = $availabilityAnnouncement;

        return $this;
    }

    public function isRemuneration(): ?bool
    {
        return $this->remuneration;
    }

    public function setRemuneration(bool $remuneration): static
    {
        $this->remuneration = $remuneration;

        return $this;
    }

    public function getExpiryAnnouncement(): ?\DateTimeInterface
    {
        return $this->expiryAnnouncement;
    }

    public function setExpiryAnnouncement(\DateTimeInterface $expiryAnnouncement): static
    {
        $this->expiryAnnouncement = $expiryAnnouncement;

        return $this;
    }

    public function getLinkAnnouncement(): ?string
    {
        return $this->linkAnnouncement;
    }

    public function setLinkAnnouncement(string $linkAnnouncement): static
    {
        $this->linkAnnouncement = $linkAnnouncement;

        return $this;
    }

    public function getCreatedAtAnnouncement(): ?\DateTimeInterface
    {
        return $this->createdAtAnnouncement;
    }

    public function setCreatedAtAnnouncement(\DateTimeInterface $createdAtAnnouncement): static
    {
        $this->createdAtAnnouncement = $createdAtAnnouncement;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getSubCategoryAnnouncement(): ?SubCategoryAnnouncement
    {
        return $this->subCategoryAnnouncement;
    }

    public function setSubCategoryAnnouncement(?SubCategoryAnnouncement $subCategoryAnnouncement): static
    {
        $this->subCategoryAnnouncement = $subCategoryAnnouncement;

        return $this;
    }
}
