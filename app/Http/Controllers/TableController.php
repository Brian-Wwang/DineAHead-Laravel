<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class TableController extends Controller
{
    public function list(Request $request)
    {
        try {
            $storeId = $request->user()->store->id;

            $tables = Table::where('store_id', $storeId)
                ->whereNull('deleted_at')
                ->get()
                ->append('status_label');

            return response()->json([
                'success' => true,
                'data'    => $tables
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function public(Request $request)
    {
        try {
            $request->validate([
                'store_id' => 'required|integer|exists:stores,id'
            ]);

            $tables = Table::where('store_id', $request->store_id)
                ->visible()
                ->whereNull('deleted_at')
                ->get()
                ->append('status_label');

            return response()->json([
                'success' => true,
                'data'    => $tables
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function create(Request $request)
    {
        try {
            $data = $request->validate([
                'name'        => 'required|string|max:255',
                'description' => 'nullable|string',
                'images'      => 'nullable|array',
                'images.*'    => 'url'
            ]);

            $data['store_id'] = $request->user()->store->id;

            $table = Table::create($data)->append('status_label');

            return response()->json([
                'success' => true,
                'data'    => $table
            ], 201);
        } catch (ValidationException $e) {
            return $this->validationError($e);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'id'          => 'required|integer|exists:tables,id',
                'name'        => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'images'      => 'nullable|array',
                'images.*'    => 'url',
                'is_active'   => 'boolean'
            ]);

            $table = Table::where('id', $request->id)
                ->where('store_id', $request->user()->store->id)
                ->firstOrFail();

            $table->update($request->only(['name','description','images','is_active']));

            return response()->json([
                'success' => true,
                'data'    => $table->fresh()->append('status_label')
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Table not found'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function delete(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:tables,id',
            ]);

            $table = Table::where('id', $request->id)
                ->where('store_id', $request->user()->store->id)
                ->firstOrFail();

            $table->delete();

            return response()->json([
                'success' => true,
                'message' => 'Deleted successfully'
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Table not found'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $request->validate([
                'id'     => 'required|integer|exists:tables,id',
                'status' => 'required|integer|in:0,1,2,3'
            ]);

            $table = Table::where('id', $request->id)
                ->where('store_id', $request->user()->store->id)
                ->firstOrFail();

            $table->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'data'    => $table->fresh()->append('status_label')
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Table not found'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * 统一处理验证失败
     */
    protected function validationError(ValidationException $e)
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $e->errors(),
        ], 422);
    }

    /**
     * 统一处理服务器错误
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
