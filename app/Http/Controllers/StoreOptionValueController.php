<?php

namespace App\Http\Controllers;

use App\Models\StoreOption;
use App\Models\StoreOptionValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreOptionValueController extends Controller
{
  public function public(Request $request) {
      $user = Auth::user();
      $storeId = $user->store->id ?? null;
      if (!$storeId) {
          return api_response(null, 'Please Create Store', 403, false);
      }

      $validated = $request->validate([
        'option_id' => 'required|integer|exists:store_options,id',
      ]);

      // 校验 option 是否属于当前店铺
      $option = StoreOption::where('id', $validated['option_id'])
          ->where('store_id', $storeId)
          ->where('is_active', true)
          ->firstOrFail();

      $values = StoreOptionValue::where('store_option_id', $option->id)
          ->orderBy('sort_order', 'asc')
          ->get();

      return api_response($values);
  }

  public function create(Request $request)
  {
      $user = Auth::user();
      $storeId = $user->store->id ?? null;
      if (!$storeId) {
          return api_response(null, 'Please Create Store', 403, false);
      }

      $validated = $request->validate([
          'option_id'   => 'required|integer|exists:store_options,id',
          'name'        => 'required|string|max:50',
          'extra_price' => 'nullable|numeric|min:0',
          'sort_order'  => 'nullable|integer',
          'is_active'   => 'boolean'
      ]);

      $option = StoreOption::where('id', $validated['option_id'])
          ->where('store_id', $storeId)
          ->firstOrFail();

      if (!isset($validated['sort_order'])) {
          $validated['sort_order'] = StoreOptionValue::where('store_option_id', $option->id)->max('sort_order') + 1;
      }

      $validated['store_option_id'] = $option->id;
      unset($validated['option_id']); // 避免重复字段

      $value = StoreOptionValue::create($validated);

      return api_response($value);
  }

  public function update(Request $request)
  {
      $user = Auth::user();
      $storeId = $user->store->id ?? null;
      if (!$storeId) {
          return api_response(null, 'Please Create Store', 403, false);
      }

      $validated = $request->validate([
          'id'          => 'required|integer|exists:store_option_values,id',
          'name'        => 'required|string|max:50',
          'extra_price' => 'nullable|numeric|min:0',
          'sort_order'  => 'nullable|integer',
          'is_active'   => 'boolean'
      ]);

      $value = StoreOptionValue::findOrFail($validated['id']);
      if ($value->option->store_id !== $storeId) {
          return api_response(null, 'Unauthorized', 403, false);
      }

      $value->update($validated);

      return api_response($value);
  }

  public function delete(Request $request)
  {
      $user = Auth::user();
      $storeId = $user->store->id ?? null;
      if (!$storeId) {
          return api_response(null, 'Please Create Store', 403, false);
      }

      $validated = $request->validate([
          'id' => 'required|integer|exists:store_option_values,id',
      ]);

      $value = StoreOptionValue::findOrFail($validated['id']);
      if ($value->option->store_id !== $storeId) {
          return api_response(null, 'Unauthorized', 403, false);
      }

      $value->delete();

      return api_response();
  }
}
