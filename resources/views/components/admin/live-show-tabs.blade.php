@props([
    'active' => 'info',
    'liveShowId' => null,
])

@php
    $liveShowId = $liveShowId ?? request('live_show_id');

    $tabs = [
        'info' => [
            'label' => 'Live show information',
            'icon' => 'fas fa-info-circle',
            'url' => $liveShowId ? route('admin.live-shows.edit', $liveShowId) : null,
        ],
        'quiz' => [
            'label' => 'Quiz',
            'icon' => 'fas fa-question-circle',
            'url' => route('admin.live-show-quizzes.index', array_filter(['live_show_id' => $liveShowId])),
        ],
        'gallery' => [
            'label' => 'Gallery Media',
            'icon' => 'fas fa-images',
            'url' => route('admin.live-shows.gallery-attach', ['live_show' => $liveShowId]),
        ],
    ];
@endphp

<div {{ $attributes->merge(['class' => 'live-show-mgmt-tabs mb-4']) }}>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark border-0 py-3">
            <ul class="nav nav-pills live-show-mgmt-tabs__list flex-wrap gap-2 mb-0" role="tablist">
                @foreach ($tabs as $key => $tab)
                    <li class="nav-item" role="presentation">
                        @if ($tab['url'] && $active !== $key)
                            <a href="{{ $tab['url'] }}" class="nav-link" role="tab">
                                <i class="{{ $tab['icon'] }} me-1"></i>{{ $tab['label'] }}
                            </a>
                        @else
                            <span @class([
                                'nav-link',
                                'active' => $active === $key,
                                'disabled' => ! $tab['url'],
                            ]) role="tab" @if ($active === $key) aria-current="page" @endif>
                                <i class="{{ $tab['icon'] }} me-1"></i>{{ $tab['label'] }}
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            .live-show-mgmt-tabs__list .nav-link {
                color: rgba(255, 255, 255, 0.75);
                border-radius: 2rem;
                padding: 0.5rem 1.25rem;
                font-weight: 500;
                transition: background-color 0.2s ease, color 0.2s ease;
            }

            .live-show-mgmt-tabs__list .nav-link:not(.disabled):not(.active):hover,
            .live-show-mgmt-tabs__list .nav-link:not(.disabled):not(.active):focus {
                color: #fff;
                background-color: rgba(255, 255, 255, 0.1);
            }

            .live-show-mgmt-tabs__list .nav-link.active {
                color: #fff;
                background-color: #0d6efd;
                cursor: default;
            }

            .live-show-mgmt-tabs__list .nav-link.disabled {
                color: rgba(255, 255, 255, 0.35);
                cursor: not-allowed;
            }

            @media (max-width: 575.98px) {
                .live-show-mgmt-tabs__list {
                    flex-direction: column;
                }

                .live-show-mgmt-tabs__list .nav-link {
                    text-align: center;
                }
            }
        </style>
    @endpush
@endonce
