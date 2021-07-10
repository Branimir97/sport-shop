<?php

namespace App\Controller;

use App\Entity\Size;
use App\Form\SizeType;
use App\Repository\SizeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route({
 *     "en": "/sizes",
 *     "hr": "/veličine",
 * })
 * @IsGranted("ROLE_ADMIN")
 */
class SizeController extends AbstractController
{
    /**
     * @Route("/", name="size_index", methods={"GET"})
     */
    public function index(SizeRepository $sizeRepository): Response
    {
        return $this->render('size/index.html.twig', [
            'sizes' => $sizeRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route({
     *     "en": "/new",
     *     "hr": "/nova",
     * }, name="size_new", methods={"GET","POST"})
     */
    public function new(Request $request, SizeRepository $sizeRepository,
                        TranslatorInterface $translator): Response
    {
        $size = new Size();
        $form = $this->createForm(SizeType::class, $size);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sizeValue = $form->get('value')->getData();
            if(!is_null($sizeRepository->findOneBy(['value' => $sizeValue]))) {
                $this->addFlash('danger',
                    $translator->trans('flash_message.size_exists',
                        [
                            '%size_value%' => $sizeValue
                        ], 'size'));
                return $this->redirectToRoute('size_new');
            }
            $entityManager = $this->getDoctrine()->getManager();
            $sizeType = $form->get('type')->getData();
            $languages = ['hr', 'en'];
            if($sizeType == "Obuća") {
                foreach($languages as $language) {
                    $size->setLocale($language);
                    if($language == 'hr') {
                        $size->setType("Obuća");
                    } else {
                        $size->setType("Footwear");
                    }
                    $entityManager->persist($size);
                    $entityManager->flush();
                }
            } else {
                foreach($languages as $language) {
                    $size->setLocale($language);
                    if($language == 'hr') {
                        $size->setType("Odjeća");
                    } else {
                        $size->setType("Clothes");
                    }
                    $entityManager->persist($size);
                    $entityManager->flush();
                }
            }
            $this->addFlash('success',
                $translator->trans('flash_message.size_added',
                    [], 'size'));
            return $this->redirectToRoute('size_index');
        }

        return $this->render('size/new.html.twig', [
            'size' => $size,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="size_show", methods={"GET"})
     */
    public function show(Size $size): Response
    {
        return $this->render('size/show.html.twig', [
            'size' => $size,
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/edit",
     *     "hr": "/{id}/uredi",
     * }, name="size_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Size $size,
                         TranslatorInterface $translator,
                         SizeRepository $sizeRepository): Response
    {
        $form = $this->createForm(SizeType::class, $size);
        $currentSizeValue = $size->getValue();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sizeValue = $form->get('value')->getData();
            if($currentSizeValue !== $sizeValue &&
                !is_null($sizeRepository->findOneBy(['value' => $sizeValue]))) {
                $this->addFlash('danger',
                    $translator->trans('flash_message.size_exists',
                        [
                            '%size_value%' => $sizeValue
                        ], 'size'));
                return $this->redirectToRoute('size_edit', ['id' => $size->getId()]);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $sizeType = $form->get('type')->getData();
            $languages = ['hr', 'en'];
            if($sizeType == "Obuća") {
                foreach($languages as $language) {
                    $size->setLocale($language);
                    if($language == 'hr') {
                        $size->setType("Obuća");
                    } else {
                        $size->setType("Footwear");
                    }
                    $entityManager->persist($size);
                    $entityManager->flush();
                }
            } else {
                foreach($languages as $language) {
                    $size->setLocale($language);
                    if($language == 'hr') {
                        $size->setType("Odjeća");
                    } else {
                        $size->setType("Clothes");
                    }
                    $entityManager->persist($size);
                    $entityManager->flush();
                }
            }

            $this->addFlash('success',
                $translator->trans('flash_message.size_edited',
                    [
                        '%size_value%' => $size->getValue()
                    ], 'size'));
            return $this->redirectToRoute('size_index');
        }

        return $this->render('size/edit.html.twig', [
            'size' => $size,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="size_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Size $size,
                           TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete'.$size->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($size);

            $this->addFlash('danger',
                $translator->trans('flash_message.size_deleted',
                    [
                        '%size_value%' => $size->getValue()
                    ], 'size'));
            $entityManager->flush();
        }

        return $this->redirectToRoute('size_index');
    }
}
