<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // <-- Thêm dòng này
use Illuminate\Support\Facades\Gate;

class SupplierController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function index(Request $request)
    {


        $this->authorize('viewAny', Supplier::class);

        $query = Supplier::query();

        // Áp dụng tìm kiếm
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%")
                    ->orWhere('contact_person', 'like', "%{$searchTerm}%");
            });
        }

        // Áp dụng bộ lọc trạng thái
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->get('is_active'));
        }

        // Phân trang và giữ lại các tham số query string
        $suppliers = $query->latest()->paginate(10)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $this->authorize('create', Supplier::class);
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request, NotificationService $notificationService)
    {
        $this->authorize('create', Supplier::class);

        try {
            $supplier = Supplier::create($request->validated());

            // Gửi thông báo vào hệ thống
            $notificationService->notify(
                Auth::user(),
                'success',
                'Nhà cung cấp mới',
                'Nhà cung cấp "' . $supplier->name . '" đã được tạo thành công.'
            );

            // Gửi toast thông báo tức thì
            session()->flash('toast', [
                'type' => 'success',
                'message' => 'Thêm nhà cung cấp thành công!'
            ]);

            return redirect()->route('admin.suppliers.index');
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo nhà cung cấp: ' . $e->getMessage());
            session()->flash('toast', [
                'type' => 'error',
                'message' => 'Đã có lỗi xảy ra, vui lòng thử lại.'
            ]);
            return redirect()->back()->withInput();
        }
    }

    public function show(Supplier $supplier)
    {
        $this->authorize('view', $supplier);

        $supplier->load(['products' => function ($query) {
            $query->orderBy('name');
        }]);

        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        $this->authorize('update', $supplier);
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier, NotificationService $notificationService)
    {
        $this->authorize('update', $supplier);

        try {
            $supplier->update($request->validated());

            // Gửi thông báo vào hệ thống
            $notificationService->notify(
                Auth::user(),
                'info',
                'Cập nhật nhà cung cấp',
                'Nhà cung cấp "' . $supplier->name . '" đã được cập nhật.'
            );

            // Gửi toast thông báo tức thì
            session()->flash('toast', [
                'type' => 'success',
                'message' => 'Cập nhật nhà cung cấp thành công!'
            ]);

            return redirect()->route('admin.suppliers.index');
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật nhà cung cấp: ' . $e->getMessage());
            session()->flash('toast', [
                'type' => 'error',
                'message' => 'Cập nhật thất bại, vui lòng thử lại.'
            ]);
            return redirect()->back()->withInput();
        }
    }

    public function destroy(Supplier $supplier, NotificationService $notificationService)
    {
        $this->authorize('delete', $supplier);

        try {
            $supplierName = $supplier->name;
            $supplier->delete();

            // Gửi thông báo vào hệ thống
            $notificationService->notify(
                Auth::user(),
                'warning',
                'Xóa nhà cung cấp',
                'Nhà cung cấp "' . $supplierName . '" đã bị xóa.'
            );

            // Gửi toast thông báo tức thì
            session()->flash('toast', [
                'type' => 'success',
                'message' => 'Xóa nhà cung cấp thành công!'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa nhà cung cấp: ' . $e->getMessage());
            session()->flash('toast', [
                'type' => 'error',
                'message' => 'Xóa nhà cung cấp thất bại.'
            ]);
        }

        return redirect()->route('admin.suppliers.index');
    }

public function bulkDelete(Request $request, NotificationService $notificationService)
    {
        // 1. Kiểm tra quyền. Đảm bảo bạn có quyền 'suppliers.delete' trong seeder.
        $this->authorize('suppliers.delete');

        // 2. Xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'supplier_ids' => 'required|array',
            'supplier_ids.*' => 'exists:suppliers,id',
        ]);

        // 3. Khởi tạo biến đếm
        $deletedCount = 0;

        try {
            // 4. Sử dụng Transaction để đảm bảo toàn vẹn dữ liệu
            DB::transaction(function () use ($validated, &$deletedCount) {
                // Gán giá trị cho biến bên trong transaction
                $deletedCount = count($validated['supplier_ids']);
                Supplier::whereIn('id', $validated['supplier_ids'])->delete();
            });

            // 5. Gửi thông báo vào hệ thống (notification dropdown)
            $notificationService->notify(
                Auth::user(),
                'warning',
                'Xóa hàng loạt nhà cung cấp',
                "Đã xóa thành công {$deletedCount} nhà cung cấp."
            );

            // 6. Trả về phản hồi thành công dạng JSON cho AJAX
            return response()->json([
                'message' => "Đã xóa thành công {$deletedCount} nhà cung cấp."
            ]);

        } catch (\Exception $e) {
            // 7. Ghi lỗi vào log
            Log::error('Lỗi khi xóa hàng loạt nhà cung cấp: ' . $e->getMessage());

            // 8. Trả về phản hồi lỗi dạng JSON
            return response()->json([
                'message' => 'Đã có lỗi xảy ra, vui lòng thử lại.'
            ], 500);
        }
    }

    /**
     * Tìm kiếm nhà cung cấp để trả về JSON cho các form (ví dụ: tạo phiếu nhập).
     */
    public function searchJson(Request $request)
    {
        $term = $request->input('term', '');
        $isNumeric = $request->boolean('numeric', false);


        if (empty($term) || strlen($term) < 2) {
            return response()->json([]);
        }
        
        try {
            $suppliers = Supplier::where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('contact_person', 'like', "%{$term}%");
            })
            ->where('is_active', true) // Chỉ lấy nhà cung cấp đang hoạt động
            ->select('id', 'name', 'contact_person', 'email', 'phone')
            ->limit(10)
            ->get();
            return response()->json($suppliers);
        } catch (\Exception $e) {
            \Log::error("Lỗi tìm kiếm nhà cung cấp JSON: " . $e->getMessage());
            return response()->json(['error' => 'Lỗi server khi tìm kiếm'], 500);
        }
    }
}
