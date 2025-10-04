<?php

namespace App\Services;

use App\Models\PlatformOption;
use App\Models\PlatformOptionValue;
use App\Models\StoreOption;
use App\Models\StoreOptionValue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Enums\OptionType;

class OptionService
{
  // 系统
  public function pList() {
    return PlatformOption::query()
      ->orderBy('id', 'desc')
      ->get();
  }

  public function pPublic() {
    return PlatformOption::query()
      ->where('is_active', true)
      ->orderBy('id', 'desc')
      ->get();
  }

  public function pTree(): array {
    $options = PlatformOption::where('is_active', true)
        ->orderBy('id', 'desc')
        ->with(['values' => function ($q) {
            $q->orderByRaw('COALESCE(sort_order, id) ASC');
        }])
        ->get();

    return $options->map(function ($option) {
        return [
            'id'        => $option->id,
            'name'      => $option->name,
            'type'      => 'platform',
            'is_active' => $option->is_active,
            'children'  => $option->values->map(function ($val, $index) {
                return [
                    'id'          => $val->id,
                    'name'        => $val->name,
                    'is_active'   => $val->is_active,
                    'sort_order'  => $val->sort_order ?? $index,
                ];
            })->toArray(),
        ];
    })->toArray();
  }

  public function pBatchCreate(array $data): void {
      DB::transaction(function () use ($data) {
          $user = Auth::user();

          foreach ($data as $group) {
              // parent
              $parentData = $group['parent'];
              $parentData['is_active'] = $parentData['is_active'] ?? true;

              if ($user) {
                  $parentData['created_by']      = $user->id;
                  $parentData['created_by_name'] = $user->name ?? 'system';
              }

              $option = PlatformOption::create($parentData);

              // children
              $children = $group['children'] ?? [];
              foreach ($children as $index => $child) {
                  $child['platform_option_id']   = $option->id;
                  $child['sort_order']           = $child['sort_order'] ?? $index;
                  $child['is_active']            = $child['is_active'] ?? true;

                  if ($user) {
                      $child['created_by']      = $user->id;
                      $child['created_by_name'] = $user->name ?? 'system';
                  }

                  PlatformOptionValue::create($child);
              }
          }
      });
  }

  public function pCreate(array $data): void {
    DB::transaction(function () use ($data) {
      $data['is_active'] = $data['is_active'] ?? true;

      if (Auth::check()) {
        $data['created_by'] = Auth::id();
        $data['created_by_name'] = Auth::user()->name ?? 'system';
      }

      PlatformOption::create($data);
    });
  }

  public function pUpdate(array $data): void {
    DB::transaction(function () use ($data) {
      $option = PlatformOption::findOrFail($data['id']);

      if (Auth::check()) {
        $data['updated_by'] = Auth::id();
        $data['updated_by_name'] = Auth::user()->name ?? 'system';
      }

      $option->update($data);
    });
  }

  public function pDelete(int $id): void {
    DB::transaction(function () use ($id) {
      $option = PlatformOption::findOrFail($id);

      if (Auth::check()) {
        $option->updated_by = Auth::id();
        $option->updated_by_name = Auth::user()->name ?? 'system';
      }

      $option->delete();
    });
  }

  // 店铺
  public function sList() {
    $storeId = Auth::user()->store->id;

    return StoreOption::query()
      ->where('store_id', $storeId)
      ->orderBy('id', 'desc')
      ->get();
  }

  public function sPublic() {
    $storeId = Auth::user()->store->id;

    return StoreOption::query()
      ->where('store_id', $storeId)
      ->where('is_active', true)
      ->orderBy('id', 'desc')
      ->get();
  }

  public function sTree() {
    $user    = Auth::user();
    $storeId = $user->store->id ?? null;

    if (!$storeId) {
        return api_response(null, 'Please Create Store', 403, false);
    }

    $options = StoreOption::where('store_id', $storeId)
        ->orderBy('id', 'desc')
        ->with(['values' => function ($q) {
            // ✅ 排序：优先 sort_order，如果为 null 用 id
            $q->orderByRaw('COALESCE(sort_order, id) ASC');
        }])
        ->get();

    $data = $options->map(function ($option) {
        return [
            'id'              => $option->id,
            'name'            => $option->name,
            'type'            => $option->type,
            'type_label'      => OptionType::toArray()[$option->type] ?? null,
            'is_active'       => $option->is_active,
            'created_by'      => $option->created_by,
            'created_by_name' => $option->created_by_name,
            'updated_by'      => $option->updated_by,
            'updated_by_name' => $option->updated_by_name,
            'created_at'      => $option->created_at,
            'updated_at'      => $option->updated_at,
            'children'        => $option->values->map(function ($val, $index) {
                return [
                    'id'              => $val->id,
                    'name'            => $val->name,
                    'extra_price'     => $val->extra_price,
                    'is_active'       => $val->is_active,
                    'sort_order'      => $val->sort_order ?? $index,
                    'created_by'      => $val->created_by,
                    'created_by_name' => $val->created_by_name,
                    'updated_by'      => $val->updated_by,
                    'updated_by_name' => $val->updated_by_name,
                    'created_at'      => $val->created_at,
                    'updated_at'      => $val->updated_at,
                ];
            })->toArray(),
        ];
    });
    return $data;
}



  /**
   * 批量创建 StoreOption 及其子项 StoreOptionValue
   * 要求：store_id / user_id 一律从 Auth token 获取；不返回数据
   */
  public function batchCreate(array $data, $user): void {
    DB::transaction(function () use ($data, $user) {
        $storeId  = $user->store->id;
        $userId   = $user->id;
        $userName = $user->name;

        foreach ($data as $group) {
            // 1. 创建 parent
            $parentData = $group['parent'];
            $parentData['store_id']         = $storeId;
            $parentData['created_by']       = $userId;
            $parentData['created_by_name']  = $userName;

            $option = StoreOption::create($parentData);

            // 2. 创建 children
            $children = $group['children'] ?? [];

            foreach ($children as $index => $child) {
                $child['store_option_id']     = $option->id;
                $child['created_by']          = $userId;
                $child['created_by_name']     = $userName;

                // ✅ 没有传 sort_order 就用 index
                $child['sort_order'] = $child['sort_order'] ?? $index;

                StoreOptionValue::create($child);
            }
        }
    });
}




  public function sCreate(array $data): void {
    $storeId = Auth::user()->store->id;

    DB::transaction(function () use ($data, $storeId) {
      $data['store_id']  = $storeId;
      $data['is_active'] = $data['is_active'] ?? true;

      $data['created_by']      = Auth::id();
      $data['created_by_name'] = Auth::user()->name ?? 'merchant';

      StoreOption::create($data);
    });
  }

  public function sUpdate(array $data): void {
    $storeId = Auth::user()->store->id;

    DB::transaction(function () use ($data, $storeId) {
      $option = StoreOption::where('id', $data['id'])
        ->where('store_id', $storeId)
        ->firstOrFail(); // ✅ 校验所属店铺

      $data['updated_by']      = Auth::id();
      $data['updated_by_name'] = Auth::user()->name ?? 'merchant';

      $option->update($data);
    });
  }

  public function sDelete(int $id): void {
    $storeId = Auth::user()->store->id;

    DB::transaction(function () use ($id, $storeId) {
      $option = StoreOption::where('id', $id)
        ->where('store_id', $storeId)
        ->firstOrFail(); // ✅ 校验所属店铺

      $option->is_active       = false;
      $option->updated_by      = Auth::id();
      $option->updated_by_name = Auth::user()->name ?? 'merchant';
      $option->save();
    });
  }
}
