<?php

namespace App\Entity;

use App\Repository\ActionCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity(repositoryClass=ActionCategoryRepository::class)
 * @Gedmo\TranslationEntity(class="App\Entity\ActionCategoryTranslation")
 */
class ActionCategory implements Translatable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Category::class, inversedBy="actionCategory", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Translatable
     */
    private $title;

    /**
     * @ORM\Column(type="integer")
     */
    private $discountPercentage;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @Gedmo\Locale
     */
    private $locale;

    /**
     * @ORM\OneToMany(targetEntity=ActionCategoryTranslation::class, mappedBy="object")
     */
    private $actionCategoryTranslations;

    public function __construct()
    {
        $this->actionCategoryTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDiscountPercentage(): ?int
    {
        return $this->discountPercentage;
    }

    public function setDiscountPercentage(int $discountPercentage): self
    {
        $this->discountPercentage = $discountPercentage;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return Collection|ActionCategoryTranslation[]
     */
    public function getActionCategoryTranslations(): Collection
    {
        return $this->actionCategoryTranslations;
    }

    public function addActionCategoryTranslation(ActionCategoryTranslation $actionCategoryTranslation): self
    {
        if (!$this->actionCategoryTranslations->contains($actionCategoryTranslation)) {
            $this->actionCategoryTranslations[] = $actionCategoryTranslation;
            $actionCategoryTranslation->setObject($this);
        }

        return $this;
    }

    public function removeActionCategoryTranslation(ActionCategoryTranslation $actionCategoryTranslation): self
    {
        if ($this->actionCategoryTranslations->removeElement($actionCategoryTranslation)) {
            // set the owning side to null (unless already changed)
            if ($actionCategoryTranslation->getObject() === $this) {
                $actionCategoryTranslation->setObject(null);
            }
        }

        return $this;
    }
}
