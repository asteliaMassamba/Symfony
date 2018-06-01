<?php
// src/OC/PlatformBundle/DataFixtures/ORM/LoadSkill.php

namespace OC\PlatformBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OC\PlatformBundle\Entity\Skill;

class LoadSkill implements FixtureInterface {
    
    // Pour inserer ces donnees dans la BDD :
    // php bin/console doctrine:fixtures:load
    
    public function load(ObjectManager $manager) {
        //Liste des noms de competences a ajouter
        $names = array("PHP", "Symfony", "C++", "Java", "Photoshop", "Blender", "Bloc-note");
        
        foreach ($names as $name) {
            // On cree la competence
            $skill = new Skill();
            $skill->setName($name);
            
            // On la persiste
            $manager->persist($skill);
        }
        
        // On declenche l'enregistrement de toutes les categoris
        $manager->flush();
    }
}
