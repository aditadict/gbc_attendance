<x-filament-panels::page>
@php
    $employee      = $this->getEmployee();
    $branch        = $employee?->branch;
    $attendance    = $this->getTodayAttendance();
    $hasCheckedIn  = $attendance && $attendance->check_in_at;
    $hasCheckedOut = $attendance && $attendance->check_out_at;
    $isHybrid      = $employee && $employee->work_type === 'Hybrid';
    $branchMap     = $this->getBranchMapData();

    $initial = $employee ? strtoupper(substr($employee->name, 0, 1)) : '?';

    $pageState = match(true) {
        !$employee     => 'no-employee',
        $hasCheckedOut => 'done',
        $hasCheckedIn  => 'checked-in',
        default        => 'ready',
    };
@endphp

<div x-data="checkInPage(@js($branchMap))" class="space-y-5">

    {{-- ── Status Banner ───────────────────────────────────────────── --}}
    <div class="rounded-2xl p-5 text-white shadow-sm
        @if($pageState === 'done') bg-gradient-to-br from-emerald-500 to-teal-600
        @elseif($pageState === 'checked-in') bg-gradient-to-br from-blue-500 to-indigo-600
        @else bg-gradient-to-br from-primary-500 to-primary-700 @endif">

        <div class="flex items-center justify-between">
            <div>
                <p class="text-white/70 text-xs font-medium uppercase tracking-wide">
                    {{ today()->translatedFormat('l, d F Y') }}
                </p>
                <p class="text-3xl font-bold tracking-tight mt-0.5" x-text="clock">--:--</p>
            </div>
            <div class="text-right">
                @if($pageState === 'done')
                    <span class="inline-flex items-center gap-1.5 bg-white/20 rounded-full px-3 py-1 text-sm font-semibold">
                        <svg style="width:1rem;height:1rem;flex-shrink:0" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/></svg>
                        Selesai
                    </span>
                @elseif($pageState === 'checked-in')
                    <span class="inline-flex items-center gap-1.5 bg-white/20 rounded-full px-3 py-1 text-sm font-semibold">
                        <svg style="width:1rem;height:1rem;flex-shrink:0" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM12.75 6a.75.75 0 00-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 000-1.5h-3.75V6z" clip-rule="evenodd"/></svg>
                        Sudah Check-in
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 bg-white/20 rounded-full px-3 py-1 text-sm font-semibold">
                        <svg style="width:1rem;height:1rem;flex-shrink:0" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm4.28 10.28a.75.75 0 000-1.06l-3-3a.75.75 0 10-1.06 1.06l1.72 1.72H8.25a.75.75 0 000 1.5h5.69l-1.72 1.72a.75.75 0 101.06 1.06l3-3z" clip-rule="evenodd"/></svg>
                        Siap Absen
                    </span>
                @endif
            </div>
        </div>

        {{-- Check-in / Check-out times --}}
        @if($hasCheckedIn)
        <div class="mt-4 grid grid-cols-2 gap-3">
            <div class="bg-white/10 rounded-xl p-3">
                <p class="text-white/60 text-xs">Check-in</p>
                <p class="text-lg font-bold">{{ $attendance->check_in_at->format('H:i') }}</p>
                @if($attendance->status === 'late')
                <p class="text-xs text-yellow-300">Terlambat {{ $attendance->late_minutes }} menit</p>
                @else
                <p class="text-xs text-emerald-300">Tepat waktu</p>
                @endif
            </div>
            <div class="bg-white/10 rounded-xl p-3">
                <p class="text-white/60 text-xs">Check-out</p>
                @if($hasCheckedOut)
                <p class="text-lg font-bold">{{ $attendance->check_out_at->format('H:i') }}</p>
                @else
                <p class="text-lg font-bold text-white/40">--:--</p>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- ── Main Grid ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5 items-start">

        {{-- ── MAP (3/5) — wire:ignore stops Livewire touching Leaflet ── --}}
        <div class="lg:col-span-3 space-y-3" wire:ignore>
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
                {{-- Map header --}}
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg style="width:1rem;height:1rem;flex-shrink:0" class="text-primary-500" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-2.003 3.5-4.697 3.5-8.027a8 8 0 00-16 0c0 3.33 1.556 6.024 3.5 8.027a19.583 19.583 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
                        <span class="font-semibold text-sm text-gray-700 dark:text-gray-200">
                            {{ $branch?->name ?? 'Peta Lokasi' }}
                        </span>
                        @if($branch?->radius)
                        <span class="text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 px-2 py-0.5 rounded-full">
                            r = {{ number_format($branch->radius) }} m
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Leaflet container --}}
                <div id="employee-checkin-map" style="height:300px;z-index:1;"></div>

                {{-- Location status bar --}}
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-sm">
                        <div x-show="locationStatus === 'idle' || locationStatus === 'loading'"
                             class="flex items-center gap-1.5 text-gray-400">
                            <x-filament::loading-indicator class="h-4 w-4" style="width:1rem;height:1rem"/>
                            <span>Mendeteksi lokasi…</span>
                        </div>
                        <div x-show="locationStatus === 'success'" class="flex items-center gap-2">
                            <span
                                :class="isInsideRadius
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
                                    : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'"
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold"
                            >
                                <span x-text="isInsideRadius ? '✓ Dalam radius' : '✗ Luar radius'"></span>
                            </span>
                            <span class="text-gray-500 text-xs" x-text="distanceText ? ('Jarak: ' + distanceText) : ''"></span>
                        </div>
                        <div x-show="locationStatus === 'error'"
                             class="flex items-center gap-1.5 text-red-500 text-xs">
                            <svg style="width:1rem;height:1rem;flex-shrink:0" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd"/></svg>
                            <span x-text="locationError"></span>
                        </div>
                    </div>
                    <button
                        x-on:click="getLocation()"
                        class="shrink-0 text-xs text-primary-600 dark:text-primary-400 font-medium hover:underline flex items-center gap-1"
                    >
                        <svg style="width:.75rem;height:.75rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        Perbarui
                    </button>
                </div>
            </div>
        </div>

        {{-- ── RIGHT PANEL (2/5) ─────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Employee card --}}
            @if($employee)
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center shrink-0">
                        <span class="text-primary-700 dark:text-primary-300 font-bold text-xl">{{ $initial }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $employee->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $employee->position?->name }} &bull; {{ $employee->department?->name }}</p>
                        <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-medium
                            @if($employee->work_type === 'WFO') bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300
                            @elseif($employee->work_type === 'WFH') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300
                            @else bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300 @endif">
                            {{ $employee->work_type }}
                        </span>
                    </div>
                </div>
            </div>
            @endif

            {{-- Schedule card --}}
            @if($branch)
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Jadwal Kerja</p>
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <svg style="width:1rem;height:1rem;flex-shrink:0" class="text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                            Jam Masuk
                        </div>
                        <span class="font-semibold text-gray-900 dark:text-white text-sm">
                            {{ \Carbon\Carbon::parse($branch->work_start_time)->format('H:i') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <svg style="width:1rem;height:1rem;flex-shrink:0" class="text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                            Jam Pulang
                        </div>
                        <span class="font-semibold text-gray-900 dark:text-white text-sm">
                            {{ \Carbon\Carbon::parse($branch->work_end_time)->format('H:i') }}
                        </span>
                    </div>
                    @if($branch->late_tolerance_minutes > 0)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <svg style="width:1rem;height:1rem;flex-shrink:0" class="text-yellow-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Toleransi
                        </div>
                        <span class="font-semibold text-gray-900 dark:text-white text-sm">
                            {{ $branch->late_tolerance_minutes }} menit
                        </span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Action card --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 space-y-4">

                {{-- Hybrid selector --}}
                @if($isHybrid && !$hasCheckedIn)
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Tipe Kerja Hari Ini</p>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center justify-center gap-2 border-2 rounded-xl p-2.5 cursor-pointer text-sm font-medium transition-colors"
                            :class="$wire.workTypeChoice === 'WFO'
                                ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300'
                                : 'border-gray-200 dark:border-gray-700 text-gray-500'">
                            <input type="radio" wire:model="workTypeChoice" value="WFO" class="sr-only">
                            <svg style="width:1rem;height:1rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                            WFO
                        </label>
                        <label class="flex items-center justify-center gap-2 border-2 rounded-xl p-2.5 cursor-pointer text-sm font-medium transition-colors"
                            :class="$wire.workTypeChoice === 'WFH'
                                ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300'
                                : 'border-gray-200 dark:border-gray-700 text-gray-500'">
                            <input type="radio" wire:model="workTypeChoice" value="WFH" class="sr-only">
                            <svg style="width:1rem;height:1rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                            WFH
                        </label>
                    </div>
                </div>
                @endif

                {{-- Main action button --}}
                @if($pageState === 'done')
                <div class="flex flex-col items-center gap-1 py-2 text-emerald-600 dark:text-emerald-400">
                    <svg style="width:2.5rem;height:2.5rem" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.491 4.491 0 01-3.497-1.307 4.491 4.491 0 01-1.307-3.497A4.49 4.49 0 012.25 12a4.49 4.49 0 011.549-3.397 4.491 4.491 0 011.307-3.497 4.491 4.491 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/></svg>
                    <p class="font-semibold text-sm">Absensi hari ini selesai</p>
                    <p class="text-xs text-gray-400">Sampai jumpa besok!</p>
                </div>

                @elseif($pageState === 'checked-in')
                <div>
                    <p class="text-xs text-gray-400 text-center mb-3">Sudah check-in pukul {{ $attendance->check_in_at->format('H:i') }}</p>
                    <button
                        x-on:click="$wire.checkOut(userLat, userLng)"
                        class="w-full flex items-center justify-center gap-2.5 bg-red-500 hover:bg-red-600 active:bg-red-700
                               text-white font-bold text-base rounded-xl py-4 transition-colors shadow-sm"
                    >
                        <svg style="width:1.25rem;height:1.25rem;flex-shrink:0" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M7.5 3.75A1.5 1.5 0 006 5.25v13.5a1.5 1.5 0 001.5 1.5h6a1.5 1.5 0 001.5-1.5V15a.75.75 0 011.5 0v3.75a3 3 0 01-3 3h-6a3 3 0 01-3-3V5.25a3 3 0 013-3h6a3 3 0 013 3V9A.75.75 0 0115 9V5.25a1.5 1.5 0 00-1.5-1.5h-6zm10.72 4.72a.75.75 0 011.06 0l3 3a.75.75 0 010 1.06l-3 3a.75.75 0 11-1.06-1.06l1.72-1.72H9a.75.75 0 010-1.5h10.94l-1.72-1.72a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
                        Check-out Sekarang
                    </button>
                </div>

                @else
                <div>
                    <button
                        x-on:click="$wire.checkIn(userLat, userLng)"
                        :disabled="locationStatus !== 'success'"
                        :class="locationStatus === 'success'
                            ? 'bg-primary-600 hover:bg-primary-700 active:bg-primary-800 shadow-sm cursor-pointer'
                            : 'bg-gray-200 dark:bg-gray-700 cursor-not-allowed'"
                        class="w-full flex items-center justify-center gap-2.5 text-white font-bold text-base rounded-xl py-4 transition-colors"
                    >
                        <span x-show="locationStatus === 'success'" style="display:none">
                            <svg style="width:1.25rem;height:1.25rem;flex-shrink:0" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M16.5 3.75a1.5 1.5 0 011.5 1.5v13.5a1.5 1.5 0 01-1.5 1.5h-6a1.5 1.5 0 01-1.5-1.5V15a.75.75 0 00-1.5 0v3.75a3 3 0 003 3h6a3 3 0 003-3V5.25a3 3 0 00-3-3h-6a3 3 0 00-3 3V9A.75.75 0 009 9V5.25a1.5 1.5 0 011.5-1.5h6zM5.78 8.47a.75.75 0 00-1.06 0l-3 3a.75.75 0 000 1.06l3 3a.75.75 0 001.06-1.06l-1.72-1.72H15a.75.75 0 000-1.5H4.06l1.72-1.72a.75.75 0 000-1.06z" clip-rule="evenodd"/></svg>
                        </span>
                        <span x-show="locationStatus !== 'success'">
                            <x-filament::loading-indicator style="width:1.25rem;height:1.25rem;flex-shrink:0"/>
                        </span>
                        <span x-text="locationStatus === 'success' ? 'Check-in Sekarang' : 'Mendeteksi Lokasi…'"></span>
                    </button>
                    <p class="text-xs text-center text-gray-400 mt-2">
                        Tombol aktif setelah lokasi terdeteksi
                    </p>
                </div>
                @endif

            </div>
        </div>

    </div>{{-- /grid --}}
</div>

<script>
window.checkInPage = function (branchMap) {
    return {
        map:            null,
        userMarker:     null,
        locationStatus: 'idle',
        locationError:  '',
        isInsideRadius: false,
        distanceText:   '',
        userLat:        0,
        userLng:        0,
        clock:          '--:--',

        async init() {
            const tick = () => {
                const now = new Date();
                this.clock = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            };
            tick();
            setInterval(tick, 1000);

            await this._loadLeaflet();
            await new Promise(resolve => {
                const check = () => document.getElementById('employee-checkin-map')
                    ? resolve()
                    : requestAnimationFrame(check);
                check();
            });
            this._initMap();
            this.getLocation();
        },

        _initMap() {
            const lat = branchMap.lat || -6.9932;
            const lng = branchMap.lng || 110.4229;

            this.map = L.map(document.getElementById('employee-checkin-map'), {
                center:             [lat, lng],
                zoom:               15,
                zoomControl:        false,
                attributionControl: false,
            });

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(this.map);
            L.control.zoom({ position: 'bottomright' }).addTo(this.map);

            if (branchMap.lat && branchMap.lng) {
                const branchIcon = L.divIcon({
                    className: '',
                    html: '<div style="width:18px;height:18px;background:#ef4444;border:3px solid white;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,.4);"></div>',
                    iconSize: [18, 18], iconAnchor: [9, 9],
                });
                L.marker([branchMap.lat, branchMap.lng], { icon: branchIcon })
                    .bindPopup(`<b>${branchMap.name}</b><br>Radius: ${branchMap.radius} m`)
                    .addTo(this.map);

                if (branchMap.radius) {
                    L.circle([branchMap.lat, branchMap.lng], {
                        radius: branchMap.radius,
                        color: '#3b82f6', fillColor: '#93c5fd',
                        fillOpacity: 0.15, weight: 2, dashArray: '6 4',
                    }).addTo(this.map);
                }
            }
        },

        getLocation() {
            this.locationStatus = 'loading';
            if (!navigator.geolocation) {
                this.locationStatus = 'error';
                this.locationError  = 'Browser tidak mendukung geolocation.';
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;

                    this.userLat        = lat;
                    this.userLng        = lng;
                    this.locationStatus = 'success';

                    const userIcon = L.divIcon({
                        className: '',
                        html: '<div style="width:14px;height:14px;background:#22c55e;border:3px solid white;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,.4);"></div>',
                        iconSize: [14, 14], iconAnchor: [7, 7],
                    });

                    if (this.userMarker) {
                        this.userMarker.setLatLng([lat, lng]);
                    } else {
                        this.userMarker = L.marker([lat, lng], { icon: userIcon })
                            .bindPopup('Lokasi Anda').addTo(this.map);
                    }

                    if (branchMap.lat && branchMap.lng) {
                        this.map.fitBounds(
                            L.latLngBounds([[lat, lng], [branchMap.lat, branchMap.lng]]),
                            { padding: [48, 48] }
                        );
                        const dist          = this._haversine(lat, lng, branchMap.lat, branchMap.lng);
                        this.isInsideRadius = dist <= branchMap.radius;
                        this.distanceText   = dist < 1000
                            ? Math.round(dist) + ' m'
                            : (dist / 1000).toFixed(2) + ' km';
                    }
                },
                (err) => {
                    this.locationStatus = 'error';
                    this.locationError  = 'Gagal mendapatkan lokasi: ' + err.message;
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        },

        _haversine(lat1, lng1, lat2, lng2) {
            const R = 6371000;
            const φ1 = lat1 * Math.PI / 180, φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lng2 - lng1) * Math.PI / 180;
            const a  = Math.sin(Δφ/2)**2 + Math.cos(φ1)*Math.cos(φ2)*Math.sin(Δλ/2)**2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        },

        async _loadLeaflet() {
            if (window.L) return;
            const cssHref = @js(asset('vendor/leaflet/leaflet.css'));
            const jsSrc   = @js(asset('vendor/leaflet/leaflet.js'));
            if (!document.getElementById('leaflet-css')) {
                const l = document.createElement('link');
                l.id = 'leaflet-css'; l.rel = 'stylesheet'; l.href = cssHref;
                document.head.appendChild(l);
            }
            await new Promise(resolve => {
                if (window.L) { resolve(); return; }
                const existing = document.getElementById('leaflet-js');
                if (existing) {
                    const poll = setInterval(() => { if (window.L) { clearInterval(poll); resolve(); } }, 30);
                } else {
                    const s = document.createElement('script');
                    s.id = 'leaflet-js'; s.src = jsSrc; s.onload = resolve;
                    document.head.appendChild(s);
                }
            });
        },
    };
};
</script>
</x-filament-panels::page>
