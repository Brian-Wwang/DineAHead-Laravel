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
  public function public() {
    $list = Cuisine::query()
      ->where('is_active', true)   // ✅ 只要启用的
      ->whereNull('deleted_at')    // ✅ 只要没被软删除的
      ->orderBy('id', 'desc')
      ->get();
    return api_response($list);
  }

  // 🟡 获取所有数据（后台）
  public function list() {
    $list = Cuisine::whereNull('deleted_at')->orderBy('id', 'desc')->get();
    return api_response($list);
  }

  // 🟢 创建
  public function create(Request $request) {
    $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'nullable|string',
      'is_active' => 'boolean',
    ]);

    $user = Auth::user();

    $cuisine = new Cuisine();
    $cuisine->name = $request->name;
    $cuisine->description = $request->description;
    $cuisine->is_active = $request->is_active ?? true;
    $cuisine->created_by = $user->id;
    $cuisine->created_by_name = $user->name;
    $cuisine->save();

    return api_response();
  }

  // 🟠 更新
  public function update(Request $request) {
    $request->validate([
      'id'          => 'required|exists:cuisines,id',
      'name'        => 'required|string|max:255',
      'description' => 'nullable|string',
      'is_active'   => 'boolean',
    ]);

    $user = Auth::user();

    // ✅ 只能找到未软删除的记录
    $cuisine = Cuisine::where('id', $request->id)
        ->whereNull('deleted_at')
        ->firstOrFail();

    $cuisine->name           = $request->name;
    $cuisine->description    = $request->description;
    $cuisine->is_active      = $request->is_active ?? true;
    $cuisine->updated_by     = $user->id;
    $cuisine->updated_by_name = $user->name;
    $cuisine->save();

    return api_response();
  }

  // 🔴 删除（软删）
  public function delete(Request $request)
  {
    $request->validate([
      'id' => 'required|exists:cuisines,id',
    ]);

    $cuisine = Cuisine::findOrFail($request->id);
    $cuisine->delete();
    return api_response();
  }
}
