<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CustomerController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Customer::class);

        $query = Customer::query();

        // Áp dụng tìm kiếm
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%")
                    ->orWhere('contact_person', 'like', "%{$searchTerm}%");
            });
        }

        // Áp dụng bộ lọc
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->get('is_active'));
        }

        // Phân trang và giữ lại các tham số query string
        $customers = $query->latest()->paginate(10)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        $this->authorize('create', Customer::class);
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request, NotificationService $notificationService)
    {
        $this->authorize('create', Customer::class);

        try {
            $customer = Customer::create($request->validated());

            // Gửi thông báo vào hệ thống
            $notificationService->notify(
                Auth::user(),
                'success',
                'Khách hàng mới',
                'Khách hàng "' . $customer->name . '" đã được tạo thành công.'
            );

            // Gửi toast thông báo tức thì
            session()->flash('toast', [
                'type' => 'success',
                'message' => 'Thêm khách hàng thành công!'
            ]);

            return redirect()->route('admin.customers.index');
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo khách hàng: ' . $e->getMessage());
            session()->flash('toast', [
                'type' => 'error',
                'message' => 'Đã có lỗi xảy ra, vui lòng thử lại.'
            ]);
            return redirect()->back()->withInput();
        }
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);

        $customer->load(['salesOrders' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $this->authorize('update', $customer);
        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer, NotificationService $notificationService)
    {
        $this->authorize('update', $customer);

        try {
            $customer->update($request->validated());

            // Gửi thông báo vào hệ thống
            $notificationService->notify(
                Auth::user(),
                'info',
                'Cập nhật khách hàng',
                'Khách hàng "' . $customer->name . '" đã được cập nhật.'
            );

            // Gửi toast thông báo tức thì
            session()->flash('toast', [
                'type' => 'success',
                'message' => 'Cập nhật khách hàng thành công!'
            ]);

            return redirect()->route('admin.customers.index');
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật khách hàng: ' . $e->getMessage());
            session()->flash('toast', [
                'type' => 'error',
                'message' => 'Cập nhật thất bại, vui lòng thử lại.'
            ]);
            return redirect()->back()->withInput();
        }
    }

    public function destroy(Customer $customer, NotificationService $notificationService)
    {
        $this->authorize('delete', $customer);

        try {
            $customerName = $customer->name;
            $customer->delete();

            // Gửi thông báo vào hệ thống
            $notificationService->notify(
                Auth::user(),
                'warning',
                'Xóa khách hàng',
                'Khách hàng "' . $customerName . '" đã bị xóa.'
            );

            // Gửi toast thông báo tức thì
            session()->flash('toast', [
                'type' => 'success',
                'message' => 'Xóa khách hàng thành công!'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa khách hàng: ' . $e->getMessage());
            session()->flash('toast', [
                'type' => 'error',
                'message' => 'Xóa khách hàng thất bại.'
            ]);
        }

        return redirect()->route('admin.customers.index');
    }

    /**
     * Xóa hàng loạt khách hàng (Phiên bản mới - AJAX).
     */
    public function bulkDelete(Request $request, NotificationService $notificationService)
    {
        $this->authorize('delete', Customer::class);

        $validated = $request->validate([
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'exists:customers,id',
        ]);

        $deletedCount = 0;

        try {
            DB::transaction(function () use ($validated, &$deletedCount) {
                $deletedCount = count($validated['customer_ids']);
                Customer::whereIn('id', $validated['customer_ids'])->delete();
            });

            // Gửi thông báo vào hệ thống
            $notificationService->notify(
                Auth::user(),
                'warning',
                'Xóa hàng loạt khách hàng',
                "Đã xóa thành công {$deletedCount} khách hàng."
            );

            // Trả về phản hồi thành công dạng JSON cho AJAX
            return response()->json([
                'message' => "Đã xóa thành công {$deletedCount} khách hàng."
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa hàng loạt khách hàng: ' . $e->getMessage());
            return response()->json(['message' => 'Đã có lỗi xảy ra, vui lòng thử lại.'], 500);
        }
    }

    /**
     * Tìm kiếm khách hàng để trả về JSON cho các form.
     */
    public function searchJson(Request $request)
    {
        $term = $request->input('term', '');
        $isNumeric = $request->boolean('numeric', false);

        if (empty($term)) {
            return response()->json([]);
        }

        try {
            $query = Customer::query();

            if ($isNumeric) {
                // Nếu là tìm kiếm số, tìm trong các trường số như phone
                $query->where('phone', 'like', "%{$term}%");
            } else {
                // Nếu là tìm kiếm chuỗi, tìm trong các trường văn bản
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('contact_person', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            }

            $customers = $query->where('is_active', true)
                ->select('id', 'name', 'contact_person', 'email', 'phone')
                ->limit(10)
                ->get();

            return response()->json($customers);
        } catch (\Exception $e) {
            \Log::error("Lỗi tìm kiếm khách hàng JSON: " . $e->getMessage());
            return response()->json(['error' => 'Lỗi server khi tìm kiếm'], 500);
        }
    }
}
