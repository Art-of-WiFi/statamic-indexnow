@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', __('IndexNow'))

@section('content')

    <header class="mb-6">
        @includeIf('statamic::partials.breadcrumb', [
            'url' => cp_route('utilities.index'),
            'title' => __('Utilities')
        ])
        <div class="flex items-center justify-between">
            <h1>{{ __('IndexNow') }}</h1>
            <div class="flex items-center gap-4">
                @if ($auto_submit)
                    <span class="badge-pill-sm bg-green-200 text-green-800">Auto-submit enabled</span>
                @endif
                <span class="text-sm text-gray-600">
                    Submitting for: <strong>{{ $production_url }}</strong>
                </span>
            </div>
        </div>
    </header>

    @if (session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded mb-4">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if (! $configured)
        <div class="card p-4">
            <p class="text-gray-700">
                IndexNow is not configured. Add <code>INDEXNOW_KEY</code> to your <code>.env</code> file and host
                a matching <code>{key}.txt</code> file at the root of <strong>{{ $production_url }}</strong>.
            </p>
        </div>
    @else
        {{-- Filters --}}
        <div class="card p-4 mb-6">
            <form method="GET" action="{{ cp_route('utilities.index-now') }}" class="flex items-center gap-4">
                <div class="w-48 shrink-0">
                    <select name="collection" class="select-input text-sm w-full" onchange="this.form.submit()">
                        <option value="">{{ __('All collections') }}</option>
                        @foreach ($collections as $collection)
                            <option value="{{ $collection }}" @selected($collection_filter === $collection)>
                                {{ $collection }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1">
                    <input type="text"
                           name="search"
                           value="{{ $search_filter }}"
                           placeholder="{{ __('Search by title...') }}"
                           class="input-text text-sm w-full" />
                </div>
                <ui-button type="submit" size="sm" text="{{ __('Filter') }}" />
                @if ($collection_filter || $search_filter)
                    <ui-button size="sm" text="{{ __('Clear') }}" href="{{ cp_route('utilities.index-now') }}" />
                @endif
            </form>
        </div>

        <form method="POST" action="{{ cp_route('utilities.index-now.submit') }}" id="indexnow-form">
            @csrf

            <div class="mb-4 flex items-center justify-end gap-2">
                <ui-button size="sm" text="{{ __('Select unsubmitted') }}" id="select-unsubmitted" />
                <ui-button size="sm" text="{{ __('Select modified') }}" id="select-modified" />
            </div>

            <div class="card p-0">
                <table class="data-table" id="indexnow-table">
                    <thead>
                        <tr>
                            <th class="w-4 pl-4 pr-0">
                                <input type="checkbox" id="select-all" class="cursor-pointer" />
                            </th>
                            <th class="cursor-pointer select-none" data-sort="title">
                                {{ __('Title') }} <span class="sort-arrow text-gray-400"></span>
                            </th>
                            <th class="cursor-pointer select-none" data-sort="collection">
                                {{ __('Collection') }} <span class="sort-arrow text-gray-400"></span>
                            </th>
                            <th class="cursor-pointer select-none whitespace-nowrap" data-sort="status">
                                {{ __('Status') }} <span class="sort-arrow text-gray-400"></span>
                            </th>
                            <th class="cursor-pointer select-none whitespace-nowrap" data-sort="updated_at">
                                {{ __('Modified') }} <span class="sort-arrow text-gray-400">&#9660;</span>
                            </th>
                            <th class="cursor-pointer select-none whitespace-nowrap" data-sort="last_submitted">
                                {{ __('Submitted') }} <span class="sort-arrow text-gray-400"></span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($entries as $entry)
                            <tr data-title="{{ $entry['title'] }}"
                                data-collection="{{ $entry['collection'] }}"
                                data-status="{{ $entry['status'] }}"
                                data-updated_at="{{ $entry['updated_at'] ?? '' }}"
                                data-last_submitted="{{ $entry['last_submitted'] ?? '' }}">
                                <td class="pl-4 pr-0">
                                    <input type="checkbox"
                                           class="url-checkbox cursor-pointer"
                                           data-url="{{ $entry['url'] }}"
                                           data-entry-id="{{ $entry['id'] }}" />
                                </td>
                                <td>
                                    <a href="{{ $entry['edit_url'] }}" class="text-blue-600 hover:text-blue-800" title="{{ $entry['url'] }}">
                                        {{ $entry['title'] }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge-pill-sm">{{ $entry['collection'] }}</span>
                                </td>
                                <td>
                                    @if ($entry['status'] === 'never')
                                        <span class="badge-pill-sm bg-gray-200 text-gray-700">Never</span>
                                    @elseif ($entry['status'] === 'modified')
                                        <span class="badge-pill-sm bg-amber-200 text-amber-800">Modified</span>
                                    @else
                                        <span class="badge-pill-sm bg-green-200 text-green-800">Submitted</span>
                                    @endif
                                </td>
                                <td class="text-sm text-gray-600 whitespace-nowrap">
                                    {{ $entry['updated_at'] ?? "\u{2014}" }}
                                </td>
                                <td class="text-sm text-gray-600 whitespace-nowrap">
                                    {{ $entry['last_submitted'] ?? "\u{2014}" }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Hidden inputs container for selected URLs --}}
            <div id="hidden-inputs"></div>

            {{-- Floating toolbar, shown while entries are selected (mirrors the CP listing bulk actions) --}}
            <div class="indexnow-toolbar" id="indexnow-toolbar" hidden>
                <div class="indexnow-toolbar-inner">
                    <span class="indexnow-toolbar-count" id="selected-count">0 selected</span>
                    <ui-button-group>
                        <ui-button text="{{ __('Deselect') }}" id="deselect-all" />
                        <ui-button type="submit" variant="primary" text="{{ __('Submit Selected to IndexNow') }}" id="submit-btn" :disabled="true" />
                    </ui-button-group>
                </div>
            </div>
        </form>
    @endif

@endsection

@section('scripts')
    {{-- Not in the content section either: the Vue template compiler strips <style> tags too --}}
    <style>
        .indexnow-toolbar {
            position: sticky;
            inset-block-end: 1.5rem;
            z-index: var(--z-index-above, 10);
            margin-block-start: 1rem;
            pointer-events: none;
        }

        .indexnow-toolbar-inner {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            inline-size: fit-content;
            margin-inline: auto;
            padding-block: 0.25rem;
            padding-inline: 0.75rem 0.25rem;
            border: 1px solid oklch(0.87 0.006 286 / 0.6);
            border-radius: 0.75rem;
            background: oklch(0.92 0.004 286 / 0.85);
            box-shadow: 0 1px 16px -2px oklch(0.37 0.013 286 / 0.2);
            backdrop-filter: blur(8px);
            pointer-events: auto;
        }

        .indexnow-toolbar-count {
            font-size: 0.875rem;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            color: oklch(0.44 0.016 286);
        }

        .dark .indexnow-toolbar-inner {
            border-color: oklch(0.37 0.013 286);
            background: oklch(0.27 0.006 286 / 0.9);
            box-shadow: 0 10px 15px oklch(0 0 none / 0.5);
        }

        .dark .indexnow-toolbar-count {
            color: oklch(0.71 0.014 286);
        }

        @media (prefers-reduced-motion: no-preference) {
            .indexnow-toolbar {
                transition: translate 0.2s ease-out, opacity 0.2s ease-out;
            }

            @starting-style {
                .indexnow-toolbar {
                    translate: 0 100%;
                    opacity: 0;
                }
            }
        }
    </style>

    {{--
        Kept outside of the content section: on Statamic 6 the content is compiled as a Vue
        template, which strips <script> tags and replaces the server-rendered DOM on mount.
        Listeners are therefore delegated to the document and elements are looked up lazily.
    --}}
    <script>
        (function () {
            var currentSort = 'updated_at';
            var currentDir = 'desc';

            function getCheckboxes() {
                return document.querySelectorAll('.url-checkbox');
            }

            function getCheckedCheckboxes() {
                return document.querySelectorAll('.url-checkbox:checked');
            }

            function getSubmitButton() {
                var submitBtn = document.getElementById('submit-btn');

                if (submitBtn && submitBtn.tagName !== 'BUTTON') {
                    submitBtn = submitBtn.querySelector('button') || submitBtn;
                }

                return submitBtn;
            }

            function updateState() {
                var checkboxes = getCheckboxes();
                var checked = getCheckedCheckboxes();
                var selectAll = document.getElementById('select-all');
                var submitBtn = getSubmitButton();
                var countLabel = document.getElementById('selected-count');
                var toolbar = document.getElementById('indexnow-toolbar');

                if (toolbar) {
                    toolbar.hidden = checked.length === 0;
                }

                if (submitBtn) {
                    submitBtn.disabled = checked.length === 0;
                }

                if (countLabel) {
                    countLabel.textContent = checked.length + ' selected';
                }

                if (selectAll) {
                    selectAll.checked = checked.length === checkboxes.length && checkboxes.length > 0;
                    selectAll.indeterminate = checked.length > 0 && checked.length < checkboxes.length;
                }
            }

            function fillHiddenInputs(form) {
                var hiddenInputs = form.querySelector('#hidden-inputs');

                hiddenInputs.innerHTML = '';

                getCheckedCheckboxes().forEach(function (cb, i) {
                    var urlInput = document.createElement('input');
                    urlInput.type = 'hidden';
                    urlInput.name = 'urls[' + i + '][url]';
                    urlInput.value = cb.dataset.url;
                    hiddenInputs.appendChild(urlInput);

                    var idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'urls[' + i + '][entry_id]';
                    idInput.value = cb.dataset.entryId;
                    hiddenInputs.appendChild(idInput);
                });
            }

            function sortTable(column, direction) {
                var tbody = document.querySelector('#indexnow-table tbody');
                var rows = Array.from(tbody.querySelectorAll('tr'));

                rows.sort(function (a, b) {
                    var aVal = (a.dataset[column] || '').toLowerCase();
                    var bVal = (b.dataset[column] || '').toLowerCase();

                    if (aVal < bVal) return direction === 'asc' ? -1 : 1;
                    if (aVal > bVal) return direction === 'asc' ? 1 : -1;
                    return 0;
                });

                rows.forEach(function (row) { tbody.appendChild(row); });

                document.querySelectorAll('#indexnow-table th[data-sort]').forEach(function (th) {
                    var arrow = th.querySelector('.sort-arrow');
                    if (th.dataset.sort === column) {
                        arrow.innerHTML = direction === 'asc' ? '&#9650;' : '&#9660;';
                        arrow.classList.remove('text-gray-400');
                        arrow.classList.add('text-gray-700');
                    } else {
                        arrow.innerHTML = '';
                        arrow.classList.remove('text-gray-700');
                        arrow.classList.add('text-gray-400');
                    }
                });
            }

            function selectByStatus(status) {
                getCheckboxes().forEach(function (cb) {
                    cb.checked = cb.closest('tr').dataset.status === status;
                });
                updateState();
            }

            function clearSelection() {
                getCheckboxes().forEach(function (cb) { cb.checked = false; });
                updateState();
            }

            document.addEventListener('change', function (e) {
                if (e.target.id === 'select-all') {
                    getCheckboxes().forEach(function (cb) { cb.checked = e.target.checked; });
                    updateState();
                } else if (e.target.classList.contains('url-checkbox')) {
                    updateState();
                }
            });

            document.addEventListener('click', function (e) {
                var target = e.target instanceof Element ? e.target : null;

                if (! target) {
                    return;
                }

                var th = target.closest('#indexnow-table th[data-sort]');

                if (th) {
                    var column = th.dataset.sort;

                    if (currentSort === column) {
                        currentDir = currentDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        currentSort = column;
                        currentDir = column === 'updated_at' || column === 'last_submitted' ? 'desc' : 'asc';
                    }

                    sortTable(currentSort, currentDir);
                } else if (target.closest('#select-unsubmitted')) {
                    e.preventDefault();
                    selectByStatus('never');
                } else if (target.closest('#select-modified')) {
                    e.preventDefault();
                    selectByStatus('modified');
                } else if (target.closest('#deselect-all')) {
                    e.preventDefault();
                    clearSelection();
                }
            });

            // Escape deselects, like the CP listing toolbar, unless focus is in a form control
            document.addEventListener('keydown', function (e) {
                var toolbar = document.getElementById('indexnow-toolbar');
                var tag = e.target instanceof Element ? e.target.tagName : '';

                if (e.key !== 'Escape' || ! toolbar || toolbar.hidden) {
                    return;
                }

                if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || document.querySelector('[role="dialog"]')) {
                    return;
                }

                e.preventDefault();
                clearSelection();
            });

            document.addEventListener('submit', function (e) {
                if (e.target.id !== 'indexnow-form') {
                    return;
                }

                if (getCheckedCheckboxes().length === 0) {
                    e.preventDefault();
                    return;
                }

                fillHiddenInputs(e.target);
            });
        })();
    </script>
@endsection
