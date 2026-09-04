---
name: reverb-add-app
description: "Use when the user wants to register a new project/app (channel) on this centralized Laravel Reverb server, e.g. 'adiciona um app novo no reverb', 'cria um canal pro projeto X', 'registra o projeto Y no reverb-main'. Only the project name is needed from the user — this skill derives the slug, generates credentials, updates .env, and prints the client-side config snippet."
license: MIT
metadata:
  author: juancoelho
---

# Reverb: Adicionar app hospedado

Este servidor (`reverb-main`) hospeda vários projetos Laravel como "apps" Reverb isolados, cadastrados via `.env` (ver `config/reverb.php` e `README.md`). Esta skill automatiza o cadastro de um novo app pedindo **apenas o nome do projeto**.

## Passos

1. Se o nome do projeto ainda não foi informado, pergunte só isso ao usuário (ex.: "crm", "loja online"). Nenhum outro dado é necessário.

2. Derive a partir do nome:
   - `slug`: kebab-case, minúsculo, sem acentos/caracteres especiais (ex.: "Loja Online" → `loja-online`).
   - `PREFIXO_ENV`: `REVERB_` + slug em maiúsculo com `-` virando `_` (ex.: `loja-online` → `REVERB_LOJA_ONLINE_`).

3. Leia o `.env` na raiz do projeto:
   - Se o `.env` não existir, avise o usuário para rodar `cp .env.example .env` primeiro e pare aqui.
   - Se o slug já aparecer em `REVERB_APPS`, avise que o app já está cadastrado e mostre o bloco de credenciais existente em vez de duplicar.

4. Gere credenciais únicas:
   - `APP_ID`: número de 6 dígitos, seguindo o padrão já usado no `.env` (ex.: `REVERB_APP_ID=197623`). Gere com `php artisan tinker --execute 'echo random_int(100000, 999999);'`.
   - `APP_KEY` e `APP_SECRET`: strings aleatórias alfanuméricas de 20 caracteres, uma para cada. Gere com `php artisan tinker --execute 'echo Str::random(20);'` (rode duas vezes, uma para cada valor).

5. Edite o `.env`:
   - Acrescente o slug em `REVERB_APPS` (lista separada por vírgula, sem espaços), preservando as entradas existentes — não reordene nem remova nada.
   - Adicione um novo bloco de três linhas, seguindo o padrão dos blocos existentes:
     ```env
     REVERB_<PREFIXO>APP_ID=<id gerado>
     REVERB_<PREFIXO>APP_KEY=<key gerada>
     REVERB_<PREFIXO>APP_SECRET=<secret gerada>
     ```

6. Não reinicie o processo do Reverb automaticamente — pergunte ao usuário se quer reiniciar agora (`node deploy.js` em produção, ou reiniciar o `reverb:start` manual em dev), já que isso derruba conexões de todos os apps hospedados, não só do novo.

7. Ao final, responda ao usuário com:
   - As credenciais geradas para o novo app.
   - Um bloco `.env` pronto para colar no **projeto cliente** (o outro Laravel que vai transmitir eventos):
     ```env
     REVERB_HOST=<mesmo valor de REVERB_HOST deste .env>
     REVERB_PORT=<mesmo valor de REVERB_PORT>
     REVERB_SCHEME=<mesmo valor de REVERB_SCHEME>
     REVERB_APP_ID=<id gerado>
     REVERB_APP_KEY=<key gerada>
     REVERB_APP_SECRET=<secret gerada>

     VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
     VITE_REVERB_HOST="${REVERB_HOST}"
     VITE_REVERB_PORT="${REVERB_PORT}"
     VITE_REVERB_SCHEME="${REVERB_SCHEME}"
     ```
   - Um lembrete de que o projeto cliente precisa do próprio `routes/channels.php` para autorizar os canais — este servidor central não faz isso, só transporta as mensagens (ver seção "Autorização de canais" do `README.md`).

## Observações

- Nunca commitar o `.env` — a skill só edita o arquivo local já existente.
- Se `php artisan tinker` não estiver disponível, gere os valores com qualquer fonte aleatória equivalente (ex.: `openssl rand -hex 10`), mantendo o mesmo formato (`APP_ID` numérico de 6 dígitos, `APP_KEY`/`APP_SECRET` alfanuméricos de 20 caracteres).
