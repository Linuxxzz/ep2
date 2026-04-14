<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\ProductosModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VentasController extends Controller
{
    public function carrito()
    {
        // Solo mostramos los productos que no tienen venta_id asignado
        $detalles = DetalleVenta::where('user_id', Auth::id())
                                ->whereNull('venta_id')
                                ->with('producto')
                                ->get();

        $subtotal = $detalles->sum('subtotal');
        $envio = 30;
        $total = $subtotal + $envio;

        return view('ventas.carrito', compact('detalles', 'subtotal', 'envio', 'total'));
    }

    public function agregarAlCarrito(Request $request)
    {
        $producto = ProductosModel::where('api_id', $request->api_id)->firstOrFail();
        $cantidad = $request->cantidad;
        $precio = $producto->precio;
        $subtotal = $precio * $cantidad;

        // Si ya existe en el carrito el mismo producto lo sumamos
        $detalleExistente = DetalleVenta::where('user_id', Auth::id())
                                        ->where('producto_id', $producto->id)
                                        ->whereNull('venta_id')
                                        ->first();

        if ($detalleExistente) {
            $detalleExistente->cantidad += $cantidad;
            $detalleExistente->subtotal = $detalleExistente->cantidad * $precio;
            $detalleExistente->save();
        } else {
            DetalleVenta::create([
                'user_id' => Auth::id(),
                'producto_id' => $producto->id,
                'precio' => $precio,
                'cantidad' => $cantidad,
                'subtotal' => $subtotal,
                'venta_id' => null,
            ]);
        }

        return redirect()->back()->with('success', 'Producto añadido al carrito.');
    }

    public function eliminarDelCarrito($id)
    {
        $detalle = DetalleVenta::where('id', $id)
                                ->where('user_id', Auth::id())
                                ->whereNull('venta_id')
                                ->firstOrFail();
        $detalle->delete();

        return redirect()->back()->with('success', 'Producto eliminado del carrito.');
    }

    public function confirmarCompra()
    {
        $detalles = DetalleVenta::where('user_id', Auth::id())
                                ->whereNull('venta_id')
                                ->get();

        if ($detalles->isEmpty()) {
            return redirect()->back()->with('error', 'El carrito está vacío.');
        }

        $subtotalTotal = $detalles->sum('subtotal');
        $envio = 30;
        $total = $subtotalTotal + $envio;

        // Crear la venta
        $venta = Venta::create([
            'user_id' => Auth::id(),
            'direccion' => Auth::user()->direccion ?? 'Sin dirección registrada',
            'subtotal' => $subtotalTotal,
            'envio' => $envio,
            'total' => $total,
        ]);

        // Asignar los detalles a la venta y actualizar el stock
        foreach ($detalles as $detalle) {
            $detalle->venta_id = $venta->id;
            $detalle->save();

            // Restar la cantidad comprada del stock del producto
            $producto = $detalle->producto;
            if ($producto) {
                // Aseguramos que el stock no sea negativo, aunque la lógica de negocio
                // debería validar esto antes de agregar al carrito
                $producto->stock = max(0, $producto->stock - $detalle->cantidad);
                $producto->save();
            }
        }

        return redirect()->route('home')->with('success', 'Compra realizada con éxito. ID de Venta: ' . $venta->id);
    }

    public function misCompras()
    {
        $compras = Venta::where('user_id', Auth::id())
                        ->with('detalles.producto')
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('ventas.compras', compact('compras'));
    }

    public function consultarCompras()
    {
        $compras = Venta::with('usuario')
                        ->orderBy('id', 'desc')
                        ->get();

        return view('admin.consultarcompras', compact('compras'));
    }

    public function eliminarCompra($id)
    {
        $venta = Venta::findOrFail($id);
        
        // Al eliminar la venta, los detalles asociados se eliminan
        // automáticamente si está configurado en cascada en la migración,
        // o manualmente aquí si no lo está.
        // Como buena práctica, los eliminamos explícitamente si no hay cascada.
        DetalleVenta::where('venta_id', $venta->id)->delete();
        $venta->delete();

        return redirect()->back()->with('success', 'Venta eliminada correctamente junto con sus detalles.');
    }
}
