<?php

namespace App\Entity;

use App\Repository\BlogPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogPostRepository::class)]
class BlogPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $slugBlogPost = null;

    #[ORM\Column(length: 255)]
    private ?string $titlePost = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $videoBlogPost = null;

    #[ORM\Column(length: 255)]
    private ?string $mainImgPost = null;

    #[ORM\Column(length: 255)]
    private ?string $imgNumber2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imgNumber3 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imgNumber4 = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $shortDescriptionBlogPost = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contentBlogPost = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contentBlogPost2 = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $publicationDateBlogPost = null;

    #[ORM\Column(length: 255)]
    private ?string $authorBlogPost = null;

    #[ORM\Column(length: 255)]
    private ?string $metaDescriptionBlogPost = null;

    #[ORM\Column(length: 255)]
    private ?string $seoKeyBlogPost = null;

    /**
     * @var Collection<int, BlogCategory>
     */
    #[ORM\ManyToMany(targetEntity: BlogCategory::class, mappedBy: 'blogPost')]
    private Collection $blogCategories;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'blogPosts')]
    private Collection $blogPost;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'blogPost')]
    private Collection $blogPosts;

    #[ORM\Column]
    private ?bool $draft = null;

    public function __construct()
    {
        $this->blogCategories = new ArrayCollection();
        $this->blogPost = new ArrayCollection();
        $this->blogPosts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlugBlogPost(): ?string
    {
        return $this->slugBlogPost;
    }

    public function setSlugBlogPost(string $slugBlogPost): static
    {
        $this->slugBlogPost = $slugBlogPost;

        return $this;
    }

    public function getTitlePost(): ?string
    {
        return $this->titlePost;
    }

    public function setTitlePost(string $titlePost): static
    {
        $this->titlePost = $titlePost;

        return $this;
    }

    public function getVideoBlogPost(): ?string
    {
        return $this->videoBlogPost;
    }

    public function setVideoBlogPost(?string $videoBlogPost): static
    {
        $this->videoBlogPost = $videoBlogPost;

        return $this;
    }

    public function getMainImgPost(): ?string
    {
        return $this->mainImgPost;
    }

    public function setMainImgPost(string $mainImgPost): static
    {
        $this->mainImgPost = $mainImgPost;

        return $this;
    }

    public function getImgNumber2(): ?string
    {
        return $this->imgNumber2;
    }

    public function setImgNumber2(string $imgNumber2): static
    {
        $this->imgNumber2 = $imgNumber2;

        return $this;
    }

    public function getImgNumber3(): ?string
    {
        return $this->imgNumber3;
    }

    public function setImgNumber3(?string $imgNumber3): static
    {
        $this->imgNumber3 = $imgNumber3;

        return $this;
    }

    public function getImgNumber4(): ?string
    {
        return $this->imgNumber4;
    }

    public function setImgNumber4(?string $imgNumber4): static
    {
        $this->imgNumber4 = $imgNumber4;

        return $this;
    }

    public function getShortDescriptionBlogPost(): ?string
    {
        return $this->shortDescriptionBlogPost;
    }

    public function setShortDescriptionBlogPost(string $shortDescriptionBlogPost): static
    {
        $this->shortDescriptionBlogPost = $shortDescriptionBlogPost;

        return $this;
    }

    public function getContentBlogPost(): ?string
    {
        return $this->contentBlogPost;
    }

    public function setContentBlogPost(string $contentBlogPost): static
    {
        $this->contentBlogPost = $contentBlogPost;

        return $this;
    }

    public function getContentBlogPost2(): ?string
    {
        return $this->contentBlogPost2;
    }

    public function setContentBlogPost2(?string $contentBlogPost2): static
    {
        $this->contentBlogPost2 = $contentBlogPost2;

        return $this;
    }

    public function getPublicationDateBlogPost(): ?\DateTimeInterface
    {
        return $this->publicationDateBlogPost;
    }

    public function setPublicationDateBlogPost(\DateTimeInterface $publicationDateBlogPost): static
    {
        $this->publicationDateBlogPost = $publicationDateBlogPost;

        return $this;
    }

    public function getAuthorBlogPost(): ?string
    {
        return $this->authorBlogPost;
    }

    public function setAuthorBlogPost(string $authorBlogPost): static
    {
        $this->authorBlogPost = $authorBlogPost;

        return $this;
    }

    public function getMetaDescriptionBlogPost(): ?string
    {
        return $this->metaDescriptionBlogPost;
    }

    public function setMetaDescriptionBlogPost(string $metaDescriptionBlogPost): static
    {
        $this->metaDescriptionBlogPost = $metaDescriptionBlogPost;

        return $this;
    }

    public function getSeoKeyBlogPost(): ?string
    {
        return $this->seoKeyBlogPost;
    }

    public function setSeoKeyBlogPost(string $seoKeyBlogPost): static
    {
        $this->seoKeyBlogPost = $seoKeyBlogPost;

        return $this;
    }

    /**
     * @return Collection<int, BlogCategory>
     */
    public function getBlogCategories(): Collection
    {
        return $this->blogCategories;
    }

    public function addBlogCategory(BlogCategory $blogCategory): static
    {
        if (!$this->blogCategories->contains($blogCategory)) {
            $this->blogCategories->add($blogCategory);
            $blogCategory->addBlogPost($this);
        }

        return $this;
    }

    public function removeBlogCategory(BlogCategory $blogCategory): static
    {
        if ($this->blogCategories->removeElement($blogCategory)) {
            $blogCategory->removeBlogPost($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getBlogPost(): Collection
    {
        return $this->blogPost;
    }

    public function addBlogPost(self $blogPost): static
    {
        if (!$this->blogPost->contains($blogPost)) {
            $this->blogPost->add($blogPost);
        }

        return $this;
    }

    public function removeBlogPost(self $blogPost): static
    {
        $this->blogPost->removeElement($blogPost);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getBlogPosts(): Collection
    {
        return $this->blogPosts;
    }

    public function isDraft(): ?bool
    {
        return $this->draft;
    }

    public function setDraft(bool $draft): static
    {
        $this->draft = $draft;

        return $this;
    }

    public function __toString(): string
    {
        return $this->titlePost ?? 'Article sans titre';
    }
}
