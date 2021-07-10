<?php

namespace App\Entity;

use App\Repository\ActionItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity(repositoryClass=ActionItemRepository::class)
 * @Gedmo\TranslationEntity(class="App\Entity\ActionItemTranslation")
 */
class ActionItem implements Translatable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Item::class, inversedBy="actionItem", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $item;

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
     * @ORM\OneToMany(targetEntity=ActionItemTranslation::class, mappedBy="object")
     */
    private $actionItemTranslations;

    public function __construct()
    {
        $this->actionItemTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(Item $item): self
    {
        $this->item = $item;

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
     * @return Collection|ActionItemTranslation[]
     */
    public function getActionItemTranslations(): Collection
    {
        return $this->actionItemTranslations;
    }

    public function addActionItemTranslation(ActionItemTranslation $actionItemTranslation): self
    {
        if (!$this->actionItemTranslations->contains($actionItemTranslation)) {
            $this->actionItemTranslations[] = $actionItemTranslation;
            $actionItemTranslation->setObject($this);
        }

        return $this;
    }

    public function removeActionItemTranslation(ActionItemTranslation $actionItemTranslation): self
    {
        if ($this->actionItemTranslations->removeElement($actionItemTranslation)) {
            // set the owning side to null (unless already changed)
            if ($actionItemTranslation->getObject() === $this) {
                $actionItemTranslation->setObject(null);
            }
        }

        return $this;
    }
}
