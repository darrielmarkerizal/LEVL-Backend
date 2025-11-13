<?php

use Modules\Operations\Models\SystemAudit;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('system audit can be created', function () {
    $user = \Modules\Auth\Models\User::factory()->create();

    $audit = SystemAudit::create([
        'action' => 'create',
        'user_id' => $user->id,
        'module' => 'Schemes',
        'target_table' => 'courses',
        'target_id' => 1,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Agent',
        'meta' => ['test' => 'data'],
    ]);

    assertDatabaseHas('system_audits', [
        'id' => $audit->id,
        'action' => 'create',
        'module' => 'Schemes',
    ]);
});

test('system audit meta is casted to array', function () {
    $audit = SystemAudit::create([
        'action' => 'update',
        'meta' => ['key' => 'value'],
    ]);

    $audit->refresh();
    expect($audit->meta)->toBeArray();
    expect($audit->meta['key'])->toEqual('value');
});