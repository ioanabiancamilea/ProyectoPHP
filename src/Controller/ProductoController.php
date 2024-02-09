<?php

// src/Controller/ProductoController.php

namespace App\Controller;

use App\Entity\Producto;
use App\Entity\Categoria;
use App\Form\ProductoType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class ProductoController extends AbstractController
{
    /**
     * Muestra los productos de una categoría específica.
     * 
     * @Route("/productos/{id}", name="productos")
     */
    public function productosPorCategoria(ManagerRegistry $doctrine, $id): Response
    {
        $categoria = $doctrine->getRepository(Categoria::class)->find($id);

        if (!$categoria) {
            throw $this->createNotFoundException('No se encontró la categoría solicitada.');
        }

        $productos = $categoria->getProductos();

        return $this->render('productos.html.twig', [
            'productos' => $productos,
        ]);
    }

    /**
     * Maneja la adición de un nuevo producto y la edición de uno existente.
     * 
     * @Route("/añadir-producto", name="añadir_producto")
     * @Route("/editar-producto/{codProd}", name="editar_producto", requirements={"codProd"="\d+"})
     */
    public function gestionProducto(Request $request, ManagerRegistry $doctrine, ?int $codProd = null): Response
    {
        $entityManager = $doctrine->getManager();

        if ($codProd) {
            $producto = $entityManager->getRepository(Producto::class)->find($codProd);
            if (!$producto) {
                throw $this->createNotFoundException('No se encontró el producto solicitado.');
            }
        } else {
            $producto = new Producto();
        }

        $form = $this->createForm(ProductoType::class, $producto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($producto);
            $entityManager->flush();

            // Redirecciona a la lista de productos de la categoría del producto añadido o editado.
            return $this->redirectToRoute('productos', ['id' => $producto->getCategoria()->getCodCat()]);
        }

        // Renderiza la plantilla adecuada para añadir o editar productos.
        // Asegúrate de tener una plantilla 'añadir_producto.html.twig' o cambia el nombre según corresponda.
        return $this->render('añadir_producto.html.twig', [
            'form' => $form->createView(),
            'editMode' => $codProd !== null
        ]);
    }
}