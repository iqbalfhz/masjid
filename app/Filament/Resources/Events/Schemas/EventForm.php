<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Filament\Support\ApprovalSchema;
use App\Models\Event;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kegiatan')
                    ->schema([
                        TextInput::make('title')
                            ->label('Nama kegiatan')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('category')
                            ->label('Kategori')
                            ->datalist(['Sosial', 'Ramadhan', 'Hari Besar', 'Anak & Remaja', 'Pelatihan'])
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),

                        FileUpload::make('poster_image')
                            ->label('Poster / flyer')
                            ->image()
                            ->directory('kegiatan')
                            ->imageEditor()
                            ->maxSize(4096)
                            ->columnSpanFull(),

                        TagsInput::make('tags')
                            ->label('Tag')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Waktu & Tempat')
                    ->schema([
                        DatePicker::make('event_date')
                            ->label('Tanggal')
                            ->native(false)
                            ->required(),

                        TimePicker::make('start_time')
                            ->label('Jam mulai')
                            ->seconds(false),

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
                    ->schema([
                        Toggle::make('rsvp_enabled')
                            ->label('Buka RSVP di website publik')
                            ->live(),

                        TextInput::make('rsvp_quota')
                            ->label('Kuota peserta')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Kosongkan bila tanpa batas.')
                            ->visible(fn (Get $get): bool => (bool) $get('rsvp_enabled')),
                    ])
                    ->columns(2),

                ApprovalSchema::section(Event::class),
            ]);
    }
}
