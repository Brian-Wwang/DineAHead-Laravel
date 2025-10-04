<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    // -------------------------
    // 系统端
    // -------------------------
    public function pList() {
        return Category::query()
            ->whereNull('store_id')   // ✅ 平台分类
            ->orderBy('id', 'desc')
            ->get();
    }

    public function pPublic() {
        return Category::query()
            ->whereNull('store_id')
            ->where('is_active', true)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function pCreate(array $data): void {
      $this->validateBusinessRules($data);

      DB::transaction(function () use ($data) {
        $data['is_active']      = $data['is_active'] ?? true;
        $data['discount_type']  = $data['discount_type'] ?? 0;
        $data['discount_value'] = $data['discount_value'] ?? 0;

        if (Auth::check()) {
            $data['created_by']      = Auth::id();
            $data['created_by_name'] = Auth::user()->name ?? 'system';
        }

        $category = Category::create($data);

        if (!empty($data['menu_ids'])) {
            $category->menus()->attach($data['menu_ids']);
        }
      });
    }

    public function pUpdate(array $data): void {
        $category = Category::findOrFail($data['id']);
        $merged   = array_merge($category->toArray(), $data);

        $this->validateBusinessRules($merged);

        DB::transaction(function () use ($data, $category) {
            if (Auth::check()) {
                $data['updated_by']      = Auth::id();
                $data['updated_by_name'] = Auth::user()->name ?? 'system';
            }

            $category->update($data);
        });
    }

    public function pDelete(int $id): void {
        DB::transaction(function () use ($id) {
            $category = Category::findOrFail($id);

            if (Auth::check()) {
                $category->updated_by      = Auth::id();
                $category->updated_by_name = Auth::user()->name ?? 'system';
            }
            $category->save();
            $category->delete();
        });
    }

    // -------------------------
    // 商户端
    // -------------------------
    public function sList() {
      $storeId = Auth::user()->store->id;

      return Category::query()
          ->where('store_id', $storeId)
          ->orderBy('id', 'desc')
          ->get();
    }

    // 用于显示在绑定菜单和分类
    public function sPublic() {
      $storeId = Auth::user()->store->id;

      return Category::query()
          ->where('store_id', $storeId)
          ->where('is_active', true)
          ->orderBy('id', 'desc')
          ->get();
    }

    public function sCreate(array $data): void {
      $storeId = Auth::user()->store->id;

      $this->validateBusinessRules($data);

      DB::transaction(function () use ($data, $storeId) {
          $data['store_id']       = $storeId;
          $data['is_active']      = $data['is_active'] ?? true;
          $data['discount_type']  = $data['discount_type'] ?? 0;
          $data['discount_value'] = $data['discount_value'] ?? 0;

          $data['created_by']      = Auth::id();
          $data['created_by_name'] = Auth::user()->name ?? 'merchant';

          Category::create($data);
      });
    }

    public function sUpdate(array $data): void {
        $storeId  = Auth::user()->store->id;
        $category = Category::where('id', $data['id'])
            ->where('store_id', $storeId)
            ->firstOrFail();

        $merged = array_merge($category->toArray(), $data);
        $this->validateBusinessRules($merged);

        DB::transaction(function () use ($data, $category) {
            $data['updated_by']      = Auth::id();
            $data['updated_by_name'] = Auth::user()->name ?? 'merchant';

            $category->update($data);
        });
    }

    public function sDelete(int $id): void {
        $storeId = Auth::user()->store->id;

        DB::transaction(function () use ($id, $storeId) {
            $category = Category::where('id', $id)
                ->where('store_id', $storeId)
                ->firstOrFail();

            $category->is_active       = false;
            $category->updated_by      = Auth::id();
            $category->updated_by_name = Auth::user()->name ?? 'merchant';
            $category->save();
        });
    }

    public function bindMenus(int $categoryId, array $menuIds)
    {
        $category = Category::findOrFail($categoryId);
        $category->menus()->syncWithoutDetaching($menuIds);

        return $category->load('menus:id,name,price'); // 绑定操作可以返回数据，方便前端确认
    }
    // -------------------------
    // 公共业务校验
    // -------------------------
    private function validateBusinessRules(array $data): void
    {
        $type          = $data['type'] ?? null;
        $discountType  = $data['discount_type'] ?? null;
        $discountValue = $data['discount_value'] ?? null;

        if ($type == 10) {
            if ($discountType != 0) {
                throw ValidationException::withMessages([
                    'discount_type' => '当 type=10 时，discount_type 必须为 0',
                ]);
            }
            if (!empty($discountValue)) {
                throw ValidationException::withMessages([
                    'discount_value' => '当 type=10 时，discount_value 必须为 0',
                ]);
            }
        }

        if ($type == 20) {
            if (!in_array($discountType, [10, 20, 30])) {
                throw ValidationException::withMessages([
                    'discount_type' => 'type=20 ,scount_type must 10, 20, 30',
                ]);
            }
            if (empty($discountValue) || $discountValue <= 0) {
                throw ValidationException::withMessages([
                    'discount_value' => 'type=20, discount_value must greater than 0',
                ]);
            }
        }
    }
}
