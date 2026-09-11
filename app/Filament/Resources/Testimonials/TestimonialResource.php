<?php

namespace App\Filament\Resources\Testimonials;

use App\Enums\ModerationStatus;
use App\Filament\Resources\Testimonials\Pages\ListTestimonials;
use App\Models\Testimonial;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

/**
 * Moderasi buku tamu & testimoni jamaah (PRD 5.2.7).
 *
 * Testimoni hanya masuk lewat form publik, jadi resource ini fokus pada
 * meninjau dan menyetujui/menolak, bukan membuat record baru.
 */
class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|UnitEnum|null $navigationGroup = 'Layanan Jamaah';

    protected static ?string $navigationLabel = 'Buku Tamu & Testimoni';

    protected static ?string $modelLabel = 'testimoni';

    protected static ?string $pluralModelLabel = 'testimoni';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name')->label('Nama')->placeholder('Anonim'),
            TextEntry::make('created_at')->label('Dikirim')->dateTime('d F Y, H:i'),
            TextEntry::make('message')->label('Pesan')->columnSpanFull(),
            TextEntry::make('status')->label('Status')->badge(),
            TextEntry::make('moderator.name')->label('Dimoderasi oleh')->placeholder('Belum dimoderasi'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->placeholder('Anonim')
                    ->searchable(),

                TextColumn::make('message')
                    ->label('Pesan')
                    ->wrap()
                    ->limit(90)
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Dikirim')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('moderator.name')
                    ->label('Dimoderasi oleh')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ModerationStatus::class)
                    ->default(ModerationStatus::Menunggu->value),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Testimoni akan tampil di halaman publik.')
                    ->visible(fn (Testimonial $record): bool => $record->status !== ModerationStatus::Disetujui
                        && Auth::user()?->can('moderate', $record) === true)
                    ->action(fn (Testimonial $record) => static::moderate($record, ModerationStatus::Disetujui)),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Testimoni tidak akan ditampilkan ke publik.')
                    ->visible(fn (Testimonial $record): bool => $record->status !== ModerationStatus::Ditolak
                        && Auth::user()?->can('moderate', $record) === true)
                    ->action(fn (Testimonial $record) => static::moderate($record, ModerationStatus::Ditolak)),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::query()->where('status', ModerationStatus::Menunggu)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTestimonials::route('/'),
        ];
    }

    private static function moderate(Testimonial $record, ModerationStatus $status): void
    {
        /** @var User $moderator */
        $moderator = Auth::user();

        $record->moderateBy($moderator, $status);

        activity('testimoni')
            ->performedOn($record)
            ->causedBy($moderator)
            ->event($status === ModerationStatus::Disetujui ? 'approved' : 'rejected')
            ->log(($status === ModerationStatus::Disetujui ? 'Menyetujui' : 'Menolak').' testimoni');
    }
}
