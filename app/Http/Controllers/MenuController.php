<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MenuService;

class MenuController extends Controller
{
  public function __construct(protected MenuService $menuService) {}

  /**
   * 公共菜单，按分类分组
   */
  public function groupMenus(Request $request) {
    $validated = $request->validate([
      'store_id' => 'required|integer|exists:stores,id',
    ]);

    $menu = $this->menuService->getPublicMenuGroups($validated['store_id']);

    return api_response($menu);
  }

  /**
   * 商家菜单列表
   */
  public function list(Request $request) {
    $storeId = $request->user()->store->id;

    $menus = $this->menuService->getStoreMenus($storeId);

    return api_response($menus);
  }


  /**
   * 商家菜单列表
   */
  public function public(Request $request) {
    $storeId = $request->user()->store->id;

    $menus = $this->menuService->getPublicMenus($storeId);

    return api_response($menus);
  }

  /**
   * 创建菜单
   */
  public function create(Request $request) {
    $validated = $request->validate([
        'name'            => 'required|string|max:255',
        'description'     => 'nullable|string',
        'price'           => 'required|decimal:0,2|gt:0',
        'image'           => 'nullable|url',
        'discount_type'   => 'nullable|integer|in:0,10,20',
        'discount_amount' => 'nullable|decimal:0,2|min:0',
        'options' => 'nullable|array',
        'options.*.platform_option_id' => 'nullable|exists:platform_options,id',
        'options.*.store_option_id'    => 'nullable|exists:store_options,id',
        'options.*.is_required'        => 'boolean',
        'options.*.max_select'         => 'integer|min:1',
    ]);

    $validated['store_id'] = $request->user()->store->id;

    $this->menuService->createMenu($validated);

    return api_response();
  }

  /**
   * 更新菜单
   */
  public function update(Request $request)
  {
      $validated = $request->validate([
          'id'              => 'required|integer|exists:menus,id',
          'name'            => 'sometimes|string|max:255',
          'description'     => 'nullable|string',
          'price'           => 'sometimes|decimal:0,2|gt:0',
          'image'           => 'nullable|url',
          'discount_type'   => 'nullable|integer|in:0,10,20,30',
          'discount_value'  => 'nullable|decimal:0,2|min:0',
          'is_active'       => 'boolean',

          'category_ids'    => 'nullable|array',
          'category_ids.*'  => 'integer|exists:categories,id',

          // ✅ 新增 options 校验
          'options'                         => 'nullable|array',
          'options.*.platform_option_id'    => 'nullable|exists:platform_options,id',
          'options.*.store_option_id'       => 'nullable|exists:store_options,id',
          'options.*.is_required'           => 'boolean',
          'options.*.max_select'            => 'integer|min:1',
      ]);

      $storeId = $request->user()->store->id;

      $menu = $this->menuService->updateMenu($validated['id'], $storeId, $validated);

      return api_response($menu, 'Menu updated successfully');
  }


  /**
   * 删除菜单
   */
  public function delete(Request $request)
  {
      $validated = $request->validate([
          'id' => 'required|integer|exists:menus,id',
      ]);

      $this->menuService->deleteMenu($validated['id'], $request->user()->store->id);

      return api_response(null, 'Menu deleted successfully');
  }
}
