<?php

namespace App\Http\Controllers;

use App\Models\DataTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataTransferController extends Controller
{
    public function download(Request $request, DataTransfer $dataTransfer): StreamedResponse
    {
        abort_unless((int) $dataTransfer->account_id === (int) $request->user()?->account_id, 403);
        abort_unless($dataTransfer->status === 'completed' && filled($dataTransfer->result_path), 404);
        abort_unless(Storage::disk('local')->exists($dataTransfer->result_path), 404);

        $filename = "{$dataTransfer->entity}-export-{$dataTransfer->created_at->format('Ymd-His')}.csv";

        return Storage::disk('local')->download($dataTransfer->result_path, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function template(Request $request, string $entity): StreamedResponse
    {
        abort_unless(in_array($entity, ['customers', 'products'], true), 404);

        $headers = $entity === 'customers'
            ? ['name', 'phone', 'email', 'city', 'country']
            : ['sku', 'name_ar', 'name_en', 'description_ar', 'description_en', 'price', 'compare_at_price', 'currency', 'quantity', 'status'];

        return response()->streamDownload(function () use ($headers): void {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);
            fclose($handle);
        }, "{$entity}-import-template.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
