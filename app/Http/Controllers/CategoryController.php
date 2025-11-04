<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

// class CategoryController extends Controller
// {
//     public function index()
//     {
//         $this->authorize('viewAny', Category::class);
//         $categories = Category::latest()->paginate(10);
//         return view('categories.index', compact('categories'))->with(['pageTitle' => 'Quản lý Danh mục']);
//     }

//     public function create()
//     {
//         $this->authorize('create', Category::class);
//         return view('categories.create')->with(['pageTitle' => 'Thêm Danh mục mới']);
//     }

//     public function store(StoreCategoryRequest $request, NotificationService $notificationService)
//     {
//         $this->authorize('create', Category::class);

//         try {
//             $category = Category::create($request->validated());

//             $notificationService->notify(
//                 auth()->user(),
//                 'success',
//                 'Danh mục mới',
//                 'Danh mục "' . $category->name . '" đã được tạo thành công.'
//             );

//             session()->flash('toast', ['type' => 'success', 'message' => 'Thêm danh mục thành công!']);
//             return redirect()->route('admin.categories.index');

//         } catch (\Exception $e) {
//             Log::error('Lỗi khi tạo danh mục: ' . $e->getMessage());
//             session()->flash('toast', ['type' => 'error', 'message' => 'Đã có lỗi xảy ra, vui lòng thử lại.']);
//             return redirect()->back()->withInput();
//         }
//     }

//     public function show(Category $category)
//     {
//         $this->authorize('view', $category);
//         return view('categories.show', compact('category'));
//     }

//     public function edit(Category $category)
//     {
//         $this->authorize('update', $category);
//         return view('categories.edit', compact('category'))->with(['pageTitle' => 'Chỉnh sửa Danh mục']);
//     }

//     public function update(UpdateCategoryRequest $request, Category $category, NotificationService $notificationService)
//     {
//         $this->authorize('update', $category);

//         try {
//             $category->update($request->validated());

//             $notificationService->notify(
//                 auth()->user(),
//                 'info',
//                 'Cập nhật danh mục',
//                 'Danh mục "' . $category->name . '" đã được cập nhật.'
//             );

//             session()->flash('toast', ['type' => 'success', 'message' => 'Cập nhật danh mục thành công!']);
//             return redirect()->route('admin.categories.index');

//         } catch (\Exception $e) {
//             Log::error('Lỗi khi cập nhật danh mục: ' . $e->getMessage());
//             session()->flash('toast', ['type' => 'error', 'message' => 'Cập nhật thất bại, vui lòng thử lại.']);
//             return redirect()->back()->withInput();
//         }
//     }

//     public function destroy(Category $category, NotificationService $notificationService)
//     {
//         $this->authorize('delete', $category);

//         try {
//             $categoryName = $category->name;
//             $category->delete();

//             $notificationService->notify(
//                 auth()->user(),
//                 'warning',
//                 'Xóa danh mục',
//                 'Danh mục "' . $categoryName . '" đã bị xóa.'
//             );

//             session()->flash('toast', ['type' => 'success', 'message' => 'Xóa danh mục thành công!']);

//         } catch (\Exception $e) {
//             Log::error('Lỗi khi xóa danh mục: ' . $e->getMessage());
//             session()->flash('toast', ['type' => 'error', 'message' => 'Xóa danh mục thất bại.']);
//         }

//         return redirect()->route('admin.categories.index');
//     }
// }
