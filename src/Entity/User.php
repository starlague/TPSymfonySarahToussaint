<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    private ?File $avatarFile = null;

    /**
     * @var Collection<int, Friendship>
     */
    #[ORM\OneToMany(targetEntity: Friendship::class, mappedBy: 'requester')]
    private Collection $friendshipSent;

    /**
     * @var Collection<int, Friendship>
     */
    #[ORM\OneToMany(targetEntity: Friendship::class, mappedBy: 'receiver')]
    private Collection $friendshipReceived;

    public function __construct()
    {
        $this->friendshipSent = new ArrayCollection();
        $this->friendshipReceived = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
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

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getAvatarFile(): ?File 
    {
        return $this->avatarFile;
    }

    public function setAvatarFile(?File $avatarFile): static
    {
        $this->avatarFile = $avatarFile;

        return $this;
    }

    /**
     * @return Collection<int, Friendship>
     */
    public function getFriendshipSent(): Collection
    {
        return $this->friendshipSent;
    }

    public function addFriendshipSent(Friendship $receiver): static
    {
        if (!$this->friendshipSent->contains($receiver)) {
            $this->friendshipSent->add($receiver);
            $receiver->setRequester($this);
        }

        return $this;
    }

    public function removeFriendshipSent(Friendship $receiver): static
    {
        if ($this->friendshipSent->removeElement($receiver)) {
            // set the owning side to null (unless already changed)
            if ($receiver->getRequester() === $this) {
                $receiver->setRequester(null);
            }
        }

        return $this;
    }

    /**
    * @return Collection<int, Friendship>
    */ 
    public function getFriendshipReceived(): Collection
    {
        return $this->friendshipReceived;
    }

    public function addFriendshipReceived(Friendship $friendship): static
    {
        if (!$this->friendshipReceived->contains($friendship)) {
            $this->friendshipReceived->add($friendship);
            $friendship->setReceiver($this);
        }

        return $this;
    }

    public function removeFriendshipReceived(Friendship $friendship): static
    {
        if ($this->friendshipReceived->removeElement($friendship)) {
            // set the owning side to null (unless already changed)
            if ($friendship->getReceiver() === $this) {
                $friendship->setReceiver(null);
            }
        }

        return $this;
    }
}
