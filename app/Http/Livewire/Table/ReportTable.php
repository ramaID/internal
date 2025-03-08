<?php

namespace App\Http\Livewire\Table;

use Illuminate\Support\Collection;
use Laravolt\Suitable\Columns\Text;
use Laravolt\Suitable\Columns\Button;
use Laravolt\AutoCrud\Tables\ResourceTable;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ReportTable extends ResourceTable
{
    public function data()
    {
        /** @var LengthAwarePaginator */
        $data = parent::data();

        /** @var Collection */
        $urls = $data->getCollection()
            ->map(fn ($item) => $this->extractIdFromMediaUrl(json_decode($item->attachment)[0]))
            ->flatten();
        $medias = Media::query()->whereIn('id', $urls->values()->toArray())->get();

        $newData = $data->getCollection()->map(function ($item) use ($medias) {
            $attachment = json_decode($item->attachment)[0];
            $mediaID = $this->extractIdFromMediaUrl($attachment);
            $media = $medias->firstWhere('id', $mediaID);
            $item->attachment = $attachment;
            $item->attachment_filename = $media->file_name ?? null;
            return $item;
        });

        return $data->setCollection($newData);
    }


    protected function prepareColumns(): array
    {
        $columns = [];

        $columns[] = Text::make('name', 'Name Laporan');
        $columns[] = Text::make('attachment_filename', 'Nama Lampiran');
        $columns[] = Button::make('attachment', 'Lampiran')->icon('file');

        return $columns;
    }

    protected function extractIdFromMediaUrl($url)
    {
        // Example implementation - adjust regex pattern based on your URL structure
        if (preg_match('#/storage/(\d+)/#', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
