<?php

return [
    'label' => 'Report',
    'model' => \App\Models\Report::class,

    // optional, if you want to override the default Table
    'table' => \App\Http\Livewire\Table\ReportTable::class,

    'schema' => [
        [
            'name' => 'name',
            'type' => \Laravolt\Fields\Field::TEXT,
            'label' => 'Nama Report',
            'rules' => ['required'],
        ],
        [
            'name' => 'description',
            'type' => \Laravolt\Fields\Field::TEXTAREA,
            'label' => 'Deskripsi',
            'rules' => ['nullable'],
        ],
        [
            'name' => 'attachment',
            'type' => \Laravolt\Fields\Field::UPLOADER,
            'label' => 'Lampiran',
            'rules' => ['required'],
        ],
    ],
];
