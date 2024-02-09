<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class TestDatabaseController extends AbstractController
{
    /**
     * @Route("/test-database", name="test_database")
     */
    public function index(EntityManagerInterface $entityManager): Response
    {
        try {
            $connection = $entityManager->getConnection();
            $connection->connect();

            if ($connection->isConnected()) {
                return new Response('Conectado a la BBDD');
            }
        } catch (\Exception $e) {
            return new Response('No se ha podido conectar a la BBDD: ' . $e->getMessage());
        }

        return new Response('No se ha podido conectar a la BBDD');
    }
}
