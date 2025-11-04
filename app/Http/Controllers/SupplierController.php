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
        $this->authorize('delete', Supplier::class);

        $validated = $request->validate([
            'supplier_ids' => 'required|array',
            'supplier_ids.*' => 'exists:suppliers,id',
        ]);

        try {
            $count = count($validated['supplier_ids']);
            Supplier::whereIn('id', $validated['supplier_ids'])->delete();

            // Gửi thông báo vào hệ thống
            $notificationService->notify(
                Auth::user(),
                'warning',
                'Xóa hàng loạt nhà cung cấp',
                "Đã xóa thành công {$count} nhà cung cấp."
            );

            // Gửi toast thông báo tức thì
            session()->flash('toast', [
                'type' => 'success',
                'message' => "Đã xóa thành công {$count} nhà cung cấp."
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa hàng loạt nhà cung cấp: ' . $e->getMessage());
            session()->flash('toast', [
                'type' => 'error',
                'message' => 'Xóa hàng loạt thất bại.'
            ]);
        }

        return redirect()->route('admin.suppliers.index');
    }
}
