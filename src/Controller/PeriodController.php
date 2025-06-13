<?php

namespace App\Controller;

use App\Entity\Period;
use App\Form\PeriodType;
use App\Repository\PeriodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/periods')]
#[IsGranted('ROLE_USER')]
class PeriodController extends AbstractController
{
    public function __construct(
        private PeriodRepository $periodRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'app_period_index', methods: ['GET'])]
    public function index(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $periods = $this->periodRepository->findByUserOrderedByDate($user);

        return $this->render('period/index.html.twig', [
            'periods' => $periods,
        ]);
    }

    #[Route('/new', name: 'app_period_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $period = new Period();
        $period->setUser($this->getUser());
        
        $form = $this->createForm(PeriodType::class, $period);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($period);
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('La période "%s" a été créée avec succès !', $period->getName()));

            return $this->redirectToRoute('app_period_show', ['id' => $period->getId()]);
        }

        return $this->render('period/new.html.twig', [
            'period' => $period,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_period_show', methods: ['GET'])]
    public function show(Period $period): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $period);

        return $this->render('period/show.html.twig', [
            'period' => $period,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_period_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Period $period): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $period);

        $form = $this->createForm(PeriodType::class, $period);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('La période "%s" a été modifiée avec succès !', $period->getName()));

            return $this->redirectToRoute('app_period_show', ['id' => $period->getId()]);
        }

        return $this->render('period/edit.html.twig', [
            'period' => $period,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_period_delete', methods: ['POST'])]
    public function delete(Request $request, Period $period): Response
    {
        $this->denyAccessUnlessGranted('DELETE', $period);

        if ($this->isCsrfTokenValid('delete'.$period->getId(), $request->request->get('_token'))) {
            $periodName = $period->getName();
            $this->entityManager->remove($period);
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('La période "%s" a été supprimée avec succès !', $periodName));
        }

        return $this->redirectToRoute('app_period_index');
    }
} 