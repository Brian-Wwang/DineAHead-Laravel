<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PreUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // 发送验证码
    public function sendVerifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $type = $request->header('type');
        if (!in_array($type, ['admin', 'user', 'merchant'])) {
            return api_response(null, 'Invalid type', 422, false);
        }

        $code = rand(100000, 999999); // 6位验证码

        PreUser::updateOrCreate(
            ['email' => $request->email, 'type' => $type],
            [
                'verify_code' => $code,
                'expired_at' => now()->addMinutes(5)
            ]
        );

        // TODO: 邮件发送逻辑
        // Mail::to($request->email)->send(new VerificationCodeMail($code));

        return api_response(['verify_code' => $code], 'Verification code sent');
    }

    // 用户注册
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'verify_code' => 'required|string',
            'avatar' => 'nullable|url'
        ]);

        $type = $request->header('type');
        if (!in_array($type, ['admin', 'user', 'merchant'])) {
            return api_response(null, 'Invalid type', 422, false);
        }

        $preUser = PreUser::where('email', $request->email)
            ->where('type', $type)
            ->where('verify_code', $request->verify_code)
            ->where('expired_at', '>', now())
            ->first();

        if (!$preUser) {
            return api_response(null, 'Invalid or expired verification code', 422, false);
        }

        if (User::where('email', $request->email)->where('type', $type)->exists()) {
            return api_response(null, 'Email already registered with this type', 409, false);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'type' => $type,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
            'avatar' => $request->avatar,
        ]);

        $preUser->delete();

        return api_response([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'type' => $user->type,
            'avatar' => $user->avatar,
        ]);
    }

    // 登录
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $type = $request->header('type');
        if (!in_array($type, ['admin', 'user', 'merchant'])) {
            return api_response(null, 'Invalid type', 422, false);
        }

        $user = User::where('email', $request->email)
            ->where('type', $type)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return api_response(null, 'Incorrect email or password', 401, false);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $refreshToken = base64_encode(Str::random(40)); // TODO: 可保存 refresh_token 到表中

        return api_response([
            'user' => $user,
            'token' => $token,
            'refresh_token' => $refreshToken,
        ], 'Login success');
    }

    // 忘记密码：重置
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'verify_code' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $type = $request->header('type');
        if (!in_array($type, ['admin', 'user', 'merchant'])) {
            return api_response(null, 'Invalid type', 422, false);
        }

        $preUser = PreUser::where('email', $request->email)
            ->where('type', $type)
            ->where('verify_code', $request->verify_code)
            ->where('expired_at', '>', now())
            ->first();

            error_log('PreUser: ' . json_encode($preUser));

        if (!$preUser) {
            return api_response(null, 'Invalid or expired verification code', 422, false);
        }

        $user = User::where('email', $request->email)->where('type', $type)->first();
        if (!$user) {
            return api_response(null, 'User not found', 404, false);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        $preUser->delete();

        return api_response(null, 'Password reset success');
    }

    // 修改密码（登录后）
    public function change_password(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();
        if (!$user) {
            return api_response(null, 'Unauthorized', 401, false);
        }

        if (!Hash::check($request->old_password, $user->password)) {
            return api_response(null, 'Old password is incorrect', 403, false);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return api_response(null, 'Password changed successfully');
    }
}
