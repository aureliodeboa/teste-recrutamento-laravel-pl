<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funcionários — Painel Administrativo</title>
    @livewireStyles
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 960px;
            margin: 2rem auto;
            padding: 0 1rem;
            background: #fafafa;
            color: #333;
        }
        h1 { font-size: 1.5rem; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <h1>Funcionários</h1>
    <livewire:funcionario-list />
    @livewireScripts
</body>
</html>
