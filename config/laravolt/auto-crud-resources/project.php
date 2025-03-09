<?php

use Spatie\MediaLibrary\MediaCollections\Models\Media;

return [
    'label' => 'Project',
    'model' => \App\Models\Project::class,

    // optional, if you want to override the default Table
    'table' => \App\Http\Livewire\Table\ProjectTable::class,

    'schema' => [
        [
            'name' => 'name',
            'type' => \Laravolt\Fields\Field::TEXT,
            'label' => 'Nama Project',
            'rules' => ['required'],
        ],
        [
            'name' => 'description',
            'type' => \Laravolt\Fields\Field::TEXTAREA,
            'label' => 'Deskripsi',
            'rules' => ['nullable'],
        ],
        [
            'name' => 'subtitle',
            'type' => \Laravolt\Fields\Field::UPLOADER,
            'label' => 'Subtitle',
            'rules' => ['required'],
        ],
    ],
];
