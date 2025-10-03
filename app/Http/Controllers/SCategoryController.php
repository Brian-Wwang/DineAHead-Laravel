<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CategoryService;

class SCategoryController extends Controller
{
  protected CategoryService $categoryService;

  public function __construct(CategoryService $categoryService)
  {
      $this->categoryService = $categoryService;
  }

  /**
   * 商户分类列表（全部）
   */
  public function list() {
    $list = $this->categoryService->sList();
    return api_response($list);
  }

  /**
   * 商户分类列表（公开）
   */
  public function public() {
    $sList = $this->categoryService->sPublic();
    $pList = $this->categoryService->pPublic();
    $list = [
        'platform' => $pList,
        'store'    => $sList
    ];

    return api_response($list);
  }

  /**
   * 商户创建分类
   */
  public function create(Request $request)
  {
    $data = $request->validate([
        'name'           => 'required|string|max:255',
        'type'           => 'integer',
        'discount_type'  => 'integer',
        'discount_value' => 'numeric|min:0|nullable',
        'is_active'      => 'boolean',
        'menu_ids'    => 'nullable|array',
        'menu_ids.*'  => 'integer|exists:menus,id',
    ]);

    $this->categoryService->sCreate($data);

    return api_response();
  }

  /**
   * 商户更新分类
   */
  public function update(Request $request)
  {
      $data = $request->validate([
        'id'             => 'required|exists:categories,id',
        'name'           => 'sometimes|string|max:255',
        'type'           => 'sometimes|integer',
        'discount_type'  => 'sometimes|integer',
        'discount_value' => 'sometimes|numeric|min:0|nullable',
        'is_active'      => 'sometimes|boolean',
        'menu_ids'    => 'nullable|array',
        'menu_ids.*'  => 'integer|exists:menus,id',
      ]);

      $this->categoryService->sUpdate($data);

      return api_response();
  }

  /**
   * 商户删除分类
   */
  public function delete(Request $request) {
    $data = $request->validate([
        'id' => 'required|exists:categories,id',
    ]);

    $this->categoryService->sDelete($data['id']);

    return api_response();
  }

  public function bind(Request $request) {
    $validated = $request->validate([
      'category_id' => 'required|integer|exists:categories,id',
      'menu_ids'    => 'required|array',
      'menu_ids.*'  => 'integer|exists:menus,id',
    ]);

    $this->categoryService->bindMenus(
      $validated['category_id'],
      $validated['menu_ids']
    );

    return api_response();
  }
}
