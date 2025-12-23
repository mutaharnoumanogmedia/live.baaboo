<x-app-dashboard-layout>
    <div class="container mt-4">
        <h2>{{ 'Create Quiz' }}</h2>

        <form method="POST" action="{{ route('admin.live-show-quizzes.store') }}">
            @csrf

            <div class="mb-3">
                <label>Live Show</label>
                <select name="live_show_id" class="form-control" required>
                    @foreach ($liveShows as $id => $title)
                        <option value="{{ $id }}">{{ $title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="my-2">
                <button type="button" class="btn btn-secondary" id="add-question">Add Question</button>
            </div>

            <div id="question-container"></div>

            <button type="submit" class="btn btn-success">Save</button>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            addQuestion(); // add initial question with 4 options
        });

        document.getElementById("add-question").addEventListener("click", function() {
            addQuestion();
        });

        // Add Question Block
        function addQuestion() {
            const container = document.getElementById("question-container");

            const html = `
                <div class="card mb-3 question-wrapper">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Question</strong>
                            <button type="button" class="btn btn-danger btn-sm remove-question">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>

                        <input type="text" class="form-control mb-3 question-input"
                               placeholder="Enter question" required>

                        <h5>Options</h5>
                        <div class="row options-wrapper"></div>

                        <button type="button" class="btn btn-secondary mt-2 add-option">Add Option</button>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML("beforeend", html);

            const wrapper = container.querySelector(".question-wrapper:last-child");
            const addOptionBtn = wrapper.querySelector(".add-option");

            // default 4 options
            addOption(addOptionBtn);
            addOption(addOptionBtn);
            addOption(addOptionBtn);
            addOption(addOptionBtn);

            reindexQuestions();
        }

        // Add Option
        function addOption(btn) {
            const questionWrapper = btn.closest(".question-wrapper");
            const optionsWrapper = questionWrapper.querySelector(".options-wrapper");

            const html = `
                <div class="col-lg-3 mb-2 option-item">
                    <div class="input-group">
                        <input type="text" class="form-control option-input" placeholder="Option text" required>

                        <div class="input-group-text">
                            <input type="radio" class="is-correct">
                            Correct
                        </div>

                        <button type="button" class="btn btn-danger remove-option">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            `;

            optionsWrapper.insertAdjacentHTML("beforeend", html);
            reindexQuestions();
        }

        // Global remove button handlers
        document.addEventListener("click", function(e) {
            if (e.target.closest(".remove-question")) {
                e.target.closest(".question-wrapper").remove();
                reindexQuestions();
            }

            if (e.target.closest(".remove-option")) {
                e.target.closest(".option-item").remove();
                reindexQuestions();
            }

            if (e.target.classList.contains("add-option")) {
                addOption(e.target);
            }
        });

        // Reindex questions + options to match backend structure
        function reindexQuestions() {
            const questions = document.querySelectorAll(".question-wrapper");

            questions.forEach((question, qIndex) => {
                // question name
                const qInput = question.querySelector(".question-input");
                qInput.name = `questions[${qIndex}][question]`;

                // correct radio group name
                const radios = question.querySelectorAll(".is-correct");

                // options
                const options = question.querySelectorAll(".option-item");

                options.forEach((opt, oIndex) => {
                    const optInput = opt.querySelector(".option-input");
                    const radio = opt.querySelector(".is-correct");

                    optInput.name = `questions[${qIndex}][options][${oIndex}][option_text]`;
                    radio.name = `questions[${qIndex}][correct]`;
                    radio.value = oIndex;
                });
            });
        }
    </script>
</x-app-dashboard-layout>
