<div>
    <div style="margin-bottom: 1rem;">
        <input
            type="text"
            wire:model.live.debounce.300ms="busca"
            placeholder="Buscar por nome..."
            style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; font-size: 1rem;"
        >
    </div>

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f5f5f5;">
                <th style="padding: 0.5rem; text-align: left; border-bottom: 2px solid #ddd;">ID</th>
                <th style="padding: 0.5rem; text-align: left; border-bottom: 2px solid #ddd;">Nome</th>
                <th style="padding: 0.5rem; text-align: left; border-bottom: 2px solid #ddd;">Login</th>
                <th style="padding: 0.5rem; text-align: right; border-bottom: 2px solid #ddd;">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($funcionarios as $func)
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.5rem;">{{ $func->id }}</td>
                    <td style="padding: 0.5rem;">{{ $func->nome }}</td>
                    <td style="padding: 0.5rem;">{{ $func->login }}</td>
                    <td style="padding: 0.5rem; text-align: right;">R$ {{ number_format($func->saldo, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="padding: 1rem; text-align: center; color: #999;">Nenhum funcionário encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 1rem;">
        {{ $funcionarios->links() }}
    </div>
</div>
