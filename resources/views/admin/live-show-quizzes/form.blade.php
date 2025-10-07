<x-app-dashboard-layout>
    <div class="container mt-4">
        <h2>{{ isset($quiz) ? 'Edit Quiz' : 'Create Quiz' }}</h2>
        <form method="POST"
            action="{{ isset($quiz) ? route('admin.live-show-quizzes.update', $quiz->id) : route('admin.live-show-quizzes.store') }}">
            @csrf
            @if (isset($quiz))
                @method('PUT')
            @endif

            <div class="mb-3">
                <label>Live Show</label>
                <select name="live_show_id" class="form-control" required
                    {{ isset($_GET['live_show_id']) ? 'readonly' : '' }}>
                    @foreach ($liveShows as $id => $title)
                        <option value="{{ $id }}"
                            {{ isset($quiz) && $quiz->live_show_id == $id ? 'selected' : '' }}
                            {{ isset($_GET['live_show_id']) && $_GET['live_show_id'] == $id ? 'selected' : '' }}>
                            {{ $title }}
                        </option>
                    @endforeach
                </select>
            </div>


            <div class="my-2">
                <button type="button" class="btn btn-secondary" id="add-question" onclick="addQuestion()">Add
                    Question</button>
            </div>


            <div class="card mb-3" id="question-container">
                <div class="card-body question-wrapper mb-3">
                    <div class="mb-3">
                        <label>Question</label>
                        <input type="text" name="question" class="form-control" value="{{ $quiz->question ?? '' }}"
                            required placeholder="Enter the quiz question # 0">
                    </div>

                    <h5>Options</h5>
                    <div id="options-wrapper" class="row">
                        @php $options = $quiz->options ?? collect([['option_text'=>'','is_correct'=>false],['option_text'=>'','is_correct'=>false],['option_text'=>'','is_correct'=>false],['option_text'=>'','is_correct'=>false]]) @endphp

                        @foreach ($options as $i => $opt)
                            <div class="col-lg-3  mb-2 option-item">
                                <div class="input-group">
                                    <input type="text" name="questions[0][options][option_text][]"
                                        class="form-control" placeholder="Option text 0, {{ $i }}"
                                        value="{{ $opt['option_text'] ?? '' }}" required>
                                    <div class="input-group-text">
                                        <input type="checkbox" name="questions[0][options][is_correct][]" value="1"
                                            {{ !empty($opt['is_correct']) ? 'checked' : '' }}> Correct
                                    </div>

                                    <button type="button" class="btn btn-danger remove-option"> <i
                                            class="bi bi-x"></i></button>
                                </div>
                            </div>
                        @endforeach


                    </div>
                    <div class="w-100">
                        <button type="button" class="btn btn-secondary mb-5" id="add-option0" onclick="addOption()">Add
                            Option</button>
                    </div>
                </div>
            </div>



            <button type="submit" class="btn btn-success">Save</button>
        </form>
    </div>

    <script>
        function addQuestion() {
            const container = document.getElementById('question-container');
            const index = container.children.length;
            const questionHtml = `
        <div class="card-body question-wrapper mb-3">
            <div class="mb-3">
                <div class="mb-2"> ${index} Question  <button class="btn btn-danger btn-sm" onclick="removeQuestion(this)">
                    <i class="bi bi-x"></i>
                    </button> </div>
                <input type="text" name="questions[${index}][question]" class="form-control" required>
            </div>

            <h5>Options</h5>
            <div id="options-wrapper${index}" class="row">
                <div class="col-lg-3  mb-2 option-item">
                    <div class="input-group">
                        <input type="text" name="questions[${index}][options][option_text][]" class="form-control" placeholder="Option text" required>
                        <div class="input-group-text">
                            <input type="checkbox" name="questions[${index}][options][is_correct][]" value="1"> Correct
                        </div>
                        <button type="button" class="btn btn-danger remove-option"> <i class="bi bi-x"></i></button>
                    </div>
                </div>
                <div class="col-lg-3  mb-2 option-item">
                    <div class="input-group">
                        <input type="text" name="questions[${index}][options][option_text][]" class="form-control" placeholder="Option text" required>
                        <div class="input-group-text">
                            <input type="checkbox" name="questions[${index}][options][is_correct][]" value="1"> Correct
                        </div>
                        <button type="button" class="btn btn-danger remove-option"> <i class="bi bi-x"></i></button>
                    </div>
                </div>
                <div class="col-lg-3  mb-2 option-item">
                    <div class="input-group">
                        <input type="text" name="questions[${index}][options][option_text][]" class="form-control" placeholder="Option text" required>
                        <div class="input-group-text">
                            <input type="checkbox" name="questions[${index}][options][is_correct][]" value="1"> Correct
                        </div>
                        <button type="button" class="btn btn-danger remove-option"> <i class="bi bi-x"></i></button>
                    </div>
                </div>
                <div class="col-lg-3  mb-2 option-item">
                    <div class="input-group">
                        <input type="text" name="questions[${index}][options][option_text][]" class="form-control" placeholder="Option text" required>
                        <div class="input-group-text">
                            <input type="checkbox" name="questions[${index}][options][is_correct][]" value="1"> Correct
                        </div>
                        <button type="button" class="btn btn-danger remove-option"> <i class="bi bi-x"></i></button>
                    </div>
                </div>
            </div>
            <div class="w-100"></div>
                <button type="button" class="btn btn-secondary mb-5" id="add-option${index}" onclick="addOption('options-wrapper${index}')">Add Option</button>
        </div>
        `;
            container.insertAdjacentHTML('beforeend', questionHtml);

        }

        function addOption(optionsWrapperId = 'options-wrapper') {
            const optionsWrapper = document.getElementById(optionsWrapperId);
            const questionIndex = optionsWrapperId.replace('options-wrapper', '') || '0';
            const optionCount = optionsWrapper.children.length;
            const optionHtml = `
            <div class="col-lg-3  mb-2 option-item">
                <div class="input-group">
                    <input type="text" name="questions[${questionIndex}][options][option_text][]" class="form-control" placeholder="Option text" required>
                    <div class="input-group-text">
                        <input type="checkbox" name="questions[${questionIndex}][options][is_correct][]" value="1"> Correct
                    </div>
                    <button type="button" class="btn btn-danger remove-option"> <i class="bi bi-x"></i></button>
                </div>
            </div>
            `;
            optionsWrapper.insertAdjacentHTML('beforeend', optionHtml);
        }

        function removeQuestion(button) {
            button.closest('.question-wrapper').remove();
        }
    </script>
</x-app-dashboard-layout>
