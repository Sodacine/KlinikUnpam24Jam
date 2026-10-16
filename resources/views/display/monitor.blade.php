<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layar Monitor Antrian - Klinik Unpam 24 Jam</title>

    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        unpam: {
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full flex flex-col font-sans text-white bg-slate-950 overflow-hidden antialiased">

    <!-- Header Layar Monitor -->
    <header class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex items-center justify-between shadow-2xl">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-unpam-700 via-unpam-600 to-teal-400 flex items-center justify-center text-white shadow-lg shadow-unpam-600/30">
                <i data-lucide="cross" class="w-7 h-7 stroke-[2.5]"></i>
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-black tracking-tight text-white uppercase">
                        Klinik Unpam 24 Jam
                    </h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-500 text-white tracking-wider animate-pulse">
                        <span class="w-2 h-2 rounded-full bg-white mr-1.5"></span>
                        LIVE DISPLAY
                    </span>
                </div>
                <p class="text-xs text-slate-400">Pusat Layanan Kesehatan Terpadu Universitas Pamulang</p>
            </div>
        </div>

        <div class="flex items-center gap-8">
            <div class="text-right">
                <div id="liveClock" class="font-mono text-3xl font-black tracking-wider text-teal-400">
                    {{ now()->format('H:i:s') }}
                </div>
                <div class="text-xs text-slate-400 font-semibold">
                    {{ now()->translatedFormat('l, d F Y') }}
                </div>
            </div>

            <a href="{{ route('login') }}" class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors" title="Masuk ke Sistem">
                <i data-lucide="log-in" class="w-5 h-5"></i>
            </a>
        </div>
    </header>

    <!-- Main Display Grid -->
    <main class="flex-1 p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 overflow-hidden">
        
        <!-- ========================================== -->
        <!-- 4 POLIKLINIK (KOLOM KIRI: 8 COLS)          -->
        <!-- ========================================== -->
        <div class="lg:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-5">
            @foreach($polyclinics as $poli)
                @php
                    $info = $activePerPoli[$poli->id] ?? null;
                    $current = $info['current'] ?? null;
                    $totalWaiting = $info['totalWaiting'] ?? 0;
                @endphp

                <div class="rounded-3xl bg-slate-900 border border-slate-800 p-6 flex flex-col justify-between shadow-2xl relative overflow-hidden group hover:border-slate-700 transition-colors">
                    
                    <!-- Top Poli Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-800 text-teal-400 flex items-center justify-center font-bold">
                                @if($poli->code === 'ANK') <i data-lucide="baby" class="w-5 h-5"></i>
                                @elseif($poli->code === 'UMU') <i data-lucide="stethoscope" class="w-5 h-5"></i>
                                @elseif($poli->code === 'KND') <i data-lucide="heart" class="w-5 h-5"></i>
                                @elseif($poli->code === 'GGI') <i data-lucide="smile" class="w-5 h-5"></i>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-white">{{ $poli->name }}</h3>
                                <p class="text-xs text-slate-400">{{ $poli->floor_room }}</p>
                            </div>
                        </div>

                        <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg bg-slate-800 text-teal-300 border border-slate-700">
                            {{ $poli->code }}
                        </span>
                    </div>

                    <!-- Nomor Antrian Raksasa yang Sedang Dipanggil -->
                    <div class="py-6 text-center">
                        <span class="text-xs uppercase tracking-widest text-slate-400 font-bold block mb-1">
                            Nomor Antrian Dipanggil
                        </span>
                        <div class="font-mono text-6xl sm:text-7xl font-black tracking-tight text-teal-400 py-2">
                            {{ $current?->queue_code ?? '---' }}
                        </div>
                        <div class="mt-1">
                            @if($current)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-teal-500/10 text-teal-300 border border-teal-500/30">
                                    <span class="w-2 h-2 rounded-full bg-teal-400 mr-1.5 animate-ping"></span>
                                    {{ $current->status_label }}
                                </span>
                            @else
                                <span class="text-xs text-slate-500 italic">
                                    Belum ada antrian dipanggil
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Bottom Info: Dokter & Antrian Menunggu -->
                    <div class="pt-4 border-t border-slate-800 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-1.5 text-slate-300">
                            <i data-lucide="user-check" class="w-4 h-4 text-slate-500"></i>
                            <span class="font-medium truncate max-w-[150px]">{{ $current?->doctor?->name ?? 'Dokter Jaga' }}</span>
                        </div>

                        <div class="text-slate-400">
                            Menunggu: <strong class="text-white">{{ $totalWaiting }}</strong> Pasien
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <!-- ========================================== -->
        <!-- LOKET APOTEK (KOLOM KANAN: 4 COLS)         -->
        <!-- ========================================== -->
        <div class="lg:col-span-4 rounded-3xl bg-gradient-to-b from-teal-950 via-slate-900 to-slate-900 border-2 border-teal-500/40 p-6 flex flex-col justify-between shadow-2xl">
            
            <div>
                <!-- Header Apotek -->
                <div class="flex items-center justify-between pb-4 border-b border-teal-800/60 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-teal-500 text-slate-950 flex items-center justify-center font-black">
                            <i data-lucide="pill" class="w-6 h-6 stroke-[2.5]"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-white tracking-tight uppercase">Loket Apotek</h2>
                            <p class="text-xs text-teal-300 font-semibold">Pengambilan Obat & Farmasi</p>
                        </div>
                    </div>
                    <span class="w-3 h-3 rounded-full bg-teal-400 animate-ping"></span>
                </div>

                <div class="mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-teal-300 block mb-1">
                        Obat Siap Diambil di Loket:
                    </span>
                    <p class="text-xs text-slate-400">
                        Pasien dengan nomor di bawah silakan langsung menuju ke Loket Apotek:
                    </p>
                </div>

                <!-- Daftar Nomor yang Siap Diambil -->
                <div class="space-y-3 max-h-[48vh] overflow-y-auto pr-1" id="pharmacyContainer">
                    @forelse($pharmacyReady as $med)
                        <div class="p-4 rounded-2xl bg-slate-900/90 border border-teal-500/40 flex items-center justify-between animate-pulse">
                            <div>
                                <span class="font-mono text-3xl font-black text-white tracking-tight">{{ $med->queue_code }}</span>
                                <span class="text-xs text-teal-300 block font-bold mt-0.5">{{ $med->patient->name }}</span>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-teal-400 text-slate-950">
                                Loket 1
                            </span>
                        </div>
                    @empty
                        <div class="py-12 text-center text-slate-500">
                            <i data-lucide="check-circle" class="w-10 h-10 mx-auto mb-2 text-slate-700"></i>
                            <p class="text-xs">Belum ada resep yang siap diambil saat ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Hotline & Lokasi -->
            <div class="pt-4 border-t border-slate-800 text-center text-xs text-slate-400">
                <span class="text-teal-400 font-bold block mb-0.5">Unit Farmasi 24 Jam</span>
                Harap siapkan nomor antrian atau kartu pasien Anda saat dipanggil.
            </div>

        </div>

    </main>

    <!-- Footer Running Text / Ticker -->
    <footer class="bg-slate-900 border-t border-slate-800 px-6 py-2.5 flex items-center gap-4 text-xs">
        <div class="flex items-center gap-2 text-rose-400 font-bold uppercase tracking-wider whitespace-nowrap">
            <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
            Informasi:
        </div>
        <div class="overflow-hidden whitespace-nowrap flex-1 text-slate-300 font-medium">
            <div class="inline-block animate-marquee">
                Selamat Datang di Klinik Unpam 24 Jam &bull; Melayani Civitas Akademika Universitas Pamulang & Masyarakat Umum &bull; Menjaga Ketertiban dan Kenyamanan Bersama &bull; Emergency Hotline 24 Jam: (021) 741-2566 &bull; Silakan Duduk di Ruang Tunggu Sampai Nomor Antrian Anda Dipanggil
            </div>
        </div>
    </footer>

    <style>
        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        .animate-marquee {
            animation: marquee 25s linear infinite;
        }
    </style>

    <script>
        lucide.createIcons();

        // Update Jam Digital Real-Time
        setInterval(() => {
            const now = new Date();
            document.getElementById('liveClock').innerText = now.toLocaleTimeString('id-ID');
        }, 1000);

        // Auto Refresh Layar Display setiap 5 detik
        setInterval(() => {
            fetch('{{ route('queue.monitor.live') }}')
                .then(res => res.json())
                .then(data => {
                    // Jika ada data baru, kita bisa reload sebagian atau reload halaman penuh jika ada nomor baru
                    // Untuk keandalan display TV, refresh halus setiap 15 detik jika ada aktivitas
                })
                .catch(e => console.log('Live display poll error', e));
        }, 5000);

        // Refresh penuh otomatis setiap 20 detik untuk keakuratan layar monitor display
        setTimeout(() => {
            window.location.reload();
        }, 20000);
    </script>
</body>
</html>
