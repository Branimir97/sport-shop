<?php

namespace App\Controller;

use App\Entity\ActionItem;
use App\Form\ActionItemType;
use App\Repository\ActionItemRepository;
use App\Repository\ItemRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route({
 *     "en": "/actions/items",
 *     "hr": "/akcije/artikli"
 * })
 * @IsGranted("ROLE_ADMIN")
 */
class ActionItemController extends AbstractController
{
    /**
     * @Route("/", name="action_item_index", methods={"GET"})
     */
    public function index(ActionItemRepository $actionItemRepository): Response
    {
        return $this->render('actions/action_item/index.html.twig', [
            'action_items' => $actionItemRepository->findBy([], ['id'=>'DESC']),
        ]);
    }

    /**
     * @Route({
     *     "en": "/new",
     *     "hr": "/nova"
     * }, name="action_item_new", methods={"GET","POST"})
     */
    public function new(Request $request, ItemRepository $itemRepository,
                        TranslatorInterface $translator): Response
    {
        $items = $itemRepository->findAll();
        $noActionItems = [];
        foreach ($items as $item) {
            $itemCategories = $item->getItemCategories();
            foreach($itemCategories as $itemCategory) {
                if(is_null($itemCategory->getCategory()->getActionCategory())) {
                    array_push($noActionItems, $item);
                }
            }
        }

        $form = $this->createForm(ActionItemType::class, null, ['noActionItems' => $noActionItems]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $formTitle = $form->get('title')->getData();
            $formItems = $form->get('item')->getData();
            $formDiscountPercentage = $form->get('discountPercentage')->getData();
            foreach($formItems as $formItem) {
                $actionItem = new ActionItem();
                $actionItem->setTitle($formTitle);
                $actionItem->setItem($formItem);
                $actionItem->setDiscountPercentage($formDiscountPercentage);
                $entityManager->persist($actionItem);
            }
            $entityManager->flush();

            $this->addFlash('success',
                $translator->trans('flash_message.action_item_created',
                    [], 'action_item'));
            return $this->redirectToRoute('action_item_index');
        }

        return $this->render('actions/action_item/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="action_item_show", methods={"GET"})
     */
    public function show(ActionItem $actionItem): Response
    {
        return $this->render('actions/action_item/show.html.twig', [
            'action_item' => $actionItem,
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/edit",
     *     "hr": "/{id}/uredi"
     * }, name="action_item_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ActionItem $actionItem, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ActionItemType::class, $actionItem, ['isEdit'=>true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success',
                $translator->trans('flash_message.action_item_edited',
                    [], 'action_item'));
            return $this->redirectToRoute('action_item_index');
        }

        return $this->render('actions/action_item/edit.html.twig', [
            'action_item' => $actionItem,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="action_item_delete", methods={"DELETE"})
     */
    public function delete(Request $request, ActionItem $actionItem, TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete'.$actionItem->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($actionItem);
            $entityManager->flush();
        }
        $this->addFlash('danger',
            $translator->trans('flash_message.action_item_deleted',
                [], 'action_item'));
        return $this->redirectToRoute('action_item_index');
    }
}
