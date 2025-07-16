<?php

namespace App\Entity;

use App\Repository\BlogCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogCategoryRepository::class)]
class BlogCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $slugBlogCategory = null;

    #[ORM\Column(length: 255)]
    private ?string $nameBlogCategory = null;

    #[ORM\Column(length: 255)]
    private ?string $metaDescriptionBlogCategory = null;

    #[ORM\Column(length: 255)]
    private ?string $seoKeyBlogCategory = null;

    /**
     * @var Collection<int, BlogPost>
     */
    #[ORM\ManyToMany(targetEntity: BlogPost::class, inversedBy: 'blogCategories')]
    private Collection $blogPost;

    public function __construct()
    {
        $this->blogPost = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlugBlogCategory(): ?string
    {
        return $this->slugBlogCategory;
    }

    public function setSlugBlogCategory(string $slugBlogCategory): static
    {
        $this->slugBlogCategory = $slugBlogCategory;

        return $this;
    }

    public function getNameBlogCategory(): ?string
    {
        return $this->nameBlogCategory;
    }

    public function setNameBlogCategory(string $nameBlogCategory): static
    {
        $this->nameBlogCategory = $nameBlogCategory;

        return $this;
    }

    public function getMetaDescriptionBlogCategory(): ?string
    {
        return $this->metaDescriptionBlogCategory;
    }

    public function setMetaDescriptionBlogCategory(string $metaDescriptionBlogCategory): static
    {
        $this->metaDescriptionBlogCategory = $metaDescriptionBlogCategory;

        return $this;
    }

    public function getSeoKeyBlogCategory(): ?string
    {
        return $this->seoKeyBlogCategory;
    }

    public function setSeoKeyBlogCategory(string $seoKeyBlogCategory): static
    {
        $this->seoKeyBlogCategory = $seoKeyBlogCategory;

        return $this;
    }

    /**
     * @return Collection<int, BlogPost>
     */
    public function getBlogPost(): Collection
    {
        return $this->blogPost;
    }

    public function addBlogPost(BlogPost $blogPost): static
    {
        if (!$this->blogPost->contains($blogPost)) {
            $this->blogPost->add($blogPost);
        }

        return $this;
    }

    public function removeBlogPost(BlogPost $blogPost): static
    {
        $this->blogPost->removeElement($blogPost);

        return $this;
    }

    public function __toString(): string
    {
        return $this->nameBlogCategory ?? 'Catégorie sans titre';
    }
}
