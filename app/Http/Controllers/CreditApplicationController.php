<?php

namespace App\Http\Controllers;

use App\Models\CreditApplication;
use App\Models\Phone;
use Illuminate\Http\Request;

class CreditApplicationController extends Controller
{
    
    /**
     * Show the form for creating a new credit application.
     */
    public function create()
    {
        return view('credit-applications.create');
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
            'amount' => 'required|numeric|min:0',
            'term' => 'required|integer|min:1',
            'monthly_interest_rate' => 'required|numeric|min:0',
        ]);

        //Then we check if the client has an approved or pending credit application
        $activeCreditFound = CreditApplication::where('client_id', $validated['client_id'])
                                                ->whereIn('state', ['approved', 'pending'])->first();

        //If the client has an approved or pending credit application, we abort and return an error 
        if($activeCreditFound){
            return response()->json([
                'message' => 'El cliente ya tiene una solicitud de crédito '
                . $activeCreditFound->state === 'approved' ? 'aprobada' : 'pendiente',
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

        // dd($validated);
        $application = CreditApplication::create($validated);


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