<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function merchantOnly(Request $request)
    {
        return response()->json([
            'success' => true,
            'msg' => '✅ 只有 merchant 可以访问',
            'user' => $request->user()
        ]);
    }

    public function adminOrMerchant(Request $request)
    {
        return response()->json([
            'success' => true,
            'msg' => '✅ admin 和 merchant 都能访问',
            'user' => $request->user()
        ]);
    }

    public function anyLoggedIn(Request $request)
    {
        return response()->json([
            'success' => true,
            'msg' => '✅ 所有已登录用户都能访问',
            'user' => $request->user()
        ]);
    }
}
