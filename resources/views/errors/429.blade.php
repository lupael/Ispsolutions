<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Too Many Requests</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }
        h1 {
            color: #667eea;
            margin: 0 0 20px 0;
            font-size: 72px;
        }
        h2 {
            color: #333;
            margin: 0 0 20px 0;
        }
        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .retry-info {
            background: #f7f7f7;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>429</h1>
        <h2>Too Many Requests</h2>
        <p>You have exceeded the rate limit. Please slow down and try again later.</p>
        <div class="retry-info">
            <strong>Please try again in {{ $retry_after ?? 60 }} seconds.</strong>
        </div>
        <p style="margin-top: 30px;">
            <a href="{{ url('/') }}">Return to Home</a>
        </p>
    </div>
</body>
</html>
