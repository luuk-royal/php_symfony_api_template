<?php

namespace App\Entity;

use App\Repository\AccessTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: AccessTokenRepository::class)]
class AccessToken
{
    private const TOKEN_LIFETIME = 3600;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $token;

    #[ORM\ManyToOne(inversedBy: 'tokens')]
    private ?User $user;

    #[ORM\Column]
    private \DateTime $origin;
    private int $DAYS_VALID = 1;

    public function __construct() {
        $this->origin = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param UserInterface|null $user
     */
    public function setUser(?UserInterface $user): void
    {
        $this->user = $user;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param string|null $token
     */
    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    public function isValid(): bool
    {
        if (
            $this->origin->diff(new \DateTime())->days > $this->DAYS_VALID
        ) {
            return false;
        }
        return true;
    }
}