<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_model_changes_and_masks_sensitive_values(): void
    {
        $account = Account::create(['name' => 'Audit Account', 'slug' => 'audit-account']);
        $user = User::factory()->create(['account_id' => $account->id, 'name' => 'Auditor']);
        $this->actingAs($user);

        $customer = Customer::create([
            'account_id' => $account->id,
            'name' => 'Original Name',
            'phone' => '0500000000',
        ]);
        $customer->update(['name' => 'Updated Name']);
        $customer->delete();

        $created = AuditLog::query()->where('auditable_type', Customer::class)->where('auditable_id', $customer->id)->where('event', 'created')->firstOrFail();
        $updated = AuditLog::query()->where('auditable_type', Customer::class)->where('auditable_id', $customer->id)->where('event', 'updated')->firstOrFail();
        $deleted = AuditLog::query()->where('auditable_type', Customer::class)->where('auditable_id', $customer->id)->where('event', 'deleted')->firstOrFail();

        self::assertSame($user->id, $created->user_id);
        self::assertSame('العملاء', $created->module);
        self::assertSame('Original Name', $updated->old_values['name']);
        self::assertSame('Updated Name', $updated->new_values['name']);
        self::assertSame('deleted', $deleted->event);

        $userCreation = AuditLog::query()->where('auditable_type', User::class)->where('auditable_id', $user->id)->where('event', 'created')->firstOrFail();
        self::assertSame('[محجوب]', $userCreation->new_values['password']);
    }
}
