<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCSM — Sales & Customer Service Management</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #F4F5F7;
            color: #1A1A1A;
            padding: 48px 24px;
        }
        .wrap { max-width: 1040px; margin: 0 auto; }
        header { margin-bottom: 40px; }
        h1 { font-size: 26px; font-weight: 600; margin-bottom: 8px; }
        header p { color: #6B7280; font-size: 15px; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #E5E7EB;
            padding: 24px;
            display: flex;
            flex-direction: column;
        }
        .card-top { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
        .abbr {
            width: 44px; height: 44px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 13px; color: #fff; flex-shrink: 0;
        }
        .card-top h2 { font-size: 16px; font-weight: 600; line-height: 1.3; }
        .card p.desc { color: #6B7280; font-size: 13.5px; line-height: 1.5; margin-bottom: 18px; flex-grow: 1; }
        .links { display: flex; flex-direction: column; gap: 8px; }
        .links a {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 14px; border-radius: 8px; background: #F9FAFB;
            color: #1A1A1A; text-decoration: none; font-size: 13.5px; font-weight: 500;
            border: 1px solid #EDEEF0; transition: background 0.15s;
        }
        .links a:hover { background: #F0F1F3; }
        .links a::after { content: '→'; color: #9CA3AF; font-weight: 400; }
    </style>
</head>
<body>
    <div class="wrap">
        <header>
            <h1>Sales & Customer Service Management</h1>
            <p>One entry point into all four submodules — After-Sales Support, Sales Order Management, Customer Relationship Management, and Sales Performance Reporting.</p>
        </header>

        <div class="grid">
            @foreach ($modules as $module)
                <div class="card">
                    <div class="card-top">
                        <div class="abbr" style="background: {{ $module['color'] }};">{{ $module['abbr'] }}</div>
                        <h2>{{ $module['name'] }}</h2>
                    </div>
                    <p class="desc">{{ $module['description'] }}</p>
                    <div class="links">
                        @foreach ($module['links'] as $link)
                            <a href="{{ route($link['route']) }}">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</body>
</html>
