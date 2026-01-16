<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Octane Status Check</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Server Status</h1>
        
        <div class="mb-6 p-4 rounded-lg {{ $is_octane ? 'bg-green-100 border border-green-400' : 'bg-yellow-100 border border-yellow-400' }}">
            <div class="flex items-center justify-center mb-2">
                @if($is_octane)
                    <svg class="w-8 h-8 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <span class="text-xl font-bold text-green-800">Running on Octane</span>
                @else
                    <svg class="w-8 h-8 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-xl font-bold text-yellow-800">Running on Standard PHP</span>
                @endif
            </div>
            <p class="text-center text-sm {{ $is_octane ? 'text-green-700' : 'text-yellow-700' }}">
                {{ $server_software }}
            </p>
        </div>

        <div class="space-y-3">
            <div class="flex justify-between border-b pb-2">
                <span class="text-gray-600">PHP Version</span>
                <span class="font-mono font-medium">{{ $php_version }}</span>
            </div>
            <div class="flex justify-between border-b pb-2">
                <span class="text-gray-600">Process ID</span>
                <span class="font-mono font-medium">{{ $pid }}</span>
            </div>
            <div class="flex justify-between border-b pb-2">
                <span class="text-gray-600">Memory Usage</span>
                <span class="font-mono font-medium">{{ number_format($memory_usage / 1024 / 1024, 2) }} MB</span>
            </div>
            <div class="flex justify-between border-b pb-2">
                <span class="text-gray-600">Environment</span>
                <span class="font-mono font-medium uppercase">{{ $environment }}</span>
            </div>
             @if($is_octane)
            <div class="flex justify-between border-b pb-2">
                <span class="text-gray-600">Octane Server</span>
                <span class="font-mono font-medium">{{ $octane_server }}</span>
            </div>
            @endif
        </div>

        <div class="mt-8 text-center text-xs text-gray-400">
            Page generated at {{ now()->toDateTimeString() }}
        </div>
    </div>
</body>
</html>
