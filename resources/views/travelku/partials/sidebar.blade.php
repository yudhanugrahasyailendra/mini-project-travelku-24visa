@php
    $active = $activeMenu ?? 'bookings';
@endphp

<aside
    class="w-56 shrink-0 bg-teal-900 flex flex-col fixed md:relative inset-y-0 left-0 z-50 h-dvh transform transition-transform duration-200 md:translate-x-0"
    :class="mobileNav ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
>
    <div class="px-4 sm:px-5 py-5 border-b border-teal-800 shrink-0">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-teal-400 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-teal-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-white font-extrabold text-base leading-none tracking-tight truncate">TravelKu</p>
                <p class="text-teal-400 text-[10px] leading-none mt-0.5 truncate">Sistem Agen Perjalanan</p>
            </div>
        </div>
    </div>

    <nav class="flex-1 min-h-0 px-3 py-3 space-y-1 overflow-y-auto">
        <a
            href="{{ route('travelku.index') }}"
            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-left transition-colors {{ $active === 'bookings' ? 'bg-teal-700 text-white' : 'text-teal-300 hover:bg-teal-800 hover:text-white' }}"
        >
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Manajemen Booking
        </a>
        <button type="button" disabled class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-teal-300 opacity-60 text-left cursor-not-allowed">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Data Pelanggan
        </button>
        <a
            href="{{ route('packages.index') }}"
            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-left transition-colors {{ $active === 'packages' ? 'bg-teal-700 text-white' : 'text-teal-300 hover:bg-teal-800 hover:text-white' }}"
        >
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Paket Wisata
        </a>
        <button type="button" disabled class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-teal-300 opacity-60 text-left cursor-not-allowed">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Laporan
        </button>
    </nav>

    <div class="px-4 py-3 border-t border-teal-800 shrink-0">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-teal-600 flex items-center justify-center text-xs text-white font-bold shrink-0">ST</div>
            <div class="min-w-0">
                <p class="text-white text-xs font-medium truncate">Staff Agen</p>
                <p class="text-teal-400 text-[10px] truncate">Internal System</p>
            </div>
        </div>
    </div>
</aside>
