<x-volt-app :title="$title">
    {!! form()->open()->post() !!}
        <h3 class="ui header">Text Corrections</h3>
        <div class="ui segment">
            <div class="ui two column grid">
                <div class="column">
                    <h4 class="ui header">Current Vocabularies</h4>
                    {!!
                        form()->multirow('vocabularies', [
                            'find' => ['type' => 'text', 'label' => 'Find', 'placeholder' => 'e.g., kisy Dev'],
                            'replace' => ['type' => 'text', 'label' => 'Replace with', 'placeholder' => 'e.g., QisthiDev'],
                            'case_sensitive' => ['type' => 'checkbox', 'label' => 'Case sensitive'],
                            'whole_word' => ['type' => 'checkbox', 'label' => 'Whole word only'],
                        ])
                        ->rows(count($vocabularies))
                        ->allowAddition(true)
                        ->allowRemoval(true)
                        ->source($vocabularies)
                    !!}
                </div>

                @if(!empty($suggestedVocabularies))
                <div class="column">
                    <h4 class="ui header">Suggested Corrections</h4>
                    <div class="ui relaxed divided list">
                        @foreach($suggestedVocabularies as $suggestion)
                            <div class="item">
                                <div class="content">
                                    <div class="header">
                                        "{{ $suggestion['find'] }}" → "{{ $suggestion['replace'] }}"
                                    </div>
                                    <div class="description">
                                        <button type="button" class="ui tiny positive button add-suggestion"
                                            data-find="{{ $suggestion['find'] }}"
                                            data-replace="{{ $suggestion['replace'] }}"
                                            data-case="{{ $suggestion['case_sensitive'] ? 1 : 0 }}"
                                            data-whole="{{ $suggestion['whole_word'] ? 1 : 0 }}">
                                            Add to vocabulary
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="field mt-4">
                {!! form()->button('Apply Corrections')->addClass('positive') !!}
            </div>
        </div>

        <h3 class="ui header">Subtitle Content</h3>
        <div class="ui segment">
            {!!
                form()->multirow('subtitles', [
                    'start_time' => ['type' => 'text', 'label' => 'Start Time', 'readonly' => true],
                    'end_time' => ['type' => 'text', 'label' => 'End Time', 'readonly' => true],
                    'text' => ['type' => 'textarea', 'label' => 'Subtitle Text', 'rows' => 2],
                ])
                ->rows(count($subtitles))
                ->allowRemoval(false)
                ->source($subtitles)
            !!}

            <div class="field mt-4">
                {!! form()->button('Save Subtitles')->addClass('primary') !!}
            </div>
        </div>
    {!! form()->close() !!}

    @push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add suggestion to vocabulary - TABLE STRUCTURE VERSION
            document.querySelectorAll('.add-suggestion').forEach(button => {
                button.addEventListener('click', function() {
                    const find = this.dataset.find;
                    const replace = this.dataset.replace;
                    const caseSensitive = this.dataset.case === '1';
                    const wholeWord = this.dataset.whole === '1';

                    // Locate the vocabulary table based on the selector you provided
                    const vocabTable = document.querySelector('form div.ui.two.column.grid > div:nth-child(1) table');

                    if (!vocabTable) {
                        console.error('Could not find vocabularies table');
                        return;
                    }

                    // Find the "Add Row" button associated with this table
                    // It's typically at the bottom of the table or nearby
                    const addRowButton =
                        // Try various possible selectors for the add button
                        vocabTable.parentElement.querySelector('.button.add') ||
                        vocabTable.parentElement.querySelector('[data-action="add"]') ||
                        vocabTable.parentElement.querySelector('.ui.button.add') ||
                        document.querySelector('form div.ui.two.column.grid > div:nth-child(1) .ui.button.add') ||
                        document.querySelector('form div.ui.two.column.grid > div:nth-child(1) button.add');

                    console.log('Add Row Button:', addRowButton); // Debug

                    if (!addRowButton) {
                        console.error('Could not find add row button. Adding row programmatically.');
                        // Try to add row programmatically by cloning the last row
                        addTableRow(vocabTable, find, replace, caseSensitive, wholeWord);
                    } else {
                        // Click the add button
                        addRowButton.click();

                        // Wait for DOM to update
                        setTimeout(() => {
                            const rows = vocabTable.querySelectorAll('tbody tr');
                            const lastRow = rows[rows.length - 1];

                            if (lastRow) {
                                fillTableRow(lastRow, find, replace, caseSensitive, wholeWord);
                            } else {
                                console.error('Could not find newly added row');
                            }
                        }, 100);
                    }

                    // Hide the suggestion
                    this.closest('.item').style.display = 'none';
                });
            });

            // Helper function to add a table row programmatically
            function addTableRow(table, find, replace, caseSensitive, wholeWord) {
                const tbody = table.querySelector('tbody');
                if (!tbody) {
                    console.error('Could not find table body');
                    return;
                }

                // Get all existing rows to determine the next index
                const existingRows = tbody.querySelectorAll('tr');
                const nextIndex = existingRows.length;

                // Clone the last row if available
                if (existingRows.length > 0) {
                    const lastRow = existingRows[existingRows.length - 1];
                    const newRow = lastRow.cloneNode(true);

                    // Update input names to use new index
                    newRow.querySelectorAll('input, select, textarea').forEach(input => {
                        const name = input.getAttribute('name');
                        if (name) {
                            const newName = name.replace(/vocabularies\[\d+\]/, `vocabularies[${nextIndex}]`);
                            input.setAttribute('name', newName);

                            // Clear values
                            if (input.type === 'checkbox') {
                                input.checked = false;
                            } else {
                                input.value = '';
                            }
                        }
                    });

                    // Fill with our values
                    fillTableRow(newRow, find, replace, caseSensitive, wholeWord);

                    // Add to table
                    tbody.appendChild(newRow);
                } else {
                    console.error('No existing rows to clone from');
                }
            }

            // Helper function to fill a table row with values
            function fillTableRow(row, find, replace, caseSensitive, wholeWord) {
                // Find inputs based on name attribute or column position
                const inputs = row.querySelectorAll('input');

                // Map inputs to expected fields based on position or name
                let findInput, replaceInput, caseSensitiveInput, wholeWordInput;

                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) {
                        if (name.includes('[find]')) findInput = input;
                        else if (name.includes('[replace]')) replaceInput = input;
                        else if (name.includes('[case_sensitive]')) caseSensitiveInput = input;
                        else if (name.includes('[whole_word]')) wholeWordInput = input;
                    }
                });

                // If we couldn't find inputs by name, try by position/type
                if (!findInput) {
                    const textInputs = Array.from(row.querySelectorAll('input[type="text"]'));
                    if (textInputs.length >= 2) {
                        findInput = textInputs[0];
                        replaceInput = textInputs[1];
                    }

                    const checkboxes = Array.from(row.querySelectorAll('input[type="checkbox"]'));
                    if (checkboxes.length >= 2) {
                        caseSensitiveInput = checkboxes[0];
                        wholeWordInput = checkboxes[1];
                    }
                }

                // Fill values if inputs found
                if (findInput) findInput.value = find;
                if (replaceInput) replaceInput.value = replace;
                if (caseSensitiveInput && caseSensitive) caseSensitiveInput.checked = true;
                if (wholeWordInput && wholeWord) wholeWordInput.checked = true;
            }

            // Apply corrections functionality - keep as is
            const applyCorrectionsBtn = document.querySelector('.positive.button:not(.add-suggestion)');

            if (applyCorrectionsBtn) {
                applyCorrectionsBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Get all vocabulary pairs
                    const vocabularies = [];
                    const findInputs = document.querySelectorAll('[name^="vocabularies"][name$="[find]"]');

                    findInputs.forEach(input => {
                        const rowIndex = input.name.match(/vocabularies\[(\d+)\]/)[1];
                        const replaceInput = document.querySelector(`[name="vocabularies[${rowIndex}][replace]"]`);
                        const caseSensitiveInput = document.querySelector(`[name="vocabularies[${rowIndex}][case_sensitive]"]`);
                        const wholeWordInput = document.querySelector(`[name="vocabularies[${rowIndex}][whole_word]"]`);

                        if (input.value && replaceInput.value) {
                            vocabularies.push({
                                find: input.value,
                                replace: replaceInput.value,
                                caseSensitive: caseSensitiveInput?.checked || false,
                                wholeWord: wholeWordInput?.checked || false
                            });
                        }
                    });

                    // Apply changes to all subtitle texts
                    const textareas = document.querySelectorAll('[name^="subtitles"][name$="[text]"]');

                    textareas.forEach(textarea => {
                        let text = textarea.value;

                        vocabularies.forEach(vocab => {
                            let regex;
                            if (vocab.wholeWord) {
                                regex = vocab.caseSensitive
                                    ? new RegExp(`\\b${escapeRegExp(vocab.find)}\\b`, 'g')
                                    : new RegExp(`\\b${escapeRegExp(vocab.find)}\\b`, 'gi');
                            } else {
                                regex = vocab.caseSensitive
                                    ? new RegExp(escapeRegExp(vocab.find), 'g')
                                    : new RegExp(escapeRegExp(vocab.find), 'gi');
                            }

                            text = text.replace(regex, vocab.replace);
                        });

                        textarea.value = text;
                    });
                });
            }

            // Helper function to escape special characters in regex
            function escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            // Debug helper - add this to help troubleshoot
            console.log('Script loaded. DOM structure:', {
                vocabularySection: document.querySelector('form div.ui.two.column.grid > div:nth-child(1)'),
                tables: document.querySelectorAll('table'),
                addButtons: document.querySelectorAll('.button.add, [data-action="add"]')
            });
        });
    </script>
    @endpush
</x-volt-app>
