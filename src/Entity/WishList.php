<?php

namespace App\Entity;

use App\Repository\WishListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=WishListRepository::class)
 */
class WishList
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="wishList", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

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
     * @ORM\OneToMany(targetEntity=WishListItem::class, mappedBy="wishList", orphanRemoval=true)
     */
    private $wishListItems;

    public function __construct()
    {
        $this->wishListItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

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
            $wishListItem->setWishList($this);
        }

        return $this;
    }

    public function removeWishListItem(WishListItem $wishListItem): self
    {
        if ($this->wishListItems->removeElement($wishListItem)) {
            // set the owning side to null (unless already changed)
            if ($wishListItem->getWishList() === $this) {
                $wishListItem->setWishList(null);
            }
        }

        return $this;
    }
}
