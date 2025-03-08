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
            $item->subtitle = $subtitle;
            return $item;
        });

        return $data->setCollection($newData);
    }


    protected function prepareColumns(): array
    {
        $columns = [];

        $columns[] = Text::make('name', 'Name');
        $columns[] = Button::make('subtitle', 'Subtitle')->icon('file');

        return $columns;
    }
}
