<x-app-dashboard-layout>
    {{-- chat_filter_module: word dictionary CRUD --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Chat Filter &middot; Words
        </h2>
    </x-slot>

    <div class="container-fluid py-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('admin.chat-filter.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to tiers
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#wordModal" onclick="resetWordForm()">
                <i class="bi bi-plus-lg me-1"></i> Add word
            </button>
        </div>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <select name="tier" class="form-select" onchange="this.form.submit()">
                    <option value="">All tiers</option>
                    @foreach ($tiers as $tier)
                        <option value="{{ $tier->id }}" @selected($selectedTier == $tier->id)>Tier {{ $tier->tier_number }} - {{ $tier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Search term...">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100" type="submit">Filter</button>
            </div>
        </form>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Term</th>
                            <th>Tier</th>
                            <th>Match</th>
                            <th>Whole word</th>
                            <th>Override</th>
                            <th>Active</th>
                            <th>Note</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($words as $word)
                            <tr>
                                <td><code>{{ $word->term }}</code></td>
                                <td>{{ $word->tier?->tier_number }}</td>
                                <td><span class="badge bg-light text-dark">{{ $word->match_type }}</span></td>
                                <td>{!! $word->whole_word ? '<i class="bi bi-check-lg text-success"></i>' : '' !!}</td>
                                <td>{{ $word->action_override ?? '-' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.chat-filter.words.toggle', $word) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm {{ $word->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                                            {{ $word->is_active ? 'Active' : 'Off' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="small text-muted">{{ $word->note }}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal" data-bs-target="#wordModal"
                                        onclick='editWord(@json($word))'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.chat-filter.words.destroy', $word) }}" class="d-inline"
                                        onsubmit="return confirm('Delete this word?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No words found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $words->links() }}
        </div>
    </div>

    {{-- chat_filter_module: shared add/edit modal --}}
    <div class="modal fade" id="wordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="wordForm" action="{{ route('admin.chat-filter.words.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="wordFormMethod" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Filter word</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Term</label>
                            <input type="text" name="term" id="w_term" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tier</label>
                            <select name="chat_filter_tier_id" id="w_tier" class="form-select" required>
                                @foreach ($tiers as $tier)
                                    <option value="{{ $tier->id }}">Tier {{ $tier->tier_number }} - {{ $tier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Match type</label>
                            <select name="match_type" id="w_match" class="form-select">
                                <option value="literal">literal</option>
                                <option value="phrase">phrase</option>
                                <option value="regex">regex</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Action override (optional)</label>
                            <select name="action_override" id="w_override" class="form-select">
                                <option value="">Use tier default</option>
                                <option value="ban">ban</option>
                                <option value="timeout">timeout</option>
                                <option value="watchlist">watchlist</option>
                                <option value="hard_block">hard_block</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <input type="text" name="note" id="w_note" class="form-control" placeholder="Note (optional)">
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="whole_word" value="0">
                            <input type="checkbox" name="whole_word" value="1" class="form-check-input" id="w_whole">
                            <label class="form-check-label" for="w_whole">Whole word only (\b boundaries)</label>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="w_active" checked>
                            <label class="form-check-label" for="w_active">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // chat_filter_module: reset modal for creating a new word
            function resetWordForm() {
                const form = document.getElementById('wordForm');
                form.action = '{{ route('admin.chat-filter.words.store') }}';
                document.getElementById('wordFormMethod').value = 'POST';
                document.getElementById('w_term').value = '';
                document.getElementById('w_match').value = 'literal';
                document.getElementById('w_override').value = '';
                document.getElementById('w_note').value = '';
                document.getElementById('w_whole').checked = false;
                document.getElementById('w_active').checked = true;
            }

            // chat_filter_module: prefill modal for editing an existing word
            function editWord(word) {
                const form = document.getElementById('wordForm');
                form.action = '{{ url('admin/chat-filter/words') }}/' + word.id;
                document.getElementById('wordFormMethod').value = 'PUT';
                document.getElementById('w_term').value = word.term;
                document.getElementById('w_tier').value = word.chat_filter_tier_id;
                document.getElementById('w_match').value = word.match_type;
                document.getElementById('w_override').value = word.action_override || '';
                document.getElementById('w_note').value = word.note || '';
                document.getElementById('w_whole').checked = !!word.whole_word;
                document.getElementById('w_active').checked = !!word.is_active;
            }
        </script>
    @endpush
</x-app-dashboard-layout>
