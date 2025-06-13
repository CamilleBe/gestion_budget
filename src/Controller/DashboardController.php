<?php

namespace App\Controller;

use App\Repository\PeriodRepository;
use App\Repository\IncomeRepository;
use App\Repository\ExpenseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    public function __construct(
        private PeriodRepository $periodRepository,
        private IncomeRepository $incomeRepository,
        private ExpenseRepository $expenseRepository
    ) {}

    #[Route('/', name: 'app_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        
        // Récupérer toutes les périodes de l'utilisateur
        $periods = $this->periodRepository->findByUserOrderedByDate($user);
        
        // Récupérer la période actuelle (celle qui contient la date d'aujourd'hui)
        $currentPeriod = $this->periodRepository->findCurrentPeriodForUser($user);
        
        // Récupérer les revenus et dépenses récents
        $recentIncomes = $this->incomeRepository->findRecentByUser($user, 5);
        $recentExpenses = $this->expenseRepository->findRecentByUser($user, 5);
        
        // Calculer les statistiques globales
        $totalIncomeAllPeriods = 0;
        $totalExpensesAllPeriods = 0;
        
        foreach ($periods as $period) {
            $totalIncomeAllPeriods += $period->getTotalIncome();
            $totalExpensesAllPeriods += $period->getTotalExpenses();
        }
        
        $globalBalance = $totalIncomeAllPeriods - $totalExpensesAllPeriods;
        
        // Statistiques pour la période actuelle
        $currentPeriodStats = null;
        if ($currentPeriod) {
            $currentPeriodStats = [
                'period' => $currentPeriod,
                'totalIncome' => $currentPeriod->getTotalIncome(),
                'totalExpenses' => $currentPeriod->getTotalExpenses(),
                'balance' => $currentPeriod->getBalance()
            ];
        }
        
        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'periods' => $periods,
            'currentPeriod' => $currentPeriod,
            'currentPeriodStats' => $currentPeriodStats,
            'recentIncomes' => $recentIncomes,
            'recentExpenses' => $recentExpenses,
            'globalStats' => [
                'totalIncome' => $totalIncomeAllPeriods,
                'totalExpenses' => $totalExpensesAllPeriods,
                'balance' => $globalBalance,
                'periodsCount' => count($periods)
            ]
        ]);
    }
} 