<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/categories')]
#[IsGranted('ROLE_USER')]
class CategoryController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'app_category_index', methods: ['GET'])]
    public function index(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $categories = $this->categoryRepository->findByUserOrderedByName($user);

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $category = new Category();
        $category->setUser($this->getUser());
        
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('La catégorie "%s" a été créée avec succès !', $category->getName()));

            return $this->redirectToRoute('app_category_index');
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $category);

        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $category);

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('La catégorie "%s" a été modifiée avec succès !', $category->getName()));

            return $this->redirectToRoute('app_category_index');
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category): Response
    {
        $this->denyAccessUnlessGranted('DELETE', $category);

        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            // Vérifier si la catégorie est utilisée
            $usageCount = count($category->getIncomes()) + count($category->getExpenses());
            
            if ($usageCount > 0) {
                $this->addFlash('error', sprintf('Impossible de supprimer la catégorie "%s" car elle est utilisée par %d transaction(s).', $category->getName(), $usageCount));
                return $this->redirectToRoute('app_category_index');
            }
            
            $categoryName = $category->getName();
            $this->entityManager->remove($category);
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('La catégorie "%s" a été supprimée avec succès !', $categoryName));
        }

        return $this->redirectToRoute('app_category_index');
    }
} 