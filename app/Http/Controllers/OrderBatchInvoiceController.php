<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\InvoiceLogo;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderBatchInvoiceController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $validated = $request->validate([
            'order_ids' => ['required', 'array', 'min:1', 'max:500'],
            'order_ids.*' => ['required', 'integer', 'distinct'],
        ], [
            'order_ids.required' => 'اختر طلبًا واحدًا على الأقل لإنشاء الفواتير.',
            'order_ids.min' => 'اختر طلبًا واحدًا على الأقل لإنشاء الفواتير.',
            'order_ids.max' => 'يمكن إنشاء 500 فاتورة كحد أقصى في الملف الواحد.',
        ]);

        $accountId = $request->user()?->account_id;
        abort_unless($accountId, 403);

        $requestedIds = collect($validated['order_ids'])->map(fn (mixed $id): int => (int) $id)->unique()->values();
        $orders = Order::query()
            ->where('account_id', $accountId)
            ->whereIn('id', $requestedIds)
            ->with(['account', 'customer', 'items', 'status', 'landingPage'])
            ->orderByDesc('created_at')
            ->get();

        abort_unless($orders->count() === $requestedIds->count(), 403);

        $options = new Options;
        $options->set('defaultFont', 'Cairo');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        $options->set('chroot', base_path());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('public.orders.batch-invoices', [
            'orders' => $orders,
            'logoData' => InvoiceLogo::dataUri($orders->first()?->account),
            'fontRegular' => base64_encode((string) file_get_contents(public_path('fonts/cairo/Cairo-Regular.ttf'))),
            'fontBold' => base64_encode((string) file_get_contents(public_path('fonts/cairo/Cairo-Bold.ttf'))),
        ])->render(), 'UTF-8');
        $dompdf->setPaper('a4');
        $dompdf->render();

        $filename = $orders->count() === 1
            ? 'invoice-'.$orders->first()->order_number.'.pdf'
            : 'order-invoices-'.now()->format('Y-m-d-His').'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }
}
