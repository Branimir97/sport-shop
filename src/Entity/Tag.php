<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=TagRepository::class)
 */
class Tag
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
     * @ORM\OneToMany(targetEntity=ItemTag::class, mappedBy="tag")
     */
    private $itemTags;

    public function __construct()
    {
        $this->itemTags = new ArrayCollection();
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
            $itemTag->setTag($this);
        }

        return $this;
    }

    public function removeItemTag(ItemTag $itemTag): self
    {
        if ($this->itemTags->removeElement($itemTag)) {
            // set the owning side to null (unless already changed)
            if ($itemTag->getTag() === $this) {
                $itemTag->setTag(null);
            }
        }

        return $this;
    }
}
