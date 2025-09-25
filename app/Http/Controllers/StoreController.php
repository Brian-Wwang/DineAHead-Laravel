<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    /**
     * 创建门店（仅商户；建议一个商户只允许一个门店）
     */
    public function create(Request $request)
    {
        if ($request->user()->store) {
            return response()->json([
                'success' => false,
                'message' => 'Store already exists',
                'data'    => $request->user()->store,
            ], 409);
        }

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'contact'      => 'required|string|max:255',
            'email'        => 'nullable|email',
            'description'  => 'nullable|string',
            'address'      => 'required|string',
            'cover'        => 'required|url',
            'price_level'  => 'required|string',
            'time_start'   => 'required|date_format:H:i',
            'time_close'   => 'required|date_format:H:i',

            // 这里 province_id/city_id 实际上传的是 code
            'province_id'  => ['required', Rule::exists('locations','code')->where(fn($q) => $q->where('type','province'))],
            'city_id'      => ['required', Rule::exists('locations','code')->where(fn($q) => $q->where('type','district'))],

            'latitute'     => 'required|string',
            'longitute'    => 'required|string',

            'cuisine_ids'   => 'required|array|min:1',
            'cuisine_ids.*' => ['required','integer', Rule::exists('cuisines','id')->where(fn($q) => $q->where('is_active', true))],
        ]);

        // 额外：校验 city 是否隶属 province
        $provinceId = Location::where('type','province')
            ->where('code', $validated['province_id'])
            ->value('id');

        $cityBelongs = Location::where('type','district')
            ->where('code', $validated['city_id'])
            ->where('parent_id', $provinceId)
            ->exists();

        if (!$cityBelongs) {
            return response()->json([
                'success' => false,
                'message' => 'city_id(code) 不属于该 province_id(code)',
            ], 422);
        }

        $data = $request->only([
            'name','contact','email','description','address','cover',
            'time_start','time_close','price_level','latitute','longitute',
            'province_id','city_id',  // 直接存 code
        ]);
        $data['user_id'] = $request->user()->id;

        $store = DB::transaction(function () use ($data, $request) {
            $store = Store::create($data);
            $store->cuisines()->sync($request->input('cuisine_ids'));
            return $store;
        });

        return response()->json([
            'success' => true,
            'message' => 'Created Success',
            'data'    => $store->load(['province','city','cuisines']),
        ], 201);
    }

    /**
     * 获取当前商户的门店详情（通过 token 反查）
     */
    public function detail(Request $request)
    {
        $store = $request->user()->store()->with(['province', 'city', 'cuisines'])->first();

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $store,
        ]);
    }

    /**
     * 更新当前商户的门店（通过 token 反查）
     */
    public function update(Request $request)
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found',
            ], 404);
        }

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'contact'     => 'sometimes|string|max:255',
            'email'       => 'nullable|email',
            'description' => 'nullable|string',
            'address'     => 'sometimes|string',
            'cover'       => 'required|url',
            'time_start'  => 'sometimes|date_format:H:i',
            'time_close'  => 'sometimes|date_format:H:i',
            'price_level' => 'required|string',
            'province_id' => [
                'required',
                Rule::exists('locations','code')->where(fn($q) => $q->where('type','province')),
            ],
            'city_id' => [
                'required',
                Rule::exists('locations','code')->where(fn($q) => $q->where('type','district')),
            ],
            'latitute'    => 'required|string',
            'longitute'   => 'required|string',
            'cuisine_ids' => 'sometimes|array|min:1',
            'cuisine_ids.*' => [
                'required_with:cuisine_ids',
                'integer',
                Rule::exists('cuisines', 'id')->where(function ($query) {
                    $query->where('is_active', true);
                }),
            ],
        ]);

        $store->update($validated);

        if ($request->filled('cuisine_ids')) {
            $store->cuisines()->sync($request->input('cuisine_ids'));
        }

        return response()->json([
            'success' => true,
            'message' => 'Updated',
            'data' => $store->fresh()->load(['province', 'city', 'cuisines']),
        ]);
    }

    /**
     * 删除当前商户的门店（软删除）
     */
    public function delete(Request $request)
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found',
            ], 404);
        }

        $store->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deleted successfully',
        ]);
    }

    /**
     * 公共接口 - 仅获取 is_active = true 的门店，支持条件过滤
     */
    public function public(Request $request)
    {
        $query = Store::query()
            ->where('is_active', true)
            ->with(['province', 'city', 'cuisines'])
            ->orderByDesc('id');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->filled('price_level')) {
            $query->where('price_level', $request->price_level);
        }

        if ($request->filled('cuisine_ids')) {
            $query->whereHas('cuisines', function ($q) use ($request) {
                $q->whereIn('cuisines.id', $request->cuisine_ids);
            });
        }

        $stores = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $stores,
        ]);
    }

    /**
     * 管理后台接口 - 获取所有门店（不限制 is_active），支持筛选
     */
    public function list(Request $request)
    {
        $query = Store::query()
            ->with(['province', 'city', 'cuisines'])
            ->orderByDesc('id');

        $filterable = [
            'id', 'name', 'contact', 'email', 'address',
            'time_start', 'time_close', 'province_id',
            'city_id', 'price_level', 'user_id', 'is_active'
        ];

        foreach ($filterable as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->$field);
            }
        }

        if ($request->filled('cuisine_ids')) {
            $query->whereHas('cuisines', function ($q) use ($request) {
                $q->whereIn('cuisines.id', $request->cuisine_ids);
            });
        }

        $stores = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $stores,
        ]);
    }
}
