<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $pizzaName = null;

    #[ORM\Column(length: 50)]
    private ?string $pizzaSize = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?int $price = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPizzaName(): ?string
    {
        return $this->pizzaName;
    }

    public function setPizzaName(string $pizzaName): static
    {
        $this->pizzaName = $pizzaName;

        return $this;
    }

    public function getPizzaSize(): ?string
    {
        return $this->pizzaSize;
    }

    public function setPizzaSize(string $pizzaSize): static
    {
        $this->pizzaSize = $pizzaSize;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }
}
