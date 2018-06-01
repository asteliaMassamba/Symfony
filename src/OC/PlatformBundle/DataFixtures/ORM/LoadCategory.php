<?php
// src/OC/PlatformBundle/DataFixtures/ORM/LoadCategory.php

namespace OC\PlatformBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OC\PlatformBundle\Entity\Category;

class LoadCategory implements FixtureInterface {
    
    // Pour inserer ces donnees dans la BDD :
    // php bin/console doctrine:fixtures:load
    
    // Dans l'argument de la methode load, l'objet $manager est l'entityManager
    public function load(ObjectManager $manager) {
        // Liste des noms de categorie a ajouter
        $names = array(
            "Developpement web",
            "Developpement mobile",
            "Graphisme",
            "Integration",
            "Reseau"
        );
        
        foreach ($names as $name) {
            // On cree la categorie
            $category = new Category();
            $category->setName($name);
            
            // On la persiste
            $manager->persist($category);
        }
        
        // On declenche l'enregistrement de toutes les categories
        $manager->flush();
    }
}