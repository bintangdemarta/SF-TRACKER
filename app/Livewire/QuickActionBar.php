<?php

namespace App\Livewire;

use App\Models\ShiftSession;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class QuickActionBar extends Component
{
    public function getHasActiveShiftProperty(): bool
    {
        return ShiftSession::where('user_id', Auth::id())
            ->where('status', 'active')
            ->exists();
    }

    #[On('shift-updated')]
    public function refresh(): void
    {
        // Computed property re-queries on every render; this forces the tick
        // so the bar switches between "Mulai Shift" and the action set
        // immediately after start/end shift instead of waiting for the
        // next unrelated re-render.
    }

    public function render()
    {
        return view('livewire.quick-action-bar');
    }
}
