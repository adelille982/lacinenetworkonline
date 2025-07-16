<?php

namespace App\Entity;

use App\Repository\PostulateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostulateRepository::class)]
class Postulate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez renseigner votre prénom.')]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez renseigner votre nom.')]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    #[Assert\Email(message: 'L\'adresse "{{ value }}" n\'est pas valide.')]
    #[Assert\NotBlank(message: 'Veuillez renseigner une adresse e-mail.')]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez renseigner un numéro de téléphone.')]
    #[Assert\Regex(
        pattern: '/^\+?[0-9]{7,15}$/',
        message: 'Le numéro de téléphone doit être valide (ex: +33612345678 ou 0612345678).'
    )]
    private ?string $telephone = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $professionalExperience = null;

    #[ORM\Column(length: 255)]
    private ?string $curiculum = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $conditionValidated = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getProfessionalExperience(): ?string
    {
        return $this->professionalExperience;
    }

    public function setProfessionalExperience(string $professionalExperience): static
    {
        $this->professionalExperience = $professionalExperience;

        return $this;
    }

    public function getCuriculum(): ?string
    {
        return $this->curiculum;
    }

    public function setCuriculum(string $curiculum): static
    {
        $this->curiculum = $curiculum;

        return $this;
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
