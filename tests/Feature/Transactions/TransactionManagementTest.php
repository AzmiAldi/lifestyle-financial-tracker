<?php

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Volt\Volt;

test('user can create income and expense transactions', function () {
    $user = User::factory()->create();
    $incomeCategory = Category::factory()->global()->create([
        'type' => TransactionType::Income,
        'name' => 'Salary',
    ]);
    $expenseCategory = Category::factory()->global()->create([
        'type' => TransactionType::Expense,
        'name' => 'Food',
    ]);

    $this->actingAs($user);

    Volt::test('transactions.create')
        ->set('type', TransactionType::Income->value)
        ->set('category_id', (string) $incomeCategory->id)
        ->set('amount', '1500.50')
        ->set('transaction_date', now()->toDateString())
        ->set('description', 'Monthly salary')
        ->call('save');

    Volt::test('transactions.create')
        ->set('type', TransactionType::Expense->value)
        ->set('category_id', (string) $expenseCategory->id)
        ->set('amount', '200.00')
        ->set('transaction_date', now()->toDateString())
        ->set('description', 'Lunch')
        ->call('save');

    expect(Transaction::query()->where('user_id', $user->id)->count())->toBe(2);
});

test('user can update owned transaction', function () {
    $user = User::factory()->create();
    $incomeCategory = Category::factory()->global()->create([
        'type' => TransactionType::Income,
    ]);
    $expenseCategory = Category::factory()->global()->create([
        'type' => TransactionType::Expense,
    ]);
    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $incomeCategory->id,
        'type' => TransactionType::Income,
        'amount' => 1000,
    ]);

    $this->actingAs($user);

    Volt::test('transactions.edit', ['transaction' => $transaction->id])
        ->set('type', TransactionType::Expense->value)
        ->set('category_id', (string) $expenseCategory->id)
        ->set('amount', '350.25')
        ->set('transaction_date', now()->toDateString())
        ->set('description', 'Updated value')
        ->call('save');

    $transaction->refresh();

    expect($transaction->type)->toBe(TransactionType::Expense)
        ->and((string) $transaction->amount)->toBe('350.25');
});

test('user can delete owned transaction', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
    ]);

    $this->actingAs($user);

    Volt::test('transactions.index')
        ->call('delete', $transaction->id);

    $this->assertDatabaseMissing('transactions', [
        'id' => $transaction->id,
    ]);
});

test('user cannot edit or delete transaction from another user', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $transaction = Transaction::factory()->create([
        'user_id' => $owner->id,
    ]);

    $this->actingAs($otherUser);

    $this->get(route('transactions.edit', $transaction))
        ->assertForbidden();

    Volt::test('transactions.index')
        ->call('delete', $transaction->id)
        ->assertForbidden();
});

test('dashboard summary displays totals from transaction service', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $incomeCategory = Category::factory()->global()->create([
        'type' => TransactionType::Income,
    ]);
    $expenseCategory = Category::factory()->global()->create([
        'type' => TransactionType::Expense,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $incomeCategory->id,
        'type' => TransactionType::Income,
        'amount' => 1000,
        'transaction_date' => now()->toDateString(),
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $expenseCategory->id,
        'type' => TransactionType::Expense,
        'amount' => 250,
        'transaction_date' => now()->toDateString(),
    ]);
    Transaction::factory()->create([
        'user_id' => $otherUser->id,
        'category_id' => $incomeCategory->id,
        'type' => TransactionType::Income,
        'amount' => 9999,
        'transaction_date' => now()->toDateString(),
    ]);

    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Rp 1.000')
        ->assertSee('Rp 250')
        ->assertSee('Rp 750');
});

test('global and owned categories are valid while other user category is rejected', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $globalCategory = Category::factory()->global()->create([
        'type' => TransactionType::Expense,
    ]);
    $ownedCategory = Category::factory()->create([
        'user_id' => $user->id,
        'type' => TransactionType::Expense,
    ]);
    $foreignCategory = Category::factory()->create([
        'user_id' => $otherUser->id,
        'type' => TransactionType::Expense,
    ]);

    $this->actingAs($user);

    Volt::test('transactions.create')
        ->set('type', TransactionType::Expense->value)
        ->set('category_id', (string) $globalCategory->id)
        ->set('amount', '50')
        ->set('transaction_date', now()->toDateString())
        ->call('save');

    Volt::test('transactions.create')
        ->set('type', TransactionType::Expense->value)
        ->set('category_id', (string) $ownedCategory->id)
        ->set('amount', '25')
        ->set('transaction_date', now()->toDateString())
        ->call('save');

    Volt::test('transactions.create')
        ->set('type', TransactionType::Expense->value)
        ->set('category_id', (string) $foreignCategory->id)
        ->set('amount', '10')
        ->set('transaction_date', now()->toDateString())
        ->call('save')
        ->assertHasErrors(['category_id']);
});

test('transaction index shows empty state when user has no transactions', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('transactions.index'))
        ->assertSuccessful()
        ->assertSee('Belum ada transaksi.');
});

test('rupiah formatting in ui does not affect backend transaction amount precision', function () {
    $user = User::factory()->create();
    $category = Category::factory()->global()->create([
        'type' => TransactionType::Income,
    ]);

    $this->actingAs($user);

    Volt::test('transactions.create')
        ->set('type', TransactionType::Income->value)
        ->set('category_id', (string) $category->id)
        ->set('amount', '150000.75')
        ->set('transaction_date', now()->toDateString())
        ->call('save');

    $transaction = Transaction::query()->where('user_id', $user->id)->firstOrFail();

    expect((string) $transaction->amount)->toBe('150000.75');
});

test('transaction behavior note can be saved optionally', function () {
    $user = User::factory()->create();
    $category = Category::factory()->global()->create([
        'type' => TransactionType::Expense,
    ]);

    $this->actingAs($user);

    Volt::test('transactions.create')
        ->set('type', TransactionType::Expense->value)
        ->set('category_id', (string) $category->id)
        ->set('amount', '75000')
        ->set('transaction_date', now()->toDateString())
        ->set('behavior_note', 'Impulse purchase after overtime')
        ->call('save');

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'behavior_note' => 'Impulse purchase after overtime',
    ]);
});
