<?php
// src/Controller/CategoriaController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Categoria;

class CategoriaController extends AbstractController
{
    /**
     * @Route("/categorias", name="categorias")
     */
    public function index(): Response
    {
        // Recuperar todas las categorías de la base de datos
        $categorias = $this->getDoctrine()->getRepository(Categoria::class)->findAll();

        // Renderizar la plantilla y pasar las categorías recuperadas
        return $this->render('categorias.html.twig', [
            'categorias' => $categorias,
        ]);
    }
}
