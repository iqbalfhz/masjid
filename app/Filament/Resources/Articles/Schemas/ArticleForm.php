<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Filament\Support\ApprovalSchema;
use App\Models\Article;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Isi Artikel')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('excerpt')
                            ->label('Ringkasan')
                            ->rows(2)
                            ->maxLength(500)
                            ->helperText('Tampil pada daftar artikel dan hasil pencarian.')
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->label('Isi artikel')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Publikasi')
                    ->schema([
                        Select::make('article_category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nama kategori')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        DatePicker::make('publish_date')
                            ->label('Tanggal tayang')
                            ->native(false)
                            ->default(today())
                            ->required()
                            ->helperText('Artikel baru tampil publik setelah disetujui dan tanggal ini terlewati.'),

                        FileUpload::make('cover_image')
                            ->label('Gambar sampul')
                            ->image()
                            ->directory('artikel')
                            ->imageEditor()
                            ->maxSize(4096),

                        TagsInput::make('tags')
                            ->label('Tag'),
                    ])
                    ->columns(2),

                ApprovalSchema::section(Article::class),
            ]);
    }
}
