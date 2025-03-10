<x-volt-app :title="$title">
    {!! form()->open()->post()->url($postUrl) !!}
        <h3 class="ui header">Subtitle Content</h3>

        <div class="ui segment">
            {!!
                form()->multirow('subtitles', [
                    'start_time' => ['type' => 'text', 'label' => 'Start Time', 'readonly' => true],
                    'end_time' => ['type' => 'text', 'label' => 'End Time', 'readonly' => true],
                    'text' => ['type' => 'textarea', 'label' => 'Subtitle Text', 'rows' => 2],
                ])
                ->rows(count($subtitles))
                ->allowRemoval(true)
                ->source($subtitles)
            !!}
        </div>

        <div class="field mt-4">
            {!! form()->submit('Translate to EN via OpenAI')->addClass('primary') !!}
        </div>
    {!! form()->close() !!}
</x-volt-app>
