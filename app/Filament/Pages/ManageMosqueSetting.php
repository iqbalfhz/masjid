<?php

namespace App\Filament\Pages;

use App\Models\MosqueSetting;
use App\Models\PrayerSchedule;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

/**
 * Pengaturan Umum (PRD 5.2.18): identitas masjid, info donasi, profil, dan
 * konfigurasi jadwal sholat beserta reminder push notification.
 */
class ManageMosqueSetting extends Page
{
    use InteractsWithFormActions;

    protected static string|UnitEnum|null $navigationGroup = 'Profil Masjid';

    protected static ?string $navigationLabel = 'Pengaturan Umum';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'pengaturan';

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public function getTitle(): string|Htmlable
    {
        return 'Pengaturan Umum';
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('viewAny', MosqueSetting::class) === true;
    }

    public function mount(): void
    {
        $this->form->fill(MosqueSetting::current()->attributesToArray());
    }

    /**
     * Susun isi halaman lewat schema bawaan Filament: form dibungkus elemen
     * <form> dengan satu baris tombol di footernya. Cara ini memastikan hanya
     * ada satu tombol simpan dan menekan Enter di field ikut menyimpan.
     */
    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make($this->getFormActions())->key('form-actions'),
                ]),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Pengaturan')
                    ->tabs([
                        Tab::make('Identitas Masjid')
                            ->icon('heroicon-o-building-library')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama masjid')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('tagline')
                                    ->label('Tagline')
                                    ->maxLength(255),

                                TextInput::make('address')
                                    ->label('Alamat')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('phone')
                                    ->label('Nomor kontak')
                                    ->tel()
                                    ->maxLength(30),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255),

                                FileUpload::make('logo')
                                    ->label('Logo masjid')
                                    ->image()
                                    ->directory('identitas')
                                    ->maxSize(2048),

                                Textarea::make('description')
                                    ->label('Deskripsi singkat')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                TextInput::make('maps_embed_url')
                                    ->label('URL embed Google Maps (opsional)')
                                    ->url()
                                    /*
                                     * Google menolak link berbagi dimuat di
                                     * dalam iframe, dan penolakannya tidak
                                     * bersuara — pengunjung hanya melihat kotak
                                     * kosong. Kesalahan ini gampang terjadi
                                     * karena tombol "Bagikan" jauh lebih mudah
                                     * ditemukan daripada "Sematkan peta", jadi
                                     * lebih baik ditangkap di sini.
                                     */
                                    ->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                        if (blank($value) || MosqueSetting::isEmbeddableMapsUrl($value)) {
                                            return;
                                        }

                                        $fail('Ini sepertinya link berbagi, bukan URL sematan. Di Google Maps pilih Bagikan → tab "Sematkan peta" → Salin HTML, lalu tempel bagian di dalam src="..." saja. Boleh juga dikosongkan: peta akan memakai koordinat di tab Jadwal Sholat.');
                                    })
                                    ->helperText('Kosongkan saja bila ragu — peta otomatis memakai koordinat masjid. Isi hanya bila ingin tampilan peta yang lebih rapi, dari menu Bagikan → "Sematkan peta".')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tab::make('Profil & Visi Misi')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Textarea::make('history')
                                    ->label('Sejarah masjid')
                                    ->rows(6),

                                Textarea::make('vision')
                                    ->label('Visi')
                                    ->rows(3),

                                Textarea::make('mission')
                                    ->label('Misi')
                                    ->rows(6)
                                    ->helperText('Tulis satu misi per baris.'),
                            ]),

                        Tab::make('Donasi')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                TextInput::make('bank_name')
                                    ->label('Nama bank')
                                    ->maxLength(255),

                                TextInput::make('bank_account_name')
                                    ->label('Atas nama')
                                    ->maxLength(255),

                                TextInput::make('bank_account_number')
                                    ->label('Nomor rekening')
                                    ->maxLength(50),

                                FileUpload::make('qris_image')
                                    ->label('Gambar QRIS')
                                    ->image()
                                    ->directory('donasi')
                                    ->maxSize(4096),
                            ])
                            ->columns(2),

                        Tab::make('Jadwal Sholat & Reminder')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                TextInput::make('latitude')
                                    ->label('Latitude')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('longitude')
                                    ->label('Longitude')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('prayer_calculation_method')
                                    ->label('Metode hisab (kode Aladhan)')
                                    ->helperText('20 = Kementerian Agama Republik Indonesia.')
                                    ->maxLength(10),

                                Toggle::make('prayer_reminder_settings.enabled')
                                    ->label('Aktifkan reminder push notification')
                                    ->live()
                                    ->columnSpanFull(),

                                CheckboxList::make('prayer_reminder_settings.prayers')
                                    ->label('Waktu sholat yang diingatkan')
                                    ->options(PrayerSchedule::REMINDABLE)
                                    ->columns(5)
                                    ->visible(fn (Get $get): bool => (bool) $get('prayer_reminder_settings.enabled'))
                                    ->columnSpanFull(),

                                TextInput::make('prayer_reminder_settings.minutes_before')
                                    ->label('Kirim berapa menit sebelum waktu sholat')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(60)
                                    ->default(10)
                                    ->visible(fn (Get $get): bool => (bool) $get('prayer_reminder_settings.enabled')),
                            ])
                            ->columns(3),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    /**
     * Tombol simpan hanya satu, diletakkan di bawah form mengikuti pola
     * "Buat / Batal" pada resource lain. Sebelumnya tombol yang sama juga
     * dipasang sebagai header action sehingga tampil dua kali.
     *
     * @return array<Action>
     */
    public function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan pengaturan')
                ->icon('heroicon-o-check')
                ->submit('save')
                ->visible(fn (): bool => $this->canEdit()),
        ];
    }

    public function canEdit(): bool
    {
        return Auth::user()?->can('update', MosqueSetting::current()) === true;
    }

    public function save(): void
    {
        abort_unless(Auth::user()?->can('update', MosqueSetting::current()) === true, 403);

        MosqueSetting::current()->update($this->form->getState());

        Notification::make()
            ->title('Pengaturan tersimpan')
            ->success()
            ->send();
    }
}
