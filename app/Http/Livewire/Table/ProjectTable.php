<?php

namespace App\Http\Livewire\Table;

use Laravolt\Fields\Field;
use Laravolt\Suitable\Columns\Text;
use Laravolt\Suitable\Columns\Button;
use Illuminate\Database\Eloquent\Model;
use Laravolt\AutoCrud\SchemaTransformer;
use Laravolt\AutoCrud\Tables\ResourceTable;

class ProjectTable extends ResourceTable
{
    public function data()
    {
        $transformer = new SchemaTransformer($this->resource);

        $this->fields = $transformer->getFieldsForIndex();

        /** @var Model $model */
        $model = app($this->resource['model']);
        $searchableFields = $this->fields
            ->reject(fn ($item) => $item['type'] === Field::BELONGS_TO && ! isset($item['searchable']))
            ->reject(function ($item) {
                return ($item['searchable'] ?? true) === false;
            })
            ->transform(function ($item) {
                if ($item['type'] === Field::BELONGS_TO) {
                    $item['name'] .= '.'.$item['searchable'];
                }

                return $item;
            })
            ->pluck('name')
            ->toArray();

        $data = $model->newQuery()
            ->orderBy('id', 'desc')
            ->whereLike($searchableFields, $this->search)
            ->paginate();

        $newData = $data->getCollection()->map(function ($item) {
            $subtitle = json_decode($item->subtitle)[0];
            $media = $this->extractIdFromMediaUrl($subtitle);

            $item->subtitle = $subtitle;
            $item->processing = route('resource.project.processing-subtitle', $media);
            $item->ai_processing = route('resource.project.ai-translation', $media);

            return $item;
        });

        return $data->setCollection($newData);
    }


    protected function prepareColumns(): array
    {
        $columns = [];

        $columns[] = Text::make('name', 'Name');
        $columns[] = Button::make('subtitle', 'Subtitle')->icon('file');
        $columns[] = Button::make('processing', 'Text Corrections')->icon('wrench');
        $columns[] = Button::make('ai_processing', 'AI Translation')->icon('magic');

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
