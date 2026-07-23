<?php

namespace Tests\Feature;

use App\Filament\Pages\DataTransferCenter;
use App\Jobs\ProcessDataExport;
use App\Jobs\ProcessDataImport;
use App\Models\Account;
use App\Models\DataTransfer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DataTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_customers_from_csv_and_tracks_progress(): void
    {
        Storage::fake('local');
        $account = Account::query()->create(['name' => 'Test', 'slug' => 'test']);
        $path = 'data-transfers/test/customers.csv';
        Storage::disk('local')->put($path, "name,phone,email,city,country\nريان,0501000000,test@example.com,دبي,الإمارات\n");

        $transfer = DataTransfer::query()->create([
            'account_id' => $account->id,
            'type' => 'import',
            'entity' => 'customers',
            'status' => 'queued',
            'source_path' => $path,
        ]);

        (new ProcessDataImport($transfer->id))->handle();

        $this->assertDatabaseHas('customers', [
            'account_id' => $account->id,
            'phone' => '0501000000',
            'email' => 'test@example.com',
        ]);
        $this->assertSame('completed', $transfer->fresh()->status);
        $this->assertSame(1, (int) $transfer->fresh()->succeeded_rows);
        $this->assertSame(100, $transfer->fresh()->progressPercentage());
    }

    public function test_it_exports_products_to_a_private_csv_file(): void
    {
        Storage::fake('local');
        $account = Account::query()->create(['name' => 'Test', 'slug' => 'test']);
        $product = Product::query()->create([
            'account_id' => $account->id,
            'sku' => 'OIL-16',
            'price' => 650,
            'currency' => 'AED',
            'quantity' => 20,
            'status' => 'active',
        ]);
        $product->translations()->create(['locale' => 'ar', 'name' => 'زيت زيتون']);

        $transfer = DataTransfer::query()->create([
            'account_id' => $account->id,
            'type' => 'export',
            'entity' => 'products',
            'status' => 'queued',
        ]);

        (new ProcessDataExport($transfer->id))->handle();
        $transfer->refresh();

        $this->assertSame('completed', $transfer->status);
        Storage::disk('local')->assertExists($transfer->result_path);
        $this->assertStringContainsString('OIL-16', Storage::disk('local')->get($transfer->result_path));
        $this->assertSame(1, (int) $transfer->succeeded_rows);
    }

    public function test_it_clears_only_finished_transfers_and_their_files(): void
    {
        Storage::fake('local');
        $account = Account::query()->create(['name' => 'Test', 'slug' => 'test']);
        $user = User::factory()->create(['account_id' => $account->id]);
        Storage::disk('local')->put('data-transfers/result.csv', 'result');

        $completed = DataTransfer::query()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'type' => 'export',
            'entity' => 'products',
            'status' => 'completed',
            'result_path' => 'data-transfers/result.csv',
        ]);
        $running = DataTransfer::query()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'type' => 'export',
            'entity' => 'orders',
            'status' => 'processing',
        ]);

        Livewire::actingAs($user)
            ->test(DataTransferCenter::class)
            ->call('clearHistory')
            ->assertHasNoErrors();

        $this->assertModelMissing($completed);
        $this->assertModelExists($running);
        Storage::disk('local')->assertMissing('data-transfers/result.csv');
    }
}
