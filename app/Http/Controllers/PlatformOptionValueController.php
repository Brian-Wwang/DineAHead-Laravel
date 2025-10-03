<?php

namespace App\Http\Controllers;

use App\Models\PlatformOption;
use App\Models\PlatformOptionValue;
use Illuminate\Http\Request;

class PlatformOptionValueController extends Controller
{
    /**
     * 获取某个平台选项的所有值 (只能 store_id = null)
     */
    public function public(Request $request)
    {
        $validated = $request->validate([
            'option_id' => 'required|integer|exists:platform_options,id',
        ]);

        $option = PlatformOption::where('id', $validated['option_id'])
            ->whereNull('store_id')          // ✅ 限制只能平台级
            ->where('is_active', true)
            ->firstOrFail();

        $values = PlatformOptionValue::where('platform_option_id', $option->id)
            ->orderBy('sort_order', 'asc')
            ->get();

        return api_response($values);
    }

    /**
     * 创建平台选项值 (只能 store_id = null)
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'option_id'   => 'required|integer|exists:platform_options,id',
            'name'        => 'required|string|max:50',
            'extra_price' => 'nullable|numeric|min:0',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'boolean'
        ]);

        $option = PlatformOption::where('id', $validated['option_id'])
            ->whereNull('store_id')          // ✅ 限制只能平台级
            ->firstOrFail();

        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = PlatformOptionValue::where('platform_option_id', $option->id)->max('sort_order') + 1;
        }

        $validated['platform_option_id'] = $option->id;
        unset($validated['option_id']);

        $value = PlatformOptionValue::create($validated);

        return api_response($value);
    }

    /**
     * 更新平台选项值 (只能 store_id = null)
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'id'          => 'required|integer|exists:platform_option_values,id',
            'name'        => 'required|string|max:50',
            'extra_price' => 'nullable|numeric|min:0',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'boolean'
        ]);

        $value = PlatformOptionValue::findOrFail($validated['id']);

        // ✅ 校验父级 option 是否是平台级
        if ($value->option->store_id !== null) {
            return api_response(null, 'Unauthorized: only platform-level options allowed', 403, false);
        }

        $value->update($validated);

        return api_response($value);
    }

    /**
     * 删除平台选项值 (只能 store_id = null)
     */
    public function delete(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:platform_option_values,id',
        ]);

        $value = PlatformOptionValue::findOrFail($validated['id']);

        if ($value->option->store_id !== null) {
            return api_response(null, 'Unauthorized: only platform-level options allowed', 403, false);
        }

        $value->delete();

        return api_response();
    }
}
