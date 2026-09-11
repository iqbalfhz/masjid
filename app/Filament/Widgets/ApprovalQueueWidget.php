<?php

namespace App\Filament\Widgets;

use App\Filament\Support\ApprovalActions;
use App\Support\ApprovableModules;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Antrean approval yang sebenarnya: daftar konten yang menunggu ditinjau,
 * lengkap dengan tombol keputusan di barisnya masing-masing.
 *
 * Sebelumnya dashboard hanya menampilkan angka "menunggu approval: 1" tanpa
 * memberi tahu yang mana. Ketua DKM harus menebak modulnya, membuka sidebar,
 * dan mencari sendiri — padahal seluruh pekerjaannya hari itu mungkin cuma
 * menyetujui satu pengumuman. Tim DKM adalah relawan yang membuka sistem ini
 * sebentar di sela kesibukan, jadi tugas tersering harus bisa selesai tanpa
 * berpindah halaman.
 *
 * Barisnya berupa array, bukan model Eloquent: untuk data source kustom,
 * Filament mengunci ulang tiap model dengan getKey() tanpa menyediakan hook,
 * sehingga Pengumuman #1 dan Kajian #1 saling menimpa. Aksinya mengembalikan
 * array itu menjadi model lewat resolver, jadi keputusan di sini menempuh jalur
 * yang sama persis dengan keputusan dari tabel resource — termasuk log
 * aktivitas dan notifikasi ke pembuat konten.
 */
class ApprovalQueueWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user !== null && ApprovableModules::userCanApproveAnything($user);
    }

    public function table(Table $table): Table
    {
        $resolve = static fn (array $record): ?Model => ApprovableModules::resolve($record['key']);

        return $table
            ->heading('Antrean Approval')
            ->description('Konten yang menunggu keputusan Anda.')
            ->records(fn (): Collection => ApprovableModules::queueRows(Auth::user()))
            ->columns([
                TextColumn::make('modul')
                    ->label('Modul')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('judul')
                    ->label('Judul')
                    ->wrap()
                    ->weight('medium'),

                TextColumn::make('penulis')
                    ->label('Diajukan oleh'),

                TextColumn::make('menunggu_sejak')
                    ->label('Menunggu sejak')
                    ->since()
                    ->dateTimeTooltip('d F Y, H:i'),
            ])
            ->recordActions([
                ApprovalActions::approve($resolve),
                ApprovalActions::reject($resolve),
            ])
            ->emptyStateHeading('Tidak ada yang menunggu')
            ->emptyStateDescription('Semua pengumuman, kajian, kegiatan, dan artikel sudah ditinjau.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated([5, 10, 25]);
    }
}
