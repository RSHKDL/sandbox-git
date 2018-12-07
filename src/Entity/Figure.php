<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_figures")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="App\Repository\FigureRepository")
 * @UniqueEntity(
 *     fields={"name"},
 *     message="This Trick already exist"
 * )
 */
class Figure
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=140)
     * @Assert\NotBlank()
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=190, unique=true)
     * @var string
     */
    private $slug;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", name="updated_at", nullable=true)
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\User",
     *     inversedBy="figures"
     * )
     * @ORM\JoinColumn(nullable=false)
     * @var User
     */
    private $author;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="App\Entity\Category",
     *     cascade={"persist"}
     * )
     * @ORM\JoinTable(name="app_figures_categories")
     * @Assert\Count(
     *      min = 1,
     *      max = 3,
     *      minMessage = "You must choose at least one category",
     *      maxMessage = "You cannot choose more than {{ limit }} category"
     * )
     */
    private $categories;

    /**
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Image",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn()
     * @var Image
     */
    private $imageFeatured;

    /**
     * @var \ArrayAccess
     *
     * @ORM\ManyToMany(
     *     targetEntity="App\Entity\Image",
     *     fetch="EXTRA_LAZY",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinTable(
     *     name="app_figures_images",
     *     joinColumns={@ORM\JoinColumn(name="trick_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="image_id", referencedColumnName="id")}
     * )
     * @Assert\Valid()
     */
    private $images;

    /**
     * @var \ArrayAccess
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Video",
     *     mappedBy="figure",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"}
     * )
     * @Assert\Valid()
     */
    private $videos;

    /**
     * @var Comment[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="App\Entity\Comment",
     *      mappedBy="figure",
     *      fetch="EXTRA_LAZY",
     *      orphanRemoval=true,
     *      cascade={"persist"}
     * )
     * @ORM\OrderBy({"createdAt": "DESC"})
     */
    private $comments;


    public function __construct(
        string $name,
        string $description,
        User $author,
        Image $imageFeatured,
        array $images = [],
        array $videos = [],
        ArrayCollection $categories
    ) {
        $this->name = $name;
        $this->slug = $this->slugify($name);
        $this->description = $description;
        $this->createdAt = new \DateTime();
        $this->author = $author;
        $this->categories = new ArrayCollection();
        $this->imageFeatured = $imageFeatured;
        $this->images = new ArrayCollection($images);
        $this->videos = new ArrayCollection($videos);
        $this->comments = new ArrayCollection();

        foreach ($categories as $category) {
            $this->addCategory($category);
        }

        foreach ($videos as $video) {
            $video->setFigure($this);
        }
    }


    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * Transform a string to an url friendly slug
     *
     * @param string $text
     * @return string
     */
    function slugify(string $text) {
        // replace non letter or digits by -
        $text = preg_replace('#[^\\pL\d]+#u', '-', $text);
        // trim
        $text = trim($text, '-');
        // transliterate
        if (function_exists('iconv'))
        {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }
        // lowercase
        $text = strtolower($text);
        // remove unwanted characters
        $text = preg_replace('#[^-\w]+#', '', $text);
        return $text;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function updateDate()
    {
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * @return Collection
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(?Comment $comment): void
    {
        $comment->setFigure($this);
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
        }
    }

    public function removeComment(Comment $comment): void
    {
        $comment->setFigure(null);
        $this->comments->removeElement($comment);
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setFigure($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getFigure() === $this) {
                $image->setFigure(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Video[]
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos[] = $video;
            $video->setFigure($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): self
    {
        if ($this->videos->contains($video)) {
            $this->videos->removeElement($video);
            // set the owning side to null (unless already changed)
            if ($video->getFigure() === $this) {
                $video->setFigure(null);
            }
        }

        return $this;
    }

    /**
     * @return Image|null
     */
    public function getImageFeatured(): ?Image
    {
        return $this->imageFeatured;
    }
}
