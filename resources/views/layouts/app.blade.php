<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Klinik Unpam 24 Jam') - Sistem Antrian & Riwayat Medis</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        unpam: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        },
                        navy: {
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        [x-cloak] { display: none !important; }
        @keyframes pulse-subtle {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.85; transform: scale(1.02); }
        }
        .pulse-subtle {
            animation: pulse-subtle 3s infinite ease-in-out;
        }
        /* ===== Custom Confirm Modal ===== */
        #agConfirmOverlay {
            position: fixed; inset: 0; z-index: 9999;
            display: flex; align-items: center; justify-content: center;
            background: rgba(15,23,42,0.55);
            backdrop-filter: blur(6px);
            opacity: 0; pointer-events: none;
            transition: opacity 0.22s cubic-bezier(.4,0,.2,1);
        }
        #agConfirmOverlay.ag-open {
            opacity: 1; pointer-events: all;
        }
        #agConfirmBox {
            background: #fff;
            border-radius: 1.5rem;
            box-shadow: 0 32px 80px -8px rgba(15,23,42,0.28), 0 0 0 1px rgba(148,163,184,0.15);
            padding: 2rem 2rem 1.5rem;
            max-width: 420px; width: 90%;
            transform: scale(0.85) translateY(24px);
            transition: transform 0.26s cubic-bezier(.34,1.56,.64,1);
            position: relative; overflow: hidden;
        }
        #agConfirmOverlay.ag-open #agConfirmBox {
            transform: scale(1) translateY(0);
        }
        #agConfirmBox::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, #0d9488, #14b8a6, #38bdf8);
        }
        #agConfirmIcon {
            width: 56px; height: 56px; border-radius: 1rem;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.75rem;
        }
        #agConfirmTitle {
            font-size: 1.1rem; font-weight: 800;
            color: #0f172a; text-align: center; margin-bottom: .4rem;
        }
        #agConfirmMessage {
            font-size: .875rem; color: #475569;
            text-align: center; line-height: 1.6; margin-bottom: 1.5rem;
        }
        .ag-confirm-btns {
            display: flex; gap: .75rem;
        }
        #agConfirmCancel {
            flex: 1; padding: .65rem 1rem; border-radius: .875rem;
            border: 1.5px solid #e2e8f0; background: #f8fafc;
            font-size: .875rem; font-weight: 600; color: #64748b;
            cursor: pointer; transition: all .15s;
        }
        #agConfirmCancel:hover { background: #f1f5f9; border-color: #cbd5e1; color: #334155; }
        #agConfirmOk {
            flex: 1; padding: .65rem 1rem; border-radius: .875rem;
            border: none; font-size: .875rem; font-weight: 700;
            color: #fff; cursor: pointer;
            transition: all .15s; position: relative; overflow: hidden;
        }
        #agConfirmOk:hover { filter: brightness(1.1); transform: translateY(-1px); box-shadow: 0 6px 20px -4px rgba(0,0,0,0.25); }
        #agConfirmOk:active { transform: translateY(0); }
    </style>
    @stack('styles')
</head>
<body class="h-full flex flex-col font-sans text-slate-800 antialiased selection:bg-unpam-500 selection:text-white">

    <!-- Navbar Atas -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200/80 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo & Brand Klinik Unpam 24 Jam -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-unpam-700 via-unpam-600 to-teal-400 flex items-center justify-center text-white shadow-md shadow-unpam-500/20 group-hover:scale-105 transition-transform">
                            <i data-lucide="cross" class="w-6 h-6 stroke-[2.5]"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-lg tracking-tight text-slate-900 group-hover:text-unpam-700 transition-colors">
                                    Klinik Unpam
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200 animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                    24 JAM
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 hidden sm:block">Sistem Antrian & Rekam Medis Terpadu</p>
                        </div>
                    </a>
                </div>

                <!-- Navigasi Tengah & Jam Digital -->
                <div class="hidden md:flex items-center gap-6">
                    @auth
                        @if(auth()->user()->isPasien())
                            <a href="{{ route('pasien.dashboard') }}" class="flex items-center gap-2 text-sm font-semibold {{ request()->routeIs('pasien.dashboard') ? 'text-unpam-700 bg-unpam-50 px-3 py-1.5 rounded-lg' : 'text-slate-600 hover:text-unpam-600' }}">
                                <i data-lucide="ticket" class="w-4 h-4"></i>
                                Antrian Saya
                            </a>
                            <a href="{{ route('pasien.riwayat-medis') }}" class="flex items-center gap-2 text-sm font-semibold {{ request()->routeIs('pasien.riwayat-medis*') ? 'text-unpam-700 bg-unpam-50 px-3 py-1.5 rounded-lg' : 'text-slate-600 hover:text-unpam-600' }}">
                                <i data-lucide="file-heart" class="w-4 h-4"></i>
                                Riwayat Medis
                            </a>
                        @elseif(auth()->user()->isSuster())
                            <a href="{{ route('suster.dashboard') }}" class="flex items-center gap-2 text-sm font-semibold {{ request()->routeIs('suster.dashboard') ? 'text-unpam-700 bg-unpam-50 px-3 py-1.5 rounded-lg' : 'text-slate-600 hover:text-unpam-600' }}">
                                <i data-lucide="activity" class="w-4 h-4"></i>
                                Monitor Antrian
                            </a>
                            <a href="{{ route('suster.riwayat-medis') }}" class="flex items-center gap-2 text-sm font-semibold {{ request()->routeIs('suster.riwayat-medis*') ? 'text-unpam-700 bg-unpam-50 px-3 py-1.5 rounded-lg' : 'text-slate-600 hover:text-unpam-600' }}">
                                <i data-lucide="folder-clock" class="w-4 h-4"></i>
                                Riwayat Medis
                            </a>
                            <a href="{{ route('suster.staff') }}" class="flex items-center gap-2 text-sm font-semibold {{ request()->routeIs('suster.staff*') ? 'text-unpam-700 bg-unpam-50 px-3 py-1.5 rounded-lg' : 'text-slate-600 hover:text-unpam-600' }}">
                                <i data-lucide="users" class="w-4 h-4"></i>
                                Manajemen Staf
                            </a>
                        @elseif(auth()->user()->isDokter())
                            <a href="{{ route('dokter.dashboard') }}" class="flex items-center gap-2 text-sm font-semibold {{ request()->routeIs('dokter.dashboard') ? 'text-unpam-700 bg-unpam-50 px-3 py-1.5 rounded-lg' : 'text-slate-600 hover:text-unpam-600' }}">
                                <i data-lucide="stethoscope" class="w-4 h-4"></i>
                                Ruang Pemeriksaan
                            </a>
                        @elseif(auth()->user()->isApoteker())
                            <a href="{{ route('apoteker.dashboard') }}" class="flex items-center gap-2 text-sm font-semibold {{ request()->routeIs('apoteker.dashboard') ? 'text-unpam-700 bg-unpam-50 px-3 py-1.5 rounded-lg' : 'text-slate-600 hover:text-unpam-600' }}">
                                <i data-lucide="pill" class="w-4 h-4"></i>
                                Loket Farmasi & Resep
                            </a>
                        @endif
                    @endauth

                    @auth
                    @if(!auth()->user()->isPasien())
                    <a href="{{ route('queue.monitor') }}" target="_blank" class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-colors">
                        <i data-lucide="tv" class="w-3.5 h-3.5 text-unpam-600"></i>
                        Layar Monitor Display
                        <i data-lucide="external-link" class="w-3 h-3 text-slate-400"></i>
                    </a>
                    @endif
                    @endauth
                </div>

                <!-- Bagian Kanan: User Info & Logout / Login -->
                <div class="flex items-center gap-3">
                    @auth
                        <div class="flex items-center gap-3">
                            <div class="text-right hidden sm:block">
                                <div class="text-sm font-bold text-slate-800 leading-tight">
                                    {{ auth()->user()->name }}
                                </div>
                                <div class="flex items-center justify-end gap-1.5 mt-0.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold
                                        @if(auth()->user()->isPasien()) bg-emerald-100 text-emerald-800
                                        @elseif(auth()->user()->isSuster()) bg-sky-100 text-sky-800
                                        @elseif(auth()->user()->isDokter()) bg-indigo-100 text-indigo-800
                                        @elseif(auth()->user()->isApoteker()) bg-amber-100 text-amber-800
                                        @endif">
                                        {{ auth()->user()->role_label }}
                                    </span>
                                    @if(auth()->user()->isDokter() && auth()->user()->polyclinic)
                                        <span class="text-[11px] text-slate-500 font-medium">({{ auth()->user()->polyclinic->name }})</span>
                                    @endif
                                    @if(in_array(auth()->user()->role, ['suster', 'dokter', 'apoteker']) && request()->routeIs(['suster.dashboard', 'dokter.dashboard', 'apoteker.dashboard']))
                                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200" title="Live Auto-Sync Aktif">
                                            <span id="syncIndicatorPulse" class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Auto-Sync Live
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" title="Keluar dari sistem" class="p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 hover:border-rose-200 transition-colors">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-unpam-600 hover:bg-unpam-700 text-white shadow-sm transition-colors">
                            <i data-lucide="log-in" class="w-4 h-4"></i>
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-xs transition-colors">
                            Daftar Pasien
                        </a>
                    @endauth
                </div>

            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        @auth
        <div class="md:hidden border-t border-slate-200 px-4 py-2 bg-slate-50 flex items-center justify-around text-xs">
            @if(auth()->user()->isPasien())
                <a href="{{ route('pasien.dashboard') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('pasien.dashboard') ? 'text-unpam-700 font-bold' : 'text-slate-600' }}">
                    <i data-lucide="ticket" class="w-4 h-4 mb-0.5"></i>
                    Antrian
                </a>
                <a href="{{ route('pasien.riwayat-medis') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('pasien.riwayat-medis*') ? 'text-unpam-700 font-bold' : 'text-slate-600' }}">
                    <i data-lucide="file-heart" class="w-4 h-4 mb-0.5"></i>
                    Riwayat
                </a>
            @elseif(auth()->user()->isSuster())
                <a href="{{ route('suster.dashboard') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('suster.dashboard') ? 'text-unpam-700 font-bold' : 'text-slate-600' }}">
                    <i data-lucide="activity" class="w-4 h-4 mb-0.5"></i>
                    Antrian
                </a>
                <a href="{{ route('suster.riwayat-medis') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('suster.riwayat-medis*') ? 'text-unpam-700 font-bold' : 'text-slate-600' }}">
                    <i data-lucide="folder-clock" class="w-4 h-4 mb-0.5"></i>
                    Riwayat
                </a>
                <a href="{{ route('suster.staff') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('suster.staff*') ? 'text-unpam-700 font-bold' : 'text-slate-600' }}">
                    <i data-lucide="users" class="w-4 h-4 mb-0.5"></i>
                    Staf Medis
                </a>
            @elseif(auth()->user()->isDokter())
                <a href="{{ route('dokter.dashboard') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('dokter.dashboard') ? 'text-unpam-700 font-bold' : 'text-slate-600' }}">
                    <i data-lucide="stethoscope" class="w-4 h-4 mb-0.5"></i>
                    Pemeriksaan
                </a>
            @elseif(auth()->user()->isApoteker())
                <a href="{{ route('apoteker.dashboard') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('apoteker.dashboard') ? 'text-unpam-700 font-bold' : 'text-slate-600' }}">
                    <i data-lucide="pill" class="w-4 h-4 mb-0.5"></i>
                    Apotek
                </a>
            @endif
            @if(!auth()->user()->isPasien())
            <a href="{{ route('queue.monitor') }}" target="_blank" class="flex flex-col items-center py-1 text-slate-600">
                <i data-lucide="tv" class="w-4 h-4 mb-0.5 text-unpam-600"></i>
                Layar Display
            </a>
            @endif
        </div>
        @endauth
    </header>

    <!-- Flash Messages Container -->
    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            @if(session('success'))
                <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 shadow-xs flex items-start gap-3">
                    <div class="p-1 bg-emerald-500 rounded-lg text-white mt-0.5">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1 text-sm font-medium text-emerald-800">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-200 p-4 shadow-xs flex items-start gap-3">
                    <div class="p-1 bg-rose-500 rounded-lg text-white mt-0.5">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1 text-sm font-medium text-rose-800">
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            @if(session('warning'))
                <div class="mb-6 rounded-2xl bg-amber-50 border border-amber-200 p-4 shadow-xs flex items-start gap-3">
                    <div class="p-1 bg-amber-500 rounded-lg text-white mt-0.5">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1 text-sm font-medium text-amber-800">
                        {{ session('warning') }}
                    </div>
                </div>
            @endif

            @if(session('info'))
                <div class="mb-6 rounded-2xl bg-sky-50 border border-sky-200 p-4 shadow-xs flex items-start gap-3">
                    <div class="p-1 bg-sky-500 rounded-lg text-white mt-0.5">
                        <i data-lucide="info" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1 text-sm font-medium text-sky-800">
                        {{ session('info') }}
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-200 p-4 shadow-xs">
                    <div class="flex items-center gap-2 text-rose-800 font-bold text-sm mb-2">
                        <i data-lucide="alert-octagon" class="w-4 h-4 text-rose-600"></i>
                        Mohon periksa kesalahan input berikut:
                    </div>
                    <ul class="list-disc list-inside text-xs text-rose-700 space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto bg-white border-t border-slate-200/80 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-700">Klinik Unpam 24 Jam</span>
                <span>&bull;</span>
                <span>Universitas Pamulang - Tangerang Selatan</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1 text-emerald-600 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-ping"></span>
                    Unit Gawat Darurat & Poliklinik Buka 24 Jam
                </span>
                <span>&bull;</span>
                <span>Emergency: (021) 741-2566</span>
            </div>
        </div>
    </footer>

    <script>
        // Inisialisasi ikon Lucide
        lucide.createIcons();

        // Audio Chime & Text to Speech Caller
        window.callQueueVoice = function(queueCode, polyclinicName) {
            if ('speechSynthesis' in window) {
                // Suara notifikasi singkat
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
                osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.15); // A5
                gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.4);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.45);

                setTimeout(() => {
                    const text = `Nomor Antrian, ${queueCode.replace('-', ' ')}, silakan menuju ke ${polyclinicName}`;
                    const utterance = new SpeechSynthesisUtterance(text);
                    utterance.lang = 'id-ID';
                    utterance.rate = 0.9;
                    window.speechSynthesis.speak(utterance);
                }, 500);
            }
        };

        // Seamless Live Sync untuk Dashboard Tenaga Medis (Suster, Dokter, Apoteker)
        // Bekerja di background dengan AJAX tanpa reload/flicker halaman sama sekali!
        @auth
            @if(in_array(auth()->user()->role, ['suster', 'dokter', 'apoteker']))
                @if(request()->routeIs(['suster.dashboard', 'dokter.dashboard', 'apoteker.dashboard']))
                    (function() {
                        let isInteracting = false;
                        let isSyncing = false;

                        // Deteksi saat pengguna sedang berinteraksi/mengetik
                        document.addEventListener('focusin', function(e) {
                            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
                                isInteracting = true;
                            }
                        });

                        document.addEventListener('focusout', function(e) {
                            // Beri jeda sedikit setelah selesai fokus
                            setTimeout(() => { isInteracting = false; }, 1000);
                        });

                        async function syncDashboardData() {
                            // Cegah sync ganda atau jika sedang berinteraksi / modal terbuka
                            const anyModalOpen = Array.from(document.querySelectorAll('[id^="modal"]')).some(m => !m.classList.contains('hidden'))
                                || document.getElementById('agConfirmOverlay')?.classList.contains('ag-open');
                            if (isInteracting || anyModalOpen || isSyncing) return;

                            isSyncing = true;
                            const syncIndicator = document.getElementById('syncIndicatorPulse');
                            if (syncIndicator) syncIndicator.classList.add('bg-amber-400');

                            try {
                                const response = await fetch(window.location.href, {
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });

                                if (response.ok) {
                                    const htmlText = await response.text();
                                    const parser = new DOMParser();
                                    const newDoc = parser.parseFromString(htmlText, 'text/html');

                                    const currentMain = document.querySelector('main');
                                    const newMain = newDoc.querySelector('main');

                                    if (currentMain && newMain) {
                                        // Bandingkan isi: jika ada perubahan antrian/status, update secara mulus!
                                        if (currentMain.innerHTML !== newMain.innerHTML) {
                                            currentMain.innerHTML = newMain.innerHTML;
                                            
                                            // Re-inisialisasi ikon Lucide setelah update DOM
                                            if (window.lucide) {
                                                window.lucide.createIcons();
                                            }
                                        }
                                    }
                                }
                            } catch (err) {
                                // Silent fail di background tanpa mengganggu pengguna
                                console.debug('Live sync background ping:', err);
                            } finally {
                                isSyncing = false;
                                if (syncIndicator) syncIndicator.classList.remove('bg-amber-400');
                            }
                        }

                        // Sync mulus setiap 4 detik
                        setInterval(syncDashboardData, 4000);
                    })();
                @endif
            @endif
        @endauth
    </script>
    @stack('scripts')

    <!-- ===== Global Custom Confirm Modal ===== -->
    <div id="agConfirmOverlay" role="dialog" aria-modal="true" aria-labelledby="agConfirmTitle">
        <div id="agConfirmBox">
            <div id="agConfirmIcon"></div>
            <div id="agConfirmTitle"></div>
            <div id="agConfirmMessage"></div>
            <div class="ag-confirm-btns">
                <button id="agConfirmCancel" type="button">Batal</button>
                <button id="agConfirmOk" type="button">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>

    <script>
    // ===== agConfirm: Global beautiful confirmation dialog =====
    (function() {
        const overlay   = document.getElementById('agConfirmOverlay');
        const box       = document.getElementById('agConfirmBox');
        const iconEl    = document.getElementById('agConfirmIcon');
        const titleEl   = document.getElementById('agConfirmTitle');
        const msgEl     = document.getElementById('agConfirmMessage');
        const cancelBtn = document.getElementById('agConfirmCancel');
        const okBtn     = document.getElementById('agConfirmOk');

        let _resolve = null;

        const PRESETS = {
            danger:  { bg: '#fff1f2', color: '#e11d48', icon: '🗑️', okBg: 'linear-gradient(135deg,#e11d48,#be123c)', title: 'Konfirmasi Tindakan' },
            warning: { bg: '#fffbeb', color: '#d97706', icon: '⚠️', okBg: 'linear-gradient(135deg,#f59e0b,#d97706)', title: 'Konfirmasi' },
            info:    { bg: '#eff6ff', color: '#2563eb', icon: '💬', okBg: 'linear-gradient(135deg,#3b82f6,#2563eb)', title: 'Konfirmasi' },
            success: { bg: '#f0fdf4', color: '#16a34a', icon: '✅', okBg: 'linear-gradient(135deg,#22c55e,#16a34a)', title: 'Konfirmasi' },
            default: { bg: '#f0fdfa', color: '#0d9488', icon: '❓', okBg: 'linear-gradient(135deg,#14b8a6,#0d9488)', title: 'Konfirmasi' },
        };

        function open(opts) {
            const preset = PRESETS[opts.type] || PRESETS.default;
            iconEl.textContent  = opts.icon  || preset.icon;
            iconEl.style.background = preset.bg;
            titleEl.textContent = opts.title || preset.title;
            msgEl.textContent   = opts.message || '';
            okBtn.textContent   = opts.okText    || 'Ya, Lanjutkan';
            cancelBtn.textContent = opts.cancelText || 'Batal';
            okBtn.style.background = preset.okBg;
            overlay.classList.add('ag-open');
            okBtn.focus();
        }

        function close() {
            overlay.classList.remove('ag-open');
        }

        okBtn.addEventListener('click', function() {
            close();
            if (_resolve) { _resolve(true); _resolve = null; }
        });

        cancelBtn.addEventListener('click', function() {
            close();
            if (_resolve) { _resolve(false); _resolve = null; }
        });

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                close();
                if (_resolve) { _resolve(false); _resolve = null; }
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && overlay.classList.contains('ag-open')) {
                close();
                if (_resolve) { _resolve(false); _resolve = null; }
            }
        });

        // Global API: returns Promise<boolean>
        window.agConfirm = function(opts) {
            if (typeof opts === 'string') opts = { message: opts };
            return new Promise(function(resolve) {
                _resolve = resolve;
                open(opts);
            });
        };
    })();

    // Helper: intercept form submission with agConfirm
    // Usage: <form data-confirm="message" data-confirm-type="danger" data-confirm-title="Judul">
    document.addEventListener('submit', function(e) {
        const form = e.target;
        const msg = form.dataset.confirm;
        if (!msg) return;
        e.preventDefault();
        agConfirm({
            message: msg,
            title: form.dataset.confirmTitle || undefined,
            type: form.dataset.confirmType || 'warning',
            icon: form.dataset.confirmIcon || undefined,
            okText: form.dataset.confirmOk || undefined,
        }).then(function(ok) {
            if (ok) form.submit();
        });
    }, true);

    // Helper: intercept button clicks with data-confirm on the button
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-btn-confirm]');
        if (!btn) return;
        const msg = btn.dataset.btnConfirm;
        if (!msg) return;
        e.preventDefault(); e.stopPropagation();
        agConfirm({
            message: msg,
            title: btn.dataset.confirmTitle || undefined,
            type: btn.dataset.confirmType || 'warning',
            icon: btn.dataset.confirmIcon || undefined,
            okText: btn.dataset.confirmOk || undefined,
        }).then(function(ok) {
            if (!ok) return;
            const form = btn.closest('form') || document.getElementById(btn.dataset.formId);
            if (form) form.submit();
            else if (btn.dataset.href) window.location.href = btn.dataset.href;
        });
    }, true);
    </script>
</body>
</html>
