<?php

namespace App\Filament\Resources\Studies\Schemas;

use App\Enums\ScheduleType;
use App\Filament\Support\ApprovalSchema;
use App\Models\Study;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class StudyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kajian')
                    ->schema([
                        TextInput::make('theme')
                            ->label('Tema kajian')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('ustadz_name')
                            ->label('Nama ustadz/pemateri')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),

                        FileUpload::make('poster_image')
                            ->label('Poster / flyer')
                            ->image()
                            ->directory('kajian')
                            ->imageEditor()
                            ->maxSize(4096)
                            ->columnSpanFull(),

                        TagsInput::make('tags')
                            ->label('Tag')
                            ->helperText('Tag dipakai lintas modul, misal "Ramadhan" atau "Anak & Remaja".')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Jadwal')
                    ->schema([
                        Select::make('schedule_type')
                            ->label('Jenis jadwal')
                            ->options(ScheduleType::class)
                            ->default(ScheduleType::Rutin)
                            ->required()
                            ->live(),

                        Select::make('day_of_week')
                            ->label('Hari')
                            ->options(Study::DAYS)
                            ->required(fn (Get $get): bool => $get('schedule_type') === ScheduleType::Rutin->value)
                            ->visible(fn (Get $get): bool => $get('schedule_type') === ScheduleType::Rutin->value),

                        DatePicker::make('start_date')
                            ->label('Tanggal pelaksanaan')
                            ->native(false)
                            ->required(fn (Get $get): bool => $get('schedule_type') === ScheduleType::Insidental->value)
                            ->visible(fn (Get $get): bool => $get('schedule_type') === ScheduleType::Insidental->value),

                        TimePicker::make('time')
                            ->label('Jam mulai')
                            ->seconds(false)
                            ->required(),

                        TimePicker::make('end_time')
                            ->label('Jam selesai')
                            ->seconds(false),

                        TextInput::make('location')
                            ->label('Lokasi')
                            ->default('Masjid An-Nur, Lantai P3a Tangcity Mall')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Konfirmasi Kehadiran (RSVP)')
                    ->description('Aktifkan bila panitia perlu memperkirakan jumlah peserta.')
                    ->schema([
                        Toggle::make('rsvp_enabled')
                            ->label('Buka RSVP di website publik')
                            ->live()
                            ->columnSpan(fn (Get $get): int => $get('rsvp_enabled') ? 1 : 2),

                        TextInput::make('rsvp_quota')
                            ->label('Kuota peserta')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Kosongkan bila tanpa batas.')
                            ->visible(fn (Get $get): bool => (bool) $get('rsvp_enabled')),
                    ])
                    ->columns(2),

                ApprovalSchema::section(Study::class),
            ]);
    }
}
