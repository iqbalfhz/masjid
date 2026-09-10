<?php

namespace App\Filament\Resources\Studies;

use App\Enums\ContentStatus;
use App\Filament\Resources\Studies\Pages\CreateStudy;
use App\Filament\Resources\Studies\Pages\EditStudy;
use App\Filament\Resources\Studies\Pages\ListStudies;
use App\Filament\Resources\Studies\RelationManagers\RsvpsRelationManager;
use App\Filament\Resources\Studies\Schemas\StudyForm;
use App\Filament\Resources\Studies\Tables\StudiesTable;
use App\Models\Study;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StudyResource extends Resource
{
    protected static ?string $model = Study::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Konten & Informasi';

    protected static ?string $navigationLabel = 'Kajian';

    protected static ?string $modelLabel = 'kajian';

    protected static ?string $pluralModelLabel = 'kajian';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'theme';

    public static function form(Schema $schema): Schema
    {
        return StudyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RsvpsRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::query()->where('status', ContentStatus::MenungguApproval)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudies::route('/'),
            'create' => CreateStudy::route('/create'),
            'edit' => EditStudy::route('/{record}/edit'),
        ];
    }
}
