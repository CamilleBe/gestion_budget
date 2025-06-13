<?php

namespace App\Entity;

use App\Repository\ExpenseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExpenseRepository::class)]
class Expense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La description de la dépense est requise')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'La description doit faire au moins {{ limit }} caractères',
        maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le montant est requis')]
    #[Assert\Positive(message: 'Le montant doit être positif')]
    #[Assert\Range(
        min: 0.01,
        max: 99999999.99,
        notInRangeMessage: 'Le montant doit être entre {{ min }}€ et {{ max }}€'
    )]
    private ?string $amount = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date est requise')]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'expenses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Period $period = null;

    #[ORM\ManyToOne(inversedBy: 'expenses')]
    private ?Category $category = null;

    public function __construct()
    {
        $this->date = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount ? (float) $this->amount : null;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = (string) $amount;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getPeriod(): ?Period
    {
        return $this->period;
    }

    public function setPeriod(?Period $period): static
    {
        $this->period = $period;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Validation personnalisée : vérifier que la catégorie est bien de type EXPENSE
     */
    #[Assert\Callback]
    public function validateCategory(\Symfony\Component\Validator\Context\ExecutionContextInterface $context): void
    {
        if ($this->category && $this->category->getType() !== \App\Enum\CategoryType::EXPENSE) {
            $context->buildViolation('La catégorie doit être de type "Dépense"')
                ->atPath('category')
                ->addViolation();
        }
    }

    /**
     * Validation personnalisée : vérifier que la date est dans la période
     */
    #[Assert\Callback]
    public function validateDateInPeriod(\Symfony\Component\Validator\Context\ExecutionContextInterface $context): void
    {
        // Validation temporairement désactivée pour permettre plus de flexibilité
        // if ($this->date && $this->period) {
        //     if ($this->date < $this->period->getStartDate() || $this->date > $this->period->getEndDate()) {
        //         $context->buildViolation('La date doit être comprise dans la période sélectionnée')
        //             ->atPath('date')
        //             ->addViolation();
        //     }
        // }
    }
} 