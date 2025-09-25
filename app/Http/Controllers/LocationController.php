<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class LocationController extends Controller
{
    /**
     * 获取地点列表（可按类型、省份过滤）
     * - type 可为 province / district
     * - parent_id：获取某个省下属的所有 district
     */
    public function list(Request $request)
    {
        try {
            $type = $request->query('type');         // 可选参数
            $parentId = $request->query('parent_id'); // 可选参数

            $query = Location::query();

            if ($type) $query->where('type', $type);
            if ($parentId) $query->where('parent_id', $parentId);

            $locations = $query->orderBy('id')->get();

            return response()->json([
                'success' => true,
                'data' => $locations
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * 创建地点（省或市/县）
     */
    public function create(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'type' => 'required|in:province,district',
                'parent_id' => 'nullable|exists:locations,id'
            ]);

            $location = Location::create($data);

            return response()->json(['success' => true, 'data' => $location]);
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

    /**
     * 更新地点信息
     */
    public function update(Request $request)
    {
        try {
            $data = $request->validate([
                'id' => 'required|exists:locations,id',
                'name' => 'sometimes|string',
                'type' => 'sometimes|in:province,district',
                'parent_id' => 'nullable|exists:locations,id'
            ]);

            $location = Location::findOrFail($data['id']);
            $location->update($data);

            return response()->json(['success' => true, 'data' => $location]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Location not found'
            ], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * 删除地点（软删）
     */
    public function delete(Request $request)
    {
        try {
            $request->validate(['id' => 'required|exists:locations,id']);

            $location = Location::findOrFail($request->id);
            $location->delete();

            return response()->json(['success' => true]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Location not found'
            ], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * 通用异常处理（含 trace）
     */
    protected function errorResponse(Throwable $e)
    {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'trace' => config('app.debug') ? $e->getTrace() : [],
        ], 500);
    }
}
