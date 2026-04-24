<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 3rem 1rem;
        }

        .container {
            text-align: center;
            max-width: 480px;
            width: 100%;
            padding: 0 2rem;
        }

        .container-large {
            max-width: 780px;
            width: 100%;
            padding: 0 2rem;
            margin: 2rem 0;
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        h2 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin: 1.5rem 0 0.75rem;
        }

        h2:first-child { margin-top: 0; }

        .env-badge {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            background: #dbeafe;
            color: #1d4ed8;
        }

        .links {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .links a {
            display: block;
            padding: 0.875rem 1.25rem;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            color: #1e293b;
            text-decoration: none;
            font-weight: 500;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .links a:hover {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .links a span {
            display: block;
            font-size: 0.8rem;
            font-weight: 400;
            color: #64748b;
            margin-top: 0.2rem;
        }

        .download {
            margin-top: 1rem;
            font-size: 0.8rem;
        }

        .download a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }

        .download a:hover { text-decoration: underline; }

        .panel {
            text-align: left;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1rem;
        }

        .panel p, .panel li {
            font-size: 0.85rem;
            color: #475569;
            line-height: 1.55;
        }

        .panel p { margin-bottom: 0.75rem; }
        .panel p:last-child, .panel ul:last-child { margin-bottom: 0; }

        .panel ul { padding-left: 1.25rem; }
        .panel li { margin-bottom: 0.35rem; }

        .panel code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, monospace;
            font-size: 0.8em;
            background: #f1f5f9;
            padding: 0.1rem 0.35rem;
            border-radius: 0.25rem;
            color: #0f172a;
        }

        .panel pre {
            background: #0f172a;
            color: #e2e8f0;
            padding: 0.85rem 1rem;
            border-radius: 0.4rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, monospace;
            font-size: 0.75rem;
            overflow-x: auto;
            margin: 0.5rem 0 0.75rem;
        }

        table.kv {
            width: 100%;
            font-size: 0.8rem;
            border-collapse: collapse;
        }

        table.kv td {
            padding: 0.35rem 0.5rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }

        table.kv td:first-child {
            color: #64748b;
            white-space: nowrap;
            padding-right: 1rem;
            font-weight: 500;
        }

        table.kv td:last-child {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, monospace;
            color: #1e293b;
            word-break: break-all;
        }

        .back-link {
            display: inline-block;
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: #3b82f6;
            text-decoration: none;
        }

        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    {{ $slot }}
</body>
</html>
