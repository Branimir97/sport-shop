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
     * @ORM\Column(type="float")
     */
    private $price;

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
     * @ORM\OneToMany(targetEntity=ItemTag::class, mappedBy="item", orphanRemoval=true)
     */
    private $itemTags;

    /**
     * @ORM\OneToMany(targetEntity=ItemSize::class, mappedBy="item", orphanRemoval=true)
     */
    private $itemSizes;

    /**
     * @ORM\OneToMany(targetEntity=ItemColor::class, mappedBy="item", orphanRemoval=true)
     */
    private $itemColors;

    /**
     * @ORM\OneToMany(targetEntity=ItemCategory::class, mappedBy="item", orphanRemoval=true)
     */
    private $itemCategories;

    /**
     * @ORM\OneToMany(targetEntity=Image::class, mappedBy="item", orphanRemoval=true)
     */
    private $images;

    /**
     * @ORM\ManyToOne(targetEntity=Manufacturer::class, inversedBy="items")
     */
    private $manufacturer;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=Review::class, mappedBy="item")
     */
    private $reviews;

    /**
     * @ORM\OneToMany(targetEntity=CartItem::class, mappedBy="item", orphanRemoval=true)
     */
    private $cartItems;

    /**
     * @ORM\OneToMany(targetEntity=WishListItem::class, mappedBy="item", orphanRemoval=true)
     */
    private $wishListItems;

    public function __construct()
    {
        $this->itemTags = new ArrayCollection();
        $this->itemSizes = new ArrayCollection();
        $this->itemColors = new ArrayCollection();
        $this->itemCategories = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->cartItems = new ArrayCollection();
        $this->wishListItems = new ArrayCollection();
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

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

    /**
     * @return Collection|ItemColor[]
     */
    public function getItemColors(): Collection
    {
        return $this->itemColors;
    }

    public function addItemColor(ItemColor $itemColor): self
    {
        if (!$this->itemColors->contains($itemColor)) {
            $this->itemColors[] = $itemColor;
            $itemColor->setItem($this);
        }

        return $this;
    }

    public function removeItemColor(ItemColor $itemColor): self
    {
        if ($this->itemColors->removeElement($itemColor)) {
            // set the owning side to null (unless already changed)
            if ($itemColor->getItem() === $this) {
                $itemColor->setItem(null);
            }
        }

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
            $itemCategory->setItem($this);
        }

        return $this;
    }

    public function removeItemCategory(ItemCategory $itemCategory): self
    {
        if ($this->itemCategories->removeElement($itemCategory)) {
            // set the owning side to null (unless already changed)
            if ($itemCategory->getItem() === $this) {
                $itemCategory->setItem(null);
            }
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
            $image->setItem($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getItem() === $this) {
                $image->setItem(null);
            }
        }

        return $this;
    }

    public function getManufacturer(): ?Manufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?Manufacturer $manufacturer): self
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Review[]
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setItem($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getItem() === $this) {
                $review->setItem(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CartItem[]
     */
    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function addCartItem(CartItem $cartItem): self
    {
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems[] = $cartItem;
            $cartItem->setItem($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): self
    {
        if ($this->cartItems->removeElement($cartItem)) {
            // set the owning side to null (unless already changed)
            if ($cartItem->getItem() === $this) {
                $cartItem->setItem(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|WishListItem[]
     */
    public function getWishListItems(): Collection
    {
        return $this->wishListItems;
    }

    public function addWishListItem(WishListItem $wishListItem): self
    {
        if (!$this->wishListItems->contains($wishListItem)) {
            $this->wishListItems[] = $wishListItem;
            $wishListItem->setItem($this);
        }

        return $this;
    }

    public function removeWishListItem(WishListItem $wishListItem): self
    {
        if ($this->wishListItems->removeElement($wishListItem)) {
            // set the owning side to null (unless already changed)
            if ($wishListItem->getItem() === $this) {
                $wishListItem->setItem(null);
            }
        }

        return $this;
    }
}
