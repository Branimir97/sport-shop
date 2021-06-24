<?php

namespace App\Controller;

use App\Entity\ActionCategory;
use App\Form\ActionCategoryType;
use App\Repository\ActionCategoryRepository;
use App\Repository\CategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route({
 *     "en": "/actions/categories",
 *     "hr": "/akcije/kategorije"
 * })
 * @IsGranted("ROLE_ADMIN")
 */
class ActionCategoryController extends AbstractController
{
    /**
     * @Route("/", name="action_category_index", methods={"GET"})
     */
    public function index(ActionCategoryRepository $actionCategoryRepository): Response
    {
        return $this->render('actions/action_category/index.html.twig', [
            'action_categories' => $actionCategoryRepository->findBy([], ['id'=>'DESC']),
        ]);
    }

    /**
     * @Route({
     *     "en": "/new",
     *     "hr": "/nova"
     * }, name="action_category_new", methods={"GET","POST"})
     */
    public function new(Request $request, CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        $noActionCategories = [];
        foreach ($categories as $category) {
            if(!is_null($category->getActionCategory())){
                array_push($noActionCategories, $category);
            }
        }
        $form = $this->createForm(ActionCategoryType::class, null,
            ['noActionCategories'=>$noActionCategories]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $formTitle = $form->get('title')->getData();
            $formCategories = $form->get('category')->getData();
            $formDiscountPercentage = $form->get('discountPercentage')->getData();
            foreach($formCategories as $formCategory) {
                $actionCategory = new ActionCategory();
                $actionCategory->setTitle($formTitle);
                $actionCategory->setCategory($formCategory);
                $actionCategory->setDiscountPercentage($formDiscountPercentage);
                $entityManager->persist($actionCategory);
            }
            $entityManager->flush();

            $this->addFlash('success',
                'Kategorija/e uspješno postavljena/e na akciju.');
            return $this->redirectToRoute('action_category_index');
        }

        return $this->render('actions/action_category/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="action_category_show", methods={"GET"})
     */
    public function show(ActionCategory $actionCategory): Response
    {
        return $this->render('actions/action_category/show.html.twig', [
            'action_category' => $actionCategory,
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/edit",
     *     "hr": "/{id}/uredi"
     * }, name="action_category_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ActionCategory $actionCategory): Response
    {
        $form = $this->createForm(ActionCategoryType::class, $actionCategory, ['isEdit'=>true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('action_category_index');
        }

        return $this->render('actions/action_category/edit.html.twig', [
            'action_category' => $actionCategory,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="action_category_delete", methods={"DELETE"})
     */
    public function delete(Request $request, ActionCategory $actionCategory): Response
    {
        if ($this->isCsrfTokenValid('delete'.$actionCategory->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($actionCategory);
            $entityManager->flush();
        }
        $this->addFlash('danger', 'Akcija uspješno obrisana.');
        return $this->redirectToRoute('action_category_index');
    }
}
