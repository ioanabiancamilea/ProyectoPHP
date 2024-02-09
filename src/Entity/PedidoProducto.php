<?php
// src/Entity/PedidoProducto.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="pedidosproductos")
 */
class PedidoProducto {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="CodPedProd")
     */
    private $codPedProd;

    /**
     * @ORM\ManyToOne(targetEntity="Pedido")
     * @ORM\JoinColumn(name="Pedido", referencedColumnName="CodPed")
     */
    private $pedido;

    /**
     * @ORM\ManyToOne(targetEntity="Producto")
     * @ORM\JoinColumn(name="Producto", referencedColumnName="CodProd")
     */
    private $producto;

    /**
     * @ORM\Column(type="integer", name = "unidades")
     */
    private $unidades;

    // Getters and Setters
    public function getCodPedProd() {
        return $this->codPedProd;
    }

    public function getPedido() {
        return $this->pedido;
    }

    public function setPedido($pedido) {
        $this->pedido = $pedido;
    }

    public function getProducto() {
        return $this->producto;
    }

    public function setProducto($producto) {
        $this->producto = $producto;
    }

    public function getUnidades() {
        return $this->unidades;
    }

    public function setUnidades($unidades) {
        $this->unidades = $unidades;
    }
}