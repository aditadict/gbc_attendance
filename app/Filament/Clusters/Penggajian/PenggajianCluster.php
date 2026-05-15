<?php

namespace App\Filament\Clusters\Penggajian;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class PenggajianCluster extends Cluster
{
    protected static ?string $navigationLabel = 'Penggajian';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 4;
}
