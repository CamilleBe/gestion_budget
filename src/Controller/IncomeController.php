<?php

namespace App\Controller;

use App\Entity\Income;
use App\Entity\Period;
use App\Form\IncomeType;
use App\Repository\IncomeRepository;
use App\Repository\PeriodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/incomes')]
#[IsGranted('ROLE_USER')]
class IncomeController extends AbstractController
{
    public function __construct(
        private IncomeRepository $incomeRepository,
        private PeriodRepository $periodRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'app_income_index', methods: ['GET'])]
    public function index(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $incomes = $this->incomeRepository->findByUserOrderedByDate($user);

        return $this->render('income/index.html.twig', [
            'incomes' => $incomes,
        ]);
    }

    #[Route('/new/{period}', name: 'app_income_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Period $period = null): Response
    {
        // Si aucune période n'est spécifiée, essayer de trouver la période actuelle
        if (!$period) {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();
            $period = $this->periodRepository->findCurrentPeriodForUser($user);
            
            if (!$period) {
                $this->addFlash('warning', 'Vous devez d\'abord créer une période avant d\'ajouter des revenus.');
                return $this->redirectToRoute('app_period_new');
            }
        }

        // Vérifier que la période appartient à l'utilisateur
        $this->denyAccessUnlessGranted('VIEW', $period);

        $income = new Income();
        $income->setPeriod($period);
        $income->setDate(new \DateTime()); // Date par défaut = aujourd'hui
        
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $form = $this->createForm(IncomeType::class, $income, ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($income);
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('Le revenu "%s" a été ajouté avec succès !', $income->getDescription()));

            return $this->redirectToRoute('app_period_show', ['id' => $period->getId()]);
        }

        return $this->render('income/new.html.twig', [
            'income' => $income,
            'period' => $period,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_income_show', methods: ['GET'])]
    public function show(Income $income): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $income->getPeriod());

        return $this->render('income/show.html.twig', [
            'income' => $income,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_income_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Income $income): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $income->getPeriod());

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $form = $this->createForm(IncomeType::class, $income, ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('Le revenu "%s" a été modifié avec succès !', $income->getDescription()));

            return $this->redirectToRoute('app_period_show', ['id' => $income->getPeriod()->getId()]);
        }

        return $this->render('income/edit.html.twig', [
            'income' => $income,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_income_delete', methods: ['POST'])]
    public function delete(Request $request, Income $income): Response
    {
        $this->denyAccessUnlessGranted('DELETE', $income->getPeriod());

        if ($this->isCsrfTokenValid('delete'.$income->getId(), $request->request->get('_token'))) {
            $incomeDescription = $income->getDescription();
            $periodId = $income->getPeriod()->getId();
            
            $this->entityManager->remove($income);
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('Le revenu "%s" a été supprimé avec succès !', $incomeDescription));

            return $this->redirectToRoute('app_period_show', ['id' => $periodId]);
        }

        return $this->redirectToRoute('app_income_index');
    }
} 