<?php

namespace App\Entity;

use App\Repository\CommentaryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaryRepository::class)]
class Commentary
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commentaries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'commentaries')]
    #[ORM\JoinColumn(nullable: true)]
    private ?ArchivedEvent $archivedEvent = null;

    #[ORM\ManyToOne(targetEntity: NetPitchFormation::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?NetPitchFormation $netPitchFormation = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $textCommentary = null;

    #[ORM\Column(length: 255)]
    private ?string $statutCommentary = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $validatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getArchivedEvent(): ?ArchivedEvent
    {
        return $this->archivedEvent;
    }

    public function setArchivedEvent(?ArchivedEvent $archivedEvent): static
    {
        $this->archivedEvent = $archivedEvent;

        return $this;
    }

    public function getNetPitchFormation(): ?NetPitchFormation
    {
        return $this->netPitchFormation;
    }

    public function setNetPitchFormation(?NetPitchFormation $netPitchFormation): static
    {
        $this->netPitchFormation = $netPitchFormation;

        return $this;
    }

    public function getTextCommentary(): ?string
    {
        return $this->textCommentary;
    }

    public function setTextCommentary(string $textCommentary): static
    {
        $this->textCommentary = $textCommentary;

        return $this;
    }

    public function getStatutCommentary(): ?string
    {
        return $this->statutCommentary;
    }

    public function setStatutCommentary(string $statutCommentary): static
    {
        $this->statutCommentary = $statutCommentary;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getValidatedAt(): ?\DateTimeInterface
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(\DateTimeInterface $validatedAt): static
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }
}
