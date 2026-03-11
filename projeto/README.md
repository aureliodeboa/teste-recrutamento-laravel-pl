# Sistema de Bonificação de Funcionários — Moeda Digital

Sistema de gestão de bonificação de funcionários com moeda digital. Permite cadastrar funcionários, registrar movimentações de entrada/saída de créditos e gerar relatórios consolidados.

## Como subir o projeto

```bash
git clone https://github.com/aureliodeboa/teste-recrutamento-laravel-pl.git
cd teste-recrutamento-laravel-pl/projeto
docker compose up -d --build
```

O ambiente sobe automaticamente: o entrypoint cria o `.env`, gera a `APP_KEY`, roda as migrations e o seeder. O primeiro build pode levar alguns minutos (download das imagens + composer install). O seeder popula 5.000 funcionários com movimentações — esse processo leva cerca de 2 minutos.

Acesse `http://localhost:8000` para verificar que o sistema está no ar.

O diferencial pedido no enunciado (listagem em Livewire) está disponível em:

- `http://localhost:8000/funcionarios` — página com listagem reativa de funcionários (busca + paginação) construída com Livewire.

Para parar:

```bash
docker compose down -v
```

## Como rodar os testes

```bash
docker compose exec app php artisan test
```

Os testes usam SQLite in-memory (não dependem do MySQL) e cobrem 30 cenários críticos do sistema.

## Credenciais padrão

| Tipo | Login | Senha |
|------|-------|-------|
| Administrador | `admin` | `123456` |

## Endpoints da API

| Método | Rota | Descrição |
|--------|------|-----------|
| POST | `/api/login` | Autenticação (retorna token) |
| POST | `/api/logout` | Encerrar sessão |
| GET | `/api/funcionarios` | Listar funcionários (paginado) |
| GET | `/api/funcionarios/{id}` | Detalhe de um funcionário |
| POST | `/api/funcionarios` | Criar funcionário |
| PUT | `/api/funcionarios/{id}` | Atualizar funcionário |
| DELETE | `/api/funcionarios/{id}` | Remover funcionário (soft delete) |
| GET | `/api/funcionarios/{id}/movimentacoes` | Listar movimentações |
| POST | `/api/funcionarios/{id}/movimentacoes` | Registrar movimentação |
| GET | `/api/relatorio` | Relatório consolidado |

> Todas as rotas (exceto login) exigem header `Authorization: Bearer {token}`.

---

## Code Review

Análise do código legado realizada antes de qualquer modificação. Todos os problemas foram catalogados por severidade, com arquivo, linha e risco real.

### Problemas encontrados

#### CRÍTICO — SQL Injection em todos os controllers

Todas as queries do sistema usam concatenação/interpolação de variáveis diretamente do input do usuário, sem nenhum binding de parâmetros. Isso permite que um atacante execute qualquer SQL no banco de dados.

| Arquivo | Linha(s) | Detalhe |
|---------|----------|---------|
| `AuthController.php` | 15 | `"SELECT * FROM administradores WHERE login = '$login' AND senha = '$senha'"` — permite bypass completo de login com `' OR '1'='1` |
| `AuthController.php` | 25, 43 | UPDATE com interpolação direta de token e id |
| `FuncionarioController.php` | 12, 19, 34, 39, 51, 56, 63 | Todas as queries usam interpolação do input do usuário |
| `MovimentacaoController.php` | 12, 23, 37, 40, 46, 49 | Todas as queries com interpolação, incluindo valores financeiros |
| `RelatorioController.php` | 11, 15 | Queries com interpolação de IDs |

**Risco real:** um atacante pode ler, alterar ou deletar todos os dados do banco, incluindo senhas e saldos.

#### CRÍTICO — Senhas armazenadas em texto puro

Nenhuma senha no sistema é hasheada. Qualquer acesso ao banco (leak, backup vazado, SQL injection) expõe todas as credenciais.

| Arquivo | Linha(s) | Detalhe |
|---------|----------|---------|
| `2024_01_01_000000_create_tables.php` | 15, 24 | Colunas `senha` definidas como `string` sem indicação de hash |
| `DatabaseSeeder.php` | 14, 34 | Senhas seedadas como `'123456'` em texto puro |
| `AuthController.php` | 15 | Comparação de senha direto no SQL (sem `Hash::check()`) |
| `FuncionarioController.php` | 39 | Senha do funcionário armazenada exatamente como recebida do request |

**Risco real:** vazamento do banco expõe todas as credenciais de administradores e funcionários.

#### CRÍTICO — Senha exposta na resposta da API

| Arquivo | Linha | Detalhe |
|---------|-------|---------|
| `AuthController.php` | 34 | `'senha' => $admin->senha` é retornado no JSON de login |

**Risco real:** qualquer cliente que faz login recebe a senha em texto puro na resposta. Se o tráfego for interceptado ou logado, a senha é comprometida.

#### CRÍTICO — Nenhuma rota protegida por autenticação

| Arquivo | Linha(s) | Detalhe |
|---------|----------|---------|
| `routes/api.php` | 14-25 | Todas as rotas (CRUD de funcionários, movimentações, relatório) são públicas e acessíveis sem autenticação |
| `bootstrap/app.php` | 14-16 | Middleware vazio — nenhum middleware customizado registrado |

**Risco real:** qualquer pessoa na internet pode listar, criar, alterar e deletar funcionários e movimentações financeiras sem nenhuma credencial.

#### CRÍTICO — Operações financeiras sem transação de banco

| Arquivo | Linha(s) | Detalhe |
|---------|----------|---------|
| `MovimentacaoController.php` | 37-40 | Para saída: INSERT da movimentação e UPDATE do saldo são queries separadas. Se o UPDATE falhar, a movimentação fica registrada mas o saldo não é atualizado |
| `MovimentacaoController.php` | 46-49 | Mesmo problema para entradas |

**Risco real:** em caso de falha parcial (timeout, crash, erro de conexão), o saldo do funcionário fica inconsistente com as movimentações registradas. Dinheiro pode "sumir" ou "aparecer" sem registro correspondente.

#### CRÍTICO — Race condition no saldo (double-spending)

| Arquivo | Linha(s) | Detalhe |
|---------|----------|---------|
| `MovimentacaoController.php` | 23, 30, 33, 40 | O fluxo é: lê o saldo (linha 30) → verifica se é suficiente (linha 33) → atualiza (linha 40). Não há `SELECT ... FOR UPDATE` nem lock pessimista |

**Risco real:** duas requisições simultâneas de saída podem ler o mesmo saldo (ex: R$ 100), ambas verificarem que é suficiente, e ambas debitarem — resultando em saldo negativo (ex: -R$ 80 quando o limite era R$ 100). Problema clássico de concorrência em operações financeiras.

#### ALTO — Token de autenticação fraco e previsível

| Arquivo | Linha | Detalhe |
|---------|-------|---------|
| `AuthController.php` | 23 | `rand(100000, 999999)` gera um token numérico de 6 dígitos com apenas 900.000 valores possíveis |

**Risco real:** um atacante pode fazer brute-force de todos os tokens possíveis em poucos minutos, assumindo a identidade de qualquer administrador logado.

#### ALTO — Zero validação de input em toda a API

| Arquivo | Detalhe |
|---------|---------|
| `AuthController.php` | `login` e `senha` não são validados como campos obrigatórios |
| `FuncionarioController.php` | `nome`, `login` e `senha` não são validados (formato, tamanho mínimo, obrigatoriedade) |
| `MovimentacaoController.php` | `tipo` não é validado como enum (`entrada`/`saida`), `valor` aceita negativo, zero ou valor não-numérico, `descricao` não é sanitizada |

**Risco real:** dados inválidos podem ser inseridos no banco, causando comportamento imprevisível. Valores negativos em movimentações poderiam inverter a lógica de débito/crédito.

#### ALTO — Problema de performance N+1 no relatório

| Arquivo | Linha(s) | Detalhe |
|---------|----------|---------|
| `RelatorioController.php` | 11, 15 | Busca todos os funcionários (linha 11) e para cada um executa uma query individual de movimentações (linha 15). Com 5.000 funcionários no seeder, são 5.001 queries por requisição |

**Risco real:** endpoint extremamente lento, provável timeout em produção. Com volume real de dados, pode derrubar o banco.

#### MÉDIO — Sem paginação nas listagens

| Arquivo | Linha | Detalhe |
|---------|-------|---------|
| `FuncionarioController.php` | 12 | Retorna todos os 5.000+ funcionários em uma única resposta JSON |
| `MovimentacaoController.php` | 12 | Retorna todas as movimentações de um funcionário (até 500+) sem limite |
| `RelatorioController.php` | 11 | Relatório de todos os funcionários sem paginação |

**Risco real:** respostas enormes (megabytes de JSON), consumo excessivo de memória no servidor, experiência degradada no cliente.

#### MÉDIO — Ausência de Models Eloquent

Não existem models para `Administrador`, `Funcionario` ou `Movimentacao`. Todo acesso ao banco é feito via `DB::select/insert/update` com SQL raw nos controllers. Isso impossibilita o uso de:
- Relacionamentos (`hasMany`, `belongsTo`)
- Scopes globais e locais
- Casts e mutators
- Events e observers
- Traits como `SoftDeletes`
- Form Request com regras `unique` e `exists`
- API Resources com relacionamentos

#### MÉDIO — Soft delete implementado manualmente

| Arquivo | Linha | Detalhe |
|---------|-------|---------|
| `2024_01_01_000000_create_tables.php` | 26 | Coluna `deleted` como `tinyInteger` em vez do padrão Laravel `SoftDeletes` (`deleted_at` timestamp) |
| `FuncionarioController.php` | 12, 19, 34, 51, 63 | Filtro `WHERE deleted = 0` repetido manualmente em toda query |

**Risco real:** fácil esquecer o filtro `deleted = 0` em novas queries, expondo registros "deletados". Sem timestamp de deleção, não há rastreio de quando o registro foi removido.

**Correção aplicada:** adicionada a trait `SoftDeletes` no model `Funcionario`, que usa a coluna padrão `deleted_at` (timestamp). A migration `2024_01_02_000000_modernize_tables.php` adiciona a coluna `deleted_at` e converte registros existentes com `deleted = 1` para `deleted_at = now()`, preservando o estado anterior. Todos os filtros manuais `WHERE deleted = 0` foram eliminados — o Eloquent aplica o scope automaticamente.

#### MÉDIO — Saldo inconsistente no Seeder

| Arquivo | Linha | Detalhe |
|---------|-------|---------|
| `DatabaseSeeder.php` | 35 | O saldo é definido como `rand(0, 2000) / 10` (valor aleatório) mas não corresponde à soma das movimentações geradas para aquele funcionário |

**Risco real:** em ambiente de desenvolvimento/teste, o saldo armazenado diverge do saldo calculado pelas movimentações, dificultando validação de integridade.

#### BAIXO — Ambiente Docker não funciona automaticamente

| Arquivo | Detalhe |
|---------|---------|
| `.env.example` | `DB_HOST=127.0.0.1` — incorreto dentro do Docker, deveria ser `db` (nome do serviço no compose) |
| `Dockerfile` | Não copia `.env`, não gera `APP_KEY`, não executa migrations nem seeder. O `docker compose up` sobe os containers mas o sistema retorna erro 500 |

#### MÉDIO — Seeder destrutivo executado a cada restart do container

| Arquivo | Linha(s) | Detalhe |
|---------|----------|---------|
| `DatabaseSeeder.php` | 18-20 | O seeder usa `truncate()` nas tabelas `funcionarios` e `movimentacoes`, apagando todos os dados existentes antes de recriar |
| `Dockerfile` / entrypoint | — | Se o seeder rodar automaticamente a cada restart do container (via `db:seed --force` no entrypoint), todos os dados reais seriam destruídos |

**Risco real:** em produção, um simples restart do container apagaria todos os funcionários e movimentações financeiras. O seeder deve rodar apenas na primeira inicialização ou sob comando manual.

**Correção aplicada:** o entrypoint verifica se a tabela `administradores` possui registros antes de decidir executar o seeder. Se o banco estiver vazio (após `docker compose down -v`, por exemplo), o seed roda automaticamente. Se já houver dados, é ignorado. Essa abordagem é mais robusta que um lock file, pois funciona corretamente com volumes montados do host.

#### BAIXO — Tabela `movimentacoes` sem `updated_at`

| Arquivo | Linha | Detalhe |
|---------|-------|---------|
| `2024_01_01_000000_create_tables.php` | 36 | Apenas `created_at` foi definido. Se for necessário corrigir uma movimentação, não há rastreio de quando foi alterada |

---

### O que priorizamos corrigir e por quê

1. **SQL Injection** — Prioridade máxima. Permite acesso total ao banco por qualquer atacante externo. Correção: substituir todas as queries raw por Eloquent ORM com bindings parametrizados.

2. **Senhas em texto puro** — Prioridade máxima. Vazamento do banco expõe todas as credenciais. Correção: usar `Hash::make()` para armazenar e `Hash::check()` para verificar.

3. **Autenticação inexistente nas rotas** — Prioridade máxima. Qualquer pessoa pode acessar e modificar todos os dados. Correção: implementar Laravel Sanctum com middleware `auth:sanctum` nas rotas protegidas.

4. **Senha exposta no response** — Prioridade máxima. Vaza credenciais em cada login. Correção: usar API Resources para controlar exatamente quais campos são retornados.

5. **Operações financeiras sem transação e sem lock** — Prioridade máxima. Causa inconsistência de saldo e permite double-spending. Correção: `DB::transaction()` com `lockForUpdate()` para garantir atomicidade e isolamento.

6. **Token de autenticação fraco** — Eliminado ao adotar Laravel Sanctum (tokens criptográficos seguros).

7. **Validação de input** — Prioridade alta. Permite dados inválidos no banco. Correção: Form Requests dedicados com regras de validação.

8. **Performance do relatório (N+1)** — Prioridade alta. Inviabiliza uso em produção. Correção: query única com `JOIN` e agregações SQL (`SUM`, `COUNT`).

9. **Paginação** — Prioridade alta. Retorno de milhares de registros de uma vez. Correção: usar paginação nativa do Laravel em todas as listagens.

10. **Models Eloquent + SoftDeletes** — Prioridade média. Base para todas as correções acima. Correção: criar models `Administrador`, `Funcionario` e `Movimentacao` com relacionamentos e traits adequados.

11. **Ambiente Docker automático** — Prioridade média. Requisito explícito do teste. Correção: script `entrypoint.sh` que configura `.env`, gera key, roda migrations e seed.

12. **Seeder destrutivo a cada restart** — Prioridade média. O seeder apaga todos os dados com `truncate()` e roda sem condição. Correção: entrypoint verifica se o banco possui dados (`SELECT COUNT(*) FROM administradores`) antes de executar o seed. A migration de hash de senhas é idempotente (verifica `str_starts_with($senha, '$2y$')` antes de hashear).

### O que decidimos não corrigir e por quê

1. **Saldo inconsistente no Seeder** — O seeder é apenas para desenvolvimento. Em produção os dados seriam reais e o saldo seria calculado corretamente pelas movimentações. Recalcular o saldo no seeder adicionaria complexidade sem benefício real para o sistema.

2. **Tabela `movimentacoes` sem `updated_at`** — Movimentações financeiras são tipicamente imutáveis (append-only). Adicionar `updated_at` não traz benefício para o caso de uso atual. Em uma próxima iteração, se surgir necessidade de auditoria de alterações, pode ser adicionado com uma migration.

3. **UserFactory padrão do Laravel** — Veio com o scaffolding do Laravel e não é utilizada pelo domínio. Não causa problema e pode ser útil futuramente.

4. **View `welcome.blade.php`** — Página padrão do Laravel. Não impacta a API e será substituída se/quando houver frontend (Livewire como diferencial).

---

## Decisões técnicas

> Seção atualizada conforme as correções forem implementadas.

| Decisão | Justificativa |
|---------|---------------|
| Adotar Laravel Sanctum para autenticação | Solução oficial do Laravel para APIs, tokens seguros, integração nativa com middleware |
| Substituir SQL raw por Eloquent | Elimina SQL injection por design, habilita relacionamentos, scopes e SoftDeletes |
| Usar `DB::transaction()` + `lockForUpdate()` nas movimentações | Garante atomicidade (tudo ou nada) e impede race conditions no saldo |
| Saldo negativo NÃO é permitido | Decisão de negócio: operações de saída que excedam o saldo são rejeitadas |
| Soft delete de funcionários preserva movimentações | Decisão de negócio: histórico financeiro deve ser mantido para auditoria |
| Paginação padrão de 15 itens | Equilíbrio entre performance e usabilidade |
| Testes com Pest PHP | Framework de testes moderno, sintaxe expressiva, recomendado pelo ecossistema Laravel |
| Seed condicional (verifica dados no banco) | Evita destruição de dados em restarts. O entrypoint consulta o banco antes de seedar — mais robusto que lock file com volumes montados |
| Covering index `(funcionario_id, tipo, valor)` em movimentações | Resolve JOIN + agregações (`SUM`, `COUNT`) inteiramente pelo índice, sem acessar linhas da tabela. EXPLAIN confirma `Using index` |
| Cache com versionamento global no relatório | Evita reexecutar query pesada a cada requisição. Invalidação automática via `Cache::increment` no service de movimentações — sem risco de dados stale |
| Migration de hash de senhas idempotente | Verifica se a senha já é bcrypt (`$2y$`) antes de hashear, permitindo reexecução segura sem corromper senhas já migradas |

## Otimização de Performance — Relatório Consolidado

O endpoint `GET /api/relatorio` foi o ponto mais crítico de performance identificado no code review. A correção aconteceu em duas etapas:

### Etapa 1 — Eliminação do N+1 (code review)

O código legado executava **5.001 queries** por requisição (1 para listar funcionários + 1 por funcionário para buscar movimentações). Substituímos por uma query única com `LEFT JOIN` e agregações SQL (`SUM`, `COUNT`, `GROUP BY`), reduzindo para **2 queries** (dados + count da paginação).

### Etapa 2 — Índices de banco e cache (otimização adicional)

Após a correção do N+1, a query única ainda fazia **full table scan** na tabela `movimentacoes` (a maior do sistema). Aplicamos duas otimizações complementares:

#### Índices criados

| Tabela | Índice | Colunas | Justificativa |
|--------|--------|---------|---------------|
| `movimentacoes` | `idx_mov_funcionario_tipo_valor` | `(funcionario_id, tipo, valor)` | **Covering index** — cobre o JOIN e as agregações `CASE WHEN`. O MySQL resolve `SUM` e `COUNT` direto pelo índice, sem ler as linhas reais da tabela |
| `funcionarios` | `idx_func_deleted_at` | `(deleted_at)` | Acelera o filtro `WHERE deleted_at IS NULL` e o `COUNT(*)` da paginação |

#### Resultado do EXPLAIN (com ~5.000 funcionários e ~1.3M movimentações)

| Tabela | Antes (sem índice) | Depois (com índice) | Extra |
|--------|-------------------|---------------------|-------|
| `movimentacoes` | Full scan (~1.3M rows) | **267 rows por funcionário** via índice | **`Using index`** — melhor cenário possível, resolve tudo pelo índice sem tocar nos dados |
| `funcionarios` | Full scan (~5.000 rows) | ~5.000 rows (esperado para tabela pequena) | `Using where` |

O ganho principal está na tabela `movimentacoes`: de **full scan em 1.3 milhão de linhas** para **lookup indexado de ~267 linhas por funcionário**, inteiramente resolvido pelo covering index.

#### Cache com versionamento

Além dos índices, adicionamos cache no `RelatorioController` com estratégia de versionamento:

- Cada página do relatório é cacheada por **5 minutos** (TTL adequado para dados administrativos)
- A chave inclui uma **versão global** (`relatorio_version`) incrementada automaticamente pelo `MovimentacaoService` a cada nova movimentação
- Quando uma movimentação é registrada, `Cache::increment('relatorio_version')` invalida todo o cache do relatório sem precisar conhecer quais páginas existem
- Funciona com qualquer driver de cache (file, redis, database)

| Cenário | Comportamento |
|---------|---------------|
| Primeira requisição (cache miss) | Executa a query (já otimizada pelos índices) e cacheia o resultado |
| Requisições subsequentes (cache hit) | Retorno instantâneo, sem query ao banco |
| Após nova movimentação | Versão incrementa, próxima requisição executa query fresca |

#### Migration

A otimização está na migration `2026_03_11_155716_add_performance_indexes_to_relatorio.php`, que pode ser revertida com `php artisan migrate:rollback` sem impacto funcional.

---

## Como a IA foi utilizada

A IA (Claude via Cursor) foi utilizada como par de programação durante todo o processo:

- **Code Review**: A análise inicial do código legado foi feita com suporte da IA para identificar e catalogar todos os problemas. A priorização e as decisões foram minhas.
- **Código gerado**: Models, Controllers, Form Requests, API Resources, Service Layer, migrations e testes foram gerados pela IA seguindo as decisões arquiteturais que defini no plano.
- **O que revisei/ajustei**: Validei cada arquivo gerado, corrigi tipos de comparação nos testes (int vs float), ajustei a migration do Sanctum para ordenação correta, refinei o entrypoint Docker para aguardar MySQL.
- **Engenharia de contexto com MCP `user-context7`**: Usei o MCP `user-context7` (content7) para buscar documentação atualizada de Laravel, Docker, Livewire e outras dependências sempre que esbarrava em bugs ou comportamentos duvidosos. Essa engenharia de contexto garantiu que a IA respondesse baseada nas versões mais recentes das ferramentas usadas no projeto.
- **O que a IA não fez**: As decisões de negócio (saldo negativo, soft delete, priorização), a arquitetura (Sanctum vs JWT, Service Layer, SoftDeletes) e a estratégia de testes foram definidas por mim.
