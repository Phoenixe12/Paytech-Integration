<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Notification;

class PaytechController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('paytech');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'API_KEY' => env('PAYTECH_API_KEY'),
            'API_SECRET' => env('PAYTECH_API_SECRET'),
            'Accept' => 'application/json',
        ];

        try {
            $response = Http::withHeaders($headers)
                ->post('https://paytech.sn/api/payment/request-payment', $request->all());

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json()
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => [
                    'message' => 'Erreur de paiement',
                    'status' => $response->status(),
                    'data' => $response->json()
                ]
            ], $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'status' => 500,
                    'data' => null
                ]
            ], 500);
        }
    }



    public function paymentSuccess(Request $request)
    {

        // Redirection vers la vue avec les données compactées

        return view('success');
    }

    public function paymentFailed(Request $request)
    {
        // Gestion des erreurs si la commande n'a pas été confirmée

        return view('cancel');
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
