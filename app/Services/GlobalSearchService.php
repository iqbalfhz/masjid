<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Article;
use App\Models\Event;
use App\Models\Faq;
use App\Models\LibraryMaterial;
use App\Models\Study;
use Illuminate\Support\Collection;

/**
 * Pencarian global lintas modul (PRD 5.1.17).
 *
 * Memakai Laravel Scout sehingga driver bisa ditukar (database → Meilisearch)
 * tanpa mengubah pemanggilnya. Hasil dikelompokkan per jenis konten.
 */
class GlobalSearchService
{
    private const PER_TYPE = 5;

    /**
     * @return Collection<int, array{type: string, label: string, items: Collection<int, array{title: string, snippet: string, url: string}>}>
     */
    public function search(string $keyword): Collection
    {
        if (trim($keyword) === '') {
            return collect();
        }

        return collect([
            $this->group('kajian', 'Kajian', $this->studies($keyword)),
            $this->group('kegiatan', 'Kegiatan', $this->events($keyword)),
            $this->group('artikel', 'Artikel', $this->articles($keyword)),
            $this->group('pengumuman', 'Pengumuman', $this->announcements($keyword)),
            $this->group('faq', 'FAQ', $this->faqs($keyword)),
            $this->group('e-library', 'Materi E-Library', $this->materials($keyword)),
        ])->filter(fn (array $group): bool => $group['items']->isNotEmpty())->values();
    }

    public function totalFor(string $keyword): int
    {
        return $this->search($keyword)->sum(fn (array $group): int => $group['items']->count());
    }

    /**
     * @param  Collection<int, array{title: string, snippet: string, url: string}>  $items
     * @return array{type: string, label: string, items: Collection<int, array{title: string, snippet: string, url: string}>}
     */
    private function group(string $type, string $label, Collection $items): array
    {
        return ['type' => $type, 'label' => $label, 'items' => $items];
    }

    /**
     * @return Collection<int, array{title: string, snippet: string, url: string}>
     */
    private function studies(string $keyword): Collection
    {
        return Study::search($keyword)->get()
            ->filter(fn (Study $study): bool => $study->isApproved())
            ->take(self::PER_TYPE)
            ->map(fn (Study $study): array => [
                'title' => $study->theme,
                'snippet' => $study->ustadz_name.' • '.$study->scheduleLabel(),
                'url' => route('kajian.show', $study),
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{title: string, snippet: string, url: string}>
     */
    private function events(string $keyword): Collection
    {
        return Event::search($keyword)->get()
            ->filter(fn (Event $event): bool => $event->isApproved())
            ->take(self::PER_TYPE)
            ->map(fn (Event $event): array => [
                'title' => $event->title,
                'snippet' => $event->event_date->translatedFormat('d F Y').' • '.($event->category ?? 'Kegiatan'),
                'url' => route('kegiatan.show', $event),
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{title: string, snippet: string, url: string}>
     */
    private function articles(string $keyword): Collection
    {
        return Article::search($keyword)->get()
            ->filter(fn (Article $article): bool => $article->isPublished())
            ->take(self::PER_TYPE)
            ->map(fn (Article $article): array => [
                'title' => $article->title,
                'snippet' => str($article->excerpt ?: strip_tags((string) $article->content))->limit(140)->value(),
                'url' => route('artikel.show', $article),
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{title: string, snippet: string, url: string}>
     */
    private function announcements(string $keyword): Collection
    {
        return Announcement::search($keyword)->get()
            ->filter(fn (Announcement $announcement): bool => $announcement->isApproved())
            ->take(self::PER_TYPE)
            ->map(fn (Announcement $announcement): array => [
                'title' => $announcement->title,
                'snippet' => str(strip_tags((string) $announcement->content))->limit(140)->value(),
                'url' => route('pengumuman.show', $announcement),
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{title: string, snippet: string, url: string}>
     */
    private function faqs(string $keyword): Collection
    {
        return Faq::search($keyword)->get()
            ->where('is_published', true)
            ->take(self::PER_TYPE)
            ->map(fn (Faq $faq): array => [
                'title' => $faq->question,
                'snippet' => str(strip_tags((string) $faq->answer))->limit(140)->value(),
                'url' => route('faq').'#faq-'.$faq->getKey(),
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{title: string, snippet: string, url: string}>
     */
    private function materials(string $keyword): Collection
    {
        return LibraryMaterial::search($keyword)->get()
            ->take(self::PER_TYPE)
            ->map(fn (LibraryMaterial $material): array => [
                'title' => $material->title,
                'snippet' => trim(($material->ustadz_name ?? '').' • '.$material->type->getLabel(), ' •'),
                'url' => route('e-library').'?q='.urlencode($material->title),
            ])
            ->values();
    }
}
