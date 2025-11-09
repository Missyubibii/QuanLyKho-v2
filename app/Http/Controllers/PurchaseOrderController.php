<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\InventoryMovement;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', PurchaseOrder::class); // Cần tạo Policy

        $query = PurchaseOrder::with('supplier', 'user')->latest();

        // Filter logic (status, supplier, date range - tương tự Products)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('po_code', 'like', "%{$term}%")
                    ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$term}%"));
            });
        }


        $purchaseOrders = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('name')->pluck('name', 'id'); // Cho bộ lọc

        return view('purchase-orders.index', compact('purchaseOrders', 'suppliers'));
    }

    public function create()
    {
        // $this->authorize('create', PurchaseOrder::class);
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        return view('purchase-orders.create', compact('suppliers'));
    }

    public function store(StorePurchaseOrderRequest $request, NotificationService $notificationService)
    {
        // $this->authorize('create', PurchaseOrder::class);
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }

            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'user_id' => Auth::id(),
                'order_date' => $validated['order_date'],
                'expected_date' => $validated['expected_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending', // Mặc định là pending
                'total_amount' => $totalAmount,
            ]);

            foreach ($validated['items'] as $item) {
                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }

            DB::commit();

            // Gửi thông báo (tương tự các controller khác)
            $notificationService->notify(Auth::user(), 'success', 'Phiếu nhập mới', 'PO ' . $purchaseOrder->po_code . ' đã được tạo.');
            session()->flash('toast', ['type' => 'success', 'message' => 'Tạo phiếu nhập kho thành công!']);

            return redirect()->route('admin.purchase-orders.index');
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Lỗi tạo PO: ' . $e->getMessage());
            session()->flash('toast', ['type' => 'error', 'message' => 'Tạo phiếu nhập kho thất bại.']);
            return redirect()->back()->withInput();
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        // $this->authorize('view', $purchaseOrder);
        $purchaseOrder->load('supplier', 'user', 'items.product'); // Eager load chi tiết
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        // $this->authorize('update', $purchaseOrder);
        if ($purchaseOrder->status !== 'pending') {
            session()->flash('toast', ['type' => 'warning', 'message' => 'Chỉ có thể sửa phiếu nhập đang chờ xử lý.']);
            return redirect()->route('admin.purchase-orders.show', $purchaseOrder);
        }
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        $purchaseOrder->load('items.product'); // Load items để pre-fill form
        return view('purchase-orders.edit', compact('purchaseOrder', 'suppliers'));
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder, NotificationService $notificationService)
    {
        // $this->authorize('update', $purchaseOrder);
        if ($purchaseOrder->status !== 'pending') {
            session()->flash('toast', ['type' => 'warning', 'message' => 'Chỉ có thể sửa phiếu nhập đang chờ xử lý.']);
            return redirect()->route('admin.purchase-orders.show', $purchaseOrder);
        }

        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }

            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'],
                // Không cập nhật user_id
                'order_date' => $validated['order_date'],
                'expected_date' => $validated['expected_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'total_amount' => $totalAmount,
            ]);

            // Xóa item cũ và thêm item mới (cách đơn giản)
            $purchaseOrder->items()->delete();
            foreach ($validated['items'] as $item) {
                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }

            DB::commit();

            $notificationService->notify(Auth::user(), 'info', 'Cập nhật phiếu nhập', 'PO ' . $purchaseOrder->po_code . ' đã được cập nhật.');
            session()->flash('toast', ['type' => 'success', 'message' => 'Cập nhật phiếu nhập kho thành công!']);

            return redirect()->route('admin.purchase-orders.show', $purchaseOrder); // Chuyển về trang show

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi cập nhật PO: ' . $e->getMessage());
            session()->flash('toast', ['type' => 'error', 'message' => 'Cập nhật phiếu nhập kho thất bại.']);
            return redirect()->back()->withInput();
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder, NotificationService $notificationService)
    {
        // $this->authorize('delete', $purchaseOrder);
        if ($purchaseOrder->status !== 'pending' && $purchaseOrder->status !== 'cancelled') {
            session()->flash('toast', ['type' => 'warning', 'message' => 'Chỉ có thể xóa phiếu nhập đang chờ hoặc đã hủy.']);
            return redirect()->route('admin.purchase-orders.show', $purchaseOrder);
        }

        try {
            $poCode = $purchaseOrder->po_code;
            $purchaseOrder->delete(); // Soft delete

            $notificationService->notify(Auth::user(), 'warning', 'Xóa phiếu nhập', 'PO ' . $poCode . ' đã được xóa.');
            session()->flash('toast', ['type' => 'success', 'message' => 'Xóa phiếu nhập kho thành công!']);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa PO: ' . $e->getMessage());
            session()->flash('toast', ['type' => 'error', 'message' => 'Xóa phiếu nhập kho thất bại.']);
        }
        return redirect()->route('admin.purchase-orders.index');
    }

    /**
     * Xử lý nhận hàng cho Phiếu Nhập Kho. [cite: 353]
     */
    public function receive(PurchaseOrder $purchaseOrder, NotificationService $notificationService)
    {
        // $this->authorize('approve', $purchaseOrder); // Cần quyền 'purchase_orders.approve' [cite: 239]
        if ($purchaseOrder->status !== 'pending' && $purchaseOrder->status !== 'processing') { // Cho phép nhận hàng nếu đang chờ hoặc đang xử lý
            session()->flash('toast', ['type' => 'warning', 'message' => 'Phiếu nhập này không thể nhận hàng.']);
            return redirect()->route('admin.purchase-orders.show', $purchaseOrder);
        }

        DB::beginTransaction();
        try {
            // Cập nhật trạng thái PO
            $purchaseOrder->update(['status' => 'completed']);

            // Cập nhật số lượng tồn kho và tạo Inventory Movement
            foreach ($purchaseOrder->items as $item) {
                // Sử dụng lockForUpdate để tránh race condition [cite: 479]
                $product = Product::lockForUpdate()->find($item->product_id);
                if ($product) {
                    $product->quantity += $item->quantity; // Tăng số lượng [cite: 353]
                    $product->save();

                    // Tạo Inventory Movement [cite: 353]
                    InventoryMovement::create([
                        'product_id' => $item->product_id,
                        'type' => 'in', // Loại 'in' [cite: 328]
                        'quantity_change' => $item->quantity, // Số lượng thay đổi
                        'quantity_after' => $product->quantity, // Số lượng sau khi thay đổi
                        'source_type' => PurchaseOrder::class, // Loại nguồn [cite: 455]
                        'source_id' => $purchaseOrder->id, // ID nguồn [cite: 456]
                        'user_id' => Auth::id(), // Người thực hiện
                        'notes' => 'Nhập hàng từ ' . $purchaseOrder->po_code, // Ghi chú [cite: 328] ('reason')
                    ]);
                } else {
                    throw new \Exception("Sản phẩm ID {$item->product_id} không tồn tại.");
                }
            }

            DB::commit();

            $notificationService->notify(Auth::user(), 'success', 'Nhận hàng thành công', 'Đã nhận hàng cho PO ' . $purchaseOrder->po_code . '.');
            session()->flash('toast', ['type' => 'success', 'message' => 'Nhận hàng thành công! Tồn kho đã được cập nhật.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi nhận hàng PO ' . $purchaseOrder->id . ': ' . $e->getMessage());
            session()->flash('toast', ['type' => 'error', 'message' => 'Nhận hàng thất bại: ' . $e->getMessage()]);
        }

        return redirect()->route('admin.purchase-orders.show', $purchaseOrder);
    }

    public function bulkDeletePOs(Request $request, NotificationService $notificationService)
    {
        $this->authorize('delete', PurchaseOrder::class);

        $validated = $request->validate([
            'po_ids' => 'required|array|min:1',
            'po_ids.*' => 'exists:purchase_orders,id',
        ]);

        $deletedCount = 0;

        try {
            DB::transaction(function () use ($validated, &$deletedCount) {
                $deletedCount = count($validated['po_ids']);
                // Soft delete các PO
                PurchaseOrder::whereIn('id', $validated['po_ids'])->delete();
            });

            // Gửi thông báo vào hệ thống
            $notificationService->notify(
                Auth::user(),
                'warning',
                'Xóa hàng loạt phiếu nhập',
                "Đã xóa thành công {$deletedCount} phiếu nhập."
            );

            // Trả về phản hồi thành công dạng JSON cho AJAX
            return response()->json([
                'message' => "Đã xóa thành công {$deletedCount} phiếu nhập."
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa hàng loạt PO: ' . $e->getMessage());
            return response()->json(['message' => 'Đã có lỗi xảy ra, vui lòng thử lại.'], 500);
        }
    }

    /**
     * Tìm kiếm Purchase Order trả về JSON cho dynamic search.
     */
    public function searchJson(Request $request)
    {
        $term = $request->input('term', '');
        if (empty($term)) {
            return response()->json([]);
        }

        $purchaseOrders = PurchaseOrder::query()
            ->where(function ($q) use ($term) {
                $q->where('po_code', 'like', "%{$term}%")
                    ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$term}%"));
            })
            ->with('supplier:id,name') // Tải trước thông tin nhà cung cấp
            ->select('id', 'po_code', 'supplier_id', 'status', 'total_amount') // Chọn các trường cần thiết
            ->limit(10)
            ->get();

        // Định dạng lại kết quả để dễ sử dụng ở frontend
        $results = $purchaseOrders->map(function ($po) {
            return [
                'id' => $po->id,
                'po_code' => $po->po_code,
                'supplier_name' => $po->supplier->name,
                'status' => $po->status,
                'status_translated' => match ($po->status) {
                    'pending' => 'Chờ xử lý',
                    'processing' => 'Đang xử lý',
                    'completed' => 'Hoàn thành',
                    'cancelled' => 'Đã hủy',
                    default => $po->status,
                },
                'total_amount' => $po->total_amount,
                'url' => route('admin.purchase-orders.show', $po->id) // Tạo sẵn URL để điều hướng
            ];
        });

        return response()->json($results);
    }
}
