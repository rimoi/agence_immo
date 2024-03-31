<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\UserInterface;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="Il existe déjà un compte avec cette email")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
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
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastName;


    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Mission", mappedBy="user")
     */
    private $missions;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $avatar;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\OneToMany(targetEntity=Review::class, mappedBy="user")
     */
    private $reviews;

    /**
     * @ORM\OneToMany(targetEntity=Conversation::class, mappedBy="user1", orphanRemoval=true)
     */
    private $conversation1s;

    /**
     * @ORM\OneToMany(targetEntity=Conversation::class, mappedBy="user2", orphanRemoval=true)
     */
    private $conversation2s;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="owner", orphanRemoval=true)
     */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity=Conversation::class, mappedBy="sender")
     */
    private $senderConversations;

    /**
     * @ORM\OneToMany(targetEntity=Reservation::class, mappedBy="owner")
     */
    private $reservations;

    /**
     * @ORM\OneToMany(targetEntity=Reservation::class, mappedBy="locataire")
     */
    private $locataires;



    public function nickname(): string
    {
        if ($this->firstName && $this->lastName) {
            return sprintf('%s %s', $this->firstName, $this->lastName);
        }

        return $this->email ?? '';
    }

    public function hasRole(string $role): bool
    {
        return !!in_array($role, $this->roles, true);
    }

    public function getImagePath(): string
    {
        return UploaderHelper::USER_AVATAR.'/'.$this->getAvatar();
    }

    public function __construct()
    {
        $this->slug = uniqid(true);
        $this->missions = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->conversation1s = new ArrayCollection();
        $this->conversation2s = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->senderConversations = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->locataires = new ArrayCollection();
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
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection|Mission[]
     */
    public function getMissions(): Collection
    {
        return $this->missions;
    }

    public function addMission(Mission $mission): self
    {
        if (!$this->missions->contains($mission)) {
            $this->missions[] = $mission;
            $mission->setUser($this);
        }

        return $this;
    }

    public function removeMission(Mission $mission): self
    {
        if ($this->missions->contains($mission)) {
            $this->missions->removeElement($mission);
            // set the owning side to null (unless already changed)
            if ($mission->getUser() === $this) {
                $mission->setUser(null);
            }
        }

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

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

    /**
     * @return Collection|Conversation[]
     */
    public function getConversation1s(): Collection
    {
        return $this->conversation1s;
    }

    public function addConversation1(Conversation $conversation1): self
    {
        if (!$this->conversation1s->contains($conversation1)) {
            $this->conversation1s[] = $conversation1;
            $conversation1->setUser1($this);
        }

        return $this;
    }

    public function removeConversation1(Conversation $conversation1): self
    {
        if ($this->conversation1s->removeElement($conversation1)) {
            // set the owning side to null (unless already changed)
            if ($conversation1->getUser1() === $this) {
                $conversation1->setUser1(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Conversation[]
     */
    public function getConversation2s(): Collection
    {
        return $this->conversation2s;
    }

    public function addConversation2(Conversation $conversation2): self
    {
        if (!$this->conversation2s->contains($conversation2)) {
            $this->conversation2s[] = $conversation2;
            $conversation2->setUser2($this);
        }

        return $this;
    }

    public function removeConversation2(Conversation $conversation2): self
    {
        if ($this->conversation2s->removeElement($conversation2)) {
            // set the owning side to null (unless already changed)
            if ($conversation2->getUser2() === $this) {
                $conversation2->setUser2(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setOwner($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getOwner() === $this) {
                $message->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Conversation[]
     */
    public function getSenderConversations(): Collection
    {
        return $this->senderConversations;
    }

    public function addSenderConversation(Conversation $senderConversation): self
    {
        if (!$this->senderConversations->contains($senderConversation)) {
            $this->senderConversations[] = $senderConversation;
            $senderConversation->setSender($this);
        }

        return $this;
    }

    public function removeSenderConversation(Conversation $senderConversation): self
    {
        if ($this->senderConversations->removeElement($senderConversation)) {
            // set the owning side to null (unless already changed)
            if ($senderConversation->getSender() === $this) {
                $senderConversation->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setOwner($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getOwner() === $this) {
                $reservation->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getLocataires(): Collection
    {
        return $this->locataires;
    }

    public function addLocataire(Reservation $reservation): self
    {
        if (!$this->locataires->contains($reservation)) {
            $this->locataires[] = $reservation;
            $reservation->setLocataire($this);
        }

        return $this;
    }

    public function removeLocataire(Reservation $reservation): self
    {
        if ($this->locataires->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getLocataire() === $this) {
                $reservation->setLocataire(null);
            }
        }

        return $this;
    }
}
