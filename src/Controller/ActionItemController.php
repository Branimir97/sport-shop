<?php

namespace App\Controller;

use App\Entity\ActionItem;
use App\Form\ActionItemType;
use App\Repository\ActionItemRepository;
use App\Repository\ItemRepository;
use App\Repository\SubscriberRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
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
            'action_items' => $actionItemRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route({
     *     "en": "/new",
     *     "hr": "/nova"
     * }, name="action_item_new", methods={"GET","POST"})
     */
    public function new(Request $request, ItemRepository $itemRepository,
                        TranslatorInterface $translator,
                        MailerInterface $mailer,
                        SubscriberRepository $subscriberRepository): Response
    {
        $items = $itemRepository->findAll();
        $actionItems = [];
        foreach ($items as $item) {
            $itemCategories = $item->getItemCategories();
            foreach($itemCategories as $itemCategory) {
                if(!is_null($itemCategory->getCategory()->getActionCategory())) {
                    array_push($actionItems, $item);
                }
            }
            if(!is_null($item->getActionItem())) {
                array_push($actionItems, $item);
            }
        }

        $form = $this->createForm(ActionItemType::class, null,
            ['actionItems' => $actionItems]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $formItems = $form->get('item')->getData();
            $formTitleHr = $form->get('title_hr')->getData();
            $formTitleEn = $form->get('title_en')->getData();
            $formDiscountPercentage = $form->get('discountPercentage')->getData();
            $languages = ['hr', 'en'];
            foreach($formItems as $formItem) {
                $actionItem = new ActionItem();
                $actionItem->setItem($formItem);
                $actionItem->setDiscountPercentage($formDiscountPercentage);
                foreach($languages as $language) {
                    $actionItem->setLocale($language);
                    if($language == 'hr') {
                        $actionItem->setTitle($formTitleHr);
                    } else {
                        $actionItem->setTitle($formTitleEn);
                    }
                    $entityManager->persist($actionItem);
                    $entityManager->flush();
                }
                $subscribers = $subscriberRepository->findAll();
                $subject = $translator->trans('new_action_item.subject',
                    [], 'email');
                foreach($subscribers as $subscriber) {
                    $receiverEmail = $subscriber->getEmail();
                    $email = (new TemplatedEmail())
                        ->to($receiverEmail)
                        ->subject($subject)
                        ->context([
                            'itemTitle' => $actionItem->getItem()->getTitle(),
                            'discountPercentage' => $actionItem->getDiscountPercentage()
                        ])
                        ->htmlTemplate('email/new_action_item.html.twig');
                    try {
                        $mailer->send($email);
                    } catch (TransportExceptionInterface $exception) {}
                }
            }

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
    public function edit(Request $request, ActionItem $actionItem,
                         TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ActionItemType::class, $actionItem, ['isEdit' => true]);
        $actionNameTranslations = [];
        foreach($actionItem->getActionItemTranslations() as $actionItemTranslation) {
            $actionNameTranslations[$actionItemTranslation->getLocale()] =
                $actionItemTranslation->getContent();
        }
        $form->get('title_hr')->setData($actionNameTranslations['hr']);
        $form->get('title_en')->setData($actionNameTranslations['en']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formTitleHr = $form->get('title_hr')->getData();
            $formTitleEn = $form->get('title_en')->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $languages = ['hr', 'en'];
            foreach($languages as $language) {
                $actionItem->setLocale($language);
                if($language == 'hr') {
                    $actionItem->setTitle($formTitleHr);
                } else {
                    $actionItem->setTitle($formTitleEn);
                }
                $entityManager->persist($actionItem);
                $entityManager->flush();
            }

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
    public function delete(Request $request, ActionItem $actionItem,
                           TranslatorInterface $translator): Response
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
