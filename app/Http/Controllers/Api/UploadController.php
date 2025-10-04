<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|string',
        ]);

        $base64File = $request->input('file');

        // 支持 data:image/png;base64,xxxx 或纯 base64
        if (preg_match('/^data:(.*?);base64,(.*)$/', $base64File, $matches)) {
            $mimeType = $matches[1];
            $base64Data = $matches[2];
        } else {
            $mimeType = 'application/octet-stream';
            $base64Data = $base64File;
        }

        $data = base64_decode($base64Data);

        if ($data === false) {
            return response()->json(['success' => false, 'message' => 'Invalid base64 data'], 422);
        }

        // 文件大小限制（5MB）
        if (strlen($data) > 20 * 1024 * 1024) {
            return response()->json(['success' => false, 'message' => 'File too large'], 413);
        }

        // 安全扩展名映射
        $allowedMimeTypes = [
            'image/jpeg' => 'jpeg',
            'image/jpg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
        ];
        $ext = $allowedMimeTypes[$mimeType] ?? 'bin';

        $fileName = Str::random(20) . '.' . $ext;
        $path = 'uploads/' . date('Y/m/d');
        $fullPath = $path . '/' . $fileName;

        // 保存文件
        Storage::disk('public')->put($fullPath, $data);

        // 使用 Storage::url() 获取相对路径
        $relativeUrl = Storage::url($fullPath);

        // 通过 request() 自动拼接域名和协议
        $absoluteUrl = $request->getSchemeAndHttpHost() . $relativeUrl;

        return response()->json([
            'success' => true,
            'url' => $absoluteUrl,
        ]);
    }
}
