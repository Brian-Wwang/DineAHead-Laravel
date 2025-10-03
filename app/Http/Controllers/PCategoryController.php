<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CategoryService;
use Illuminate\Validation\Rule;

class PCategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * 系统分类列表（全部）
     */
    public function list()
    {
        $list = $this->categoryService->pList();
        return api_response($list);
    }

    /**
     * 系统分类列表（仅启用）
     */
    public function public()
    {
        $list = $this->categoryService->pPublic();
        return api_response($list);
    }

    /**
     * 创建分类
     */
    public function create(Request $request) {
      $data = $request->validate([
        'name'           => 'required|string|max:255',
        'type'           => ['required', 'integer', Rule::in([10, 20])],
        'discount_type'  => ['required', 'integer', Rule::in([0, 10, 20, 30])],
        'discount_value' => 'nullable|numeric|min:0',
        'is_active'      => 'boolean',
      ]);

      $this->categoryService->pCreate($data);

      return api_response();
    }

    /**
     * 更新分类
     */
    public function update(Request $request) {
      $data = $request->validate([
        'id'             => 'required|exists:categories,id',
        'name'           => 'sometimes|string|max:255',
        'type'           => 'sometimes|integer|in:10,20',
        'discount_type'  => 'sometimes|integer|in:0,10,20,30',
        'discount_value' => 'sometimes|numeric|min:0|nullable',
        'is_active'      => 'sometimes|boolean',
      ]);

      $this->categoryService->pUpdate($data);

      return api_response();
    }

    /**
     * 删除分类（逻辑删除）
     */
    public function delete(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:categories,id',
        ]);

        $this->categoryService->pDelete($data['id']);

        return api_response();
    }
}
