/**
 * Local / QA only: loads when the live show page is opened with ?debug_bot=1
 *
 * - Play overlay: clicks #playButton shortly after load (dismisses tap-to-play overlay).
 * - Register modal: when #registerModal opens, fills a unique random email, checks terms, submits.
 * - Quiz: watches #quizSection and picks a random answer after a random delay (before timer ends).
 *
 * Do not use in production against real competitions.
 */
(function () {
    "use strict";

    var MIN_DELAY_MS = 500;
    var MAX_DELAY_MS = 4000;

    var lastQuizId = null;

    function randomBetween(min, max) {
        return min + Math.floor(Math.random() * (max - min + 1));
    }

    function generateDebugEmail() {
        return "test." + generateUniqueString() + "@test.com";
    }

    function initRegisterModalAutoFill() {
        var modalEl = document.getElementById("registerModal");
        if (!modalEl) {
            return;
        }

        function fillAndSubmit() {
            var form = document.getElementById("registerForm");
            var emailInput = document.getElementById("registerEmail");
            var agree = document.getElementById("agree");
            if (!form || !emailInput || !agree) {
                return;
            }

            emailInput.value = generateDebugEmail();
            agree.checked = true;
            agree.dispatchEvent(new Event("change", { bubbles: true }));
            agree.dispatchEvent(new Event("input", { bubbles: true }));

            console.warn(
                "[live-show quiz debug bot] register modal — submitting with email " +
                    emailInput.value,
            );

            setTimeout(function () {
                if (typeof form.requestSubmit === "function") {
                    form.requestSubmit();
                } else {
                    form.dispatchEvent(
                        new Event("submit", {
                            bubbles: true,
                            cancelable: true,
                        }),
                    );
                }
            }, 150);
        }

        modalEl.addEventListener("shown.bs.modal", function () {
            console.warn(
                "[live-show quiz debug bot] register modal shown — auto-fill + submit",
            );
            fillAndSubmit();
        });

        if (modalEl.classList.contains("show")) {
            setTimeout(fillAndSubmit, 200);
        }
    }

    function tryAnswerQuiz() {
        var quizIdEl = document.getElementById("quizId");
        if (!quizIdEl) {
            return;
        }

        var quizId = quizIdEl.value;
        if (quizId === lastQuizId) {
            return;
        }
        lastQuizId = quizId;

        var radios = document.querySelectorAll(
            '#quizSection input[name="option"]:not(:disabled)',
        );
        if (!radios.length) {
            return;
        }

        var delay = randomBetween(MIN_DELAY_MS, MAX_DELAY_MS);
        console.warn(
            "[live-show quiz debug bot] scheduling answer in " +
                delay +
                "ms (quiz " +
                quizId +
                ")",
        );

        setTimeout(function () {
            var still = document.querySelectorAll(
                '#quizSection input[name="option"]:not(:disabled)',
            );
            if (!still.length) {
                return;
            }
            var pick = still[Math.floor(Math.random() * still.length)];
            pick.checked = true;
            pick.dispatchEvent(new Event("change", { bubbles: true }));
            console.warn(
                "[live-show quiz debug bot] selected option " + pick.value,
            );
        }, delay);
    }

    function initPlayButtonAutoClick() {
        function tryClick() {
            var btn = document.getElementById("playButton");
            if (!btn) {
                console.warn(
                    "[live-show quiz debug bot] #playButton not found",
                );
                return;
            }
            console.warn(
                "[live-show quiz debug bot] clicking #playButton (tap-to-play overlay)",
            );
            btn.click();
        }

        setTimeout(tryClick, 300);
    }

    function initQuizAutoAnswer() {
        var section = document.getElementById("quizSection");
        if (!section) {
            console.warn(
                "[live-show quiz debug bot] #quizSection not found (quiz auto-answer skipped)",
            );
            return;
        }

        var obs = new MutationObserver(function () {
            tryAnswerQuiz();
        });
        obs.observe(section, { childList: true, subtree: true });

        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", tryAnswerQuiz);
        } else {
            tryAnswerQuiz();
        }
    }

    function generateUniqueString(length = 10) {
        return crypto
            .randomUUID()
            .replace(/[^a-zA-Z]/g, "") // keep letters only
            .slice(0, length);
    }

    initPlayButtonAutoClick();
    initRegisterModalAutoFill();
    initQuizAutoAnswer();
})();
