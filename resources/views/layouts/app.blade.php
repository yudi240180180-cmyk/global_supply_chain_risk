<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Global Supply Chain Risk Platform')</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Leaflet -->
    <link rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet"
href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css"/>

<link rel="stylesheet"
href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css"/>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        html,
        body{
            margin:0;
            padding:0;
            background:#0f172a;
            color:#fff;
            font-family:Arial, Helvetica, sans-serif;
        }

        #world-map{
            width:100%;
            height:550px;
            border-radius:18px;
        }

        .glass{
            background:rgba(255,255,255,.05);
            backdrop-filter:blur(12px);
            border:1px solid rgba(255,255,255,.08);
            transition:.3s;
        }

        .glass:hover{
            transform:translateY(-4px);
            box-shadow:0 15px 30px rgba(0,0,0,.35);
        }

        .sidebar{
            width:250px;
        }

        .scrollbar::-webkit-scrollbar{
            width:6px;
        }

        .scrollbar::-webkit-scrollbar-thumb{
            background:#475569;
            border-radius:10px;
        }

        canvas{
            max-width:100%;
        }

        .leaflet-container{
            background:#0f172a;
        }
    </style>

</head>

<body class="bg-slate-900 text-white">

<div class="flex">

    @include('components.sidebar')

    <div class="flex-1 ml-[250px] min-h-screen">

        @include('components.navbar')

        <main class="p-8">

            @yield('content')

        </main>

    </div>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<script>
// Wait for Leaflet to be fully loaded before executing page scripts
if (typeof L === 'undefined') {
    console.error('Leaflet not loaded!');
} else {
    console.log('Leaflet loaded successfully, version:', L.version);
}

// Ensure DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready, initializing maps...');
});
</script>

@stack('scripts')

</body>
</html>