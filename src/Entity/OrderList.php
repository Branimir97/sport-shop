<?php

namespace App\Entity;

use App\Repository\OrderListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=OrderListRepository::class)
 */
class OrderList
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="orderList", cascade={"persist", "remove"})
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
     * @ORM\OneToMany(targetEntity=OrderListItem::class, mappedBy="orderList", orphanRemoval=true)
     */
    private $orderListItems;

    public function __construct()
    {
        $this->orderListItems = new ArrayCollection();
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
     * @return Collection|OrderListItem[]
     */
    public function getOrderListItems(): Collection
    {
        return $this->orderListItems;
    }

    public function addOrderListItem(OrderListItem $orderListItem): self
    {
        if (!$this->orderListItems->contains($orderListItem)) {
            $this->orderListItems[] = $orderListItem;
            $orderListItem->setOrderList($this);
        }

        return $this;
    }

    public function removeOrderListItem(OrderListItem $orderListItem): self
    {
        if ($this->orderListItems->removeElement($orderListItem)) {
            // set the owning side to null (unless already changed)
            if ($orderListItem->getOrderList() === $this) {
                $orderListItem->setOrderList(null);
            }
        }

        return $this;
    }
}
