<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Get all clients.
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