<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\NotificationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        // Query cơ bản với các mối quan hệ để tránh N+1 problem
        $query = Product::with(['category', 'supplier']);

        // Áp dụng tìm kiếm
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('sku', 'like', "%{$searchTerm}%")
                    ->orWhereHas('supplier', function ($subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Áp dụng bộ lọc
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->get('supplier_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Phân trang và giữ lại các tham số query string
        $products = $query->latest()->paginate(10)->withQueryString();

        // Lấy dữ liệu cho bộ lọc
        $categories = Category::orderBy('name')->pluck('name', 'id');
        $suppliers = Supplier::orderBy('name')->pluck('name', 'id');

        // Tính toán các chỉ số
        $lowStockCount = Product::whereColumn('quantity', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->count();

        // Tạm thời dùng lowStockCount làm số thông báo
        // Trong thực tế, bạn sẽ xây dựng một hệ thống thông báo riêng
        $notificationCount = $lowStockCount;

        return view('products.index', compact(
            'products',
            'categories',
            'suppliers',
            'lowStockCount',
            'notificationCount'
        ));
    }

    public function create()
    {
        // Kiểm tra quyền thủ công
        $this->authorize('create', Product::class);

        $categories = Category::orderBy('name')->pluck('name', 'id');
        $suppliers = Supplier::orderBy('name')->pluck('name', 'id');

        return view('products.create', compact('categories', 'suppliers'));
    }

    public function store(StoreProductRequest $request, NotificationService $notificationService)
    {
        $this->authorize('create', Product::class);

        $validatedData = $request->validated();
        $validatedData['quantity'] = 0; // Luôn đặt số lượng là 0 khi tạo mới

        try {
            $product = Product::create($validatedData);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
                $product->image = $imagePath;
                $product->save();
            }

            // Gửi thông báo vào hệ thống
            $notificationService->notify(
                Auth::user(),
                'success',
                'Sản phẩm mới',
                'Sản phẩm "' . $product->name . '" đã được tạo thành công.'
            );

            // Gửi toast thông báo tức thì
            session()->flash('toast', [
                'type' => 'success',
                'message' => 'Thêm sản phẩm thành công!'
            ]);

            return redirect()->route('admin.products.index');
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo sản phẩm: ' . $e->getMessage());
            session()->flash('toast', [
                'type' => 'error',
                'message' => 'Đã có lỗi xảy ra, vui lòng thử lại.'
            ]);
            return redirect()->back()->withInput();
        }
    }

    public function show(Product $product)
    {
        // Kiểm tra quyền thủ công
        $this->authorize('view', $product);

        $product->load(['category', 'supplier' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        // Kiểm tra quyền thủ công
        $this->authorize('update', $product);

        $categories = Category::orderBy('name')->pluck('name', 'id');
        $suppliers = Supplier::orderBy('name')->pluck('name', 'id');

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(UpdateProductRequest $request, Product $product, NotificationService $notificationService)
    {
        $this->authorize('update', $product);

        $validatedData = $request->validated();
        unset($validatedData['quantity']); // Không cho phép cập nhật số lượng từ form

        try {
            $product->update($validatedData);

            if ($request->hasFile('image')) {
                // Xóa ảnh cũ
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $imagePath = $request->file('image')->store('products', 'public');
                $product->image = $imagePath;
                $product->save();
            }

            // Gửi thông báo vào hệ thống
            $notificationService->notify(
                Auth::user(),
                'info',
                'Cập nhật sản phẩm',
                'Sản phẩm "' . $product->name . '" đã được cập nhật.'
            );

            // Gửi toast thông báo tức thì
            session()->flash('toast', [
                'type' => 'success',
                'message' => 'Cập nhật sản phẩm thành công!'
            ]);

            return redirect()->route('admin.products.index');
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật sản phẩm: ' . $e->getMessage());
            session()->flash('toast', [
                'type' => 'error',
                'message' => 'Cập nhật thất bại, vui lòng thử lại.'
            ]);
            return redirect()->back()->withInput();
        }
    }

    public function destroy(Product $product, NotificationService $notificationService)
    {
        $this->authorize('delete', $product);

        try {
            $productName = $product->name;
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->delete();

            // Gửi thông báo vào hệ thống
            $notificationService->notify(
                Auth::user(),
                'warning',
                'Xóa sản phẩm',
                'Sản phẩm "' . $productName . '" đã bị xóa.'
            );

            // Gửi toast thông báo tức thì
            session()->flash('toast', [
                'type' => 'success',
                'message' => 'Xóa sản phẩm thành công!'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa sản phẩm: ' . $e->getMessage());
            session()->flash('toast', [
                'type' => 'error',
                'message' => 'Xóa sản phẩm thất bại.'
            ]);
        }

        return redirect()->route('admin.products.index');
    }

    public function bulkDelete(Request $request, NotificationService $notificationService)
    {
        $this->authorize('products.delete');

        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        try {
            $count = count($validated['product_ids']);

            // --- Xóa ảnh liên quan (QUAN TRỌNG NẾU CÓ) ---
            $productsToDelete = Product::whereIn('id', $validated['product_ids'])->get();
            foreach ($productsToDelete as $product) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
            }
            // --- Kết thúc phần xóa ảnh ---

            // 3. Deletion Logic
            Product::whereIn('id', $validated['product_ids'])->delete();

            // 4. Notification (Optional but good)
            $notificationService->notify(
                Auth::user(),
                'warning',
                'Xóa hàng loạt sản phẩm',
                "Đã xóa thành công {$count} sản phẩm."
            );

            // 5. Return JSON Response (QUAN TRỌNG)
            return response()->json(['message' => "Đã xóa thành công {$count} sản phẩm."]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa hàng loạt sản phẩm: ' . $e->getMessage());
            // Return JSON error response
            return response()->json(['message' => 'Xóa hàng loạt thất bại.'], 500);
        }
    }

    public function searchJson(Request $request)
    {
        $term = $request->input('term', '');
        if (empty($term) || strlen($term) < 2) { // Thêm kiểm tra độ dài tối thiểu
            return response()->json([]);
        }
        try { // Thêm try-catch để bắt lỗi query
            $products = Product::where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%");
            })
                ->where('status', '!=', 'maintenance')
                ->select('id', 'name', 'sku', 'price_buy', 'unit')
                ->limit(10)
                ->get();
            return response()->json($products); // Phải trả về JSON
        } catch (\Exception $e) {
            \Log::error("Lỗi tìm kiếm sản phẩm JSON: " . $e->getMessage());
            return response()->json(['error' => 'Lỗi server khi tìm kiếm'], 500); // Trả về lỗi JSON 500
        }
    }
}
