<?php

namespace App\Entity;

use App\Repository\ColorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity(repositoryClass=ColorRepository::class)
 * @Gedmo\TranslationEntity(class="App\Entity\ColorTranslation")
 */
class Color implements Translatable
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
    private $value;

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
     * @ORM\OneToMany(targetEntity=ItemColor::class, mappedBy="color")
     */
    private $itemColors;

    /**
     * @ORM\OneToMany(targetEntity=CartItem::class, mappedBy="color")
     */
    private $cartItems;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Translatable
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=OrderItem::class, mappedBy="color")
     */
    private $orderItems;

    /**
     * @Gedmo\Locale
     */
    private $locale;

    /**
     * @ORM\OneToMany(targetEntity=ColorTranslation::class, mappedBy="object")
     */
    private $colorTranslations;

    public function __construct()
    {
        $this->itemColors = new ArrayCollection();
        $this->cartItems = new ArrayCollection();
        $this->orderItems = new ArrayCollection();
        $this->colorTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

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
            $itemColor->setColor($this);
        }

        return $this;
    }

    public function removeItemColor(ItemColor $itemColor): self
    {
        if ($this->itemColors->removeElement($itemColor)) {
            // set the owning side to null (unless already changed)
            if ($itemColor->getColor() === $this) {
                $itemColor->setColor(null);
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
            $cartItem->setColor($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): self
    {
        if ($this->cartItems->removeElement($cartItem)) {
            // set the owning side to null (unless already changed)
            if ($cartItem->getColor() === $this) {
                $cartItem->setColor(null);
            }
        }

        return $this;
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

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return Collection|OrderItem[]
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): self
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems[] = $orderItem;
            $orderItem->setColor($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): self
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getColor() === $this) {
                $orderItem->setColor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ColorTranslation[]
     */
    public function getColorTranslations(): Collection
    {
        return $this->colorTranslations;
    }

    public function addColorTranslation(ColorTranslation $colorTranslation): self
    {
        if (!$this->colorTranslations->contains($colorTranslation)) {
            $this->colorTranslations[] = $colorTranslation;
            $colorTranslation->setObject($this);
        }

        return $this;
    }

    public function removeColorTranslation(ColorTranslation $colorTranslation): self
    {
        if ($this->colorTranslations->removeElement($colorTranslation)) {
            // set the owning side to null (unless already changed)
            if ($colorTranslation->getObject() === $this) {
                $colorTranslation->setObject(null);
            }
        }

        return $this;
    }
}
