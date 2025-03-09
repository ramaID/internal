<?php

namespace App\Http\Livewire\Table;

use Laravolt\Suitable\Columns\Text;
use Laravolt\Suitable\Columns\Button;
use Laravolt\AutoCrud\Tables\ResourceTable;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectTable extends ResourceTable
{
    public function data()
    {
        /** @var LengthAwarePaginator */
        $data = parent::data();

        $newData = $data->getCollection()->map(function ($item) {
            $subtitle = json_decode($item->subtitle)[0];
            $media = $this->extractIdFromMediaUrl($subtitle);

            $item->subtitle = $subtitle;
            $item->processing = route('resource.project.processing-subtitle', $media);

            return $item;
        });

        return $data->setCollection($newData);
    }


    protected function prepareColumns(): array
    {
        $columns = [];

        $columns[] = Text::make('name', 'Name');
        $columns[] = Button::make('subtitle', 'Subtitle')->icon('file');
        $columns[] = Button::make('processing', 'Processing')->icon('magic');

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
