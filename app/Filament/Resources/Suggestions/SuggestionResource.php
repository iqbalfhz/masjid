<?php

namespace App\Filament\Resources\Suggestions;

use App\Enums\SuggestionCategory;
use App\Enums\SuggestionStatus;
use App\Filament\Resources\Suggestions\Pages\EditSuggestion;
use App\Filament\Resources\Suggestions\Pages\ListSuggestions;
use App\Models\Suggestion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Kotak saran & pengaduan jamaah (PRD 5.2.8). Isi masukan tidak bisa diubah
 * pengurus — yang dikelola hanya status dan catatan tindak lanjut.
 */
class SuggestionResource extends Resource
{
    protected static ?string $model = Suggestion::class;

    protected static string|UnitEnum|null $navigationGroup = 'Layanan Jamaah';

    protected static ?string $navigationLabel = 'Kotak Saran & Pengaduan';

    protected static ?string $modelLabel = 'masukan';

    protected static ?string $pluralModelLabel = 'masukan';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'ticket_code';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Masukan Jamaah')
                ->schema([
                    TextEntry::make('ticket_code')->label('Nomor tiket'),
                    TextEntry::make('created_at')->label('Diterima')->dateTime('d F Y, H:i'),
                    TextEntry::make('name')->label('Nama')->placeholder('Anonim'),
                    TextEntry::make('contact')->label('Kontak')->placeholder('Tidak diisi'),
                    TextEntry::make('category')->label('Kategori')->badge(),
                    TextEntry::make('message')->label('Isi masukan')->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Tindak Lanjut')
                ->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options(SuggestionStatus::class)
                        ->required()
                        ->columnSpanFull(),

                    Textarea::make('response_note')
                        ->label('Catatan tindak lanjut')
                        ->rows(4)
                        ->columnSpanFull()
                        ->helperText('Catatan ini untuk arsip pengurus. Jamaah yang mengisi kontak dapat dihubungi terpisah.'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_code')
                    ->label('Tiket')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge(),

                TextColumn::make('name')
                    ->label('Pengirim')
                    ->placeholder('Anonim')
                    ->searchable(),

                TextColumn::make('message')
                    ->label('Masukan')
                    ->wrap()
                    ->limit(80)
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Diterima')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('handler.name')
                    ->label('Ditangani oleh')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(SuggestionStatus::class),

                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(SuggestionCategory::class),
            ])
            ->recordActions([
                EditAction::make()->label('Tindak lanjuti'),
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
        $new = static::getModel()::query()->where('status', SuggestionStatus::Baru)->count();

        return $new > 0 ? (string) $new : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuggestions::route('/'),
            'edit' => EditSuggestion::route('/{record}/edit'),
        ];
    }
}
