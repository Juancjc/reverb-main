<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo"></a></p>

<p align="center">Servidor Laravel Reverb centralizado, capaz de hospedar múltiplos projetos (apps) em uma única instância WebSocket.</p>

## Sobre o projeto

Este repositório roda um único processo [Laravel Reverb](https://laravel.com/docs/reverb) que serve como servidor de WebSocket compartilhado por vários projetos Laravel. Em vez de subir um `reverb:start` por projeto, cada projeto é cadastrado aqui como um **app** com suas próprias credenciais (`app_id`/`key`/`secret`), todos passando pelo mesmo host/porta.

A configuração fica centralizada em `config/reverb.php`, que lê a lista de apps a partir da variável `REVERB_APPS` no `.env`.

## Requisitos

- PHP 8.3+
- Composer
- Node.js (apenas para assets de exemplo/dashboard, se usados)
- [Laravel Herd](https://herd.laravel.com) (ambiente local já expõe `https://reverb-main.test`)

## Instalação

```bash
composer install
cp .env.example .env
php artisan key:generate
```

## Configuração (.env)

Variáveis principais para o processo do Reverb:

```env
# Endereço público que os clientes usam para conectar (Herd faz o proxy/TLS)
REVERB_HOST=reverb-main.test
REVERB_PORT=443
REVERB_SCHEME=https

# Endereço em que o processo `reverb:start` efetivamente escuta (atrás do proxy)
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

# App padrão (mantido por compatibilidade com um único app)
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=

# Lista de slugs dos apps/canais hospedados neste servidor
REVERB_APPS=default
```

## Como usar: adicionando um novo app (canal)

> **Atalho:** existe uma skill do Claude Code (`.claude/skills/reverb-add-app`) que automatiza os passos abaixo — basta informar o nome do projeto e ela cadastra o slug, gera as credenciais e devolve o bloco `.env` pronto para o projeto cliente.

Cada projeto que vai transmitir eventos por este servidor precisa de um slug próprio em `REVERB_APPS` e de suas credenciais dedicadas. Passo a passo:

1. Escolha um slug em kebab-case para o novo projeto, por exemplo `crm` ou `loja-online`.
2. Adicione o slug à lista em `REVERB_APPS` (separada por vírgula):

   ```env
   REVERB_APPS=default,crm,loja-online
   ```

3. Defina as credenciais desse app usando o prefixo `REVERB_<SLUG_EM_MAIUSCULO>_`:

   ```env
   REVERB_CRM_APP_ID=100001
   REVERB_CRM_APP_KEY=crm-key-gerada
   REVERB_CRM_APP_SECRET=crm-secret-gerada

   REVERB_LOJA_ONLINE_APP_ID=100002
   REVERB_LOJA_ONLINE_APP_KEY=loja-online-key-gerada
   REVERB_LOJA_ONLINE_APP_SECRET=loja-online-secret-gerada
   ```

   Gere valores aleatórios com, por exemplo:

   ```bash
   php artisan tinker --execute 'echo Str::random(20);'
   ```

4. (Opcional) Personalize limites por app — origem permitida, rate limiting, tamanho máximo de mensagem, timeouts — sobrescrevendo as chaves correspondentes com o mesmo prefixo. Veja todas as opções em `config/reverb.php`:

   ```env
   REVERB_CRM_ALLOWED_ORIGINS=https://crm.exemplo.com
   REVERB_CRM_RATE_LIMITING_ENABLED=true
   REVERB_CRM_RATE_LIMIT_MAX_ATTEMPTS=120
   ```

5. No projeto cliente (o outro Laravel que vai transmitir/ouvir eventos), configure `config/broadcasting.php` apontando para `REVERB_HOST`/`PORT`/`SCHEME` deste servidor, mas usando a `key`/`secret`/`app_id` do slug criado.
6. Reinicie o processo do Reverb (veja [Executando o servidor](#executando-o-servidor)) para carregar o novo app.

Qualquer app sem `key`, `secret` e `app_id` definidos é ignorado automaticamente (veja o `array_filter` em `config/reverb.php`), então não é preciso remover slugs não utilizados manualmente — basta deixar as variáveis em branco.

## Autorização de canais

As regras de autorização ficam em `routes/channels.php`. Hoje existe um canal privado de exemplo:

```php
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

Para adicionar mais canais, siga os três padrões suportados pelo Reverb:

```php
// Canal público — sem autorização, qualquer cliente pode ouvir
Broadcast::channel('avisos', function () {
    return true;
});

// Canal privado — nome prefixado com "private-" no cliente,
// a closure decide se o usuário autenticado pode entrar
Broadcast::channel('pedidos.{pedidoId}', function ($user, $pedidoId) {
    return $user->pedidos()->whereKey($pedidoId)->exists();
});

// Canal de presença — nome prefixado com "presence-" no cliente,
// retorna os dados do usuário que ficam visíveis para os outros membros
Broadcast::channel('sala.{salaId}', function ($user, $salaId) {
    if (! $user->salas()->whereKey($salaId)->exists()) {
        return null;
    }

    return ['id' => $user->id, 'nome' => $user->name];
});
```

Como cada projeto cliente tem seu próprio app (`key`/`secret`), os canais definidos em `routes/channels.php` deste servidor valem para todos os apps hospedados — a separação entre projetos é feita pelo par de credenciais usado na conexão, não pelo nome do canal.

## Executando o servidor

Em desenvolvimento:

```bash
php artisan reverb:start --host=0.0.0.0 --port=8080 --debug
```

Em produção, o processo é gerenciado pelo PM2 através do `deploy.js`:

```bash
node deploy.js
```

O script instala o PM2 se necessário, registra/reinicia o processo `reverb-main` rodando `php artisan reverb:start --host=0.0.0.0 --port=8080` e persiste a configuração com `pm2 save`.

## Conectando um cliente (Laravel Echo)

No projeto que vai consumir os eventos, configure o Echo com a `key` do app correspondente:

```js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

## Escalando horizontalmente

Este servidor suporta múltiplas instâncias via Redis (`REVERB_SCALING_ENABLED=true`), útil quando o tráfego de um dos apps exigir mais de um processo Reverb atrás de um load balancer. Configure `REDIS_URL`/`REDIS_HOST`/`REDIS_PORT` e `REVERB_SCALING_CHANNEL` no `.env`.

## Licença

Este projeto é construído sobre o [Laravel Reverb](https://laravel.com/docs/reverb), open source sob a [licença MIT](https://opensource.org/licenses/MIT).
