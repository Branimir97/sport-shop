<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=ItemRepository::class)
 */
class Item
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $cipher;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $price;

    /**
     * @ORM\ManyToMany(targetEntity=Category::class, inversedBy="items")
     */
    private $category;

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
     * @ORM\OneToMany(targetEntity=ItemTag::class, mappedBy="item")
     */
    private $itemTags;

    /**
     * @ORM\OneToMany(targetEntity=ItemSize::class, mappedBy="item")
     */
    private $itemSizes;

    public function __construct()
    {
        $this->category = new ArrayCollection();
        $this->itemTags = new ArrayCollection();
        $this->itemSizes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCipher(): ?string
    {
        return $this->cipher;
    }

    public function setCipher(string $cipher): self
    {
        $this->cipher = $cipher;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->category->contains($category)) {
            $this->category[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->category->removeElement($category);

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
     * @return Collection|ItemTag[]
     */
    public function getItemTags(): Collection
    {
        return $this->itemTags;
    }

    public function addItemTag(ItemTag $itemTag): self
    {
        if (!$this->itemTags->contains($itemTag)) {
            $this->itemTags[] = $itemTag;
            $itemTag->setItem($this);
        }

        return $this;
    }

    public function removeItemTag(ItemTag $itemTag): self
    {
        if ($this->itemTags->removeElement($itemTag)) {
            // set the owning side to null (unless already changed)
            if ($itemTag->getItem() === $this) {
                $itemTag->setItem(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ItemSize[]
     */
    public function getItemSizes(): Collection
    {
        return $this->itemSizes;
    }

    public function addItemSize(ItemSize $itemSize): self
    {
        if (!$this->itemSizes->contains($itemSize)) {
            $this->itemSizes[] = $itemSize;
            $itemSize->setItem($this);
        }

        return $this;
    }

    public function removeItemSize(ItemSize $itemSize): self
    {
        if ($this->itemSizes->removeElement($itemSize)) {
            // set the owning side to null (unless already changed)
            if ($itemSize->getItem() === $this) {
                $itemSize->setItem(null);
            }
        }

        return $this;
    }
}
