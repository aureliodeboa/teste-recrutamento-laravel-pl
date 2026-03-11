<div>
    <div class="search-box">
        <input
            type="text"
            wire:model.live.debounce.300ms="busca"
            placeholder="Buscar funcionário por nome..."
        >
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Login</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($funcionarios as $func)
                <tr>
                    <td>#{{ $func->id }}</td>
                    <td>{{ $func->nome }}</td>
                    <td>{{ $func->login }}</td>
                    <td>R$ {{ number_format($func->saldo, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="empty-state">Nenhum funcionário encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($funcionarios->hasPages())
        <div class="pagination-wrapper">
            {{ $funcionarios->links() }}
        </div>
    @endif
</div>
