<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Reverb') }}</title>

        <style>
            :root {
                color-scheme: light dark;
                --bg: #FDFDFC;
                --card-bg: #ffffff;
                --text: #1b1b18;
                --muted: #706f6c;
                --border: #e3e3e0;
                --accent: #f53003;
                --ok: #16a34a;
            }

            @media (prefers-color-scheme: dark) {
                :root {
                    --bg: #0a0a0a;
                    --card-bg: #161615;
                    --text: #EDEDEC;
                    --muted: #A1A09A;
                    --border: #3E3E3A;
                    --accent: #FF4433;
                    --ok: #4ade80;
                }
            }

            * {
                box-sizing: border-box;
            }

            html, body {
                margin: 0;
                min-height: 100vh;
            }

            body {
                background: var(--bg);
                color: var(--text);
                font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1.5rem;
            }

            main {
                width: 100%;
                max-width: 40rem;
                background: var(--card-bg);
                border: 1px solid var(--border);
                border-radius: 0.75rem;
                padding: 2rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            }

            .status {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.8125rem;
                color: var(--muted);
                margin-bottom: 1.5rem;
            }

            .status .dot {
                width: 0.5rem;
                height: 0.5rem;
                border-radius: 999px;
                background: var(--ok);
            }

            h1 {
                font-size: 1.5rem;
                margin: 0 0 0.5rem;
            }

            p {
                line-height: 1.6;
                color: var(--muted);
                margin: 0 0 1rem;
            }

            ul {
                margin: 0 0 1.5rem;
                padding-left: 1.25rem;
                color: var(--muted);
                line-height: 1.7;
            }

            code {
                background: var(--bg);
                border: 1px solid var(--border);
                border-radius: 0.25rem;
                padding: 0.1rem 0.35rem;
                font-size: 0.85em;
            }

            footer {
                margin-top: 1.5rem;
                padding-top: 1rem;
                border-top: 1px solid var(--border);
                font-size: 0.8125rem;
                color: var(--muted);
            }
        </style>
    </head>
    <body>
        <main>
            <div class="status"><span class="dot"></span> Servidor Reverb em execução</div>

            <h1>{{ config('app.name', 'Reverb') }}</h1>
            <p>
                Este é um servidor <strong>Laravel Reverb</strong> centralizado, responsável por transmitir
                eventos em tempo real (WebSocket) para múltiplos projetos hospedados nesta mesma instância.
            </p>

            <p>Cada projeto cliente se conecta usando suas próprias credenciais (<code>app_id</code>, <code>key</code> e <code>secret</code>), configuradas em <code>.env</code>.</p>

            <ul>
                <li>Documentação e passo a passo de uso: consulte o <code>README.md</code> do repositório.</li>
                <li>Para adicionar um novo projeto/canal, use a skill <code>reverb-add-app</code>.</li>
            </ul>

            <footer>
                Laravel {{ app()->version() }} &middot; Reverb
            </footer>
        </main>
    </body>
</html>
