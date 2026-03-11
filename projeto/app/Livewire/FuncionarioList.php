<?php

namespace App\Livewire;

use App\Models\Funcionario;
use Livewire\Component;
use Livewire\WithPagination;

class FuncionarioList extends Component
{
    use WithPagination;

    public string $busca = '';

    public function paginationView(): string
    {
        return 'vendor.livewire.pagination';
    }

    public function updatingBusca(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $funcionarios = Funcionario::query()
            ->when($this->busca, fn ($q) => $q->where('nome', 'like', "%{$this->busca}%"))
            ->orderBy('nome')
            ->paginate(15);

        return view('livewire.funcionario-list', compact('funcionarios'));
    }
}
