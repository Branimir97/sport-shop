<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Color;
use App\Entity\Language;
use App\Entity\Manufacturer;
use App\Entity\Size;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        $user = new User();
        $user->setName("Branimir");
        $user->setSurname("Butković");
        $user->setEmail("branimir@gmail.com");
        $user->setRoles(["ROLE_USER", "ROLE_ADMIN"]);
        $user->setBirthDate(new \DateTime("1997-10-27"));
        $user->setGender("Muški");
        $user->setPassword('$argon2id$v=19$m=65536,t=4,p=1$avj5HbJl56Te5US1YZiQAQ$T9qpVqy9QHEokQZya9zJLjpHsS0pqh8aqFRHZkKcMOI');

        $manager->persist($user);
        $languageCodes = ['hr', 'en'];
        foreach($languageCodes as $languageCode) {
            $language = new Language();
            $language->setCode($languageCode);
            $manager->persist($language);
            $manager->flush();
        }
        $categories = [
            "Muškarci",
            "Žene",
            "Djeca",
            "Sportska oprema",
            "Ostalo",
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
            "#A5C423"
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
            "Maslinasta"
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
            "Olive"
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
          "Skechers"
        ];

        foreach($categories as $categoryName) {
            $category = new Category();
            $category->setName($categoryName);
            $manager->persist($category);
        }

        foreach($sizesClothes as $size) {
            $sizeObj = new Size();
            $sizeObj->setValue($size);
            foreach($languageCodes as $language) {
                $sizeObj->setLocale($language);
                if($language == 'hr') {
                    $sizeObj->setType("Odjeća");
                } else {
                    $sizeObj->setType("Clothes");
                }
                $manager->persist($sizeObj);
                $manager->flush();
            }
        }

        foreach($sizesFootWear as $size) {
            $sizeObj = new Size();
            $sizeObj->setValue($size);
            foreach($languageCodes as $language) {
                $sizeObj->setLocale($language);
                if($language == 'hr') {
                    $sizeObj->setType("Obuća");
                } else {
                    $sizeObj->setType("Footwear");
                }
                $manager->persist($sizeObj);
                $manager->flush();
            }
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
