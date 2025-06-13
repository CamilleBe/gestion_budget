<?php

namespace App\Entity;

use App\Repository\PeriodRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PeriodRepository::class)]
class Period
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de la période est requis')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit faire au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de début est requise')]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de fin est requise')]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\ManyToOne(inversedBy: 'periods')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'period', targetEntity: Income::class, orphanRemoval: true)]
    private Collection $incomes;

    #[ORM\OneToMany(mappedBy: 'period', targetEntity: Expense::class, orphanRemoval: true)]
    private Collection $expenses;

    public function __construct()
    {
        $this->incomes = new ArrayCollection();
        $this->expenses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Income>
     */
    public function getIncomes(): Collection
    {
        return $this->incomes;
    }

    public function addIncome(Income $income): static
    {
        if (!$this->incomes->contains($income)) {
            $this->incomes->add($income);
            $income->setPeriod($this);
        }

        return $this;
    }

    public function removeIncome(Income $income): static
    {
        if ($this->incomes->removeElement($income)) {
            // set the owning side to null (unless already changed)
            if ($income->getPeriod() === $this) {
                $income->setPeriod(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Expense>
     */
    public function getExpenses(): Collection
    {
        return $this->expenses;
    }

    public function addExpense(Expense $expense): static
    {
        if (!$this->expenses->contains($expense)) {
            $this->expenses->add($expense);
            $expense->setPeriod($this);
        }

        return $this;
    }

    public function removeExpense(Expense $expense): static
    {
        if ($this->expenses->removeElement($expense)) {
            // set the owning side to null (unless already changed)
            if ($expense->getPeriod() === $this) {
                $expense->setPeriod(null);
            }
        }

        return $this;
    }

    /**
     * Calcule le total des revenus pour cette période
     */
    public function getTotalIncome(): float
    {
        $total = 0;
        foreach ($this->incomes as $income) {
            $total += $income->getAmount();
        }
        return $total;
    }

    /**
     * Calcule le total des dépenses pour cette période
     */
    public function getTotalExpenses(): float
    {
        $total = 0;
        foreach ($this->expenses as $expense) {
            $total += $expense->getAmount();
        }
        return $total;
    }

    /**
     * Calcule le solde (revenus - dépenses) pour cette période
     */
    public function getBalance(): float
    {
        return $this->getTotalIncome() - $this->getTotalExpenses();
    }

    /**
     * Validation personnalisée : la date de fin doit être postérieure à la date de début
     */
    #[Assert\Callback]
    public function validateDates(\Symfony\Component\Validator\Context\ExecutionContextInterface $context): void
    {
        if ($this->startDate && $this->endDate && $this->startDate > $this->endDate) {
            $context->buildViolation('La date de fin doit être postérieure à la date de début')
                ->atPath('endDate')
                ->addViolation();
        }
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }
} 