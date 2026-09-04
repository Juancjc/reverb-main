<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Página não encontrada &middot; {{ config('app.name', 'Reverb') }}</title>

        <style>
            :root {
                color-scheme: light dark;
                --bg: #FDFDFC;
                --card-bg: #ffffff;
                --text: #1b1b18;
                --muted: #706f6c;
                --border: #e3e3e0;
                --accent: #f53003;
            }

            @media (prefers-color-scheme: dark) {
                :root {
                    --bg: #0a0a0a;
                    --card-bg: #161615;
                    --text: #EDEDEC;
                    --muted: #A1A09A;
                    --border: #3E3E3A;
                    --accent: #FF4433;
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
                max-width: 28rem;
                text-align: center;
                background: var(--card-bg);
                border: 1px solid var(--border);
                border-radius: 0.75rem;
                padding: 2.5rem 2rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            }

            .code {
                font-size: 3rem;
                font-weight: 600;
                color: var(--accent);
                margin: 0;
                line-height: 1;
            }

            h1 {
                font-size: 1.25rem;
                margin: 0.75rem 0 0.5rem;
            }

            p {
                color: var(--muted);
                line-height: 1.6;
                margin: 0 0 1.5rem;
            }

            a.button {
                display: inline-block;
                padding: 0.5rem 1.25rem;
                border-radius: 0.375rem;
                background: var(--text);
                color: var(--bg);
                text-decoration: none;
                font-size: 0.875rem;
            }
        </style>
    </head>
    <body>
        <main>
            <p class="code">404</p>
            <h1>Página não encontrada</h1>
            <p>A rota que você tentou acessar não existe neste servidor.</p>
            <a class="button" href="{{ url('/') }}">Voltar ao início</a>
        </main>
    </body>
</html>
