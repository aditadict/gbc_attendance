<?php

namespace App\Filament\Clusters\Karyawan;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class KaryawanCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Karyawan';

    protected static ?string $clusterBreadcrumb = 'Karyawan';
}
