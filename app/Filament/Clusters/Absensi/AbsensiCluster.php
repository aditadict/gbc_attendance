<?php

namespace App\Filament\Clusters\Absensi;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class AbsensiCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Absensi';

    protected static ?string $clusterBreadcrumb = 'Absensi';
}
