<?php

namespace App\Controller;

use App\Entity\Expense;
use App\Entity\Period;
use App\Form\ExpenseType;
use App\Repository\ExpenseRepository;
use App\Repository\PeriodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/expenses')]
#[IsGranted('ROLE_USER')]
class ExpenseController extends AbstractController
{
    public function __construct(
        private ExpenseRepository $expenseRepository,
        private PeriodRepository $periodRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'app_expense_index', methods: ['GET'])]
    public function index(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $expenses = $this->expenseRepository->findByUserOrderedByDate($user);

        return $this->render('expense/index.html.twig', [
            'expenses' => $expenses,
        ]);
    }

    #[Route('/new/{period}', name: 'app_expense_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Period $period = null): Response
    {
        // Si aucune période n'est spécifiée, essayer de trouver la période actuelle
        if (!$period) {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();
            $period = $this->periodRepository->findCurrentPeriodForUser($user);
            
            if (!$period) {
                $this->addFlash('warning', 'Vous devez d\'abord créer une période avant d\'ajouter des dépenses.');
                return $this->redirectToRoute('app_period_new');
            }
        }

        // Vérifier que la période appartient à l'utilisateur
        $this->denyAccessUnlessGranted('VIEW', $period);

        $expense = new Expense();
        $expense->setPeriod($period);
        $expense->setDate(new \DateTime()); // Date par défaut = aujourd'hui
        
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $form = $this->createForm(ExpenseType::class, $expense, ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($expense);
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('La dépense "%s" a été ajoutée avec succès !', $expense->getDescription()));

            return $this->redirectToRoute('app_period_show', ['id' => $period->getId()]);
        }

        return $this->render('expense/new.html.twig', [
            'expense' => $expense,
            'period' => $period,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_expense_show', methods: ['GET'])]
    public function show(Expense $expense): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $expense->getPeriod());

        return $this->render('expense/show.html.twig', [
            'expense' => $expense,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_expense_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Expense $expense): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $expense->getPeriod());

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $form = $this->createForm(ExpenseType::class, $expense, ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('La dépense "%s" a été modifiée avec succès !', $expense->getDescription()));

            return $this->redirectToRoute('app_period_show', ['id' => $expense->getPeriod()->getId()]);
        }

        return $this->render('expense/edit.html.twig', [
            'expense' => $expense,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_expense_delete', methods: ['POST'])]
    public function delete(Request $request, Expense $expense): Response
    {
        $this->denyAccessUnlessGranted('DELETE', $expense->getPeriod());

        if ($this->isCsrfTokenValid('delete'.$expense->getId(), $request->request->get('_token'))) {
            $expenseDescription = $expense->getDescription();
            $periodId = $expense->getPeriod()->getId();
            
            $this->entityManager->remove($expense);
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('La dépense "%s" a été supprimée avec succès !', $expenseDescription));

            return $this->redirectToRoute('app_period_show', ['id' => $periodId]);
        }

        return $this->redirectToRoute('app_expense_index');
    }
} 