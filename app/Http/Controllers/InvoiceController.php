<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function createInvoice(Request $request){
        $validatedData = $request->validate([
            'email' => 'required|string|email',
            'amount' => 'required|numeric|min:0',
         ]);

        $email = $validatedData['email'];
        $amount = $validatedData['amount'];

        $metadata = "igromir pay email: $email";

        $httpClient = new Client();
        

        try {
            $response = $httpClient->post('https://admin.vanilapay.com/api/v2/invoices', [
                'json' => [
                    'order_id' => Str::uuid(),
                    'amount' => $amount,
                    'payment_methods' => ['card','sbp','sbp-a']
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . env('API_KEY_TOKEN_INVOICE'),
                    'Meta-Data' => $metadata
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            
            if (!isset($data['payment_url'])) {
                return response()->json(['error' => 'Не удалось создать инвойс'], 500);
            }
            
            return response()->json(['payment_url' => $data['payment_url']]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка API: ' . $e->getMessage()], 500);
        }
    }
}
