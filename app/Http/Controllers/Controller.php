<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Documentación API FinanciaCel",
 *     description="Documentación de la API para el sistema de solicitudes de crédito FinanciaCel",
 *     @OA\Contact(
 *         email="admin@financiacel.com",
 *         name="Soporte FinanciaCel"
 *     )
 * )
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API FinanciaCel"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
