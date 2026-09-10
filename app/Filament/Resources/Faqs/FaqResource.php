<?php

namespace App\Filament\Resources\Faqs;

use App\Filament\Resources\Faqs\Pages\CreateFaq;
use App\Filament\Resources\Faqs\Pages\EditFaq;
use App\Filament\Resources\Faqs\Pages\ListFaqs;
use App\Models\Faq;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Konten & Informasi';

    protected static ?string $navigationLabel = 'FAQ';

    protected static ?string $modelLabel = 'FAQ';

    protected static ?string $pluralModelLabel = 'FAQ';

    protected static ?int $navigationSort = 8;

    protected static ?string $recordTitleAttribute = 'question';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('question')
                ->label('Pertanyaan')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            RichEditor::make('answer')
                ->label('Jawaban')
                ->required()
                ->columnSpanFull(),

            TextInput::make('category')
                ->label('Kategori')
                ->datalist(['Umum', 'Donasi', 'Kajian', 'Fasilitas', 'Layanan'])
                ->required()
                ->maxLength(255),

            TextInput::make('sort_order')
                ->label('Urutan tampil')
                ->numeric()
                ->default(0),

            Toggle::make('is_published')
                ->label('Tampilkan di website')
                ->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')
                    ->label('Pertanyaan')
                    ->searchable()
                    ->wrap()
                    ->limit(80),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),

                IconColumn::make('is_published')
                    ->label('Tayang')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(fn (): array => Faq::query()->distinct()->pluck('category', 'category')->all()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFaqs::route('/'),
            'create' => CreateFaq::route('/create'),
            'edit' => EditFaq::route('/{record}/edit'),
        ];
    }
}
