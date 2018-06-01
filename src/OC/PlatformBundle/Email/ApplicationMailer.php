<?php
// src/OC/PlatformBundle/Email/ApplicationMailer.php

namespace OC\PlatformBundle\Email;

use OC\PlatformBundle\Entity\Application;

class ApplicationMailer {
    
    /**
     * @var \Swift_Mailer Message
     */
    private $mailer;
    
    public function __construct(\Swift_Mailer $mailer) {
        $this->mailer = $mailer;
    }
    
    public function sendNewNotification(Application $application) {
        $message = new \Swift_Mailer(
           "Nouvelle candidature",
           "Vous avez reçu une nouvelle candidature."
        );
        
        $message
           ->addTo($application->getAdvert()->getAuthor())
           // Ici bien sûr il faudrait un attribut "email", on utilise "author" à la place
           ->addFrom("admin@votresite.com");
        
        $this->mailer->send($message);
    }
}