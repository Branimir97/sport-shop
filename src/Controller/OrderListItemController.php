<?php

namespace App\Controller;

use App\Entity\OrderListItem;
use App\Repository\OrderListItemRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route({
 *     "en": "/all/orders",
 *     "hr": "/sve/narudžbe"
 * })
 * @IsGranted("ROLE_ADMIN")
 */
class OrderListItemController extends AbstractController
{
    /**
     * @Route("/", name="order_list_item_index", methods={"GET"})
     */
    public function index(OrderListItemRepository $orderListItemRepository): Response
    {
        return $this->render('order_list_item/index.html.twig', [
            'order_list_items' => $orderListItemRepository->findBy([], ['id'=>'DESC']),
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/status/in/process",
     *     "hr": "/{id}/status/obrada"
     * }, name="order_list_item_status_processing", methods={"GET", "PATCH"})
     */
    public function setStatusProcessing(Request $request,
                                        OrderListItemRepository $orderListItemRepository): RedirectResponse
    {
        $orderListItem = $orderListItemRepository->findOneBy(['id'=>$request->get('id')]);
        $entityManager = $this->getDoctrine()->getManager();
        $orderListItem->setStatus('U OBRADI');
        $entityManager->persist($orderListItem);
        $entityManager->flush();
        $this->addFlash('success',
            'Status narudžbe uspješno ažuriran.');
        return $this->redirectToRoute('order_list_item_index');
    }

    /**
     * @Route({
     *     "en": "/{id}/status/delivering",
     *     "hr": "/{id}/status/dostava"
     * }, name="order_list_item_status_delivering", methods={"GET", "PATCH"})
     */
    public function setStatusDelivering(Request $request,
                                        OrderListItemRepository $orderListItemRepository): RedirectResponse
    {
        $orderListItem = $orderListItemRepository->findOneBy(['id'=>$request->get('id')]);
        $entityManager = $this->getDoctrine()->getManager();
        $orderListItem->setStatus('NA DOSTAVI');
        $entityManager->persist($orderListItem);
        $entityManager->flush();
        $this->addFlash('success', 'Status narudžbe uspješno ažuriran.');
        return $this->redirectToRoute('order_list_item_index');
    }

    /**
     * @Route({
     *     "en": "/{id}/status/delivered",
     *     "hr": "/{id}/status/dostavljeno"
     * }, name="order_list_item_status_delivered", methods={"GET", "PATCH"})
     */
    public function setStatusDelivered(Request $request,
                                       OrderListItemRepository $orderListItemRepository): RedirectResponse
    {
        $orderListItem = $orderListItemRepository->findOneBy(['id'=>$request->get('id')]);
        $entityManager = $this->getDoctrine()->getManager();
        $orderListItem->setStatus('DOSTAVLJENO');
        $entityManager->persist($orderListItem);
        $entityManager->flush();
        $this->addFlash('success', 'Status narudžbe uspješno ažuriran.');
        return $this->redirectToRoute('order_list_item_index');
    }

    /**
     * @Route("/{id}", name="order_list_item_delete", methods={"DELETE"})
     */
    public function delete(Request $request, OrderListItem $orderListItem): Response
    {
        if ($this->isCsrfTokenValid('delete'.$orderListItem->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            foreach($orderListItem->getOrderItem() as $item) {
                $entityManager->remove($item);
            }
            $entityManager->remove($orderListItem);
            $entityManager->flush();
        }
        $this->addFlash('danger', 'Narudžba uspješno obrisana.');
        return $this->redirectToRoute('order_list_item_index');
    }
}
