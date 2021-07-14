<?php

namespace App\Controller;

use App\Entity\Manufacturer;
use App\Form\ManufacturerType;
use App\Repository\ManufacturerRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route({
 *     "en": "/manufacturers",
 *     "hr": "/proizvođači"
 * })
 * @IsGranted("ROLE_ADMIN")
 */
class ManufacturerController extends AbstractController
{
    /**
     * @Route("/", name="manufacturer_index", methods={"GET"})
     */
    public function index(ManufacturerRepository $manufacturerRepository): Response
    {
        return $this->render('manufacturer/index.html.twig', [
            'manufacturers' => $manufacturerRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route({
     *     "en": "/new",
     *     "hr": "/novi"
     * }, name="manufacturer_new", methods={"GET","POST"})
     */
    public function new(Request $request,
                        ManufacturerRepository $manufacturerRepository,
                        TranslatorInterface $translator): Response
    {
        $manufacturer = new Manufacturer();
        $form = $this->createForm(ManufacturerType::class, $manufacturer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manufacturerName = $form->get('name')->getData();
            if(!is_null($manufacturerRepository->findOneBy(
                ['name' => $manufacturerName]))) {
                $this->addFlash('danger',
                    $translator->trans('flash_message.manufacturer_exists',
                        [
                            '%manufacturer_name%' => $manufacturer->getName()
                        ], 'manufacturer'));
                return $this->redirectToRoute('manufacturer_new');
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($manufacturer);
            $entityManager->flush();

            $this->addFlash('success',
                $translator->trans('flash_message.manufacturer_added',
                    [], 'manufacturer'));
            return $this->redirectToRoute('manufacturer_index');
        }

        return $this->render('manufacturer/new.html.twig', [
            'manufacturer' => $manufacturer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="manufacturer_show", methods={"GET"})
     */
    public function show(Manufacturer $manufacturer): Response
    {
        return $this->render('manufacturer/show.html.twig', [
            'manufacturer' => $manufacturer,
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/edit",
     *     "hr": "/{id}/uredi"
     * }, name="manufacturer_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Manufacturer $manufacturer,
                         TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ManufacturerType::class, $manufacturer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success',
                $translator->trans('flash_message.manufacturer_edited',
                    [
                        '%manufacturer_name%' => $manufacturer->getName()
                    ], 'manufacturer'));
            return $this->redirectToRoute('manufacturer_index');
        }

        return $this->render('manufacturer/edit.html.twig', [
            'manufacturer' => $manufacturer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="manufacturer_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Manufacturer $manufacturer,
                           TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete'.$manufacturer->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($manufacturer);

            $this->addFlash('danger',
                $translator->trans('flash_message.manufacturer_deleted',
                    [
                        '%manufacturer_name%' => $manufacturer->getName()
                    ], 'manufacturer'));
            $entityManager->flush();
        }
        return $this->redirectToRoute('manufacturer_index');
    }
}
