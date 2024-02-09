<?php
// src/Controller/CarritoController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ProductoRepository;

class CarritoController extends AbstractController
{

    public function mostrarCarrito(SessionInterface $session, ProductoRepository $productoRepository): Response
    {
        // Suponiendo que en la sesión guardas los IDs de los productos en el carrito
        $carrito = $session->get('carrito', []);

        $productosEnCarrito = [];

        foreach ($carrito as $idProducto => $cantidad) {
            $producto = $productoRepository->find($idProducto);
            if ($producto) {
                $productosEnCarrito[] = [
                    'codProd' => $producto->getCodProd(),
                    'nombre' => $producto->getNombre(),
                    'descripcion' => $producto->getDescripcion(),
                    'peso' => $producto->getPeso(),
                    'stock' => $producto->getStock(),
                    'unidades' => $cantidad,
                ];
            }
        }

        return $this->render('carrito.html.twig', [
            'productos' => $productosEnCarrito,
        ]);
    }
}
?>
