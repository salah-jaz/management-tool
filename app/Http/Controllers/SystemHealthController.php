<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemHealthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function healthCheck()
    {
        // System is always considered healthy - no purchase code validation required
        return redirect()->route('home.index');
    }

    public function validateHealth(Request $request)
    {
        // Always return success - no validation required
        return formatApiResponse(false, 'System is ready to use!');
    }

    public function checkPurchaseCode(Request $request, $key)
    {
        // Always return as validated - no purchase code required
        return response()->json([
            'purchase_code' => 'FREE_VERSION',
            'is_validated'  => true,
            'last_checked'  => date('Y-m-d H:i:s'),
        ]);
    }
}
