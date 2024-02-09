<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\Categoria;
use App\Entity\Producto;
use App\Entity\Pedido;
use App\Entity\PedidoProducto;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted; // Esta línea estaba faltando



class PedidosBase extends AbstractController {
    /**
     * @Route("/categorias", name="categorias")
     */
    public function mostrarCategorias() {
        $categorias = $this->getDoctrine()->getRepository(Categoria::class)->findAll();
        return $this->render("categorias.html.twig", ['categorias' => $categorias]);
    }

    /**
     * @Route("/productos/{id}", name="productos")
     */
    public function mostrarProductos($id) {
        $productos = $this->getDoctrine()->getRepository(Categoria::class)->find($id)->getProductos();
        if (!$productos) {
            throw $this->createNotFoundException('Categoría no encontrada');
        }
        return $this->render("productos.html.twig", ['productos' => $productos]);
    }

    /**
     * @Route("/realizarPedido", name="realizarPedido")
     */
    public function realizarPedido(SessionInterface $session, MailerInterface $mailer, EntityManagerInterface $entityManager) {
        $carrito = $session->get('carrito', []);
        if (empty($carrito)) {
            return $this->render("pedido.html.twig", ['error' => 1]);
        }

        $pedido = new Pedido();
        $pedido->setFecha(new \DateTime());
        $pedido->setEnviado(false);
        $pedido->setRestaurante($this->getUser());
        $entityManager->persist($pedido);

        foreach ($carrito as $codigo => $cantidad) {
            $producto = $entityManager->getRepository(Producto::class)->find($codigo);
            if (!$producto) {
                continue;
            }

            $fila = new PedidoProducto();
            $fila->setProducto($producto);
            $fila->setUnidades($cantidad);
            $fila->setPedido($pedido);
            $entityManager->persist($fila);

            $producto->setStock($producto->getStock() - $cantidad);
            $entityManager->persist($producto);
        }

        try {
            $entityManager->flush();
        } catch (\Exception $e) {
            return $this->render("pedido.html.twig", ['error' => 2]);
        }

        $productos = [];
        foreach ($carrito as $codigo => $cantidad) {
            $producto = $entityManager->getRepository(Producto::class)->find($codigo);
            if ($producto) {
                $productos[] = [
                    'codProd' => $producto->getCodProd(), // Corregido de getId() a getCodProd()
                    'nombre' => $producto->getNombre(),
                    'peso' => $producto->getPeso(),
                    'stock' => $producto->getStock(),
                    'descripcion' => $producto->getDescripcion(),
                    'unidades' => $cantidad,
                ];
            }
        }

        $session->set('carrito', []);

        $email = (new Email())
        ->from('noreply@empresafalsa.com')
        ->to($this->getUser()->getEmail())
        // Asegúrate de cambiar getId() por getCodPed() para obtener el ID del pedido.
        ->subject("Pedido " . $pedido->getCodPed() . " confirmado")
        ->html($this->renderView(
            'correo.html.twig',
            [
                'id' => $pedido->getCodPed(),
                'productos' => $productos
            ]
        ));
    
    $mailer->send($email);
    

      // Correcto, usando getCodPed() que está definido en la entidad Pedido
return $this->render("pedido.html.twig", ['error' => 0, 'id' => $pedido->getCodPed(), 'productos' => $productos]);

    }

    /**
     * @Route("/carrito", name="carrito")
     */
    public function mostrarCarrito(SessionInterface $session) {
        $carrito = $session->get('carrito', []);
        $productos = [];

        foreach ($carrito as $codigo => $cantidad) {
            $producto = $this->getDoctrine()->getRepository(Producto::class)->find($codigo);
            if ($producto) {
                $productos[] = [
                    'codProd' => $producto->getCodProd(), // Cambiado de getId() a getCodProd()
                    'nombre' => $producto->getNombre(),
                    'peso' => $producto->getPeso(),
                    'stock' => $producto->getStock(),
                    'descripcion' => $producto->getDescripcion(),
                    'unidades' => $cantidad,
                ];
            }
        }
        

        return $this->render("carrito.html.twig", ['productos' => $productos]);
    }

    /**
     * @Route("/anadir", name="anadir", methods={"POST"})
     */
    public function anadir(SessionInterface $session, Request $request) {
        $id = $request->request->get('cod');
        $unidadesToAdd = intval($request->request->get('unidades'));
        $carrito = $session->get('carrito', []);
        
        // Aseguramos que el valor de unidades en el carrito sea siempre un entero.
        if (isset($carrito[$id])) {
            // Si existe, simplemente sumamos las unidades.
            $carrito[$id] += $unidadesToAdd;
        } else {
            // Si no existe, lo añadimos al carrito.
            $carrito[$id] = $unidadesToAdd;
        }
        
        // Asegurarse de que no estamos almacenando un valor negativo o cero.
        if ($carrito[$id] <= 0) {
            unset($carrito[$id]);
        } else {
            // Guardamos el carrito actualizado en la sesión.
            $session->set('carrito', $carrito);
        }
        
        return $this->redirectToRoute('carrito');
    }
    
    /**
     * @Route("/eliminar", name="eliminar", methods={"POST"})
     */
    public function eliminar(SessionInterface $session, Request $request) {
        $id = $request->request->get('cod');
        $unidades = $request->request->get('unidades');
        $carrito = $session->get('carrito', []);
    
        if (isset($carrito[$id]['unidades'])) {
            $carrito[$id]['unidades'] -= intval($unidades);
            if ($carrito[$id]['unidades'] <= 0) {
                unset($carrito[$id]);
            }
        }
    
        $session->set('carrito', $carrito);
        return $this->redirectToRoute('carrito');
    }
    
}