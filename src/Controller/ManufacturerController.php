<?php

namespace App\Controller;

use App\Entity\Manufacturer;
use App\Form\ManufacturerType;
use App\Repository\ManufacturerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/manufacturer")
 */
class ManufacturerController extends AbstractController
{
    /**
     * @Route("/", name="manufacturer_index", methods={"GET"})
     */
    public function index(ManufacturerRepository $manufacturerRepository): Response
    {
        return $this->render('manufacturer/index.html.twig', [
            'manufacturers' => $manufacturerRepository->findBy([], ['id'=>'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="manufacturer_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $manufacturer = new Manufacturer();
        $form = $this->createForm(ManufacturerType::class, $manufacturer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categoryName = $form->get('name')->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($manufacturer);
            $entityManager->flush();

            $this->addFlash('success', 'Proizvođač uspješno dodan.');
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
     * @Route("/{id}/edit", name="manufacturer_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Manufacturer $manufacturer): Response
    {
        $form = $this->createForm(ManufacturerType::class, $manufacturer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', '"'.$manufacturer->getName().'" proizvođač uspješno ažuriran.');
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
    public function delete(Request $request, Manufacturer $manufacturer): Response
    {
        if ($this->isCsrfTokenValid('delete'.$manufacturer->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($manufacturer);

            $this->addFlash('danger', '"'.$manufacturer->getName().'" proizvođač uspješno obrisan.');
            $entityManager->flush();
        }
        return $this->redirectToRoute('manufacturer_index');
    }
}
