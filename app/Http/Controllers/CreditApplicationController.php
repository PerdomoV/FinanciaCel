<?php

namespace App\Http\Controllers;

use App\Models\CreditApplication;
use App\Models\Phone;
use App\Models\Installment;
use Illuminate\Http\Request;
use App\Http\Controllers\PhoneController;

/**
 * @OA\Tag(
 *     name="Aplicaciones de crédito",
 *     description="Endpoints de la API para la gestión de solicitudes de crédito"
 * )
 */
class CreditApplicationController extends Controller
{
    
    /**
     * Show the form for creating a new credit application.
     */
    public function create()
    {
        return view('credit-applications.create');
    }

    public function amortization($price, $rate, $term){

        $i = $rate/100;

        //valor cuota = capital * (1 + rate/100 * plazo) / plazo
        $installmentAmount = $price * (1 + $i * $term ) / $term;

        $totalIntereses = 0;
        $totalCuotas = 0;
        $totalPagado = 0;

        for($k = 1; $k <= $term; $k++){

            if($k == 1){
                $saldoInicial = (float)$price;
            }else{
                $saldoInicial = $saldoInicial - $installmentAmount;
            }

            $totalIntereses += $saldoInicial * $i;
            $totalCuotas += $installmentAmount;
            $totalPagado += $installmentAmount;

            $amortizationTable[] = [
                'periodo' => $k,
                'saldo_inicial' => $saldoInicial,
                'valor_cuota' => $installmentAmount,
                'valor_interes' => $saldoInicial * $i,
                'saldo_capital' => $saldoInicial - ($installmentAmount - $saldoInicial * $i),
            ];
        }

        $amortizationData = [
            'valor_credito' => (float)$price,
            'tasa_interes' => $rate,
            'plazo' => $term,
            'tabla_amortizacion' => $amortizationTable,
            'total_intereses' => $totalIntereses,
            'total_cuotas' => $totalCuotas,
            'total_pagado' => $totalPagado,
        ];

        return $amortizationData;
    }

    /**
     * Simulate a credit application
     * 
     * @OA\Post(
     *     path="/api/credits/simulate",
     *     tags={"Aplicaciones de crédito"},
     *     summary="Simular una solicitud de crédito",
     *     description="Simula una solicitud de crédito con los parámetros proporcionados y devuelve la tabla de amortización",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"client_id", "phone_id", "term", "monthly_interest_rate"},
     *             @OA\Property(property="client_id", type="integer", example=1),
     *             @OA\Property(property="phone_id", type="integer", example=1),
     *             @OA\Property(property="term", type="integer", example=12),
     *             @OA\Property(property="monthly_interest_rate", type="number", format="float", example=2.5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Simulación exitosa",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Simulación de crédito realizada exitosamente"),
     *             @OA\Property(
     *                 property="amortizationData",
     *                 type="object",
     *                 @OA\Property(property="valor_credito", type="number", format="float", example=799.99),
     *                 @OA\Property(property="tasa_interes", type="number", format="float", example=2.5),
     *                 @OA\Property(property="plazo", type="integer", example=12),
     *                 @OA\Property(
     *                     property="tabla_amortizacion",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="periodo", type="integer", example=1),
     *                         @OA\Property(property="saldo_inicial", type="number", format="float", example=799.99),
     *                         @OA\Property(property="valor_cuota", type="number", format="float", example=73.33),
     *                         @OA\Property(property="valor_interes", type="number", format="float", example=20.00),
     *                         @OA\Property(property="saldo_capital", type="number", format="float", example=746.66)
     *                     )
     *                 ),
     *                 @OA\Property(property="total_intereses", type="number", format="float", example=240.00),
     *                 @OA\Property(property="total_cuotas", type="number", format="float", example=880.00),
     *                 @OA\Property(property="total_pagado", type="number", format="float", example=880.00)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Solicitud incorrecta",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="El cliente ya tiene una solicitud de crédito pendiente")
     *         )
     *     )
     * )
     */
    public function simulate(Request $request){
        
        //First we validate the request body fields
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'phone_id' => 'required|exists:phones,id',
            'term' => 'required|integer|min:1',
            'monthly_interest_rate' => 'required|numeric|min:0',
        ]);

        //Then we check if the client has an approved or pending credit application
        $activeCreditFound = CreditApplication::where('client_id', $validated['client_id'])
                                                ->whereIn('state', ['approved', 'pending'])->first();

        //If the client has an approved or pending credit application, we abort and return an error 
        if($activeCreditFound){
            return response()->json([
                'message' => 'El cliente ya tiene una solicitud de crédito ' . ($activeCreditFound->state === 'approved' ? 'aprobada' : 'pendiente'),
            ], 400);
        }

        //Then we check if the phone is available
        $phone = Phone::find($validated['phone_id']);

        //If the phone is not available, we abort and return an error
        if($phone->stock <= 0){
            return response()->json([
                'message' => 'El teléfono solicitado no está disponible por el momento',
            ], 400);
        }
        
        $amortizationData = $this->amortization($phone['price'], $validated['monthly_interest_rate'], $validated['term']);


        return response()->json([
            'message' => 'Simulación de crédito realizada exitosamente',
            'amortizationData' => $amortizationData
        ], 201);
    }

    /**
     * Store a newly created credit application
     * 
     * @OA\Post(
     *     path="/api/credits",
     *     tags={"Aplicaciones de crédito"},
     *     summary="Crear una nueva solicitud de crédito",
     *     description="Crea una nueva solicitud de crédito con los parámetros proporcionados",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"client_id", "phone_id", "term", "monthly_interest_rate"},
     *             @OA\Property(property="client_id", type="integer", example=1),
     *             @OA\Property(property="phone_id", type="integer", example=1),
     *             @OA\Property(property="term", type="integer", example=12),
     *             @OA\Property(property="monthly_interest_rate", type="number", format="float", example=2.5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Solicitud de crédito creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Aplicación de crédito creada exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="client_id", type="integer", example=1),
     *                 @OA\Property(property="phone_id", type="integer", example=1),
     *                 @OA\Property(property="term", type="integer", example=12),
     *                 @OA\Property(property="monthly_interest_rate", type="number", format="float", example=2.5),
     *                 @OA\Property(property="amount", type="number", format="float", example=799.99),
     *                 @OA\Property(property="state", type="string", example="pending")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Solicitud incorrecta",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="El cliente ya tiene una solicitud de crédito pendiente")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        //First we validate the request body fields
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'phone_id' => 'required|exists:phones,id',
            'term' => 'required|integer|min:1',
            'monthly_interest_rate' => 'required|numeric|min:0',
        ]);

        //Then we check if the client has an approved or pending credit application
        $activeCreditFound = CreditApplication::where('client_id', $validated['client_id'])
                                                ->whereIn('state', ['approved', 'pending'])->first();

        //If the client has an approved or pending credit application, we abort and return an error 
        if($activeCreditFound){
            return response()->json([
                'message' => 'El cliente ya tiene una solicitud de crédito ' . ($activeCreditFound->state === 'approved' ? 'aprobada' : 'pendiente'),
            ], 400);
        }

        //Then we check if the phone is available
        $phone = Phone::find($validated['phone_id']);

        //If the phone is not available, we abort and return an error
        if($phone->stock <= 0){
            return response()->json([
                'message' => 'El teléfono solicitado no está disponible por el momento',
            ], 400);
        }
        
        //Then we create the credit application
        $validated['state'] = 'pending';
        $validated['amount'] = $phone->price;

        $application = CreditApplication::create($validated);

        PhoneController::updatePhoneStock($validated['phone_id'], 1);

        //Then we create the installments
        //valor cuota = capital * (1 + rate/100 * plazo) / plazo
        $installmentAmount = $validated['amount'] * (1 + $validated['monthly_interest_rate']/100 * $validated['term']) / $validated['term'];

        $installment = [
            'application_id' => $application->id,
            'quantity' => $validated['term'],
            'amount' => $installmentAmount
        ];

        $installments = Installment::create($installment);


        return response()->json([
            'message' => 'Aplicación de crédito creada exitosamente',
            'data' => $application
        ], 201);
    }

    /**
     * Get the status of a credit application
     * 
     * @OA\Get(
     *     path="/api/credits/{id}/status",
     *     tags={"Aplicaciones de crédito"},
     *     summary="Obtener estado de solicitud de crédito",
     *     description="Devuelve el estado de una solicitud de crédito específica",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la solicitud de crédito",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Estado de la solicitud de crédito obtenido exitosamente"),
     *             @OA\Property(property="estado", type="string", example="pending")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Solicitud de crédito no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No se encontró la solicitud de crédito")
     *         )
     *     )
     * )
     */
    public function getCreditStatus($id)
    {
       $credit = CreditApplication::where('id', $id)->first();

       if(!$credit){
        return response()->json([
            'message' => 'No se encontró la solicitud de crédito',
        ], 404);
       }

       return response()->json([
        'message' => 'Estado de la solicitud de crédito obtenido exitosamente',
        'estado' => $credit->state 
       ], 200);
    }

    /**
     * Get the installments of a credit application
     * 
     * @OA\Get(
     *     path="/api/credits/{id}/installments",
     *     tags={"Aplicaciones de crédito"},
     *     summary="Obtener cuotas de solicitud de crédito",
     *     description="Devuelve las cuotas de una solicitud de crédito específica",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la solicitud de crédito",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cuotas del crédito obtenidas exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="application_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=12),
     *                     @OA\Property(property="amount", type="number", format="float", example=73.33)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Solicitud de crédito no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No se encontró la solicitud de crédito")
     *         )
     *     )
     * )
     */
    public function indexInstallments($id)
    {
        $credit = CreditApplication::where('id', $id)->first();

        if(!$credit){
            return response()->json([
                'message' => 'No se encontró la solicitud de crédito',
            ], 404);
        }

        return response()->json([
            'message' => 'Cuotas del crédito obtenidas exitosamente',
            'data' => $credit->installments
        ], 200);
    }


    /**
     * Display the specified credit application.
     */
    public function show(CreditApplication $creditApplication)
    {
        $creditApplication->load(['client', 'phone', 'installments']);
        return view('credit-applications.show', ['application' => $creditApplication]);
    }

   
} 