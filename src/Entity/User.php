<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Un problème est survenu avec cet email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The picture user
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pictureUser = null;

    /**
     * @var string The firstname user
     */
    #[Assert\NotBlank(message: 'Veuillez renseigner votre prénom.')]
    #[ORM\Column(length: 255)]
    private ?string $firstnameUser = null;

    /**
     * @var string The lastname user
     */
    #[Assert\NotBlank(message: 'Veuillez renseigner votre nom.')]
    #[ORM\Column(length: 255)]
    private ?string $lastnameUser = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\Email(message: 'L\'adresse "{{ value }}" n\'est pas valide.')]
    private ?string $email = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var string The telephone user
     */
    #[Assert\Regex(
        pattern: '/^\+?[0-9]{7,15}$/',
        message: 'Le numéro de téléphone doit être valide (ex: +33612345678 ou 0612345678).'
    )]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephoneUser = null;

    /**
     * @var string The field of evolution user
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $fieldOfEvolutionUser = null;

    #[ORM\Column(type: 'boolean')]
    private bool $intermittentUser = false;

    /**
     * @var string The curriculum user
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $curriculumUser = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAtUser = null;

    /**
     * @var Collection<int, Announcement>
     */
    #[ORM\OneToMany(targetEntity: Announcement::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $announcements;

    #[ORM\Column]
    private bool $isVerified = false;

    /**
     * @var Collection<int, UserEvent>
     */
    #[ORM\OneToMany(targetEntity: UserEvent::class, mappedBy: 'user')]
    private Collection $userEvents;
    /**
     * @var Collection<int, Commentary>
     */
    #[ORM\OneToMany(targetEntity: Commentary::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $commentaries;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $conditionValidated = null;

    public function __construct()
    {
        $this->announcements = new ArrayCollection();
        $this->createdAtUser = new \DateTimeImmutable();
        $this->userEvents = new ArrayCollection();
        $this->commentaries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPictureUser(): ?string
    {
        return $this->pictureUser;
    }

    public function setPictureUser(?string $pictureUser): static
    {
        $this->pictureUser = $pictureUser;

        return $this;
    }

    public function getFirstnameUser(): ?string
    {
        return $this->firstnameUser;
    }

    public function setFirstnameUser(string $firstnameUser): static
    {
        $this->firstnameUser = $firstnameUser;

        return $this;
    }

    public function getLastnameUser(): ?string
    {
        return $this->lastnameUser;
    }

    public function setLastnameUser(string $lastnameUser): static
    {
        $this->lastnameUser = $lastnameUser;

        return $this;
    }

    public function getTelephoneUser(): ?string
    {
        return $this->telephoneUser;
    }

    public function setTelephoneUser(string $telephoneUser): static
    {
        $this->telephoneUser = $telephoneUser;

        return $this;
    }

    public function getFieldOfEvolutionUser(): ?string
    {
        return $this->fieldOfEvolutionUser;
    }

    public function setFieldOfEvolutionUser(?string $fieldOfEvolutionUser): static
    {
        $this->fieldOfEvolutionUser = $fieldOfEvolutionUser;

        return $this;
    }

    public function isIntermittentUser(): bool
    {
        return $this->intermittentUser;
    }

    public function setIntermittentUser(bool $intermittentUser): static
    {
        $this->intermittentUser = $intermittentUser;
        return $this;
    }

    public function getCurriculumUser(): ?string
    {
        return $this->curriculumUser;
    }

    public function setCurriculumUser(?string $curriculumUser): static
    {
        $this->curriculumUser = $curriculumUser;

        return $this;
    }

    public function getCreatedAtUser(): ?\DateTimeInterface
    {
        return $this->createdAtUser;
    }

    public function setCreatedAtUser(\DateTimeInterface $createdAtUser): static
    {
        $this->createdAtUser = $createdAtUser;

        return $this;
    }

    /**
     * @return Collection<int, Announcement>
     */
    public function getAnnouncements(): Collection
    {
        return $this->announcements;
    }

    public function addAnnouncement(Announcement $announcement): static
    {
        if (!$this->announcements->contains($announcement)) {
            $this->announcements->add($announcement);
            $announcement->setUser($this);
        }

        return $this;
    }

    public function removeAnnouncement(Announcement $announcement): static
    {
        if ($this->announcements->removeElement($announcement)) {
            if ($announcement->getUser() === $this) {
                $announcement->setUser(null);
            }
        }

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function setIsVerified(bool $isVerified): static
    {
        return $this->setVerified($isVerified);
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
            $userEvent->setUser($this);
        }

        return $this;
    }

    public function removeUserEvent(UserEvent $userEvent): static
    {
        if ($this->userEvents->removeElement($userEvent)) {
            if ($userEvent->getUser() === $this) {
                $userEvent->setUser(null);
            }
        }

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
            $commentary->setUser($this);
        }

        return $this;
    }

    public function removeCommentary(Commentary $commentary): static
    {
        if ($this->commentaries->removeElement($commentary)) {
            if ($commentary->getUser() === $this) {
                $commentary->setUser(null);
            }
        }

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

    public function getFullName(): string
    {
        return trim($this->firstnameUser . ' ' . $this->lastnameUser);
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }
}
