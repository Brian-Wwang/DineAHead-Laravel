<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class MenuService
{
  /**
   * 获取公共菜单，按分类分组
   */
  public function getPublicMenuGroups(int $storeId) {
    // 1. 获取正常分类 + 分类下的启用菜单
    $categories = Category::where('store_id', $storeId)
        ->where('is_active', true)
        ->with(['menus' => function ($q) {
            $q->where('is_active', true)
              ->orderBy('id', 'asc');
        }])
        ->orderBy('id', 'asc')
        ->get()
        ->toArray();

    // 2. 获取没有分类的菜单
    $menusWithoutCategory = Menu::where('store_id', $storeId)
        ->where('is_active', true)
        ->whereDoesntHave('categories') // 没有关联分类
        ->orderBy('id', 'asc')
        ->get()
        ->toArray();

    // 3. 如果有无分类菜单，拼接一个 "未分类" 分类返回
    if (!empty($menusWithoutCategory)) {
        $categories[] = [
            'id' => null,
            'name' => '未分类',
            'menus' => $menusWithoutCategory,
        ];
    }

    return $categories;
  }


  /**
   * 获取商家菜单列表
   */
  public function getStoreMenus(int $storeId) {
    return Menu::where('store_id', $storeId)
      ->orderBy('id', 'desc')
      ->with('categories:id,name')
      ->get();
  }


  /**
   * 获取公共商家菜单列表
   */
  public function getPublicMenus(int $storeId) {
    return Menu::where('store_id', $storeId)
      ->orderBy('id', 'desc')
      ->where('is_active', true)
      ->with('categories:id,name')
      ->get();
  }

  /**
   * 创建菜单
   */
  public function createMenu(array $data) {
    return DB::transaction(function () use ($data) {
        // 1. 创建菜单
        $menu = Menu::create($data);

        // 2. 分类绑定
        if (!empty($data['category_ids'])) {
            $menu->categories()->attach($data['category_ids']);
        }

        // 3. 选项绑定（platform / store option）
        if (!empty($data['options'])) {
            foreach ($data['options'] as $opt) {
                $menu->options()->create([
                    'platform_option_id' => $opt['platform_option_id'] ?? null,
                    'store_option_id'    => $opt['store_option_id'] ?? null,
                    'is_required'        => $opt['is_required'] ?? false,
                    'max_select'         => $opt['max_select'] ?? 1,
                ]);
            }
        }

        // 4. 预加载分类和选项数据
        return $menu->load([
            'categories:id,name',
            'options.platformOption.values',
            'options.storeOption.values',
        ]);
    });
  }


  public function updateMenu(int $id, int $storeId, array $data) {
    return DB::transaction(function () use ($id, $storeId, $data) {
        $menu = Menu::where('id', $id)
            ->where('store_id', $storeId)
            ->firstOrFail();

        // 1. 更新菜单本身
        $menu->update($data);

        // 2. 更新分类
        if (isset($data['category_ids'])) {
            $menu->categories()->sync($data['category_ids']);
        }

        // 3. 更新选项
        if (isset($data['options'])) {
            // 先清空再写入（保持和 attachOptions 一致）
            $menu->options()->delete();

            foreach ($data['options'] as $opt) {
                $menu->options()->create([
                    'platform_option_id' => $opt['platform_option_id'] ?? null,
                    'store_option_id'    => $opt['store_option_id'] ?? null,
                    'is_required'        => $opt['is_required'] ?? false,
                    'max_select'         => $opt['max_select'] ?? 1,
                ]);
            }
        }

        // 4. 返回最新数据
        return $menu->load([
            'categories:id,name',
            'options.platformOption.values',
            'options.storeOption.values',
        ]);
    });
}


  /**
   * 删除菜单
   */
  public function deleteMenu(int $id, int $storeId)
  {
      $menu = Menu::where('id', $id)
          ->where('store_id', $storeId)
          ->firstOrFail();

      $menu->delete();
      return true;
  }
}
