<?php

namespace App\Controller;

use App\Entity\Color;
use App\Form\ItemColorType;
use App\Repository\ColorRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route({
 *     "en": "/colors",
 *     "hr": "/boje"
 * })
 * @IsGranted("ROLE_ADMIN")
 */
class ColorController extends AbstractController
{
    /**
     * @Route("/", name="color_index", methods={"GET"})
     */
    public function index(ColorRepository $colorRepository): Response
    {
        return $this->render('color/index.html.twig', [
            'colors' => $colorRepository->findBy([], ['id'=>'DESC']),
        ]);
    }

    /**
     * @Route({
     *     "en": "/new",
     *     "hr": "/nova"
     * }, name="color_new", methods={"GET","POST"})
     */
    public function new(Request $request, ColorRepository $colorRepository,
                        TranslatorInterface $translator): Response
    {
        $color = new Color();
        $form = $this->createForm(ItemColorType::class, $color);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $colorValue = $form->get('value')->getData();
            if(!is_null($colorRepository->findOneBy(['value'=>$colorValue]))) {
                $this->addFlash('danger',
                    $translator->trans('flash_message.color_exists',
                        [
                            '%color_code%' => $colorValue
                        ], 'color'));
                return $this->redirectToRoute('color_new');
            }
            $entityManager = $this->getDoctrine()->getManager();
            $colorNameHr = $form->get('name_hr')->getData();
            $colorNameEn = $form->get('name_en')->getData();
            $languages = ['hr', 'en'];
            foreach($languages as $language) {
                $color->setLocale($language);
                if($language == 'hr') {
                    $color->setName($colorNameHr);
                } else {
                    $color->setName($colorNameEn);
                }
                $entityManager->persist($color);
                $entityManager->flush();
            }
            $this->addFlash('success',
                $translator->trans('flash_message.color_added',
                    [], 'color'));
            return $this->redirectToRoute('color_index');
        }

        return $this->render('color/new.html.twig', [
            'color' => $color,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="color_show", methods={"GET"})
     */
    public function show(Color $color): Response
    {
        return $this->render('color/show.html.twig', [
            'color' => $color,
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/edit",
     *     "hr": "/{id}/uredi"
     * }, name="color_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Color $color,
                         TranslatorInterface $translator,
                         ColorRepository $colorRepository): Response
    {
        $form = $this->createForm(ItemColorType::class, $color);
        $colorNameTranslations = [];
        foreach($color->getColorTranslations() as $colorTranslation) {
            $colorNameTranslations[$colorTranslation->getLocale()] =
                                        $colorTranslation->getContent();
        }
        $form->get('name_hr')->setData($colorNameTranslations['hr']);
        $form->get('name_en')->setData($colorNameTranslations['en']);
        $currentColor = $color->getValue();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $colorValue = $form->get('value')->getData();
            if($currentColor !== $colorValue && !is_null(
                    $colorRepository->findOneBy(['value' => $colorValue]))) {
                $this->addFlash('danger',
                    $translator->trans('flash_message.color_exists',
                        [
                            '%color_code%' => $colorValue
                        ], 'color'));
                return $this->redirectToRoute('color_edit',
                    ['id' => $color->getId()]);
            }
            $colorNameHr = $form->get('name_hr')->getData();
            $colorNameEn = $form->get('name_en')->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $languages = ['hr', 'en'];
            foreach($languages as $language) {
                $color->setLocale($language);
                if($language == 'hr') {
                    $color->setName($colorNameHr);
                } else {
                    $color->setName($colorNameEn);
                }
                $entityManager->persist($color);
                $entityManager->flush();
            }
            $this->addFlash('success',
                $translator->trans('flash_message.color_edited',
                    [
                        '%color_code%' => $color->getValue()
                    ], 'color'));
            return $this->redirectToRoute('color_index');
        }

        return $this->render('color/edit.html.twig', [
            'color' => $color,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="color_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Color $color,
                           TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete'.$color->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($color);

            $this->addFlash('danger',
                $translator->trans('flash_message.color_deleted',
                    [
                        '%color_code%' => $color->getValue()
                    ], 'color'));
            $entityManager->flush();
        }
        return $this->redirectToRoute('color_index');
    }
}
