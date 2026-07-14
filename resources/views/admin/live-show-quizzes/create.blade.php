<x-app-dashboard-layout>
    <div class="container mt-4">
        <h2>{{ 'Create Quiz' }}</h2>

        <form method="POST" action="{{ route('admin.live-show-quizzes.store') }}">
            @csrf

            <div class="mb-3">
                <label>Live Show</label>
                <select name="live_show_id" class="form-control" required>
                    @foreach ($liveShows as $id => $title)
                        <option value="{{ $id }}" {{ isset($_GET['live_show_id']) && $_GET['live_show_id'] == $id ? 'selected' : '' }}>{{ $title }}</option>
                    @endforeach
                </select>
            </div>

            <p class="text-muted small mb-2">
                <i class="fas fa-grip-vertical me-1"></i> Drag questions to set their order before saving.
            </p>

            <div class="my-2">
                <button type="button" class="btn btn-secondary" id="add-question">Add Question</button>
            </div>

            <div id="question-container"></div>

            <button type="submit" class="btn btn-success">Save</button>
        </form>
    </div>

    <style>
        .sortable-ghost {
            opacity: 0.4;
            background: rgba(255, 255, 255, 0.08);
        }

        .sortable-chosen {
            background: rgba(255, 255, 255, 0.05);
        }

        .quiz-drag-handle:active {
            cursor: grabbing;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            addQuestion();
            initQuestionSortable();
        });

        document.getElementById("add-question").addEventListener("click", function() {
            addQuestion();
        });

        function initQuestionSortable() {
            const container = document.getElementById("question-container");
            if (!container || typeof Sortable === 'undefined') return;

            if (container._questionSortable) {
                container._questionSortable.destroy();
            }

            container._questionSortable = new Sortable(container, {
                handle: '.quiz-drag-handle',
                draggable: '.question-wrapper',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function() {
                    reindexQuestions();
                }
            });
        }

        function addQuestion() {
            const container = document.getElementById("question-container");

            const html = `
                <div class="card mb-3 question-wrapper">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="quiz-drag-handle text-muted" style="cursor: grab;" title="Drag to reorder">
                                    <i class="fas fa-grip-vertical"></i>
                                </span>
                                <strong>Question <span class="question-index-label">1</span></strong>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-question">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>

                        <input type="text" class="form-control mb-3 question-input"
                               placeholder="Enter question" required>

                        <div class="mb-3">
                            <label class="form-label d-block"><strong>Question Type</strong></label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input quiz-type-radio" type="radio" value="0" checked>
                                <label class="form-check-label">Main Quiz Question</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input quiz-type-radio" type="radio" value="1">
                                <label class="form-check-label">Special Quiz Question</label>
                            </div>
                        </div>

                        <h5>Options</h5>
                        <div class="row options-wrapper"></div>

                        <button type="button" class="btn btn-secondary mt-2 add-option">Add Option</button>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML("beforeend", html);

            const wrapper = container.querySelector(".question-wrapper:last-child");
            const addOptionBtn = wrapper.querySelector(".add-option");

            addOption(addOptionBtn);
            addOption(addOptionBtn);
            addOption(addOptionBtn);
            addOption(addOptionBtn);

            reindexQuestions();
        }

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

        function reindexQuestions() {
            const questions = document.querySelectorAll(".question-wrapper");

            questions.forEach((question, qIndex) => {
                const indexLabel = question.querySelector(".question-index-label");
                if (indexLabel) {
                    indexLabel.textContent = qIndex + 1;
                }

                const qInput = question.querySelector(".question-input");
                qInput.name = `questions[${qIndex}][question]`;

                const typeRadios = question.querySelectorAll(".quiz-type-radio");
                typeRadios.forEach((typeRadio) => {
                    typeRadio.name = `questions[${qIndex}][is_special]`;
                });

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
