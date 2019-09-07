<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;
use App\Client;
use App\ClientCap;

class ClientController extends Controller
{
    public function get() {
        $Client = new Client;
        return response()->json(['success' => true, 'clients' => $Client::get()], 200);
    }

    public function put(Request $request, $id) {
        $Client = Client::find($id);
        $active = $request->input('active');
    
        if (empty($Client)){
            return response()->json([
                'success' => false,
                'error' => 'invalid client id'
            ], 422);
        }
        
        $Client->update(['active' => $active]);
    
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $id,
                'active' => $Client->active
            ]
        ], 200);
    }
}
