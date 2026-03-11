# Teste Laravel Pleno — Nano Incub

## Antes de começar

Leia **tudo** antes de escrever qualquer linha de código.

Depois, estime suas horas e envie para `rh@nanoincub.com.br` com o assunto `[Teste Backend] Estimativa`.

Ao finalizar, publique no seu GitHub e envie o link para `rh@nanoincub.com.br` com o assunto `[Teste Backend] Finalizado`.

---

## Contexto

Você entrou em um time que mantém sistemas legados e sistemas modernos ao mesmo tempo. Esse é o dia a dia: código que funciona mas foi escrito sem muitos cuidados, convivendo com código novo que precisa ser sustentável.

Você vai receber um projeto Laravel já existente — um sistema de bonificação de funcionários com moeda digital. O sistema **funciona**, mas foi desenvolvido de forma apressada e acumulou problemas sérios ao longo do tempo.

Sua missão é **modernizá-lo** para garantir mais segurança, confiabilidade e sustentabilidade.

Você pode usar IA para te ajudar. O que avaliamos é a qualidade das suas decisões — se você enxergou os problemas certos, se o sistema ficou mais seguro e confiável, e se o ambiente roda de verdade.

---

## Sua Missão

### 0. Documentação

Este é um projeto legado, não existe nenhuma documentação. Crie um README que descreva o projeto, como subir o ambiente, como rodar os testes, como acessar o sistema, etc. Este README será usado para avaliar o projeto.

---

### 1. Code Review — obrigatório

Antes de qualquer mudança no código legado, dedique uma seção no seu `README.md` chamada **"Code Review"** com:

- Os problemas que você encontrou (seja específico: arquivo, linha, qual o risco real)
- O que você priorizou corrigir e por quê
- O que você decidiu não corrigir (se deixou algo de fora) e por quê

> Esse é um dos critérios mais importantes da avaliação. Queremos ver como você lê código de outra pessoa e o que você considera urgente versus o que pode esperar.

---

### 2. Modernização — o que esperamos ver

Leia o código, identifique os problemas, e corrija o que você considera necessário. Não há uma lista fechada de tarefas — você decide o que precisa ser feito e em que ordem.

Ao final, o sistema deve atender a estes resultados:

**A API deve ser segura para exposição pública**
O sistema atualmente não tem nenhuma proteção. Uma API em produção precisa autenticar quem acessa, proteger os dados dos usuários e não vazar informações sensíveis nas respostas.

**Performance e escalabilidade**
O sistema deve ser performático e escalável para suportar um grande volume de requisições e dados.

**As operações financeiras devem ser confiáveis**
Movimentações de saldo precisam funcionar corretamente mesmo quando algo falha no meio do processo ou quando múltiplas requisições chegam ao mesmo tempo.

**O código deve ser sustentável**
Um dev novo no time precisa conseguir entender, modificar e testar o código sem depender de quem o escreveu. Duplificações, responsabilidades misturadas e ausência de validação são problemas reais de manutenção.

**O ambiente deve funcionar**
O projeto deve subir com `docker compose up` sem configuração manual. Migrações e dados iniciais devem rodar automaticamente.

**Durante a modernização, você vai encontrar situações onde a solução técnica depende de uma decisão de negócio.** 
Documente essas decisões no seu README — o que você decidiu e por quê.

---

### 3. Testes — obrigatório

Escreva testes automatizados para os cenários que você considerar mais críticos do negócio. Use Pest PHP ou PHPUnit.

A escolha dos cenários a testar faz parte da avaliação.

---

### 4. Diferenciais — opcional, mas valorizado

- **Módulo Livewire:** Criar um módulo Livewire para listagem de funcionários com filtro por nome e paginação reativa
- **Movimentações assíncronas:** ao registrar uma movimentação, invés de persistir a movimentação, disparar um Job que realizará o processamento e "notificará" o funcionário quando finalizar o processamento.

---

## Stack

| Camada | Tecnologia |
|--------|------------|
| Framework | Laravel (versão atual) |
| Banco | MySQL 8 |
| Ambiente | Docker + Docker Compose |

A stack acima é a que usamos. Não troque o framework ou banco de dados.

**Sobre versões:** Este projeto de teste usa Laravel 12 + PHP 8.4. No dia a dia, o time mantém projetos com versões variadas (Laravel 10 + PHP 8.1 até versões mais recentes).

---

## O que colocar no README

O README é parte do teste. Deve conter:

- **Como subir o projeto** (deve ser simples — preferencialmente um comando)
- **Como rodar os testes**
- **Credenciais padrão** para acessar o sistema em desenvolvimento
- **Code Review** — problemas encontrados, o que você priorizou e por quê
- **Decisões técnicas** — o que você escolheu fazer e por quê, o que deixaria para uma próxima iteração
- **Como você usou IA** — seja honesto: o que foi gerado, o que você revisou, o que mudou

---

## Critérios de Avaliação

**O que mais pesa:**

| Critério | O que observamos |
|----------|-----------------|
| Code Review | Você identificou os problemas reais? Identificou o que é urgente? Foi específico? |
| Segurança | O sistema está seguro para uso em produção? |
| Regras de negócio | As operações financeiras são confiáveis e a regra de negócio é garantida? |
| Ambiente | `docker compose up` sobe sem erros? |
| Testes | Os testes cobrem o que realmente importa? Passam no CI? |
| README | Um dev do time consegue rodar o projeto só com o README? |

**O que também avaliamos:**

- Commits com mensagens que contam uma história do que foi feito, usando conventional commits
- Código legível — nomes que fazem sentido, funções com responsabilidade clara
- Raciocínio documentado — por que você fez as escolhas que fez

**O que não é um ponto negativo na nossa avaliação:**

- Se você usou IA para escrever código
- Se o projeto está "perfeito" — queremos ver como você pensa e prioriza

---

## Dúvidas

Envie para `rh@nanoincub.com.br` com o assunto `[Teste Backend] Dúvida sobre X`.

---

## Por que esse formato?

No dia a dia você vai receber código legado, vai usar IA para acelerar, e vai tomar decisões com informação incompleta. Esse teste simula exatamente isso — não queremos saber se você memorizou a documentação do Laravel, queremos ver como você age quando o código já existe e tem problemas reais.
