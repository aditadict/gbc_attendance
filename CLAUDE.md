# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

GBC Attendance is an HR/attendance management system for Global Bahtera College, built with Laravel 13 and Filament 4. The entire UI is a single Filament admin panel (no separate frontend routes).

## Commands

```bash
# First-time setup
composer setup

# Start all dev services (server, queue, logs, vite) concurrently
composer dev

# Run tests
composer test

# Run a single test
php artisan test --filter TestName

# Lint (Laravel Pint)
./vendor/bin/pint

# Build frontend assets
npm run build
```

## Architecture

### Filament Admin Panel
The panel is mounted at the root path (`/`) via [AdminPanelProvider](app/Providers/Filament/AdminPanelProvider.php). Resources, Pages, Widgets, and Clusters are all auto-discovered from their respective directories under `app/Filament/`.

Panel access is restricted to users with `@atlasdigitalize.com` or `@gbc.com` email addresses (enforced in `User::canAccessPanel()`).

### Filament Clusters
Resources are grouped using Filament Clusters. The existing cluster is `OrganisasiCluster` at `app/Filament/Clusters/Organisasi/`. When adding a resource to a cluster, place it at `app/Filament/Clusters/{ClusterName}/Resources/` and set the `$cluster` property on the resource.

### Domain Models
The organizational hierarchy (all in early schema stage — columns not yet defined in migrations):
- `Company` → `Branch` → `Department` → `Position`
- `Holiday` — public holidays calendar
- `SalaryComponent` — payroll component definitions
- `User` — implements `FilamentUser`; attributes use PHP 8 `#[Fillable]` and `#[Hidden]` attributes instead of array properties

### Testing
Tests use Pest with in-memory SQLite (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`). Tests live in `tests/Unit/` and `tests/Feature/`.
