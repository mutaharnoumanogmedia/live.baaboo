<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Edit Live Show') }}
        </h2>
    </x-slot>

    <div class="container-fluid form-container px-3">
        @if (request('live_show_id'))
            <x-admin.live-show-tabs :live-show-id="$liveShow->id" active="info" />
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Left: Basic Information & Schedule -->
            <div class="col-lg-6">
                <form method="POST" action="{{ route('admin.live-shows.update', $liveShow->id) }}"
                    enctype="multipart/form-data" id="liveShowDetailsForm">
                    @csrf
                    @method('PUT')

                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-info-circle me-2"></i>Basic Information
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label required-field">Title</label>
                                    <input type="text" name="title" class="form-control"
                                        placeholder="Enter live show title"
                                        value="{{ old('title', $liveShow->title ?? '') }}" required>
                                </div>

                                <div class="mb-3 col-md-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="4" placeholder="Provide a description of your live show">{{ old('description', $liveShow->description ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-calendar-alt me-2"></i>Schedule & Stream Details
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label required-field">Scheduled At</label>
                                    <input type="datetime-local" name="scheduled_at" class="form-control"
                                        value="{{ old('scheduled_at', isset($liveShow) ? \Carbon\Carbon::parse($liveShow->scheduled_at)->format('Y-m-d\TH:i') : '') }}"
                                        required>
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="scheduled"
                                            {{ old('status', $liveShow->status ?? '') == 'scheduled' ? 'selected' : '' }}>
                                            Scheduled</option>
                                        <option value="live"
                                            {{ old('status', $liveShow->status ?? '') == 'live' ? 'selected' : '' }}>
                                            Live
                                        </option>
                                        <option value="completed"
                                            {{ old('status', $liveShow->status ?? '') == 'completed' ? 'selected' : '' }}>
                                            Completed</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Is Test Show</label>
                                    <select name="is_test_show" class="form-select">
                                        <option value="0"
                                            {{ old('is_test_show', $liveShow->is_test_show ?? '') == 0 ? 'selected' : '' }}>
                                            No
                                        </option>
                                        <option value="1"
                                            {{ old('is_test_show', $liveShow->is_test_show ?? '') == 1 ? 'selected' : '' }}>
                                            Yes
                                        </option>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label required-field">Max Players</label>
                                    <input type="number" name="max_players" class="form-control" min="1"
                                        max="100000" required
                                        value="{{ old('max_players', $liveShow->max_players ?? 1000) }}">
                                    <div class="form-text">Maximum number of participants allowed to join this show
                                    </div>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label required-field">Chat Status</label>
                                    <select name="chat_enabled" class="form-select" required>
                                        <option value="1"
                                            {{ (string) old('chat_enabled', (int) ($liveShow->chat_enabled ?? 1)) === '1' ? 'selected' : '' }}>
                                            Enabled
                                        </option>
                                        <option value="0"
                                            {{ (string) old('chat_enabled', (int) ($liveShow->chat_enabled ?? 1)) === '0' ? 'selected' : '' }}>
                                            Disabled
                                        </option>
                                    </select>
                                    <div class="form-text">Participants can send messages only when chat is enabled
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-images me-2"></i>Media Uploads
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="mb-4 col-md-12">
                                    <label class="form-label">Thumbnail</label>
                                    <div class="preview-container" id="thumbnailPreview">
                                        @if (isset($liveShow) && $liveShow->thumbnail)
                                            <p>Current Thumbnail:</p>
                                            <img src="{{ $liveShow->thumbnail }}" class="preview-image"
                                                style="max-width:200px;">
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>
                            {{ __('Update Live Show Details') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right: Winners & Prize Split -->
            <div class="col-lg-6">
                @php
                    $activeWinnerTab = $errors->hasAny(['special_gifts', 'special_max_winners']) ? 'special' : 'regular';
                @endphp

                <ul class="nav nav-tabs mb-3" id="winnerConfigTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold {{ $activeWinnerTab === 'regular' ? 'active' : '' }}"
                            id="regularWinnersTabBtn" data-bs-toggle="tab" data-bs-target="#regularWinnersTabPane"
                            type="button" role="tab" aria-controls="regularWinnersTabPane"
                            aria-selected="{{ $activeWinnerTab === 'regular' ? 'true' : 'false' }}">
                            <i class="fas fa-trophy me-1"></i> Winners & Prize Split
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link fw-semibold special-winner-tab {{ $activeWinnerTab === 'special' ? 'active' : '' }}"
                            id="specialWinnersTabBtn" data-bs-toggle="tab" data-bs-target="#specialWinnersTabPane"
                            type="button" role="tab" aria-controls="specialWinnersTabPane"
                            aria-selected="{{ $activeWinnerTab === 'special' ? 'true' : 'false' }}">
                            <i class="fas fa-gift me-1"></i> Special Quiz Gifts
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="winnerConfigTabContent">
                    <div class="tab-pane fade {{ $activeWinnerTab === 'regular' ? 'show active' : '' }}"
                        id="regularWinnersTabPane" role="tabpanel" aria-labelledby="regularWinnersTabBtn">
                        <form method="POST" action="{{ route('admin.live-shows.winner-prizes.update', $liveShow->id) }}"
                            id="liveShowWinnerPrizesForm">
                        @csrf
                        @method('PUT')

                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-trophy me-2"></i>Winners & Prize Split
                            </div>
                            <div class="card-body">
                                @php $maxWinnerSlots = 50; @endphp
                                <div class="mb-3 row">
                                    <div class="col-md-6">
                                        <label class="form-label required-field">Max winners per show</label>
                                        <input type="number" name="max_winners" id="maxWinners"
                                            class="form-control" min="1" max="{{ $maxWinnerSlots }}" required
                                            value="{{ old('max_winners', $liveShow->max_winners ?? 3) }}">
                                        <div class="form-text">Number of winners (1–{{ $maxWinnerSlots }}) who share
                                            the
                                            prizes
                                        </div>
                                    </div>
                                </div>
                                @error('winner_prizes')
                                    <div class="py-2 alert alert-danger">{{ $message }}</div>
                                @enderror
                                <p class="mb-2 text-muted small">Prize percentage per rank (must total 100% for the
                                    first
                                    <span id="maxWinnersLabel">3</span> winner(s):
                                </p>
                                <div class="table-responsive">
                                    <table class="table align-middle table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Rank</th>
                                                <th>Prize</th>
                                                <th>Is Voucher</th>
                                                <th>Voucher Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody id="winnerPrizesBody">
                                            @php
                                                $maxW = (int) old('max_winners', $liveShow->max_winners ?? 3);
                                                $percentagesByRank = $liveShow->winnerPrizes
                                                    ->keyBy('rank')
                                                    ->map(fn($p) => $p->prize)
                                                    ->toArray();
                                                $voucherByRank = $liveShow->winnerPrizes
                                                    ->keyBy('rank')
                                                    ->map(fn($p) => (bool) ($p->voucher ?? ($p->is_voucher ?? false)))
                                                    ->toArray();
                                                $voucherAmountByRank = $liveShow->winnerPrizes
                                                    ->keyBy('rank')
                                                    ->map(
                                                        fn($p) => $p->voucher_amount ??
                                                            ($p->winner_voucher_amount ?? ''),
                                                    )
                                                    ->toArray();
                                                $defaultPct = [1 => 50, 2 => 30, 3 => 20];
                                            @endphp
                                            @for ($r = 1; $r <= $maxWinnerSlots; $r++)
                                                <tr class="winner-percent-row" data-rank="{{ $r }}"
                                                    style="{{ $r > $maxW ? 'display:none' : '' }}">
                                                    <td class="text-white">
                                                        {{ $r }}{{ $r === 1 ? 'st' : ($r === 2 ? 'nd' : ($r === 3 ? 'rd' : 'th')) }}
                                                        place</td>
                                                    <td style="max-width: 120px;">
                                                        <input type="text"
                                                            name="winner_prizes[{{ $r }}]"
                                                            class="form-control winner-pct-input"
                                                            placeholder="Dailixir Starterset, 50€, 10€ baaboo Voucher"
                                                            value="{{ old('winner_prizes.' . $r, $percentagesByRank[$r] ?? ($defaultPct[$r] ?? '')) }}">
                                                    </td>
                                                    <td style="max-width: 120px;" class="text-center">
                                                        <input type="checkbox"
                                                            name="winner_voucher[{{ $r }}]"
                                                            class="form-check-input voucher-checkbox" value="1"
                                                            {{ old('winner_voucher.' . $r, $voucherByRank[$r] ?? false) ? 'checked' : '' }}>
                                                    </td>
                                                    <td style="max-width: 120px;">
                                                        <input type="number" min="0" step="0.01"
                                                            name="winner_voucher_amount[{{ $r }}]"
                                                            class="form-control winner-pct-input voucher-amount"
                                                            {{ !old('winner_voucher.' . $r, $voucherByRank[$r] ?? false) ? 'readonly' : '' }}
                                                            value="{{ old('winner_voucher_amount.' . $r, $voucherAmountByRank[$r] ?? '') }}">
                                                    </td>
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                                <div id="winnerPrizesError" class="invalid-feedback d-none"></div>
                            </div>
                        </div>

                        <div class="mb-4 text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-trophy me-2"></i>
                                {{ __('Update Winner Prizes') }}
                            </button>
                        </div>
                        </form>
                    </div>

                    <div class="tab-pane fade {{ $activeWinnerTab === 'special' ? 'show active' : '' }}"
                        id="specialWinnersTabPane" role="tabpanel" aria-labelledby="specialWinnersTabBtn">
                        <form method="POST" action="{{ route('admin.live-shows.special-gifts.update', $liveShow->id) }}"
                            id="liveShowSpecialGiftsForm">
                        @csrf
                        @method('PUT')

                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-gift me-2"></i>Special Quiz Gifts
                            </div>
                            <div class="card-body">
                                @php $maxSpecialSlots = 50; @endphp
                                <div class="mb-3 row">
                                    <div class="col-md-6">
                                        <label class="form-label">Max special winners per show</label>
                                        <input type="number" name="special_max_winners" id="specialMaxWinners"
                                            class="form-control" min="0" max="{{ $maxSpecialSlots }}"
                                            value="{{ old('special_max_winners', $liveShow->special_max_winners ?? 0) }}">
                                        <div class="form-text">Number of Special Quiz winners (0 = no special winners).
                                        </div>
                                    </div>
                                </div>
                                @error('special_gifts')
                                    <div class="py-2 alert alert-danger">{{ $message }}</div>
                                @enderror
                                <p class="mb-2 text-muted small">Define the gift awarded to each Special Quiz rank.</p>
                                <div class="table-responsive">
                                    <table class="table align-middle table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Rank</th>
                                                <th>Gift Name</th>
                                                <th>Type</th>
                                                <th>Value</th>
                                                <th>Voucher Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody id="specialGiftsBody">
                                            @php
                                                $maxSW = (int) old(
                                                    'special_max_winners',
                                                    $liveShow->special_max_winners ?? 0,
                                                );
                                                $giftByRank = $liveShow->specialGifts->keyBy('rank');
                                            @endphp
                                            @for ($r = 1; $r <= $maxSpecialSlots; $r++)
                                                @php $gift = $giftByRank[$r] ?? null; @endphp
                                                <tr class="special-gift-row" data-rank="{{ $r }}"
                                                    style="{{ $r > $maxSW ? 'display:none' : '' }}">
                                                    <td class="text-white">
                                                        {{ $r }}{{ $r === 1 ? 'st' : ($r === 2 ? 'nd' : ($r === 3 ? 'rd' : 'th')) }}
                                                        place</td>
                                                    <td style="max-width: 200px;">
                                                        <input type="text"
                                                            name="special_gifts[{{ $r }}][name]"
                                                            class="form-control"
                                                            placeholder="e.g. PlayStation 5, 50€ cash"
                                                            value="{{ old('special_gifts.' . $r . '.name', $gift->name ?? '') }}">
                                                    </td>
                                                @php
                                                    $giftType = old('special_gifts.' . $r . '.type', $gift->type ?? 'cash');
                                                    $isVoucherType = $giftType === 'voucher';
                                                @endphp
                                                    <td style="max-width: 140px;">
                                                        <select name="special_gifts[{{ $r }}][type]"
                                                            class="form-select special-gift-type">
                                                            @foreach ($specialGiftTypes as $tVal => $tLabel)
                                                                <option value="{{ $tVal }}"
                                                                    {{ $giftType === $tVal ? 'selected' : '' }}>
                                                                    {{ $tLabel }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td style="max-width: 120px;">
                                                        <input type="number" min="0" step="0.01"
                                                            name="special_gifts[{{ $r }}][value]"
                                                            class="form-control special-gift-value"
                                                            {{ $isVoucherType ? 'readonly' : '' }}
                                                            value="{{ old('special_gifts.' . $r . '.value', $gift->value ?? '') }}">
                                                    </td>
                                                    <td style="max-width: 120px;">
                                                        <input type="number" min="0" step="0.01"
                                                            name="special_gifts[{{ $r }}][voucher_amount]"
                                                            class="form-control special-gift-voucher-amount"
                                                            {{ $isVoucherType ? '' : 'readonly' }}
                                                            value="{{ old('special_gifts.' . $r . '.voucher_amount', $gift->voucher_amount ?? '') }}">
                                                    </td>
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 text-center">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-gift me-2"></i>
                                {{ __('Update Special Gifts') }}
                            </button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>


        </div>
    </div>

    @push('styles')
        <style>
            .form-container {
                max-width: 1400px;
                margin: 0 auto;
            }

            .card {
                border: none;
                border-radius: 12px;
                box-shadow: var(--card-shadow);
                margin-bottom: 1.5rem;
                transition: transform 0.3s ease;
            }

            .card:hover {
                transform: translateY(-5px);
            }

            .card-header {
                color: white;
                border-radius: 12px 12px 0 0 !important;
                padding: 1rem 1.5rem;
                font-weight: 600;
            }

            .card-body {
                padding: 1.5rem;
            }

            .form-label {
                font-weight: 500;
                margin-bottom: 0.5rem;
                color: #495057;
            }

            .form-control,
            .form-select {
                border-radius: 8px;
                padding: 0.75rem 1rem;
                border: 1px solid #dee2e6;
                transition: all 0.3s;
            }

            .form-control:focus,
            .form-select:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
            }

            #winnerConfigTabs .nav-link {
                color: #64748b;
                border-radius: 0;
            }

            #winnerConfigTabs .nav-link.active {
                color: #0d6efd;
                border-bottom: 2px solid #0d6efd !important;
            }

            #winnerConfigTabs .special-winner-tab,
            #winnerConfigTabs .special-winner-tab:hover {
                color: var(--bs-warning);
            }

            #winnerConfigTabs .special-winner-tab.active {
                color: var(--bs-warning);
                border-bottom: 2px solid var(--bs-warning) !important;
            }
        </style>
    @endpush
    @push('scripts')
        <script>
            (function() {
                var maxWinnersEl = document.getElementById('maxWinners');
                var labelEl = document.getElementById('maxWinnersLabel');
                var rows = document.querySelectorAll('.winner-percent-row');

                function voucherChecks() {
                    $("[type='checkbox']").off()
                    $("[type='checkbox']").on('change', function() {
                        var row = $(this).closest(".winner-percent-row");
                        var voucherAmountInput = row.find(".voucher-amount");
                        if ($(this).is(":checked")) {
                            voucherAmountInput.removeAttr("readonly");
                            voucherAmountInput.focus();
                        } else {
                            voucherAmountInput.attr("readonly", "readonly");
                            voucherAmountInput.val('');
                        }
                    })
                }

                function update() {
                    var maxRank = rows.length;
                    var n = parseInt(maxWinnersEl.value, 10) || 1;
                    n = Math.max(1, Math.min(maxRank, n));
                    if (labelEl) labelEl.textContent = n;
                    rows.forEach(function(tr) {
                        var rank = parseInt(tr.getAttribute('data-rank'), 10);
                        tr.style.display = rank <= n ? '' : 'none';
                    });
                    voucherChecks();
                }
                if (maxWinnersEl) {
                    maxWinnersEl.addEventListener('change', update);
                    maxWinnersEl.addEventListener('input', update);
                    update();
                }
            })();

            (function() {
                var specialMaxEl = document.getElementById('specialMaxWinners');
                var specialRows = document.querySelectorAll('.special-gift-row');

                function applySpecialGiftTypeFields(row, clearInactive) {
                    var typeSelect = row.querySelector('.special-gift-type');
                    var valueInput = row.querySelector('.special-gift-value');
                    var voucherInput = row.querySelector('.special-gift-voucher-amount');
                    if (!typeSelect || !valueInput || !voucherInput) {
                        return;
                    }

                    if (typeSelect.value === 'voucher') {
                        valueInput.setAttribute('readonly', 'readonly');
                        if (clearInactive) {
                            valueInput.value = '';
                        }
                        voucherInput.removeAttribute('readonly');
                    } else {
                        valueInput.removeAttribute('readonly');
                        voucherInput.setAttribute('readonly', 'readonly');
                        if (clearInactive) {
                            voucherInput.value = '';
                        }
                    }
                }

                function bindSpecialGiftTypeFields() {
                    document.querySelectorAll('.special-gift-row .special-gift-type').forEach(function(select) {
                        select.removeEventListener('change', select._specialGiftTypeHandler);
                        select._specialGiftTypeHandler = function() {
                            applySpecialGiftTypeFields(select.closest('.special-gift-row'), true);
                        };
                        select.addEventListener('change', select._specialGiftTypeHandler);
                    });
                    specialRows.forEach(function(row) {
                        applySpecialGiftTypeFields(row, false);
                    });
                }

                function updateSpecial() {
                    var maxRank = specialRows.length;
                    var n = parseInt(specialMaxEl.value, 10);
                    if (isNaN(n) || n < 0) n = 0;
                    n = Math.min(maxRank, n);
                    specialRows.forEach(function(tr) {
                        var rank = parseInt(tr.getAttribute('data-rank'), 10);
                        tr.style.display = rank <= n ? '' : 'none';
                    });
                    bindSpecialGiftTypeFields();
                }
                if (specialMaxEl) {
                    specialMaxEl.addEventListener('change', updateSpecial);
                    specialMaxEl.addEventListener('input', updateSpecial);
                    updateSpecial();
                } else {
                    bindSpecialGiftTypeFields();
                }
            })();
        </script>
    @endpush
</x-app-dashboard-layout>
