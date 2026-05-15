<x-filament-panels::page>
    @php
        $employee = $this->getEmployee();
        $attendance = $this->getTodayAttendance();
        $hasCheckedIn = $attendance && $attendance->check_in_at;
        $hasCheckedOut = $attendance && $attendance->check_out_at;
        $isHybrid = $employee && $employee->work_type === 'Hybrid';
    @endphp

    <div
        x-data="{
            latitude: $wire.entangle('latitude'),
            longitude: $wire.entangle('longitude'),
            locationStatus: 'idle',
            locationError: '',
            getLocation() {
                this.locationStatus = 'loading';
                if (!navigator.geolocation) {
                    this.locationStatus = 'error';
                    this.locationError = 'Browser tidak mendukung geolocation.';
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.latitude = pos.coords.latitude;
                        this.longitude = pos.coords.longitude;
                        this.locationStatus = 'success';
                    },
                    (err) => {
                        this.locationStatus = 'error';
                        this.locationError = 'Gagal mendapatkan lokasi: ' + err.message;
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            }
        }"
        x-init="getLocation()"
        class="space-y-6"
    >
        {{-- Status Karyawan --}}
        @if($employee)
        <x-filament::section>
            <x-slot name="heading">Selamat datang, {{ $employee->name }}</x-slot>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="font-medium text-gray-500">Cabang:</span> {{ $employee->branch->name }}</div>
                <div><span class="font-medium text-gray-500">Tipe Kerja:</span> {{ $employee->work_type }}</div>
                <div><span class="font-medium text-gray-500">Jam Masuk:</span> {{ $employee->branch->work_start_time }}</div>
                <div><span class="font-medium text-gray-500">Jam Pulang:</span> {{ $employee->branch->work_end_time }}</div>
                @if($employee->branch->late_tolerance_minutes > 0)
                <div><span class="font-medium text-gray-500">Toleransi Terlambat:</span> {{ $employee->branch->late_tolerance_minutes }} menit</div>
                @endif
            </div>
        </x-filament::section>
        @endif

        {{-- Status Lokasi --}}
        <x-filament::section>
            <x-slot name="heading">Lokasi Anda</x-slot>
            <div class="space-y-2">
                <div x-show="locationStatus === 'loading'" class="flex items-center gap-2 text-yellow-600">
                    <x-filament::loading-indicator class="h-5 w-5"/>
                    <span>Mendeteksi lokasi...</span>
                </div>
                <div x-show="locationStatus === 'success'" class="flex items-center gap-2 text-green-600">
                    <x-heroicon-o-map-pin class="h-5 w-5"/>
                    <span>Lokasi berhasil didapatkan</span>
                </div>
                <div x-show="locationStatus === 'error'" class="flex items-center gap-2 text-red-600">
                    <x-heroicon-o-exclamation-circle class="h-5 w-5"/>
                    <span x-text="locationError"></span>
                </div>
                <div x-show="locationStatus === 'success'" class="text-sm text-gray-500">
                    Lat: <span x-text="latitude.toFixed(6)"></span>, Lng: <span x-text="longitude.toFixed(6)"></span>
                </div>
                <x-filament::button
                    color="gray"
                    size="sm"
                    x-on:click="getLocation()"
                >
                    Perbarui Lokasi
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Status Absensi Hari Ini --}}
        <x-filament::section>
            <x-slot name="heading">Absensi — {{ today()->translatedFormat('l, d F Y') }}</x-slot>
            <div class="space-y-4">
                @if($attendance)
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        @if($attendance->check_in_at)
                        <div>
                            <span class="font-medium text-gray-500">Check-in:</span>
                            {{ $attendance->check_in_at->format('H:i') }}
                            @if($attendance->status === 'late')
                                <span class="ml-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">
                                    Terlambat {{ $attendance->late_minutes }} menit
                                </span>
                            @endif
                        </div>
                        @endif
                        @if($attendance->check_out_at)
                        <div>
                            <span class="font-medium text-gray-500">Check-out:</span>
                            {{ $attendance->check_out_at->format('H:i') }}
                        </div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-gray-500">Anda belum melakukan absensi hari ini.</p>
                @endif

                {{-- Pilihan WFO/WFH untuk Hybrid --}}
                @if($isHybrid && !$hasCheckedIn)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Kerja Hari Ini</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model="workTypeChoice" value="WFO" class="text-primary-600">
                            <span class="text-sm">WFO</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model="workTypeChoice" value="WFH" class="text-primary-600">
                            <span class="text-sm">WFH</span>
                        </label>
                    </div>
                </div>
                @endif

                {{-- Tombol Check-in / Check-out --}}
                <div class="flex gap-3">
                    @if(!$hasCheckedIn)
                    <x-filament::button
                        wire:click="checkIn"
                        color="success"
                        icon="heroicon-o-arrow-right-end-on-rectangle"
                        x-bind:disabled="locationStatus !== 'success'"
                    >
                        Check-in
                    </x-filament::button>
                    @elseif(!$hasCheckedOut)
                    <x-filament::button
                        wire:click="checkOut"
                        color="danger"
                        icon="heroicon-o-arrow-left-end-on-rectangle"
                    >
                        Check-out
                    </x-filament::button>
                    @else
                    <div class="flex items-center gap-2 text-green-600 text-sm font-medium">
                        <x-heroicon-o-check-circle class="h-5 w-5"/>
                        Absensi hari ini selesai.
                    </div>
                    @endif
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
