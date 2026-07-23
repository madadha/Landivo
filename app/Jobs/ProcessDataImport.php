<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\DataTransfer;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProcessDataImport implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1200;

    public int $tries = 1;

    public function __construct(public int $transferId) {}

    public function handle(): void
    {
        $transfer = DataTransfer::query()->findOrFail($this->transferId);

        if ($transfer->status !== 'queued' || blank($transfer->source_path)) {
            return;
        }

        $transfer->update(['status' => 'processing', 'started_at' => now(), 'error_message' => null]);
        $handle = fopen(Storage::disk('local')->path($transfer->source_path), 'rb');

        if ($handle === false) {
            throw new \RuntimeException('تعذر فتح ملف الاستيراد.');
        }

        $errors = [];

        try {
            $delimiter = $this->detectDelimiter($handle);
            $headers = $this->readHeaders($handle, $delimiter);
            $total = max(0, $this->lineCount($transfer->source_path) - 1);
            $transfer->update(['total_rows' => $total]);

            $line = 1;
            while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
                $line++;
                if ($values === [null] || $values === []) {
                    continue;
                }

                $row = array_combine($headers, array_pad(array_slice($values, 0, count($headers)), count($headers), null));

                try {
                    match ($transfer->entity) {
                        'customers' => $this->importCustomer($transfer, $row),
                        'products' => $this->importProduct($transfer, $row),
                        default => throw new \InvalidArgumentException('الاستيراد متاح للعملاء والمنتجات فقط.'),
                    };
                    DataTransfer::query()->whereKey($transfer->getKey())->increment('succeeded_rows');
                } catch (Throwable $exception) {
                    DataTransfer::query()->whereKey($transfer->getKey())->increment('failed_rows');
                    if (count($errors) < 50) {
                        $errors[] = ['line' => $line, 'message' => $exception->getMessage()];
                    }
                }

                DataTransfer::query()->whereKey($transfer->getKey())->increment('processed_rows');
            }

            fclose($handle);
            $transfer->refresh()->update([
                'status' => 'completed',
                'completed_at' => now(),
                'metadata' => array_merge((array) $transfer->metadata, ['row_errors' => $errors]),
            ]);
        } catch (Throwable $exception) {
            fclose($handle);
            $this->markFailed($exception);
            throw $exception;
        }
    }

    private function importCustomer(DataTransfer $transfer, array $row): void
    {
        $name = trim((string) ($row['name'] ?? ''));
        $phone = trim((string) ($row['phone'] ?? ''));

        if ($name === '' || $phone === '') {
            throw new \InvalidArgumentException('الاسم والهاتف مطلوبان.');
        }

        Customer::query()->updateOrCreate(
            ['account_id' => $transfer->account_id, 'phone' => $phone],
            [
                'name' => $name,
                'email' => $this->nullable($row['email'] ?? null),
                'city' => $this->nullable($row['city'] ?? null),
                'country' => $this->nullable($row['country'] ?? null),
            ],
        );
    }

    private function importProduct(DataTransfer $transfer, array $row): void
    {
        $sku = trim((string) ($row['sku'] ?? ''));
        $nameAr = trim((string) ($row['name_ar'] ?? ''));
        $nameEn = trim((string) ($row['name_en'] ?? ''));

        if ($sku === '' || ($nameAr === '' && $nameEn === '')) {
            throw new \InvalidArgumentException('SKU واسم واحد على الأقل مطلوبان.');
        }

        $status = in_array($row['status'] ?? null, ['draft', 'active', 'archived'], true)
            ? $row['status']
            : 'draft';

        $product = Product::query()->updateOrCreate(
            ['account_id' => $transfer->account_id, 'sku' => $sku],
            [
                'price' => max(0, (float) ($row['price'] ?? 0)),
                'compare_at_price' => filled($row['compare_at_price'] ?? null) ? max(0, (float) $row['compare_at_price']) : null,
                'currency' => strtoupper(substr(trim((string) ($row['currency'] ?? 'AED')), 0, 3)),
                'quantity' => max(0, (int) ($row['quantity'] ?? 0)),
                'status' => $status,
            ],
        );

        foreach (['ar' => $nameAr, 'en' => $nameEn] as $locale => $name) {
            if ($name === '') {
                continue;
            }
            $product->translations()->updateOrCreate(
                ['locale' => $locale],
                ['name' => $name, 'description' => $this->nullable($row["description_{$locale}"] ?? null)],
            );
        }
    }

    private function detectDelimiter($handle): string
    {
        $position = ftell($handle);
        $line = (string) fgets($handle);
        fseek($handle, $position);
        $counts = [',' => substr_count($line, ','), ';' => substr_count($line, ';'), "\t" => substr_count($line, "\t")];

        return (string) array_search(max($counts), $counts, true);
    }

    private function readHeaders($handle, string $delimiter): array
    {
        $headers = fgetcsv($handle, 0, $delimiter);
        if (! is_array($headers) || $headers === []) {
            throw new \InvalidArgumentException('ملف CSV لا يحتوي على عناوين أعمدة.');
        }

        return array_map(function ($header): string {
            $header = preg_replace('/^\xEF\xBB\xBF/', '', trim((string) $header));

            return Str::of($header)->lower()->replace([' ', '-'], '_')->toString();
        }, $headers);
    }

    private function lineCount(string $path): int
    {
        $file = new \SplFileObject(Storage::disk('local')->path($path), 'r');
        $file->seek(PHP_INT_MAX);

        return $file->key() + 1;
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function markFailed(Throwable $exception): void
    {
        DataTransfer::query()->whereKey($this->transferId)->update([
            'status' => 'failed',
            'error_message' => Str::limit($exception->getMessage(), 2000),
            'completed_at' => now(),
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception) {
            $this->markFailed($exception);
        }
    }
}
