<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Repository\CategoryTranslationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route({
 *     "en": "/categories",
 *     "hr": "/kategorije"
 * })
 * @IsGranted("ROLE_ADMIN")
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/", name="category_index", methods={"GET"})
     */
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route({
     *     "en": "/new",
     *     "hr": "/nova"
     * }, name="category_new", methods={"GET","POST"})
     */
    public function new(Request $request, CategoryRepository $categoryRepository,
                        TranslatorInterface $translator): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categoryNameHr = $form->get('name_hr')->getData();
            $categoryNameEn = $form->get('name_en')->getData();
            if(!is_null($categoryRepository->findOneBy(['name' => $categoryNameHr]))) {
                if($request->getLocale() == 'hr') {
                    $this->addFlash('danger',
                        $translator->trans('flash_message.category_exists',
                            [
                                '%category_name%' => $categoryNameHr
                            ], 'category'));
                } else {
                    $this->addFlash('danger',
                        $translator->trans('flash_message.category_exists',
                            [
                                '%category_name%' => $categoryNameEn
                            ], 'category'));
                }
                return $this->redirectToRoute('category_new');
            }
            $entityManager = $this->getDoctrine()->getManager();
            $languages = ['hr', 'en'];
            foreach($languages as $language) {
                $category->setLocale($language);
                if($language == 'hr') {
                    $category->setName($categoryNameHr);
                } else {
                    $category->setName($categoryNameEn);
                }
                $entityManager->persist($category);
                $entityManager->flush();
            }
            $this->addFlash('success',
                $translator->trans('flash_message.category_added',
                    [], 'category'));
            return $this->redirectToRoute('category_index');
        }
        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="category_show", methods={"GET"})
     */
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/edit",
     *     "hr": "/{id}/uredi"
     * }, name="category_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Category $category,
                         TranslatorInterface $translator,
                         CategoryRepository $categoryRepository): Response
    {
        $form = $this->createForm(CategoryType::class);
        $categoryNameTranslations = [];
        foreach($category->getCategoryTranslations() as $categoryTranslation) {
            $categoryNameTranslations[$categoryTranslation->getLocale()] =
                $categoryTranslation->getContent();
        }
        $form->get('name_hr')->setData($categoryNameTranslations['hr']);
        $form->get('name_en')->setData($categoryNameTranslations['en']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $categoryNameHr = $form->get('name_hr')->getData();
            $categoryNameEn = $form->get('name_en')->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $languages = ['hr', 'en'];
            foreach($languages as $language) {
                $category->setLocale($language);
                if ($language == 'hr') {
                    $category->setName($categoryNameHr);
                } else {
                    $category->setName($categoryNameEn);
                }
                $entityManager->persist($category);
                $entityManager->flush();
                $entityManager->persist($category);
                $entityManager->flush();
            }
            if($request->getLocale() == 'hr') {
                $this->addFlash('success',
                    $translator->trans('flash_message.category_edited',
                        [
                            '%category_name%' => $categoryNameHr
                        ], 'category'));
            } else {
                $this->addFlash('success',
                    $translator->trans('flash_message.category_edited',
                        [
                            '%category_name%' => $categoryNameEn
                        ], 'category'));
            }
            return $this->redirectToRoute('category_index');
        }
        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="category_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Category $category,
                           TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($category);

            $this->addFlash('danger',
                $translator->trans('flash_message.category_deleted',
                    [
                        '%category_name%' => $category->getName()
                    ], 'category'));
            $entityManager->flush();
        }
        return $this->redirectToRoute('category_index');
    }
}
