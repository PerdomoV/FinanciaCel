<?php

namespace App\Http\Controllers;

use App\Models\Phone;
use Illuminate\Http\JsonResponse;

class PhoneController extends Controller
{
    /**
     * List all phones
     */
    public function index(): JsonResponse
    {
        $phones = Phone::all();
        
        return response()->json([
            'data' => $phones,
            'message' => 'Phones retrieved successfully'
        ]);
    }
} 