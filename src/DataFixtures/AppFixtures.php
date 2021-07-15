<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Color;
use App\Entity\Manufacturer;
use App\Entity\Size;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $languageCodes = ['hr', 'en'];

        $user = new User();
        $user->setName("Branimir");
        $user->setSurname("Butković");
        $user->setEmail("branimir@gmail.com");
        $user->setRoles(["ROLE_USER", "ROLE_ADMIN"]);
        $user->setPassword('$argon2id$v=19$m=65536,t=4,p=1$avj5HbJl56Te5US1YZiQAQ$T9qpVqy9QHEokQZya9zJLjpHsS0pqh8aqFRHZkKcMOI');
        $manager->persist($user);

        $categoriesHr = [
            "Muškarci",
            "Žene",
            "Djeca",
            "Sportska oprema",
            "Obuća",
            "Odjeća",
            "Tenisice",
            "Kopačke",
            "Patike za trčanje",
            "Čizme",
            "Dodaci za obuću",
            "Ulošci",
            "Vezice",
            "Impregnacijski sprejevi",
            "Majice",
            "Hlače",
            "Jakne",
            "Odjevni dodaci",
            "Kape",
            "Rukavice",
            "Šalovi",
            "Nogomet",
            "Rukomet",
            "Košarka",
            "Odbojka",
            "Tenis",
            "Stolni tenis",
            "Fitnes"
        ];

        $categoriesEn = [
            "Men",
            "Women",
            "Children",
            "Sports equipment",
            "Footwear",
            "Clothes",
            "Sneakers",
            "Football boots",
            "Running shoes",
            "Boots",
            "Footwear accessories",
            "Insoles",
            "Laces",
            "Impregnation sprays",
            "T-shirts",
            "Pants",
            "Jackets",
            "Clothing accessories",
            "Caps",
            "Gloves",
            "Scarfs",
            "Football",
            "Handball",
            "Basketball",
            "Volleyball",
            "Tennis",
            "Table tennis",
            "Fitness"
        ];

        $sizesClothes = [
            "XS",
            "S",
            "M",
            "L",
            "XL",
            "XXL",
            "XXXL"
        ];

        $sizesFootWear = [
            "30",
            "31",
            "32",
            "33",
            "34",
            "35",
            "36",
            "37",
            "38",
            "39",
            "40",
            "41",
            "42",
            "43",
            "44",
            "45",
            "46",
            "47",
        ];

        $sizesSport = [
            "0",
            "1",
            "2",
            "3",
            "4",
            "5",
            "6",
            "7",
            "N/D"
        ];

        $colorCodes = [
            "#7E4539",
            "#397E55",
            "#C4E4D1",
            "#DEC4E4",
            "#FF0000",
            "#0101DF",
            "#FFBF00",
            "#F781F3",
            "#084B8A",
            "#26A837",
            "#A5C423",
            "#000000",
            "#FFFFFF"
        ];

        $colorNamesHr = [
            "Smeđa",
            "Tamno zelena",
            "Svijetlo plava",
            "Ljubičasta",
            "Crvena",
            "Plava",
            "Žuta",
            "Roza",
            "Tamno plava",
            "Zelena",
            "Maslinasta",
            "Crna",
            "Bijela"
        ];

        $colorNamesEn = [
            "Brown",
            "Dark green",
            "Light blue",
            "Purple",
            "Red",
            "Blue",
            "Yellow",
            "Pink",
            "Dark blue",
            "Green",
            "Olive",
            "Black",
            "White"
        ];

        $manufacturers = [
          "Nike",
          "Adidas",
          "Umbro",
          "Vty",
          "Puma",
          "Rebook",
          "Fila",
          "Converse",
          "Levi's",
          "Lotto",
          "Crocs",
          "Carrera",
          "Diadora",
          "Hummel",
          "Lacoste",
          "Timberland",
          "Salomon",
          "Skechers",
          "Kipsta",
          "Kalenji",
          "KipRun",
          "Fouganza",
          "Solognac",
          "Kaperlan",
          "Artengo",
          "Quechua",
          "Forclaz",
          "Newfeel",
          "Sidas",
          "Select",
          "Tarmak",
          "Spalding",
          "Atorka",
          "Copaya",
          "Mikasa",
          "Wilson",
          "Pongori",
          "Nyamba",
          "Domyos",
        ];

        for($i = 0; $i <= 27; $i++) {
            $category = new Category();
            foreach($languageCodes as $language) {
                $category->setLocale($language);
                if($language == 'hr') {
                    $category->setName($categoriesHr[$i]);
                } else {
                    $category->setName($categoriesEn[$i]);
                }
                $manager->persist($category);
                $manager->flush();
            }
        }

        foreach($sizesClothes as $size) {
            $sizeObj = new Size();
            $sizeObj->setValue($size);
            $sizeObj->setType("Obuća");
            $manager->persist($sizeObj);
            $manager->flush();
        }

        foreach($sizesFootWear as $size) {
            $sizeObj = new Size();
            $sizeObj->setValue($size);
            $sizeObj->setType("Odjeća");
            $manager->persist($sizeObj);
            $manager->flush();
        }

        foreach($sizesSport as $size) {
            $sizeObj = new Size();
            $sizeObj->setValue($size);
            $sizeObj->setType("Sport");
            $manager->persist($sizeObj);
            $manager->flush();
        }

        $i = 0;
        foreach($colorCodes as $color) {
            $colorObj = new Color();
            $colorObj->setValue($color);
            foreach($languageCodes as $language) {
                $colorObj->setLocale($language);
                if($language == 'hr') {
                    $colorObj->setName($colorNamesHr[$i]);
                } else {
                    $colorObj->setName($colorNamesEn[$i]);
                }
                $manager->persist($colorObj);
                $manager->flush();
            }
            $i++;
        }

        foreach($manufacturers as $manufacturer) {
            $manufacturerObj = new Manufacturer();
            $manufacturerObj->setName($manufacturer);
            $manager->persist($manufacturerObj);
        }

        $manager->flush();
    }
}
