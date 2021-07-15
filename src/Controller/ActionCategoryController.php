<?php

namespace App\Controller;

use App\Entity\ActionCategory;
use App\Form\ActionCategoryType;
use App\Repository\ActionCategoryRepository;
use App\Repository\CategoryRepository;
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
            'action_categories' => $actionCategoryRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route({
     *     "en": "/new",
     *     "hr": "/nova"
     * }, name="action_category_new", methods={"GET","POST"})
     */
    public function new(Request $request, CategoryRepository $categoryRepository,
                        TranslatorInterface $translator,
                        SubscriberRepository $subscriberRepository,
                        MailerInterface $mailer): Response
    {
        $categories = $categoryRepository->findAll();
        $noActionCategories = [];
        foreach ($categories as $category) {
            if(!is_null($category->getActionCategory())){
                array_push($noActionCategories, $category);
            }
        }
        $form = $this->createForm(ActionCategoryType::class, null,
            ['noActionCategories' => $noActionCategories]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $formCategories = $form->get('category')->getData();
            $formTitleHr = $form->get('title_hr')->getData();
            $formTitleEn = $form->get('title_en')->getData();
            $formDiscountPercentage = $form->get('discountPercentage')->getData();
            $languages = ['hr', 'en'];
            foreach($formCategories as $formCategory) {
                $actionCategory = new ActionCategory();
                $actionCategory->setCategory($formCategory);
                $actionCategory->setDiscountPercentage($formDiscountPercentage);
                foreach($languages as $language) {
                    $actionCategory->setLocale($language);
                    if($language == 'hr') {
                        $actionCategory->setTitle($formTitleHr);
                    } else {
                        $actionCategory->setTitle($formTitleEn);
                    }
                    $entityManager->persist($actionCategory);
                    $entityManager->flush();

                }
                $subscribers = $subscriberRepository->findAll();
                $subject = $translator->trans('new_action_category.subject',
                    [], 'email');
                foreach($subscribers as $subscriber) {
                    $receiverEmail = $subscriber->getEmail();
                    $email = (new TemplatedEmail())
                        ->to($receiverEmail)
                        ->subject($subject)
                        ->context([
                            'categoryName' => $actionCategory->getCategory()->getName(),
                            'discountPercentage' => $actionCategory->getDiscountPercentage()
                        ])
                        ->htmlTemplate('email/new_action_category.html.twig');
                    try {
                        $mailer->send($email);
                    } catch (TransportExceptionInterface $exception) {}
                }
            }

            $this->addFlash('success',
                    $translator->trans('flash_message.action_category_created',
                        [], 'action_category'));
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
    public function edit(Request $request, ActionCategory $actionCategory,
                         TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ActionCategoryType::class, $actionCategory, ['isEdit' => true]);
        $actionNameTranslations = [];
        foreach($actionCategory->getActionCategoryTranslations() as $actionCategoryTranslation) {
            $actionNameTranslations[$actionCategoryTranslation->getLocale()] =
                $actionCategoryTranslation->getContent();
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
                $actionCategory->setLocale($language);
                if($language == 'hr') {
                    $actionCategory->setTitle($formTitleHr);
                } else {
                    $actionCategory->setTitle($formTitleEn);
                }
                $entityManager->persist($actionCategory);
                $entityManager->flush();
            }

            $this->addFlash('success',
                $translator->trans('flash_message.action_category_edited',
                    [], 'action_category'));
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
    public function delete(Request $request, ActionCategory $actionCategory,
                           TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete'.$actionCategory->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($actionCategory);
            $entityManager->flush();
        }
        $this->addFlash('danger',
            $translator->trans('flash_message.action_category_deleted',
                [], 'action_category'));
        return $this->redirectToRoute('action_category_index');
    }
}
