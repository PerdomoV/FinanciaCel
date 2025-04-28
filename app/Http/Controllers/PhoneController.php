<?php

namespace App\Http\Controllers;

use App\Models\Phone;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Telefonos",
 *     description="Endpoints de la API para gestión de teléfonos"
 * )
 */
class PhoneController extends Controller
{
    /**
     * List all phones
     * 
     * @OA\Get(
     *     path="/api/phones",
     *     tags={"Telefonos"},
     *     summary="Obtener lista de todos los teléfonos",
     *     description="Devuelve la lista de todos los teléfonos disponibles",
     *     @OA\Response(
     *         response=200,
     *         description="Operación exitosa",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="model", type="string", example="iPhone 13"),
     *                     @OA\Property(property="price", type="number", format="float", example=799.99),
     *                     @OA\Property(property="stock", type="integer", example=10)
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Teléfonos obtenidos exitosamente")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $phones = Phone::all();
        
        return response()->json([
            'data' => $phones,
            'message' => 'Phones retrieved successfully'
        ]);
    }

    /**
     * Update phone stock
     * 
     * @param int $phoneId
     * @param int $quantity
     */
    public static function updatePhoneStock($phoneId, $quantity)
    {
        $phone = Phone::find($phoneId);
        $phone->stock -= $quantity;
        $phone->save();
    }
} 
