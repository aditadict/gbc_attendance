<?php

namespace App\Fields;

use Filament\Forms\Components\Field;

class LeafletMapField extends Field
{
    protected string $view = 'fields.leaflet-map';

    protected float $defaultLat  = -6.9932;
    protected float $defaultLng  = 110.4229;
    protected int   $defaultZoom = 14;

    public function defaultCenter(float $lat, float $lng): static
    {
        $this->defaultLat = $lat;
        $this->defaultLng = $lng;
        return $this;
    }

    public function zoom(int $zoom): static
    {
        $this->defaultZoom = $zoom;
        return $this;
    }

    public function getDefaultLat(): float  { return $this->defaultLat; }
    public function getDefaultLng(): float  { return $this->defaultLng; }
    public function getDefaultZoom(): int   { return $this->defaultZoom; }
}
