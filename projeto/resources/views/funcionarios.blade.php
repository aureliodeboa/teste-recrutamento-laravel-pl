<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funcionários — Painel Administrativo</title>
    @livewireStyles
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f4f6f9;
            color: #2d3748;
            min-height: 100vh;
        }

        .page-wrapper {
            max-width: 980px;
            margin: 0 auto;
            padding: 2.5rem 1.5rem;
        }

        .page-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        .page-header svg {
            width: 28px;
            height: 28px;
            color: #4a6cf7;
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a202c;
            letter-spacing: -0.02em;
        }

        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            overflow: hidden;
        }

        .search-box {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #edf0f4;
        }

        .search-box input {
            width: 100%;
            padding: 0.6rem 0.9rem 0.6rem 2.4rem;
            border: 1px solid #dde1e8;
            border-radius: 7px;
            font-size: 0.9rem;
            color: #2d3748;
            background: #f8f9fb url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%23a0aec0' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='7' cy='7' r='5'/%3E%3Cpath d='m13 13-3-3'/%3E%3C/svg%3E") 0.75rem center no-repeat;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .search-box input:focus {
            border-color: #4a6cf7;
            box-shadow: 0 0 0 3px rgba(74,108,247,0.12);
            background-color: #fff;
        }

        .search-box input::placeholder { color: #a0aec0; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            padding: 0.75rem 1.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #8895a7;
            text-align: left;
            background: #fafbfc;
            border-bottom: 1px solid #edf0f4;
        }

        thead th:last-child { text-align: right; }

        tbody tr {
            transition: background 0.15s;
        }

        tbody tr:hover {
            background: #f7f9fc;
        }

        tbody td {
            padding: 0.8rem 1.5rem;
            font-size: 0.9rem;
            border-bottom: 1px solid #f0f2f5;
        }

        tbody td:first-child {
            color: #8895a7;
            font-size: 0.8rem;
            font-weight: 500;
        }

        tbody td:nth-child(2) {
            font-weight: 600;
            color: #1a202c;
        }

        tbody td:nth-child(3) {
            color: #64748b;
            font-family: 'SF Mono', SFMono-Regular, Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 0.82rem;
        }

        tbody td:last-child {
            text-align: right;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
            color: #16a34a;
        }

        .empty-state {
            padding: 3rem 1rem;
            text-align: center;
            color: #a0aec0;
            font-size: 0.95rem;
        }

        .pagination-wrapper {
            padding: 1rem 1.5rem;
            border-top: 1px solid #edf0f4;
            display: flex;
            justify-content: center;
        }

        .custom-pagination {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }

        .custom-pagination .page-item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            height: 2rem;
            padding: 0 0.4rem;
            font-size: 0.82rem;
            font-weight: 500;
            border-radius: 6px;
            border: none;
            background: transparent;
            color: #4a5568;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
            font-family: inherit;
            line-height: 1;
        }

        .custom-pagination .page-item:hover:not(.active):not(.disabled):not(.dots) {
            background: #edf0f7;
            color: #4a6cf7;
        }

        .custom-pagination .page-item.active {
            background: #4a6cf7;
            color: #fff;
            font-weight: 700;
            cursor: default;
        }

        .custom-pagination .page-item.disabled {
            color: #cbd5e0;
            cursor: default;
        }

        .custom-pagination .page-item.dots {
            cursor: default;
            color: #a0aec0;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="page-header">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            <h1>Funcionários</h1>
        </div>

        <div class="card">
            <livewire:funcionario-list />
        </div>
    </div>
    @livewireScripts
</body>
</html>
