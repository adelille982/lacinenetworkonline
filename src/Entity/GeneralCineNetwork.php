<?php

namespace App\Entity;

use App\Repository\GeneralCineNetworkRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GeneralCineNetworkRepository::class)]
class GeneralCineNetwork
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $emailCompany = null;

    #[ORM\Column(length: 255)]
    private ?string $personalEmail = null;

    #[ORM\Column(length: 255)]
    private ?string $titleGeneralCineNetwork = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresseCompany = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telCompany = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $textAbout = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rgpdContent = null;

    #[ORM\Column(length: 255)]
    private ?string $imgFormAnnouncement = null;

    #[ORM\Column(length: 255)]
    private ?string $imgFormPostulate = null;

    #[ORM\Column(length: 255)]
    private ?string $imgFormNetPitch = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $textRgpd = null;

    #[ORM\Column(length: 255)]
    private ?string $metaDescriptionGeneralCineNetwork = null;

    #[ORM\Column(length: 255)]
    private ?string $seoKeyGeneralCineNetwork = null;

    #[ORM\Column(length: 255)]
    private ?string $imgCommentNetwork = null;

    #[ORM\Column(length: 255)]
    private ?string $imgCommentNetPitch = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imgFormLogin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imgFormRegistration = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkFacebook = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkInstagram = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imgFormResetPasword = null;

    #[ORM\Column]
    private ?bool $shortFilmProposalHome = null;

    #[ORM\Column(length: 255)]
    private ?string $replacementImage = null;

    #[ORM\Column(length: 255)]
    private ?string $imgAbout = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmailCompany(): ?string
    {
        return $this->emailCompany;
    }

    public function setEmailCompany(string $emailCompany): static
    {
        $this->emailCompany = $emailCompany;

        return $this;
    }

    public function getPersonalEmail(): ?string
    {
        return $this->personalEmail;
    }

    public function setPersonalEmail(string $personalEmail): static
    {
        $this->personalEmail = $personalEmail;

        return $this;
    }

    public function getTitleGeneralCineNetwork(): ?string
    {
        return $this->titleGeneralCineNetwork;
    }

    public function setTitleGeneralCineNetwork(string $titleGeneralCineNetwork): static
    {
        $this->titleGeneralCineNetwork = $titleGeneralCineNetwork;

        return $this;
    }

    public function getAdresseCompany(): ?string
    {
        return $this->adresseCompany;
    }

    public function setAdresseCompany(?string $adresseCompany): static
    {
        $this->adresseCompany = $adresseCompany;
        return $this;
    }

    public function getTelCompany(): ?string
    {
        return $this->telCompany;
    }

    public function setTelCompany(?string $telCompany): static
    {
        $this->telCompany = $telCompany;

        return $this;
    }

    public function getTextAbout(): ?string
    {
        return $this->textAbout;
    }

    public function setTextAbout(string $textAbout): static
    {
        $this->textAbout = $textAbout;

        return $this;
    }

    public function getRgpdContent(): ?string
    {
        return $this->rgpdContent;
    }

    public function setRgpdContent(?string $rgpdContent): static
    {
        $this->rgpdContent = $rgpdContent;

        return $this;
    }

    public function getImgFormAnnouncement(): ?string
    {
        return $this->imgFormAnnouncement;
    }

    public function setImgFormAnnouncement(string $imgFormAnnouncement): static
    {
        $this->imgFormAnnouncement = $imgFormAnnouncement;

        return $this;
    }

    public function getImgFormPostulate(): ?string
    {
        return $this->imgFormPostulate;
    }

    public function setImgFormPostulate(string $imgFormPostulate): static
    {
        $this->imgFormPostulate = $imgFormPostulate;

        return $this;
    }

    public function getImgFormNetPitch(): ?string
    {
        return $this->imgFormNetPitch;
    }

    public function setImgFormNetPitch(string $imgFormNetPitch): static
    {
        $this->imgFormNetPitch = $imgFormNetPitch;

        return $this;
    }

    public function getTextRgpd(): ?string
    {
        return $this->textRgpd;
    }

    public function setTextRgpd(?string $textRgpd): static
    {
        $this->textRgpd = $textRgpd;

        return $this;
    }

    public function getMetaDescriptionGeneralCineNetwork(): ?string
    {
        return $this->metaDescriptionGeneralCineNetwork;
    }

    public function setMetaDescriptionGeneralCineNetwork(string $metaDescriptionGeneralCineNetwork): static
    {
        $this->metaDescriptionGeneralCineNetwork = $metaDescriptionGeneralCineNetwork;

        return $this;
    }

    public function getSeoKeyGeneralCineNetwork(): ?string
    {
        return $this->seoKeyGeneralCineNetwork;
    }

    public function setSeoKeyGeneralCineNetwork(string $seoKeyGeneralCineNetwork): static
    {
        $this->seoKeyGeneralCineNetwork = $seoKeyGeneralCineNetwork;

        return $this;
    }

    public function getImgCommentNetwork(): ?string
    {
        return $this->imgCommentNetwork;
    }

    public function setImgCommentNetwork(string $imgCommentNetwork): static
    {
        $this->imgCommentNetwork = $imgCommentNetwork;

        return $this;
    }

    public function getImgCommentNetPitch(): ?string
    {
        return $this->imgCommentNetPitch;
    }

    public function setImgCommentNetPitch(string $imgCommentNetPitch): static
    {
        $this->imgCommentNetPitch = $imgCommentNetPitch;

        return $this;
    }

    public function getImgFormLogin(): ?string
    {
        return $this->imgFormLogin;
    }

    public function setImgFormLogin(?string $imgFormLogin): static
    {
        $this->imgFormLogin = $imgFormLogin;

        return $this;
    }

    public function getImgFormRegistration(): ?string
    {
        return $this->imgFormRegistration;
    }

    public function setImgFormregistration(?string $imgFormRegistration): static
    {
        $this->imgFormRegistration = $imgFormRegistration;

        return $this;
    }

    public function getImgFormResetPasword(): ?string
    {
        return $this->imgFormResetPasword;
    }

    public function setImgFormResetPasword(?string $imgFormResetPasword): static
    {
        $this->imgFormResetPasword  = $imgFormResetPasword;

        return $this;
    }

    public function getLinkFacebook(): ?string
    {
        return $this->linkFacebook;
    }

    public function setLinkFacebook(?string $linkFacebook): static
    {
        $this->linkFacebook = $linkFacebook;

        return $this;
    }

    public function getLinkInstagram(): ?string
    {
        return $this->linkInstagram;
    }

    public function setLinkInstagram(?string $linkInstagram): static
    {
        $this->linkInstagram = $linkInstagram;

        return $this;
    }

    public function isShortFilmProposalHome(): ?bool
    {
        return $this->shortFilmProposalHome;
    }

    public function setShortFilmProposalHome(bool $shortFilmProposalHome): static
    {
        $this->shortFilmProposalHome = $shortFilmProposalHome;

        return $this;
    }

    public function getReplacementImage(): ?string
    {
        return $this->replacementImage;
    }

    public function setReplacementImage(string $replacementImage): static
    {
        $this->replacementImage = $replacementImage;

        return $this;
    }

    public function getImgAbout(): ?string
    {
        return $this->imgAbout;
    }

    public function setImgAbout(string $imgAbout): static
    {
        $this->imgAbout = $imgAbout;

        return $this;
    }
}
