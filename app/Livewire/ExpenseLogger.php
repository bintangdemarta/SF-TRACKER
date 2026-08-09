<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ShiftSession;
use App\Services\WalletReconciliationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ExpenseLogger extends Component
{
    public Collection $categories;

    public ?int $category_id = null;

    public string $amount = '';

    public string $payment_source = 'cash';

    public string $odometer = '';

    public string $notes = '';

    public bool $is_reimbursable = false;

    public ?string $warningMessage = null;

    protected $listeners = ['wallet-updated' => '$refresh'];

    public function mount(): void
    {
        $this->categories = ExpenseCategory::where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    public function getSelectedCategoryProperty(): ?ExpenseCategory
    {
        if (! $this->category_id) {
            return null;
        }

        return $this->categories->firstWhere('id', $this->category_id);
    }

    public function getShowOdometerFieldProperty(): bool
    {
        return $this->selectedCategory?->type === 'bbm';
    }

    public function getActiveShiftProperty(): ?ShiftSession
    {
        return ShiftSession::where('user_id', Auth::id())
            ->where('status', 'active')
            ->latest('started_at')
            ->first();
    }

    public function getRecentExpensesProperty()
    {
        return Expense::where('user_id', Auth::id())
            ->with('category')
            ->latest()
            ->take(5)
            ->get();
    }

    public function updatedCategoryId(): void
    {
        if (! $this->showOdometerField) {
            $this->odometer = '';
        }
    }

    public function logExpense(WalletReconciliationService $wallet): void
    {
        $activeShift = $this->activeShift;

        $rules = [
            'category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'payment_source' => ['required', 'in:cash,digital_balance'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_reimbursable' => ['boolean'],
        ];

        if ($this->showOdometerField) {
            $minOdometer = $activeShift?->start_odometer ?? 0;
            $rules['odometer'] = ['nullable', 'integer', 'min:'.$minOdometer];
        }

        $validated = $this->validate($rules, [
            'odometer.min' => 'Odometer tidak boleh lebih kecil dari odometer awal shift ('.($activeShift?->start_odometer ?? 0).' km).',
        ]);

        $expense = Expense::create([
            'user_id' => Auth::id(),
            'shift_session_id' => $activeShift?->id,
            'category_id' => $validated['category_id'],
            'amount' => $validated['amount'],
            'payment_source' => $validated['payment_source'],
            'odometer' => $this->showOdometerField ? ($validated['odometer'] ?? null) : null,
            'notes' => $validated['notes'] ?: null,
            'is_reimbursable' => $validated['is_reimbursable'] ?? false,
        ]);

        $this->warningMessage = $wallet->recordExpense($expense);

        $this->reset(['category_id', 'amount', 'odometer', 'notes', 'is_reimbursable']);
        $this->payment_source = 'cash';
        $this->dispatch('wallet-updated');
    }

    public function render()
    {
        return view('livewire.expense-logger');
    }
}
