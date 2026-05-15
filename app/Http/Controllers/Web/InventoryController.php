<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryController extends Controller
{
    protected function filteredStocksQuery(Request $request)
    {
        $query = Stock::with(['product', 'warehouse'])->whereHas('product');

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('product', function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('sku', 'like', "%$s%");
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 15);
        if (!in_array($perPage, [5, 10, 15, 20, 50], true)) {
            $perPage = 15;
        }
        $query = $this->filteredStocksQuery($request);

        $stocks     = $query->paginate($perPage)->withQueryString();
        $warehouses = Warehouse::all();

        $statsBaseQuery = Stock::whereHas('product');

        $stats = [
            'total'            => (clone $statsBaseQuery)->count(),
            'low_stock'        => (clone $statsBaseQuery)->whereRaw('quantity - reserved_qty <= 10')->where('quantity', '>', 0)->count(),
            'out_of_stock'     => (clone $statsBaseQuery)->whereRaw('quantity - reserved_qty <= 0')->count(),
            'warehouses_count' => Warehouse::count(),
            'total_reserved'   => (float) (clone $statsBaseQuery)->sum('reserved_qty'),
            'total_dispatched' => (float) (clone $statsBaseQuery)->sum('dispatched_qty'),
        ];

        if ($request->ajax()) {
            return response()->json([
                'table' => view('inventory.partials.table', compact('stocks'))->render(),
                'stats' => $stats,
            ]);
        }

        return view('inventory.index', compact('stocks', 'warehouses', 'stats'));
    }

    public function export(Request $request): StreamedResponse
    {
        $stocks = $this->filteredStocksQuery($request)
            ->orderBy('id')
            ->get();

        $filename = 'inventory-export-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($stocks) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'product_sku',
                'product_name',
                'warehouse_code',
                'warehouse_name',
                'quantity',
                'reserved_qty',
                'available_qty',
                'dispatched_qty',
                'committed_qty',
                'in_transit_qty',
                'status',
                'min_stock_level',
            ]);

            foreach ($stocks as $stock) {
                $available = max(0, (float) $stock->quantity - (float) $stock->reserved_qty);
                fputcsv($out, [
                    $stock->product?->sku,
                    $stock->product?->name,
                    $stock->warehouse?->code,
                    $stock->warehouse?->name,
                    $stock->quantity,
                    $stock->reserved_qty,
                    $available,
                    $stock->dispatched_qty,
                    $stock->committed_qty,
                    $stock->in_transit_qty,
                    $stock->status,
                    $stock->product?->min_stock_level,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function import(Request $request, InventoryService $inventoryService)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return back()->with('error', 'Unable to read uploaded file.');
        }

        $firstRow = fgetcsv($handle);
        if ($firstRow === false) {
            fclose($handle);
            return back()->with('error', 'CSV file is empty.');
        }

        $normalized = array_map(
            fn ($v) => strtolower(trim((string) $v)),
            $firstRow
        );

        $hasHeader = in_array('product_sku', $normalized, true) || in_array('sku', $normalized, true);
        $updated = 0;
        $skipped = 0;
        $lineNo = 1;

        $extractByHeader = function (array $row, array $header, array $keys): ?string {
            foreach ($keys as $key) {
                $index = array_search($key, $header, true);
                if ($index !== false) {
                    return isset($row[$index]) ? trim((string) $row[$index]) : null;
                }
            }
            return null;
        };

        if (!$hasHeader) {
            $sku = trim((string) ($firstRow[0] ?? ''));
            $warehouseCode = trim((string) ($firstRow[1] ?? ''));
            $quantity = trim((string) ($firstRow[2] ?? ''));

            if ($sku === '' || $warehouseCode === '' || !is_numeric($quantity) || (float) $quantity < 0) {
                $skipped++;
            } else {
                $product = Product::where('sku', $sku)->first();
                $warehouse = Warehouse::where('code', $warehouseCode)->first();

                if (!$product || !$warehouse) {
                    $skipped++;
                } else {
                    $inventoryService->setStock($product->id, $warehouse->id, (float) $quantity);
                    $updated++;
                }
            }
        }

        while (($row = fgetcsv($handle)) !== false) {
            $lineNo++;

            if ($hasHeader) {
                $sku = $extractByHeader($row, $normalized, ['product_sku', 'sku']);
                $warehouseCode = $extractByHeader($row, $normalized, ['warehouse_code', 'warehouse']);
                $quantity = $extractByHeader($row, $normalized, ['quantity', 'qty']);
            } else {
                $sku = trim((string) ($row[0] ?? ''));
                $warehouseCode = trim((string) ($row[1] ?? ''));
                $quantity = trim((string) ($row[2] ?? ''));
            }

            if ($sku === '' && $warehouseCode === '' && $quantity === '') {
                continue;
            }

            if ($sku === '' || $warehouseCode === '' || !is_numeric($quantity) || (float) $quantity < 0) {
                $skipped++;
                continue;
            }

            $product = Product::where('sku', $sku)->first();
            $warehouse = Warehouse::where('code', $warehouseCode)->first();

            if (!$product || !$warehouse) {
                $skipped++;
                continue;
            }

            $inventoryService->setStock($product->id, $warehouse->id, (float) $quantity);
            $updated++;
        }

        fclose($handle);

        $message = "Inventory import completed. Updated {$updated} row(s).";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} invalid row(s).";
        }

        return redirect()->route('inventory.index')->with('success', $message);
    }

    public function update(Request $request, Stock $stock, InventoryService $inventoryService)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0',
        ]);

        $inventoryService->setStock($stock->product_id, $stock->warehouse_id, $request->quantity);

        return back()->with('success', 'Inventory updated successfully.');
    }
}
