<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Server Error</title>
    
    <style nonce="{{ csp_nonce() }}">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-container {
            text-align: center;
            color: white;
            max-width: 600px;
        }

        h1 {
            font-size: 120px;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        h2 {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        p {
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
        }

        .error-code {
            background: rgba(255,255,255,0.2);
            padding: 15px 30px;
            border-radius: 10px;
            display: inline-block;
            margin-top: 20px;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>500</h1>
        <h2>Server Error</h2>
        <p>
            Oops! Something went wrong on our end. We're working to fix it.
            Please try again later or contact support if the problem persists.
        </p>
        <a href="/" class="btn">Go to Homepage</a>
        
        @if(config('app.debug') && isset($exception))
            <div class="error-code">
                {{ $exception->getMessage() }}
            </div>
        @endif
    </div>
</body>
</html>
