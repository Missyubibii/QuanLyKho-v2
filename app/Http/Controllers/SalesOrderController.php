<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SalesOrderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', SalesOrder::class);
        $query = SalesOrder::with('customer', 'user')->latest();

        // Filter logic
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('customer_id')) $query->where('customer_id', $request->customer_id);
        if ($request->filled('date_from')) $query->whereDate('order_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('order_date', '<=', $request->date_to);
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('so_code', 'like', "%{$term}%")
                    ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$term}%"));
            });
        }

        $salesOrders = $query->paginate(15)->withQueryString();
        $customers = Customer::orderBy('name')->pluck('name', 'id');

        return view('sales-orders.index', compact('salesOrders', 'customers'));
    }

    public function create()
    {
        $this->authorize('create', SalesOrder::class);
        // Truyền danh sách khách hàng đang hoạt động vào view
        $customers = Customer::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        return view('sales-orders.create', compact('customers'));
    }

    public function store(Request $request, NotificationService $notificationService)
    {
        $this->authorize('create', SalesOrder::class);

        // Bắt lỗi validation vào một try-catch
        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'order_date' => 'required|date',
                'expected_date' => 'nullable|date|after_or_equal:order_date',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Nếu có lỗi validation, trả về JSON với lỗi
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors()
            ], 422); // Mã 422 cho lỗi validation
        }

        DB::beginTransaction();
        try {
            $totalAmount = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['price'];
            });

            $salesOrder = SalesOrder::create([
                'customer_id' => $validated['customer_id'],
                'user_id' => Auth::id(),
                'order_date' => $validated['order_date'],
                'expected_date' => $validated['expected_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'total_amount' => $totalAmount,
            ]);

            foreach ($validated['items'] as $item) {
                $salesOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }

            DB::commit();

            $notificationService->notify(Auth::user(), 'success', 'Phiếu xuất mới', 'SO ' . $salesOrder->so_code . ' đã được tạo.');

            // TRẢ VỀ JSON THAY VÌ REDIRECT
            return response()->json([
                'success' => true,
                'message' => 'Tạo phiếu xuất kho thành công!',
                'redirect_url' => route('admin.sales-orders.show', $salesOrder)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo SO: ' . $e->getMessage());

            // Trả về JSON cho các lỗi khác (ví dụ: lỗi trùng mã)
            return response()->json([
                'success' => false,
                'message' => 'Tạo phiếu xuất kho thất bại: ' . $e->getMessage()
            ], 500); // Mã 500 cho lỗi server
        }
    }

    public function show(SalesOrder $salesOrder)
    {
        $this->authorize('view', $salesOrder);
        // Tải các quan hệ cần thiết để hiển thị
        $salesOrder->load('customer', 'user', 'items.product');
        return view('sales-orders.show', compact('salesOrder'));
    }

    public function edit(SalesOrder $salesOrder)
    {
        $this->authorize('update', $salesOrder);
        if ($salesOrder->status !== 'pending') {
            session()->flash('toast', ['type' => 'warning', 'message' => 'Chỉ có thể sửa phiếu xuất đang chờ xử lý.']);
            return redirect()->route('admin.sales-orders.show', $salesOrder);
        }
        // Truyền danh sách khách hàng và các item hiện tại của SO
        $customers = Customer::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        $salesOrder->load('items.product');
        return view('sales-orders.edit', compact('salesOrder', 'customers'));
    }

    public function update(Request $request, SalesOrder $salesOrder, NotificationService $notificationService)
    {
        $this->authorize('update', $salesOrder);
        if ($salesOrder->status !== 'pending') {
            session()->flash('toast', ['type' => 'warning', 'message' => 'Chỉ có thể sửa phiếu xuất đang chờ xử lý.']);
            return redirect()->route('admin.sales-orders.show', $salesOrder);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $totalAmount = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['price'];
            });

            $salesOrder->update([
                'customer_id' => $validated['customer_id'],
                'order_date' => $validated['order_date'],
                'expected_date' => $validated['expected_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'total_amount' => $totalAmount,
            ]);

            // Xóa các item cũ và tạo lại item mới
            $salesOrder->items()->delete();
            foreach ($validated['items'] as $item) {
                $salesOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }

            DB::commit();

            $notificationService->notify(Auth::user(), 'info', 'Cập nhật phiếu xuất', 'SO ' . $salesOrder->so_code . ' đã được cập nhật.');
            session()->flash('toast', ['type' => 'success', 'message' => 'Cập nhật phiếu xuất kho thành công!']);

            return redirect()->route('admin.sales-orders.show', $salesOrder);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi cập nhật SO: ' . $e->getMessage());
            session()->flash('toast', ['type' => 'error', 'message' => 'Cập nhật phiếu xuất kho thất bại.']);
            return redirect()->back()->withInput();
        }
    }

    public function destroy(SalesOrder $salesOrder, NotificationService $notificationService)
    {
        $this->authorize('delete', $salesOrder);
        if ($salesOrder->status !== 'pending' && $salesOrder->status !== 'cancelled') {
            session()->flash('toast', ['type' => 'warning', 'message' => 'Chỉ có thể xóa phiếu xuất đang chờ hoặc đã hủy.']);
            return redirect()->route('admin.sales-orders.show', $salesOrder);
        }

        try {
            $soCode = $salesOrder->so_code;
            $salesOrder->delete();

            $notificationService->notify(Auth::user(), 'warning', 'Xóa phiếu xuất', 'SO ' . $soCode . ' đã được xóa.');
            session()->flash('toast', ['type' => 'success', 'message' => 'Xóa phiếu xuất kho thành công!']);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa SO: ' . $e->getMessage());
            session()->flash('toast', ['type' => 'error', 'message' => 'Xóa phiếu xuất kho thất bại.']);
        }
        return redirect()->route('admin.sales-orders.index');
    }

    /**
     * Xử lý giao hàng cho Phiếu Xuất Kho.
     */
    public function ship(SalesOrder $salesOrder, NotificationService $notificationService)
    {
        $this->authorize('approve', $salesOrder);
        if ($salesOrder->status !== 'pending' && $salesOrder->status !== 'processing') {
            session()->flash('toast', ['type' => 'warning', 'message' => 'Phiếu xuất này không thể giao hàng.']);
            return redirect()->route('admin.sales-orders.show', $salesOrder);
        }

        DB::beginTransaction();
        try {
            $salesOrder->update(['status' => 'shipped']);

            foreach ($salesOrder->items as $item) {
                // Sử dụng lockForUpdate để tránh race condition
                $product = Product::lockForUpdate()->find($item->product_id);
                if (!$product) {
                    throw new \Exception("Sản phẩm ID {$item->product_id} không tồn tại.");
                }
                if ($product->quantity < $item->quantity) {
                    // Kiểm tra tồn kho trước khi trừ
                    throw new \Exception("Sản phẩm '{$product->name}' (ID: {$product->id}) không đủ hàng tồn kho. Tồn kho hiện tại: {$product->quantity}, yêu cầu: {$item->quantity}.");
                }

                $product->quantity -= $item->quantity; // Giảm số lượng
                $product->save();

                // Tạo Inventory Movement
                InventoryMovement::create([
                    'product_id' => $item->product_id,
                    'type' => 'out',
                    'quantity_change' => $item->quantity,
                    'quantity_after' => $product->quantity,
                    'source_type' => SalesOrder::class,
                    'source_id' => $salesOrder->id,
                    'user_id' => Auth::id(),
                    'notes' => 'Xuất hàng cho ' . $salesOrder->so_code,
                ]);
            }

            DB::commit();

            $notificationService->notify(Auth::user(), 'success', 'Giao hàng thành công', 'Đã giao hàng cho SO ' . $salesOrder->so_code . '.');
            session()->flash('toast', ['type' => 'success', 'message' => 'Giao hàng thành công! Tồn kho đã được cập nhật.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi giao hàng SO ' . $salesOrder->id . ': ' . $e->getMessage());
            session()->flash('toast', ['type' => 'error', 'message' => 'Giao hàng thất bại: ' . $e->getMessage()]);
        }

        return redirect()->route('admin.sales-orders.show', $salesOrder);
    }

    public function bulkDeleteSOs(Request $request, NotificationService $notificationService)
    {
        $this->authorize('delete', SalesOrder::class);
        $validated = $request->validate([
            'so_ids' => 'required|array|min:1',
            'so_ids.*' => 'exists:sales_orders,id',
        ]);

        $deletedCount = 0;
        try {
            DB::transaction(function () use ($validated, &$deletedCount) {
                $deletedCount = count($validated['so_ids']);
                SalesOrder::whereIn('id', $validated['so_ids'])->delete();
            });

            $notificationService->notify(Auth::user(), 'warning', 'Xóa hàng loạt phiếu xuất', "Đã xóa thành công {$deletedCount} phiếu xuất.");
            return response()->json(['message' => "Đã xóa thành công {$deletedCount} phiếu xuất."]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa hàng loạt SO: ' . $e->getMessage());
            return response()->json(['message' => 'Đã có lỗi xảy ra, vui lòng thử lại.'], 500);
        }
    }

    public function searchJson(Request $request)
    {
        dd('Vào hàm searchJson');

        $term = $request->input('term', '');
        if (empty($term)) return response()->json([]);

        $salesOrders = SalesOrder::query()
            ->where(function ($q) use ($term) {
                $q->where('so_code', 'like', "%{$term}%")
                    ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$term}%"));
            })
            ->with('customer:id,name')
            ->select('id', 'so_code', 'customer_id', 'status', 'total_amount')
            ->limit(10)
            ->get();

        $results = $salesOrders->map(function ($so) {
            return [
                'id' => $so->id,
                'so_code' => $so->so_code,
                'customer_name' => $so->customer->name,
                'status' => $so->status,
                'status_translated' => match ($so->status) {
                    'pending' => 'Chờ xử lý',
                    'processing' => 'Đang xử lý',
                    'shipped' => 'Đã giao hàng',
                    'cancelled' => 'Đã hủy',
                    default => $so->status,
                },
                'total_amount' => $so->total_amount,
                'url' => route('admin.sales-orders.show', $so->id)
            ];
        });

        return response()->json($results);
    }
}
