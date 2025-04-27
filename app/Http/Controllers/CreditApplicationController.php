<?php

namespace App\Http\Controllers;

use App\Models\CreditApplication;
use App\Models\Phone;
use App\Models\Installment;
use Illuminate\Http\Request;
use App\Http\Controllers\PhoneController;


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
                $saldoInicial = $price;
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
            'valor_credito' => $price,
            'tasa_interes' => $rate,
            'plazo' => $term,
            'tabla_amortizacion' => $amortizationTable,
            'total_intereses' => $totalIntereses,
            'total_cuotas' => $totalCuotas,
            'total_pagado' => $totalPagado,
        ];

        return $amortizationData;
    }

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
     * Store a newly created credit application in storage.
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
     * Get the status of a credit application.
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
     * Get the installments of a credit application.
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