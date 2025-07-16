<?php

namespace App\Entity;

use App\Repository\RegistrationNetPitchFormationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RegistrationNetPitchFormationRepository::class)]
class RegistrationNetPitchFormation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Veuillez renseigner votre prénom.')]
    #[ORM\Column(length: 255)]
    private ?string $firstnameRegistration = null;

    #[Assert\NotBlank(message: 'Veuillez renseigner votre nom.')]
    #[ORM\Column(length: 255)]
    private ?string $lastnameRegistration = null;

    #[ORM\Column(length: 255)]
    private ?string $emailRegistration = null;

    #[Assert\NotBlank(message: 'Veuillez renseigner un numéro de téléphone.')]
    #[Assert\Regex(
        pattern: '/^\+?[0-9]{7,15}$/',
        message: 'Le numéro de téléphone doit être valide (ex: +33612345678 ou 0612345678).'
    )]
    #[ORM\Column(length: 255)]
    private ?string $telRegistration = null;

    #[ORM\Column]
    private ?bool $afdas = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $professionalProjectRegistration = null;

    #[ORM\Column(length: 255)]
    private ?string $cvRegistration = null;

    #[ORM\Column(length: 255)]
    private ?string $statutRegistration = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAtRegistration = null;

    #[ORM\ManyToOne(inversedBy: 'registrationNetPitchFormations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SessionNetPitchFormation $sessionNetPitchFormation = null;

    #[ORM\ManyToOne(inversedBy: 'registrationSessionNetPitchFormation')]
    private ?ArchivedSessionNetPitchFormation $archivedSessionNetPitchFormation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $conditionValidated = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstnameRegistration(): ?string
    {
        return $this->firstnameRegistration;
    }

    public function setFirstnameRegistration(string $firstnameRegistration): static
    {
        $this->firstnameRegistration = $firstnameRegistration;

        return $this;
    }

    public function getLastnameRegistration(): ?string
    {
        return $this->lastnameRegistration;
    }

    public function setLastnameRegistration(string $lastnameRegistration): static
    {
        $this->lastnameRegistration = $lastnameRegistration;

        return $this;
    }

    public function getEmailRegistration(): ?string
    {
        return $this->emailRegistration;
    }

    public function setEmailRegistration(string $emailRegistration): static
    {
        $this->emailRegistration = $emailRegistration;

        return $this;
    }

    public function getTelRegistration(): ?string
    {
        return $this->telRegistration;
    }

    public function setTelRegistration(string $telRegistration): static
    {
        $this->telRegistration = $telRegistration;

        return $this;
    }

    public function getProfessionalProjectRegistration(): ?string
    {
        return $this->professionalProjectRegistration;
    }

    public function setProfessionalProjectRegistration(string $professionalProjectRegistration): static
    {
        $this->professionalProjectRegistration = $professionalProjectRegistration;

        return $this;
    }

    public function getCvRegistration(): ?string
    {
        return $this->cvRegistration;
    }

    public function setCvRegistration(string $cvRegistration): static
    {
        $this->cvRegistration = $cvRegistration;

        return $this;
    }

    public function getStatutRegistration(): ?string
    {
        return $this->statutRegistration;
    }

    public function setStatutRegistration(string $statutRegistration): static
    {
        $this->statutRegistration = $statutRegistration;

        return $this;
    }

    public function getCreatedAtRegistration(): ?\DateTimeImmutable
    {
        return $this->createdAtRegistration;
    }

    public function setCreatedAtRegistration(\DateTimeImmutable $createdAtRegistration): static
    {
        $this->createdAtRegistration = $createdAtRegistration;

        return $this;
    }

    public function getSessionNetPitchFormation(): ?SessionNetPitchFormation
    {
        return $this->sessionNetPitchFormation;
    }

    public function setSessionNetPitchFormation(?SessionNetPitchFormation $sessionNetPitchFormation): static
    {
        $this->sessionNetPitchFormation = $sessionNetPitchFormation;

        return $this;
    }

    public function getArchivedSessionNetPitchFormation(): ?ArchivedSessionNetPitchFormation
    {
        return $this->archivedSessionNetPitchFormation;
    }

    public function setArchivedSessionNetPitchFormation(?ArchivedSessionNetPitchFormation $archivedSessionNetPitchFormation): static
    {
        $this->archivedSessionNetPitchFormation = $archivedSessionNetPitchFormation;

        return $this;
    }

        public function isAfdas(): ?bool
    {
        return $this->afdas;
    }

    public function setAfdas(bool $afdas): static
    {
        $this->afdas = $afdas;

        return $this;
    }

    public function __toString(): string
    {
        return $this->firstnameRegistration . ' ' . $this->lastnameRegistration;
    }

    public function getConditionValidated(): ?\DateTimeInterface
    {
        return $this->conditionValidated;
    }

    public function setConditionValidated(\DateTimeInterface $conditionValidated): static
    {
        $this->conditionValidated = $conditionValidated;

        return $this;
    }
}
