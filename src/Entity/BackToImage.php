<?php

namespace App\Entity;

use App\Repository\BackToImageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BackToImageRepository::class)]
class BackToImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(mappedBy: 'backToImage', targetEntity: ArchivedEvent::class)]
    private ?ArchivedEvent $archivedEvent = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $textBackToImage = null;

    /**
     * @var Collection<int, ImageBackToImage>
     */
    #[ORM\OneToMany(targetEntity: ImageBackToImage::class, mappedBy: 'backToImage')]
    private Collection $imageBackToImages;

    public function __construct()
    {
        $this->imageBackToImages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTextBackToImage(): ?string
    {
        return $this->textBackToImage;
    }

    public function setTextBackToImage(string $textBackToImage): static
    {
        $this->textBackToImage = $textBackToImage;

        return $this;
    }

    /**
     * @return Collection<int, ImageBackToImage>
     */
    public function getImageBackToImages(): Collection
    {
        return $this->imageBackToImages;
    }

    public function addImageBackToImage(ImageBackToImage $imageBackToImage): static
    {
        $existingAssociation = $imageBackToImage->getBackToImage();

        if ($existingAssociation !== null && $existingAssociation !== $this) {
            $event = $existingAssociation->getArchivedEvent()?->getEvent();
            $eventLabel = $event
                ? sprintf('"%s" du %s', $event->getTitleEvent(), $event->getDateEvent()->format('d/m/Y'))
                : 'un événement inconnu';

            throw new \LogicException(sprintf(
                'Cette image est déjà associée au retour sur image n°%d lié à %s.',
                $existingAssociation->getId(),
                $eventLabel
            ));
        }

        if (!$this->imageBackToImages->contains($imageBackToImage)) {
            $this->imageBackToImages->add($imageBackToImage);
            $imageBackToImage->setBackToImage($this);
        }

        return $this;
    }

    public function removeImageBackToImage(ImageBackToImage $imageBackToImage): static
    {
        if ($this->imageBackToImages->removeElement($imageBackToImage)) {
            if ($imageBackToImage->getBackToImage() === $this) {
                $imageBackToImage->setBackToImage(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        $id = $this->getId() ?? '???';

        if ($this->archivedEvent && $this->archivedEvent->getEvent()) {
            $event = $this->archivedEvent->getEvent();
            $date = $event->getDateEvent();
            $title = $event->getTitleEvent();

            if ($date instanceof \DateTimeInterface && $title) {
                return sprintf(
                    'N°%d - Retour sur image de la soirée "%s" du %s',
                    $id,
                    $title,
                    $date->format('d/m/Y')
                );
            }
        }

        return sprintf('N°%d - Ce retour sur image doit être lié à un événement archivé, contenir au minimum 2 images et un texte descriptif.', $id);
    }

    public function getImagesPreviewAsHtml(): string
    {
        $html = '<div style="display: flex; flex-wrap: wrap; gap: 10px;">';

        foreach ($this->getImageBackToImages() as $image) {
            $imgName = htmlspecialchars($image->getImgBackToImage());
            $html .= sprintf(
                '<img src="/images/événements/retours-sur-images/%s" alt="" style="max-height: 100px; border-radius: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.2);" />',
                $imgName
            );
        }

        $html .= '</div>';
        return $html;
    }
}
