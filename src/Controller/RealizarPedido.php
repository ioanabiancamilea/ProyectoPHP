<?php
// src/Controller/RealizarPedidoController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ProductoRepository;
use App\Entity\Pedido;
use App\Entity\Restaurante;
use App\Entity\PedidoProducto;
use App\Entity\Producto; // <-- Añade esta línea
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;


class RealizarPedido extends AbstractController
{
    /**
     * @Route("/realizarPedido", name="realizarPedido")
     */
    public function realizarPedido(SessionInterface $session, ProductoRepository $productoRepository, EntityManagerInterface $em): Response
{
    $carrito = $session->get('carrito', []);
    if (empty($carrito)) {
        $this->addFlash('error', 'El carrito está vacío.');
        return $this->redirectToRoute('mostrar_carrito');
    }

    $pedido = new Pedido();

    // Obtiene el restaurante autenticado actualmente
    $restaurante = $this->getUser();
    
    // Asegúrate de que el usuario autenticado es una instancia de Restaurante
    if (!$restaurante instanceof Restaurante) {
        // Lanza una excepción o maneja este caso como mejor te parezca
        throw new \LogicException('El usuario actual no es un restaurante.');
    }
    
    // No es necesario obtener CodRes para setear el restaurante al pedido,
    // a menos que necesites CodRes para otra lógica específica aquí.
    // $codRes = $restaurante->getCodRes();
    
    $pedido->setRestaurante($restaurante);
    $pedido->setFecha(new \DateTime());
    $pedido->setEnviado(false);
    
    // Lógica para asociar productos al pedido...
    // Asumiendo que tienes una lista de productos en $carrito
    foreach ($carrito as $idProducto => $cantidad) {
        $producto = $em->getRepository(Producto::class)->find($idProducto);
        if ($producto) {
            $pedidoProducto = new PedidoProducto();
            $pedidoProducto->setPedido($pedido);
            $pedidoProducto->setProducto($producto);
            $pedidoProducto->setUnidades($cantidad);
            $em->persist($pedidoProducto);
        }
    }
    
    $em->persist($pedido);
    try {
        $em->flush();
        // Puedes agregar lógica adicional aquí si el flush es exitoso
        $this->addFlash('success', 'Pedido realizado con éxito.');
    } catch (\Exception $e) {
        // Manejo de excepción si algo falla durante el flush
        $this->addFlash('error', 'Hubo un problema al procesar tu pedido.');
    }
    
    // Redirecciona al usuario a la página que desees después de crear el pedido
    $session->set('carrito', []); // Limpiar el carrito después de realizar el pedido
    return $this->redirectToRoute('pedido.html.twig');
    }
}