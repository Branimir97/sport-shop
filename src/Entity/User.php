<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @UniqueEntity(fields={"email"}, message="entity.site.unique")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = ["ROLE_USER"];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $surname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $gender;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $facebookId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $googleId;

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
     * @ORM\OneToMany(targetEntity=DeliveryAddress::class, mappedBy="user", orphanRemoval=true)
     */
    private $deliveryAddresses;

    /**
     * @ORM\OneToMany(targetEntity=Review::class, mappedBy="user", orphanRemoval=true)
     */
    private $reviews;

    /**
     * @ORM\OneToOne(targetEntity=LoyaltyCard::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $loyaltyCard;

    /**
     * @ORM\OneToOne(targetEntity=Cart::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $cart;

    /**
     * @ORM\OneToOne(targetEntity=WishList::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $wishList;

    /**
     * @ORM\OneToMany(targetEntity=PromoCodeUser::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $promoCodeUsers;

    /**
     * @ORM\OneToOne(targetEntity=OrderList::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $orderList;

    /**
     * @ORM\OneToMany(targetEntity=UserSearch::class, mappedBy="user", orphanRemoval=true)
     */
    private $userSearches;

    /**
     * @ORM\OneToMany(targetEntity=UserProminentCategory::class, mappedBy="user", orphanRemoval=true)
     */
    private $userProminentCategories;

    public function __construct()
    {
        $this->deliveryAddresses = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->promoCodeUsers = new ArrayCollection();
        $this->userSearches = new ArrayCollection();
        $this->userProminentCategories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->name . ' ' . $this->surname;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getFacebookId(): ?string
    {
        return $this->facebookId;
    }

    public function setFacebookId(?string $facebookId): self
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): self
    {
        $this->googleId = $googleId;

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
     * @return Collection|DeliveryAddress[]
     */
    public function getDeliveryAddresses(): Collection
    {
        return $this->deliveryAddresses;
    }

    public function addDeliveryAddress(DeliveryAddress $deliveryAddress): self
    {
        if (!$this->deliveryAddresses->contains($deliveryAddress)) {
            $this->deliveryAddresses[] = $deliveryAddress;
            $deliveryAddress->setUser($this);
        }

        return $this;
    }

    public function removeDeliveryAddress(DeliveryAddress $deliveryAddress): self
    {
        if ($this->deliveryAddresses->removeElement($deliveryAddress)) {
            // set the owning side to null (unless already changed)
            if ($deliveryAddress->getUser() === $this) {
                $deliveryAddress->setUser(null);
            }
        }

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
            $review->setUser($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getUser() === $this) {
                $review->setUser(null);
            }
        }

        return $this;
    }

    public function getLoyaltyCard(): ?LoyaltyCard
    {
        return $this->loyaltyCard;
    }

    public function setLoyaltyCard(LoyaltyCard $loyaltyCard): self
    {
        // set the owning side of the relation if necessary
        if ($loyaltyCard->getUser() !== $this) {
            $loyaltyCard->setUser($this);
        }

        $this->loyaltyCard = $loyaltyCard;

        return $this;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): self
    {
        // unset the owning side of the relation if necessary
        if ($cart === null && $this->cart !== null) {
            $this->cart->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($cart !== null && $cart->getUser() !== $this) {
            $cart->setUser($this);
        }

        $this->cart = $cart;

        return $this;
    }

    public function getWishList(): ?WishList
    {
        return $this->wishList;
    }

    public function setWishList(WishList $wishList): self
    {
        // set the owning side of the relation if necessary
        if ($wishList->getUser() !== $this) {
            $wishList->setUser($this);
        }

        $this->wishList = $wishList;

        return $this;
    }

    /**
     * @return Collection|PromoCodeUser[]
     */
    public function getPromoCodeUsers(): Collection
    {
        return $this->promoCodeUsers;
    }

    public function addPromoCodeUser(PromoCodeUser $promoCodeUser): self
    {
        if (!$this->promoCodeUsers->contains($promoCodeUser)) {
            $this->promoCodeUsers[] = $promoCodeUser;
            $promoCodeUser->setUser($this);
        }

        return $this;
    }

    public function removePromoCodeUser(PromoCodeUser $promoCodeUser): self
    {
        if ($this->promoCodeUsers->removeElement($promoCodeUser)) {
            // set the owning side to null (unless already changed)
            if ($promoCodeUser->getUser() === $this) {
                $promoCodeUser->setUser(null);
            }
        }

        return $this;
    }

    public function getOrderList(): ?OrderList
    {
        return $this->orderList;
    }

    public function setOrderList(OrderList $orderList): self
    {
        // set the owning side of the relation if necessary
        if ($orderList->getUser() !== $this) {
            $orderList->setUser($this);
        }

        $this->orderList = $orderList;

        return $this;
    }

    /**
     * @return Collection|UserSearch[]
     */
    public function getUserSearches(): Collection
    {
        return $this->userSearches;
    }

    public function addUserSearch(UserSearch $userSearch): self
    {
        if (!$this->userSearches->contains($userSearch)) {
            $this->userSearches[] = $userSearch;
            $userSearch->setUser($this);
        }

        return $this;
    }

    public function removeUserSearch(UserSearch $userSearch): self
    {
        if ($this->userSearches->removeElement($userSearch)) {
            // set the owning side to null (unless already changed)
            if ($userSearch->getUser() === $this) {
                $userSearch->setUser(null);
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
            $userProminentCategory->setUser($this);
        }

        return $this;
    }

    public function removeUserProminentCategory(UserProminentCategory $userProminentCategory): self
    {
        if ($this->userProminentCategories->removeElement($userProminentCategory)) {
            // set the owning side to null (unless already changed)
            if ($userProminentCategory->getUser() === $this) {
                $userProminentCategory->setUser(null);
            }
        }

        return $this;
    }
}
