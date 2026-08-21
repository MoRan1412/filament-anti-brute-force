<?php

namespace MoRan1412\FilamentAntiBruteForce\Pages;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Clusters\Cluster;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use MoRan1412\FilamentAntiBruteForce\Models\LoginAttempt;

class AntiBruteForce extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament-anti-brute-force::pages.anti-brute-force';

    /**
     * Filament v5 pages default to "any authenticated panel user"; gate the page
     * behind the admin_page config and an optional ability.
     */
    public static function canAccess(): bool
    {
        if (! (bool) config('anti-brute-force.admin_page.enabled', true)) {
            return false;
        }

        $ability = config('anti-brute-force.admin_page.authorize');

        if (blank($ability)) {
            return true;
        }

        $user = Auth::user();

        return $user !== null && method_exists($user, 'can') && (bool) $user->can($ability);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) config('anti-brute-force.admin_page.enabled', true);
    }

    public static function getDefaultSlug(): string
    {
        return (string) (config('anti-brute-force.admin_page.slug') ?: 'anti-brute-force');
    }

    public static function getCluster(): ?string
    {
        $cluster = config('anti-brute-force.admin_page.cluster');

        return is_string($cluster) && is_subclass_of($cluster, Cluster::class) ? $cluster : null;
    }

    public static function getNavigationLabel(): string
    {
        return (string) (config('anti-brute-force.admin_page.navigation_label')
            ?: __('filament-anti-brute-force::anti-brute-force.page.navigation_label'));
    }

    public static function getNavigationIcon(): string
    {
        return (string) (config('anti-brute-force.admin_page.navigation_icon')
            ?: 'heroicon-o-shield-exclamation');
    }

    public static function getNavigationGroup(): ?string
    {
        $group = config('anti-brute-force.admin_page.navigation_group');

        return is_string($group) ? $group : null;
    }

    public static function getNavigationSort(): ?int
    {
        $sort = config('anti-brute-force.admin_page.navigation_sort');

        return is_int($sort) ? $sort : null;
    }

    public function getTitle(): string
    {
        return __('filament-anti-brute-force::anti-brute-force.page.title');
    }

    public function getHeading(): string
    {
        return __('filament-anti-brute-force::anti-brute-force.page.heading');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(LoginAttempt::query())
            ->defaultSort('last_attempt_at', 'desc')
            ->columns([
                TextColumn::make('ip')
                    ->label(__('filament-anti-brute-force::anti-brute-force.page.table.columns.ip'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('filament-anti-brute-force::anti-brute-force.page.table.columns.email'))
                    ->searchable(),
                TextColumn::make('attempts')
                    ->label(__('filament-anti-brute-force::anti-brute-force.page.table.columns.attempts'))
                    ->badge(),
                TextColumn::make('lockout_count')
                    ->label(__('filament-anti-brute-force::anti-brute-force.page.table.columns.lockout_count'))
                    ->badge(),
                TextColumn::make('locked_until')
                    ->label(__('filament-anti-brute-force::anti-brute-force.page.table.columns.locked_until'))
                    ->dateTime()
                    ->color(fn (LoginAttempt $record): string => $record->isLocked() ? 'danger' : 'gray'),
                TextColumn::make('last_attempt_at')
                    ->label(__('filament-anti-brute-force::anti-brute-force.page.table.columns.last_attempt_at'))
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament-anti-brute-force::anti-brute-force.page.table.filters.status'))
                    ->options([
                        'locked' => __('filament-anti-brute-force::anti-brute-force.page.table.filters.locked'),
                        'tracked' => __('filament-anti-brute-force::anti-brute-force.page.table.filters.tracked'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value']) {
                            'locked' => $query->where('locked_until', '>', now()),
                            'tracked' => $query->where('attempts', '>', 0),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                Action::make('unblock')
                    ->label(__('filament-anti-brute-force::anti-brute-force.page.table.actions.unblock'))
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (LoginAttempt $record): void {
                        $record->unlock();

                        Notification::make()
                            ->title(__('filament-anti-brute-force::anti-brute-force.page.table.actions.unblocked'))
                            ->success()
                            ->send();
                    }),
                DeleteAction::make()
                    ->label(__('filament-anti-brute-force::anti-brute-force.page.table.actions.delete')),
            ])
            ->bulkActions([
                BulkAction::make('unblockMany')
                    ->label(__('filament-anti-brute-force::anti-brute-force.page.table.actions.unblock_many'))
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): Collection {
                        $records->each(fn (LoginAttempt $record) => $record->unlock());

                        return $records;
                    }),
                DeleteBulkAction::make(),
            ]);
    }
}
