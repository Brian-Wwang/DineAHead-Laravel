<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function store(Request $request) {
        $data = $request->validate([
            'token' => 'required|string',
            'platform' => 'nullable|string',
            'device_id' => 'nullable|string',
        ]);

        DeviceToken::updateOrCreate(
            ['token'=>$data['token']],
            ['user_id'=>$request->user()->id, 'platform'=>$data['platform'] ?? null, 'device_id'=>$data['device_id'] ?? null]
        );

        return response()->json(['success'=>true]);
    }

    public function destroy(Request $request) {
        $data = $request->validate(['token'=>'required|string']);
        DeviceToken::where('token',$data['token'])->delete();
        return response()->json(['success'=>true]);
    }
}
