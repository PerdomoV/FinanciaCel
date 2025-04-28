<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Clientes",
 *     description="Endpoints de la API para gestión de clientes"
 * )
 */
class ClientController extends Controller
{
    /**
     * Get all clients.
     * 
     * @OA\Get(
     *     path="/api/clients",
     *     tags={"Clientes"},
     *     summary="Obtener lista de todos los clientes",
     *     description="Devuelve la lista de todos los clientes con su información",
     *     @OA\Response(
     *         response=200,
     *         description="Operación exitosa",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Clientes obtenidos exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="cc", type="string", example="1234567890")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $clients = Client::all(['id', 'name', 'cc']);
        
        return response()->json([
            'message' => 'Clientes obtenidos exitosamente',
            'data' => $clients
        ]);
    }
} 