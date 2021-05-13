<?php

namespace App\Entity;

use App\Repository\ManufacturerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=ManufacturerRepository::class)
 */
class Manufacturer
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
     * @ORM\OneToMany(targetEntity=ItemManufacturer::class, mappedBy="manufacturer")
     */
    private $itemManufacturers;

    public function __construct()
    {
        $this->itemManufacturers = new ArrayCollection();
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
     * @return Collection|ItemManufacturer[]
     */
    public function getItemManufacturers(): Collection
    {
        return $this->itemManufacturers;
    }

    public function addItemManufacturer(ItemManufacturer $itemManufacturer): self
    {
        if (!$this->itemManufacturers->contains($itemManufacturer)) {
            $this->itemManufacturers[] = $itemManufacturer;
            $itemManufacturer->setManufacturer($this);
        }

        return $this;
    }

    public function removeItemManufacturer(ItemManufacturer $itemManufacturer): self
    {
        if ($this->itemManufacturers->removeElement($itemManufacturer)) {
            // set the owning side to null (unless already changed)
            if ($itemManufacturer->getManufacturer() === $this) {
                $itemManufacturer->setManufacturer(null);
            }
        }

        return $this;
    }
}
