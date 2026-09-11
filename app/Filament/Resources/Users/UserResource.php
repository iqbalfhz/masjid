<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use UnitEnum;

/**
 * Manajemen user & role (PRD 5.2.15).
 *
 * Aturan hierarki: Superadmin bisa mengelola semua akun, sedangkan Admin hanya
 * bisa mengelola role yang levelnya di bawah dirinya — jadi Admin tidak bisa
 * membuat sesama Admin maupun Superadmin.
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'User';

    protected static ?string $modelLabel = 'user';

    protected static ?string $pluralModelLabel = 'user';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identitas')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama lengkap')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    TextInput::make('phone')
                        ->label('Nomor kontak')
                        ->tel()
                        ->maxLength(30),

                    FileUpload::make('avatar_path')
                        ->label('Foto profil')
                        ->image()
                        ->avatar()
                        ->directory('avatar')
                        ->imageEditor()
                        ->maxSize(2048),
                ])
                ->columns(2),

            Section::make('Akses')
                ->schema([
                    Select::make('roles')
                        ->label('Role')
                        ->relationship(
                            name: 'roles',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query): Builder => $query->where(
                                fn (Builder $q) => Auth::user()?->isSuperadmin()
                                    ? $q
                                    : $q->where('level', '>', Auth::user()?->roleLevel() ?? 99)
                            ),
                        )
                        ->getOptionLabelFromRecordUsing(fn (Role $record): string => $record->displayName())
                        ->preload()
                        ->required()
                        ->helperText('Anda hanya dapat memberikan role yang tingkatannya di bawah role Anda sendiri.'),

                    Toggle::make('is_active')
                        ->label('Akun aktif')
                        ->default(true)
                        ->helperText('Akun nonaktif tidak bisa masuk ke admin panel.'),

                    TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->revealable()
                        ->minLength(8)
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->helperText('Kosongkan bila tidak ingin mengubah password.')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_path')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(asset('images/placeholder.svg')),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('roles')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (Role $state): string => $state->displayName()),

                TextColumn::make('phone')
                    ->label('Kontak')
                    ->placeholder('—')
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (User $record): bool => static::canManage($record)),
                DeleteAction::make()
                    ->visible(fn (User $record): bool => static::canManage($record) && $record->isNot(Auth::user())),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Sembunyikan akun yang levelnya sejajar atau di atas user yang login,
     * kecuali bagi Superadmin.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user?->isSuperadmin()) {
            return $query;
        }

        return $query->whereHas('roles', fn (Builder $roles) => $roles->where('level', '>', $user?->roleLevel() ?? 99));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    private static function canManage(User $record): bool
    {
        return Auth::user()?->canManageRoleLevel($record->roleLevel()) === true;
    }
}
