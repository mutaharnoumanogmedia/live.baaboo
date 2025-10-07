<x-app-dashboard-layout>
    <div class="container mt-4">
        <h2>{{ 'Create Quiz' }}</h2>
        <form method="POST" action="{{ route('admin.live-show-quizzes.store') }}">
            @csrf

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
                <button type="button" class="btn btn-secondary" id="add-question">Add Question</button>
            </div>

            <div id="question-container">
                <!-- Question template goes here -->
            </div>

            <button type="submit" class="btn btn-success">Save</button>
        </form>
    </div>

    <script>
        // Initial question
        document.addEventListener('DOMContentLoaded', function() {
            addQuestion();


        });


        document.getElementById('add-question').addEventListener('click', function() {
            addQuestion();
        });

        // Add Question
        function addQuestion() {
            const container = document.getElementById('question-container');
            const questionHtml = `
                <div class="card mb-3 question-wrapper">
                    <div class="card-body">
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <strong>Question</strong>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeQuestion(this)">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                        <input type="text" class="form-control mb-3 question-input" placeholder="Enter question" required>

                        <h5>Options</h5>
                        <div class="row options-wrapper"></div>
                        <button type="button" class="btn btn-secondary mt-2 add-option">Add Option</button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', questionHtml);
            const addOptionBtn = container.querySelector('.question-wrapper:last-child .add-option');
            addOptions(addOptionBtn); // Add initial option
            addOptions(addOptionBtn); // Add second option
            addOptions(addOptionBtn); // Add third option
            addOptions(addOptionBtn); // Add fourth option

            reindexQuestions();
        }

        function addOptions(button) {
            const questionWrapper = button.closest('.question-wrapper');
            const optionsWrapper = questionWrapper.querySelector('.options-wrapper');
            const questionIndex = Array.from(document.querySelectorAll('.question-wrapper')).indexOf(questionWrapper);
            const optionCount = optionsWrapper.children.length;

            const optionHtml = `
            <div class="col-lg-3  mb-2 option-item">
                <div class="input-group">
                    <input type="text" name="questions[${questionIndex}][options][option_text][]" class="form-control option-input" placeholder="Option text" required>
                    <div class="input-group-text">
                        <input type="checkbox" name="questions[${questionIndex}][options][is_correct][]" value="1" class="is-correct"> Correct
                    </div>
                    <button type="button" class="btn btn-danger remove-option"> <i class="bi bi-x"></i></button>
                </div>
            </div>
            `;
            optionsWrapper.insertAdjacentHTML('beforeend', optionHtml);
            reindexQuestions();
        }

        // Add Option
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('add-option')) {
                addOptions(e.target);
            }

            // Remove option
            if (e.target.classList.contains('remove-option')) {
                e.target.closest('.option-item').remove();
                reindexQuestions();
            }
        });

        // Remove Question
        function removeQuestion(button) {
            button.closest('.question-wrapper').remove();
            reindexQuestions();
        }

        // Reindex everything (questions + options)
        function reindexQuestions() {
            const questions = document.querySelectorAll('#question-container .question-wrapper');
            questions.forEach((q, qIndex) => {
                // Question name
                const qInput = q.querySelector('.question-input');
                qInput.name = `questions[${qIndex}][question]`;

                // Options
                const options = q.querySelectorAll('.option-item');
                options.forEach((opt, oIndex) => {
                    const optionInput = opt.querySelector('.option-input');
                    const checkInput = opt.querySelector('.is-correct');
                    optionInput.name = `questions[${qIndex}][options][${oIndex}][option_text]`;
                    checkInput.name = `questions[${qIndex}][options][${oIndex}][is_correct]`;
                });
            });
        }

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-option')) {
                e.target.closest('.option-item').remove();
                reindexQuestions();
            }
        });
    </script>
</x-app-dashboard-layout>
