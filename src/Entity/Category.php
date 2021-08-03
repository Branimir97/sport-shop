<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 * @Gedmo\TranslationEntity(class="App\Entity\CategoryTranslation")
 */
class Category implements Translatable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Translatable
     */
    private $name;

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
     * @ORM\OneToMany(targetEntity=ItemCategory::class, mappedBy="category")
     */
    private $itemCategories;

    /**
     * @ORM\OneToOne(targetEntity=ActionCategory::class, mappedBy="category", cascade={"persist", "remove"})
     */
    private $actionCategory;

    /**
     * @Gedmo\Locale
     */
    private $locale;

    /**
     * @ORM\OneToMany(targetEntity=CategoryTranslation::class, mappedBy="object")
     */
    private $categoryTranslations;

    /**
     * @ORM\OneToMany(targetEntity=UserProminentCategory::class, mappedBy="category", orphanRemoval=true)
     */
    private $userProminentCategories;

    public function __construct()
    {
        $this->itemCategories = new ArrayCollection();
        $this->categoryTranslations = new ArrayCollection();
        $this->userProminentCategories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    /**
     * @return Collection|ItemCategory[]
     */
    public function getItemCategories(): Collection
    {
        return $this->itemCategories;
    }

    public function addItemCategory(ItemCategory $itemCategory): self
    {
        if (!$this->itemCategories->contains($itemCategory)) {
            $this->itemCategories[] = $itemCategory;
            $itemCategory->setCategory($this);
        }

        return $this;
    }

    public function removeItemCategory(ItemCategory $itemCategory): self
    {
        if ($this->itemCategories->removeElement($itemCategory)) {
            // set the owning side to null (unless already changed)
            if ($itemCategory->getCategory() === $this) {
                $itemCategory->setCategory(null);
            }
        }

        return $this;
    }

    public function getActionCategory(): ?ActionCategory
    {
        return $this->actionCategory;
    }

    public function setActionCategory(ActionCategory $actionCategory): self
    {
        // set the owning side of the relation if necessary
        if ($actionCategory->getCategory() !== $this) {
            $actionCategory->setCategory($this);
        }

        $this->actionCategory = $actionCategory;

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
     * @return Collection|CategoryTranslation[]
     */
    public function getCategoryTranslations(): Collection
    {
        return $this->categoryTranslations;
    }

    public function addCategoryTranslation(CategoryTranslation $categoryTranslation): self
    {
        if (!$this->categoryTranslations->contains($categoryTranslation)) {
            $this->categoryTranslations[] = $categoryTranslation;
            $categoryTranslation->setObject($this);
        }

        return $this;
    }

    public function removeCategoryTranslation(CategoryTranslation $categoryTranslation): self
    {
        if ($this->categoryTranslations->removeElement($categoryTranslation)) {
            // set the owning side to null (unless already changed)
            if ($categoryTranslation->getObject() === $this) {
                $categoryTranslation->setObject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserProminentCategory[]
     */
    public function getUserProminentCategories(): Collection
    {
        return $this->userProminentCategories;
    }

    public function addUserProminentCategory(UserProminentCategory $userProminentCategory): self
    {
        if (!$this->userProminentCategories->contains($userProminentCategory)) {
            $this->userProminentCategories[] = $userProminentCategory;
            $userProminentCategory->setCategory($this);
        }

        return $this;
    }

    public function removeUserProminentCategory(UserProminentCategory $userProminentCategory): self
    {
        if ($this->userProminentCategories->removeElement($userProminentCategory)) {
            // set the owning side to null (unless already changed)
            if ($userProminentCategory->getCategory() === $this) {
                $userProminentCategory->setCategory(null);
            }
        }

        return $this;
    }
}
