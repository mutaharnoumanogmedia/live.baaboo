<div class="card shadow  p-4 bg-light text-left justify-content-start align-items-start"
    style="max-width: 480px; margin: 2rem auto; border-radius:50px; text-align: left;">

    <div class="card-body">
        <h3 class="mb-3 text-dark text-center fw-bold"><i class="fas fa-user-plus me-2 text-primary"></i>
            Kostenlos anmelden
        </h3>
        <form method="POST" id="registerationForm" action="{{ route('register-user-via-form-submit') }}">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label text-dark fw-semibold">Dein Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Max Mustermann"
                    autocomplete="off" value="{{ old('name') }}">
                @error('name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label text-dark fw-semibold">Deine E-Mail-Adresse <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" placeholder="du@email.com"
                    required autocomplete="off" value="{{ old('email') }}">
                @error('email')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            @if (isset($referredByUser))
                <input type="hidden" name="referred_by" value="{{ $referredByUser->id }}">
            @endif

            <div class="mb-3 form-check   mx-auto" style="max-width: 400px;">
                <input type="checkbox" class="form-check-input" name="agree_for_terms" id="agree" value="1"
                    required>
                <label class="form-check-label" for="agree">
                    Ich bestätige, dass ich mindestens 18 Jahre alt bin und die Allgemeinen <a href="#"
                        class="text-danger text-decoration-underline">Geschäftsbedingungen</a> akzeptiere. <span class="text-danger">*</span>

                </label>
                @error('agree_for_terms')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-3 form-check   mx-auto" style="max-width: 400px;">
                <input type="checkbox" name="agree_for_email" class="form-check-input" id="optional" value="1">
                <label class="form-check-label" for="optional">
                    Ich bin damit einverstanden, Informationen zu Angeboten, Aktionen und Neuigkeiten per E-Mail zu
                    erhalten. 
                </label>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn bg-purple text-white fw-bold btn-lg"
                    style="background-color: #6f42c1; border: none; border-radius: 20px;">
                    <i class="fas fa-arrow-right"></i> Anmelden
                </button>
            </div>
        </form>

        <div id="registerationError" class="text-danger small py-4" style="display:none;"></div>
        <div id="registerationSuccess" class="text-success small py-4" style="display:none;"></div>
        {{-- <div class="mt-3 text-center text-secondary">
        Already have an account? <a href="#" class="text-danger text-decoration-underline">Login</a>
    </div> --}}
    </div>
</div>


<script>
    document.getElementById('registerationForm').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Wird gesendet...';
        }
    });
</script>

{{-- <script>
    function registerUserViaForm(event) {
        //loading state
        const registerButton = document.querySelector('button[type="submit"]');
        registerButton.disabled = true;
        registerButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Registering...';
        event.preventDefault();
        const form = document.getElementById('registerationForm');
        const data = new FormData(form);
        const dataObject = Object.fromEntries(data);
        console.log(dataObject);
        fetch('{{ route('register-user-via-form-submit') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify(dataObject)
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                if (data.success) {
                    //redirect to the live show
                    //show success message
                    document.getElementById('registerationSuccess').textContent = data.message;
                    document.getElementById('registerationSuccess').innerHTML +=
                        `<br><a href="${data.referral_link}" target="_blank">${data.referral_link}</a>`;
                    document.getElementById('registerationSuccess').style.display = 'block';

                    //remove error message
                    document.getElementById('registerationError').style.display = 'none';
                    document.getElementById('registerationError').textContent = '';

                } else {
                    //show error message
                    document.getElementById('registerationError').textContent = data.message;
                    document.getElementById('registerationError').style.display = 'block';

                    //remove success message
                    document.getElementById('registerationSuccess').style.display = 'none';
                    document.getElementById('registerationSuccess').textContent = '';

                }
            })
            .catch(error => {
                console.error('Error registering user:', error);
                //show error message
                alert('Error registering user. Please try again.');
            })
            .finally(() => {
                registerButton.disabled = false;
                registerButton.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Register';
            });
    }
</script> --}}
