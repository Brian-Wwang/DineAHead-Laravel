<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cuisine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class CuisineController extends Controller
{
    // 🟢 Public List：只获取 is_active 为 true 的数据
    public function public()
    {
        try {
            $list = Cuisine::where('is_active', true)->orderBy('id', 'desc')->get();
            return response()->json(['success' => true, 'data' => $list]);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    // 🟡 获取所有数据（后台）
    public function list()
    {
        try {
            $list = Cuisine::orderBy('id', 'desc')->get();
            return response()->json(['success' => true, 'data' => $list]);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    // 🟢 创建
    public function create(Request $request)
    {
      try {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $user = Auth::user();

        $cuisine = new Cuisine();
        $cuisine->name = $request->name;
        $cuisine->description = $request->description;
        $cuisine->is_active = $request->is_active ?? true;
        $cuisine->created_by = $user->id;
        $cuisine->created_by_name = $user->name;
        $cuisine->save();

        return response()->json(['success' => true, 'data' => $cuisine]);
      } catch (ValidationException $e) {
          return response()->json([
              'success' => false,
              'message' => 'Validation failed',
              'errors' => $e->errors()
          ], 422);
      } catch (Throwable $e) {
        return $this->errorResponse($e);
      }
    }

    // 🟠 更新
    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:cuisines,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $user = Auth::user();
            $cuisine = Cuisine::findOrFail($request->id);

            $cuisine->name = $request->name;
            $cuisine->description = $request->description;
            $cuisine->is_active = $request->is_active ?? true;
            $cuisine->updated_by = $user->id;
            $cuisine->updated_by_name = $user->name;
            $cuisine->save();

            return response()->json(['success' => true, 'data' => $cuisine]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Cuisine not found'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    // 🔴 删除（软删）
    public function delete(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:cuisines,id',
            ]);

            $cuisine = Cuisine::findOrFail($request->id);

            $cuisine->is_active = false;
            $cuisine->save();

            return response()->json(['success' => true, 'message' => 'Marked as inactive']);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Cuisine not found'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * 统一异常处理格式
     */
    protected function errorResponse(Throwable $e)
    {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'trace'   => config('app.debug') ? $e->getTrace() : [],
        ], 500);
    }
}
