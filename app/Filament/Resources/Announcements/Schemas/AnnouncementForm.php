<?php

namespace App\Filament\Resources\Announcements\Schemas;

use App\Enums\AnnouncementPriority;
use App\Filament\Support\ApprovalSchema;
use App\Models\Announcement;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Isi Pengumuman')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->label('Isi pengumuman')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Masa Tayang')
                    ->description('Pengumuman hanya muncul di running text beranda selama rentang tanggal ini.')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Mulai tayang')
                            ->native(false)
                            ->default(today())
                            ->required(),

                        DatePicker::make('end_date')
                            ->label('Berakhir')
                            ->native(false)
                            ->afterOrEqual('start_date')
                            ->helperText('Kosongkan bila pengumuman berlaku sampai dicabut manual.'),

                        Select::make('priority')
                            ->label('Prioritas')
                            ->options(AnnouncementPriority::class)
                            ->default(AnnouncementPriority::Normal)
                            ->required(),
                    ])
                    ->columns(3),

                ApprovalSchema::section(Announcement::class),
            ]);
    }
}
