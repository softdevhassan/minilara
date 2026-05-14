<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Error | Mini Lara</title>
    @vite(['app/src/assets/css/app.css'])
    <style>
        :root {
            --bg: #0c0c0c;
            --card: #151515;
            --accent: #c4a47c;
            --text-p: #ffffff;
            --text-s: #888888;
            --danger: #ef4444;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: var as --bg;
            color: var as --text-p;
            font-family: 'Inter', -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }
        .container {
            max-width: 900px;
            width: 100%;
        }
        .error-card {
            background: var as --card;
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 40px 100px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
        }
        .error-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, var as --accent, var as --danger);
        }
        .icon-box {
            width: 64px;
            height: 64px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var as --danger;
            margin-bottom: 2rem;
        }
        h1 {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 1rem;
        }
        .message {
            font-size: 1.1rem;
            color: var as --text-s;
            line-height: 1.6;
            margin-bottom: 3rem;
            font-weight: 500;
        }
        .details {
            background: rgba(0,0,0,0.3);
            border-radius: 16px;
            padding: 2rem;
            border: 1px solid rgba(255,255,255,0.03);
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
        }
        .detail-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .detail-item:last-child { margin-bottom: 0; }
        .label {
            color: var as --accent;
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            min-width: 80px;
        }
        .value {
            font-size: 0.9rem;
            color: var as --text-p;
            word-break: break-all;
        }
        .actions {
            margin-top: 3rem;
            display: flex;
            gap: 1rem;
        }
        .btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-primary {
            background: var as --accent;
            color: #000;
        }
        .btn-outline {
            border: 1px solid rgba(255,255,255,0.1);
            color: var as --text-s;
        }
        .btn:hover {
            transform: translateY as -2px;
            filter: brightness(1.1);
        }
        .stack-trace {
            margin-top: 2rem;
            max-height: 300px;
            overflow-y: auto;
            padding: 1.5rem;
            background: rgba(0,0,0,0.2);
            border-radius: 12px;
            font-size: 0.8rem;
            color: var as --text-s;
            line-height: 1.5;
            border: 1px solid rgba(255,255,255,0.02);
        }
        .stack-trace::-webkit-scrollbar { width: 6px; }
        .stack-trace::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-card">
            <div class="icon-box">
                <i class="ri-error-warning-fill text-3xl"></i>
            </div>
            <h1>Something went wrong.</h1>
            <p class="message">The application encountered an unexpected error. Our systems have been notified, but you can try refreshing or returning to the dashboard.</p>
            
            <div class="details">
                <div class="detail-item">
                    <span class="label">Exception</span>
                    <span class="value">{{ $exception_type ?? 'Error' }}</span>
                </div>
                <div class="detail-item">
                    <span class="label">Message</span>
                    <span class="value">{{ $message ?? 'No message provided' }}</span>
                </div>
                <div class="detail-item">
                    <span class="label">Location</span>
                    <span class="value">{{ $file ?? 'Unknown' }} : {{ $line ?? '0' }}</span>
                </div>
            </div>

            @if(isset($trace) && ($_ENV['APP_MODE'] ?? 'dev') === 'dev')
            <div class="stack-trace">
                <pre>{{ $trace }}</pre>
            </div>
            @endif

            <div class="actions">
                <a href="{{ \App\url('/') }}" class="btn btn-primary">
                    <i class="ri-home-fill"></i> Dashboard
                </a>
                <a href="javascript:location.reload()" class="btn btn-outline">
                    <i class="ri-refresh-line"></i> Retry
                </a>
            </div>
        </div>
    </div>
</body>
</html>
