<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Image;
use App\Entity\Item;
use App\Entity\ItemCategory;
use App\Entity\ItemColor;
use App\Entity\ItemSize;
use App\Entity\ItemTag;
use App\Entity\Review;
use App\Entity\Tag;
use App\Form\CartItemType;
use App\Form\CartType;
use App\Form\ColorQuantityType;
use App\Form\EditColorQuantityType;
use App\Form\EditSizeQuantityType;
use App\Form\NewItemCategoryType;
use App\Form\NewItemColorType;
use App\Form\NewItemImageType;
use App\Form\NewItemSizeType;
use App\Form\NewItemTagType;
use App\Form\QuantityType;
use App\Form\ItemType;
use App\Form\ReviewType;
use App\Form\SizeQuantityType;
use App\Repository\CartItemRepository;
use App\Repository\ColorRepository;
use App\Repository\ImageRepository;
use App\Repository\ItemCategoryRepository;
use App\Repository\ItemColorRepository;
use App\Repository\ItemRepository;
use App\Repository\ItemSizeRepository;
use App\Repository\ItemTagRepository;
use App\Repository\SizeRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use App\Service\ImageUploadHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/item")
 */
class ItemController extends AbstractController
{
    /**
     * @Route("/", name="item_index", methods={"GET"})
     */
    public function index(ItemRepository $itemRepository): Response
    {
        return $this->render('item/index.html.twig', [
            'items' => $itemRepository->findBy([], ['id'=>'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="item_new", methods={"GET","POST"})
     */
    public function new(Request $request, TagRepository $tagRepository, ImageUploadHelper $uploadHelper): Response
    {
        $item = new Item();
        $formNewItem = $this->createForm(ItemType::class, $item);
        $formNewItem->handleRequest($request);
        if ($formNewItem->isSubmitted() && $formNewItem->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $item->setCipher(uniqid());
            $categories = $formNewItem->get('category')->getData();
            $tags = $formNewItem->get('tag')->getData();
            $sizes = $formNewItem->get('size')->getData();
            $colors = $formNewItem->get('color')->getData();
            $images = $formNewItem->get('image')->getData();
            if (count($sizes) != 0 || count($colors) != 0) {
                $formSetQuantity = $this->createForm(QuantityType::class, null, [
                    'sizes' => $sizes, 'colors' => $colors
                ]);
                $formSetQuantity->handleRequest($request);
                if ($formSetQuantity->isSubmitted() && $formSetQuantity->isValid()) {
                    $this->addFlash('success', 'Artikl uspješno dodan.');
                    return $this->redirectToRoute('item_index');
                }
                return $this->render('item/set_quantity.html.twig', [
                    'formSetQuantity' => $formSetQuantity->createView()
                ]);
            } else {
                foreach($categories as $category) {
                    $itemCategory = new ItemCategory();
                    $itemCategory->setItem($item);
                    $itemCategory->setCategory($category);
                    $entityManager->persist($itemCategory);
                }
                if(!is_null($tags)) {
                    $explodedTags = explode(PHP_EOL, $tags);
                    foreach ($explodedTags as $tagName) {
                        $tagObject = $tagRepository->findOneBy(['name'=>$tagName]);
                        if(is_null($tagObject)) {
                            $tagObject = new Tag();
                            $tagObject->setName($tagName);
                        }
                        $entityManager->persist($tagObject);
                        $itemTag = new ItemTag();
                        $itemTag->setItem($item);
                        $itemTag->setTag($tagObject);
                        $entityManager->persist($itemTag);
                    }
                }
                foreach ($images as $image) {
                    $newFileName = $uploadHelper->uploadItemImage($image);
                    if(is_null($newFileName)) {
                        $this->addFlash('danger',
                            'Pogreška prilikom prijenosa fotografija. 
                                    Dopušteni formati fotografija: jpg, jpeg i png.'
                        );
                        return $this->redirectToRoute('item_new');
                    }
                    $image = new Image();
                    $image->setPath($newFileName);
                    $image->setItem($item);
                    $entityManager->persist($image);
                }
                $entityManager->persist($item);
                $entityManager->flush();
                $this->addFlash('success', 'Artikl uspješno dodan.');
                return $this->redirectToRoute('item_index');
            }
        }
        return $this->render('item/new_item.html.twig', [
            'item' => $item,
            'formNewItem' => $formNewItem->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="item_show", methods={"GET"})
     */
    public function show(Item $item): Response
    {
        return $this->render('item/show_item.html.twig', [
            'item' => $item,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="item_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Item $item): Response
    {
        $formNewItem = $this->createForm(ItemType::class, $item, ['isEdit'=>true]);
        $formNewItem->get('manufacturer')->setData($item->getManufacturer());
        $formNewItem->handleRequest($request);
        if ($formNewItem->isSubmitted() && $formNewItem->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($item);
            $entityManager->flush();

            $this->addFlash('success', 'Artikl "'.$item->getTitle().'" uspješno ažuriran.');
            return $this->redirectToRoute('item_index');
        }

        return $this->render('item/edit_item.html.twig', [
            'item' => $item,
            'formNewItem' => $formNewItem->createView(),
        ]);
    }

    /**
     * @Route("/{id}/image/add", name="item_image_add", methods={"GET", "POST"})
     */
    public function addImage(Request $request, ImageUploadHelper $uploadHelper, ItemRepository $itemRepository): Response
    {
        $form = $this->createForm(NewItemImageType::class);
        $form->handleRequest($request);
        $item = $itemRepository->findOneBy(['id'=>$request->get('id')]);
        if($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $images = $form->get('image')->getData();
            foreach ($images as $image) {
                $newFileName = $uploadHelper->uploadItemImage($image);
                if(is_null($newFileName)) {
                    $this->addFlash('danger',
                        'Pogreška prilikom prijenosa fotografija. 
                                    Dopušteni formati fotografija: jpg, jpeg i png.'
                    );
                    return $this->redirectToRoute('item_edit', ['id'=>$item->getId()]);
                }
                $image = new Image();
                $image->setPath($newFileName);
                $image->setItem($item);
                $entityManager->persist($image);
            }
            $entityManager->flush();
            $this->addFlash('success', "Fotografija/e uspješno dodana/e.");
            return $this->redirectToRoute('item_edit', ['id'=>$item->getId()]);
        }
        return $this->render('item/new_image.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/image/delete", name="item_image_delete", methods={"DELETE"})
     */
    public function deleteImage(Request $request, Image $image, ImageRepository $imageRepository): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $image = $imageRepository->findOneBy(['id'=>$image->getId()]);
            $itemId = $image->getItem()->getId();
            unlink('../public/uploads/'.$image->getPath());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($image);
            $this->addFlash('success', 'Fotografija uspješno obrisana.');
            $entityManager->flush();
        }
        return $this->redirectToRoute('item_edit', ['id'=>$itemId]);
    }

    /**
     * @Route("/{id}/add/category", name="item_add_category", methods={"GET","POST"})
     */
    public function addCategory(Request $request, ItemRepository $itemRepository): Response
    {
        $item = $itemRepository->findOneBy(['id'=>$request->get('id')]);
        $categories = $item->getItemCategories();
        $categoryNames = [];
        foreach($categories as $category) {
            array_push($categoryNames, $category->getCategory()->getName());
        }
        $form = $this->createForm(NewItemCategoryType::class, [], ['category_names'=>$categoryNames]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $categories = $form->get('category')->getData();
            foreach($categories as $category) {
                $itemCategory = new ItemCategory();
                $itemCategory->setItem($item);
                $itemCategory->setCategory($category);
                $entityManager->persist($itemCategory);
            }
            $entityManager->flush();
            $this->addFlash('success', 'Kategorija/e uspješno dodana/e.');
            return $this->redirectToRoute('item_edit', ['id'=>$item->getId()]);
        }

        return $this->render('item/new_category.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete/category", name="item_category_delete", methods={"DELETE"})
     */
    public function deleteCategory(Request $request, ItemCategory $itemCategory, ItemCategoryRepository $itemCategoryRepository): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$itemCategory->getId(), $request->request->get('_token'))) {
            $itemId = $itemCategory->getItem()->getId();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($itemCategory);
            $this->addFlash('success', 'Kategorija "'.$itemCategory->getCategory()->getName().'" uspješno obrisana.');
            $entityManager->flush();
        }
        return $this->redirectToRoute('item_edit', ['id'=>$itemId]);
    }

    /**
     * @Route("/{id}/add/tag", name="item_add_tag", methods={"GET","POST"})
     */
    public function addTag(Request $request, ItemRepository $itemRepository, TagRepository $tagRepository, ItemTagRepository $itemTagRepository): Response
    {
        $item = $itemRepository->findOneBy(['id'=>$request->get('id')]);
        $form = $this->createForm(NewItemTagType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $tags = $form->get('tag')->getData();
            $explodedTags = explode(PHP_EOL, $tags);
            foreach ($explodedTags as $tagName) {
                $tagObject = $tagRepository->findOneBy(['name'=>$tagName]);
                if(is_null($tagObject)) {
                    $tagObject = new Tag();
                    $tagObject->setName($tagName);
                    $entityManager->persist($tagObject);
                    $entityManager->flush();
                }
                $itemTagObj = $itemTagRepository->findOneBy(['tag'=>$tagObject->getId()]);
                if(is_null($itemTagObj)) {
                    $itemTag = new ItemTag();
                    $itemTag->setItem($item);
                    $itemTag->setTag($tagObject);
                    $entityManager->persist($itemTag);
                } else {
                    $this->addFlash('danger', 'Tag/ovi koje ste pokušali unijeti već su povezani s ovim artiklom.');
                    return $this->redirectToRoute('item_edit', ['id'=>$item->getId()]);
                }
            }
            $entityManager->flush();
            $this->addFlash('success', 'Tag/ovi uspješno dodan/i.');
            return $this->redirectToRoute('item_edit', ['id'=>$item->getId()]);
        }
        return $this->render('item/new_tag.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete/tag", name="item_tag_delete", methods={"DELETE"})
     */
    public function deleteTag(Request $request, ItemTag $itemTag, ItemTagRepository $itemTagRepository): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$itemTag->getId(), $request->request->get('_token'))) {
            $itemId = $itemTag->getItem()->getId();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($itemTag);
            $this->addFlash('success', 'Tag "'.$itemTag->getTag()->getName().'" uspješno obrisan.');
            $entityManager->flush();
        }
        return $this->redirectToRoute('item_edit', ['id'=>$itemId]);
    }

    /**
     * @Route("/{id}/add/color", name="item_add_color", methods={"GET","POST"})
     */
    public function addColor(Request $request, ItemRepository $itemRepository): Response
    {
        $item = $itemRepository->findOneBy(['id'=>$request->get('id')]);
        $colors = $item->getItemColors();
        $colorValues = [];
        foreach($colors as $color) {
            array_push($colorValues, $color->getColor()->getValue());
        }
        $form = $this->createForm(NewItemColorType::class, null, ['color_values'=>$colorValues]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $selectedColors = $form->get('color')->getData();
            $session = new Session();
            $session->set('colors', $selectedColors);
            $session->set('item', $item);
            return $this->redirectToRoute('item_color_set_quantity');
        }
        return $this->render('item/new_color.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     *@Route("/set/new/color/quantity", name="item_color_set_quantity", methods={"GET", "POST"})
     */
    public function setNewColorQuantity(Request $request, ColorRepository $colorRepository): Response
    {
        $markedColors = [];
        $colorQuantities = [];
        $colors = $this->get('session')->get('colors');
        $item = $this->get('session')->get('item');
        $form = $this->createForm(ColorQuantityType::class, null, ['colors'=>$colors]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($colors as $color) {
                array_push($markedColors, $color->getId());
            }
            $formData = $form->getData();
            $formKeys = array_keys($formData);

            foreach ($formKeys as $key) {
                if (str_contains($key, "color")) {
                    array_push($colorQuantities, $formData[$key]);
                }
            }
            $colorsDone = array_combine($markedColors, $colorQuantities);

            foreach ($colorsDone as $key => $value) {
                $colorObj = $colorRepository->findOneBy(['id' => $key]);
                $itemColor = new ItemColor();
                $itemColor->setItem($item);
                $itemColor->setColor($colorObj);
                $itemColor->setQuantity($value);
                $entityManager->merge($itemColor);
            }
            $entityManager->flush();
            $this->addFlash('success', 'Boja/e uspješno dodana/e.');
            return $this->redirectToRoute('item_edit', ['id'=>$item->getId()]);
        }
        return $this->render('item/set_color_quantity.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/color/{id}/quantity", name="item_color_quantity_edit", methods={"GET", "POST"})
     */
    public function editColorQuantity(Request $request, ItemColorRepository $itemColorRepository): Response
    {
        $itemColor = $itemColorRepository->findOneBy(['id'=>$request->get('id')]);
        $form = $this->createForm(EditColorQuantityType::class, $itemColor);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $itemColor->getColor()->getName().' boja - količina uspješno ažurirana.');
            return $this->redirectToRoute('item_edit', ['id'=>$itemColor->getItem()->getId()]);
        }
        return $this->render('item/edit_color_quantity.html.twig', [
            'form'=>$form->createView(),
            'itemColor'=>$itemColor
        ]);
    }

    /**
     * @Route("/{id}/delete/color", name="item_color_delete", methods={"DELETE"})
     */
    public function deleteColor(Request $request, ItemColor $itemColor, ItemColorRepository $itemColorRepository): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$itemColor->getId(), $request->request->get('_token'))) {
            $itemId = $itemColor->getItem()->getId();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($itemColor);
            $this->addFlash('success', 'Boja uspješno izbrisana.');
            $entityManager->flush();
        }
        return $this->redirectToRoute('item_edit', ['id'=>$itemId]);
    }


    /**
     * @Route("/{id}/add/size", name="item_add_size", methods={"GET","POST"})
     */
    public function addSize(Request $request, ItemRepository $itemRepository): Response
    {
        $item = $itemRepository->findOneBy(['id'=>$request->get('id')]);
        $sizes = $item->getItemSizes();
        $sizeValues = [];
        foreach($sizes as $size) {
            array_push($sizeValues, $size->getSize()->getValue());
        }
        $form = $this->createForm(NewItemSizeType::class, null, ['size_values'=>$sizeValues]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $selectedSizes = $form->get('size')->getData();
            $session = new Session();
            $session->set('sizes', $selectedSizes);
            $session->set('item', $item);
            return $this->redirectToRoute('item_size_set_quantity');
        }
        return $this->render('item/new_size.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     *@Route("/set/new/size/quantity", name="item_size_set_quantity", methods={"GET", "POST"})
     */
    public function setNewSizeQuantity(Request $request, SizeRepository $sizeRepository): Response
    {
        $markedSizes = [];
        $sizeQuantities = [];
        $sizes = $this->get('session')->get('sizes');
        $item = $this->get('session')->get('item');
        $form = $this->createForm(SizeQuantityType::class, null, ['sizes'=>$sizes]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($sizes as $size) {
                array_push($markedSizes, $size->getId());
            }
            $formData = $form->getData();
            $formKeys = array_keys($formData);

            foreach ($formKeys as $key) {
                if (str_contains($key, "size")) {
                    array_push($sizeQuantities, $formData[$key]);
                }
            }
            $colorsDone = array_combine($markedSizes, $sizeQuantities);

            foreach ($colorsDone as $key => $value) {
                $sizeObj = $sizeRepository->findOneBy(['id' => $key]);
                $itemSize = new ItemSize();
                $itemSize->setItem($item);
                $itemSize->setSize($sizeObj);
                $itemSize->setQuantity($value);
                $entityManager->merge($itemSize);
            }
            $entityManager->flush();
            $this->addFlash('success', 'Veličina/e uspješno dodana/e.');
            return $this->redirectToRoute('item_edit', ['id'=>$item->getId()]);
        }
        return $this->render('item/set_size_quantity.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/size/{id}/quantity", name="item_size_quantity_edit", methods={"GET", "POST"})
     */
    public function editSizeQuantity(Request $request, ItemSizeRepository $itemSizeRepository)
    {
        $itemSize = $itemSizeRepository->findOneBy(['id'=>$request->get('id')]);
        $form = $this->createForm(EditSizeQuantityType::class, $itemSize);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $itemSize->getSize()->getValue().' veličina - količina uspješno ažurirana.');
            return $this->redirectToRoute('item_edit', ['id'=>$itemSize->getItem()->getId()]);
        }
        return $this->render('item/edit_size_quantity.html.twig', [
            'form'=>$form->createView(),
            'itemSize'=>$itemSize
        ]);
    }

    /**
     * @Route("/{id}/delete/size", name="item_size_delete", methods={"DELETE"})
     */
    public function deleteSize(Request $request, ItemSize $itemSize, ItemSizeRepository $itemSizeRepository): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$itemSize->getId(), $request->request->get('_token'))) {
            $itemId = $itemSize->getItem()->getId();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($itemSize);
            $this->addFlash('success', 'Veličina uspješno izbrisana.');
            $entityManager->flush();
        }
        return $this->redirectToRoute('item_edit', ['id'=>$itemId]);
    }

    /**
     * @Route("/{id}", name="item_delete", methods={"DELETE"})
     */
    public function deleteItem(Request $request, Item $item, ImageRepository $imageRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$item->getId(), $request->request->get('_token'))) {
            $images = $imageRepository->findBy(['item'=>$item]);
            if($images !== null) {
                foreach($images as $image) {
                    unlink('../public/uploads/'.$image->getPath());
                }
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($item);
            $this->addFlash('danger', 'Artikl "'.$item->getTitle().'" uspješno obrisan.');
            $entityManager->flush();
        }
        return $this->redirectToRoute('item_index');
    }

    /**
     * @Route("/{id}/details", name="item_details", methods={"GET", "POST"})
     */
    public function showItemdetails(Request $request, ItemRepository $itemRepository, ItemSizeRepository $itemSizeRepository,
                                    ItemColorRepository $itemColorRepository,
                                    UserRepository $userRepository, CartItemRepository $cartItemRepository): Response
    {
        $item = $itemRepository->findOneBy(['id'=>$request->get('id')]);
        $entityManager = $this->getDoctrine()->getManager();
        $sizeChoices = [];
        $colorChoices = [];
        foreach($item->getItemSizes() as $itemSize) {
            $sizeObject = $itemSize->getSize();
            $sizeValue = $itemSize->getSize()->getValue();
            $sizeChoices[$sizeValue]=$sizeObject;
        }
        foreach($item->getItemColors() as $itemColor) {
            $colorObject = $itemColor->getColor();
            $colorValue = $itemColor->getColor()->getName();
            $colorChoices[$colorValue]=$colorObject;
        }
        $cartItem = new CartItem();
        $formCart = $this->createForm(CartItemType::class, $cartItem, ['sizeChoices'=>$sizeChoices, 'colorChoices'=>$colorChoices]);
        $formCart->handleRequest($request);
        if ($formCart->isSubmitted() && $formCart->isValid()) {
            $formCartQuantity = $formCart->get('quantity')->getData();
            $cartItemSizeObj = $itemSizeRepository->findOneBy(['size' => $formCart->get('size')->getData()]);
            $cartItemColorObj = $itemColorRepository->findOneBy(['color' => $formCart->get('color')->getData()]);
            if ($formCartQuantity > $cartItemSizeObj->getQuantity()) {
                $this->addFlash('danger',
                    'Max. količina veličine "' . $cartItemSizeObj->getSize()->getValue() . '" za ovaj artikl je ' . $cartItemSizeObj->getQuantity() . ".");
                return $this->redirectToRoute('item_details', ['id' => $item->getId()]);
            } else if ($formCartQuantity > $cartItemColorObj->getQuantity()) {
                $this->addFlash('danger',
                    'Max. količina "' . $cartItemColorObj->getColor()->getName() . '" boje za ovaj artikl je ' . $cartItemColorObj->getQuantity() . ".");
                return $this->redirectToRoute('item_details', ['id' => $item->getId()]);
            } else {
                $user = $userRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
                if (is_null($user->getCart())) {
                    $cart = new Cart();
                    $cart->setUser($this->getUser());
                    $entityManager->persist($cart);
                    $entityManager->flush();
                } else {
                    $cart = $user->getCart();
                }
                $cartItemDb = $cartItemRepository->findOneBy(['cart' => $cart,
                    'item' => $item,
                    'size' => $formCart->get('size')->getData(),
                    'color' => $formCart->get('color')->getData()
                ]);
                if (is_null($cartItemDb)) {
                    $cartItem->setItem($item);
                    $entityManager->persist($cartItem);
                    $cart->addCartItem($cartItem);
                    $entityManager->persist($cart);
                    $entityManager->flush();
                    $this->addFlash('success', 'Artikl "' . $item->getTitle() . '" uspješno dodan u košaricu.');
                    return $this->redirectToRoute('cart_index');
                } else {
                    $previousQuantity = $cartItemDb->getQuantity();
                    if (($previousQuantity + $formCartQuantity) > $cartItemColorObj->getQuantity()) {
                        $this->addFlash('danger', 'Količina istog artikla u košarici i 
                        trenutno odabrana količina prelaze ukupnu količinu artikla za odabranu boju. 
                        Max = '.($cartItemColorObj->getQuantity()-$previousQuantity));
                    } else if (($previousQuantity + $formCartQuantity) > $cartItemSizeObj->getQuantity()) {
                        $this->addFlash('danger', 'Količina istog artikla u košarici i 
                        trenutno odabrana količina prelaze ukupnu količinu artikla za odabranu veličinu. 
                        Max = '.($cartItemSizeObj->getQuantity()-$previousQuantity));
                        return $this->redirectToRoute('item_details', ['id' => $item->getId()]);
                    } else {
                        $cartItemDb->setQuantity($previousQuantity + $formCartQuantity);
                        $entityManager->persist($cartItemDb);
                        $entityManager->flush();
                        $this->addFlash('success', 'Artikl "' . $item->getTitle() . '" uspješno dodan u košaricu.');
                        return $this->redirectToRoute('cart_index');
                    }
                }
            }
        }

        $review = new Review();
        $formReview = $this->createForm(ReviewType::class, $review);
        $formReview->handleRequest($request);

        if($formReview->isSubmitted() && $formReview->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $review->setUser($this->getUser());
            $review->setItem($item);
            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success', 'Recenzija uspješno dodana.');
            return $this->redirect($request->getUri());
//            $this->redirectToRoute('item_details', ['id'=>$item->getId()]);
        }

        return $this->render('item/item_details.html.twig', [
            'item' => $item,
            'formReview'=>$formReview->createView(),
            'formCart'=>$formCart->createView()
        ]);
    }
}
