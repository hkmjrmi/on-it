@php($canBulk = auth()->user()->can('changeWorkflow', new \App\Models\Issue()))

<div class="row g-2 mb-3">
    <div class="col-md">
        <x-label for="status">{{ __('Status') }}</x-label>
        <x-select name="status" :options="['all' => __('All')] + \App\Enums\IssueStatus::labels()" x-model="params.status" x-on:change="load()" :selected="request()->query('status')" />
    </div>

    <div class="col-md">
        <x-label for="progress">{{ __('Progress') }}</x-label>
        <x-select name="progress" :options="['all' => __('All')] + \App\Enums\IssueProgress::labels()" x-model="params.progress" x-on:change="load()" :selected="request()->query('progress')" />
    </div>

    <div class="col-md">
        <x-label for="system">{{ __('System') }}</x-label>
        <x-select name="system" :options="['all' => __('All')] + \App\Enums\IssueSystem::labels()" x-model="params.system" x-on:change="load()" :selected="request()->query('system')" />
    </div>

    <div class="col-md">
        <x-label for="type">{{ __('Type') }}</x-label>
        <x-select name="type" :options="['all' => __('All')] + \App\Enums\IssueType::labels()" x-model="params.type" x-on:change="load()" :selected="request()->query('type')" />
    </div>

    <div class="col-md">
        <x-label for="priority">{{ __('Priority') }}</x-label>
        <x-select name="priority" :options="['all' => __('All')] + \App\Enums\IssuePriority::labels()" x-model="params.priority" x-on:change="load()" :selected="request()->query('priority')" />
    </div>

    <div class="col-md">
        <x-label for="assignee">{{ __('Assignee') }}</x-label>
        <x-select name="assignee" :options="['all' => __('All'), 'unassigned' => __('Unassigned')] + $assignees->all()" x-model="params.assignee" x-on:change="load()" :selected="request()->query('assignee')" />
    </div>
</div>

<div class="d-flex flex-wrap align-items-center gap-2 mb-2">
    <div class="dropdown">
        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="far fa-bookmark me-1" aria-hidden="true"></span>
            {{ __('Saved filters') }}
        </button>
        <div class="dropdown-menu" style="min-width: 280px">
            <template x-for="filter in savedFilters" x-bind:key="filter.id">
                <div class="d-flex align-items-center gap-2 px-3 py-1">
                    <a href="#" class="flex-grow-1 text-reset text-decoration-none text-truncate" x-on:click.prevent="applyFilter(filter)" x-text="filter.name"></a>
                    <button type="button" class="btn btn-link btn-sm p-0 text-muted" x-on:click="renameFilter(filter)" title="{{ __('Rename') }}" aria-label="{{ __('Rename') }}">
                        <span class="far fa-edit" aria-hidden="true"></span>
                    </button>
                    <button type="button" class="btn btn-link btn-sm p-0 text-danger" x-on:click="removeFilter(filter)" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}">
                        <span class="far fa-trash-alt" aria-hidden="true"></span>
                    </button>
                </div>
            </template>
            <span class="dropdown-item-text small text-muted" x-show="savedFilters.length === 0">{{ __('No saved filters yet.') }}</span>
            <hr class="dropdown-divider" />
            <button type="button" class="dropdown-item" x-on:click="saveFilter()">
                <span class="fas fa-plus me-1" aria-hidden="true"></span>
                {{ __('Save current filters') }}
            </button>
        </div>
    </div>

    <span class="badge text-bg-light border" x-show="params.open == 1" @unless (request()->boolean('open')) x-cloak @endunless>
        {{ __('Open issues only') }}
        <a href="#" class="ms-1 text-reset text-decoration-none" x-on:click.prevent="
            params.open = 0
            load()
        " aria-label="{{ __('Remove') }}">&times;</a>
    </span>
    <span class="badge text-bg-light border" x-show="params.released_this_week == 1" @unless (request()->boolean('released_this_week')) x-cloak @endunless>
        {{ __('Released This Week') }}
        <a href="#" class="ms-1 text-reset text-decoration-none" x-on:click.prevent="
            params.released_this_week = 0
            load()
        " aria-label="{{ __('Remove') }}">&times;</a>
    </span>

    <div class="form-check form-switch ms-auto mb-0">
        <input
            class="form-check-input"
            type="checkbox"
            role="switch"
            id="filter-archived"
            x-bind:checked="params.archived == 1"
            x-on:change="
                params.archived = $event.target.checked ? 1 : 0
                params.page = 1
                load()
            "
            @checked(request()->boolean('archived'))
        />
        <label class="form-check-label small" for="filter-archived">{{ __('Archived issues only') }}</label>
    </div>
</div>

<x-listpage-searchbox class="mb-3" name="search_value" :placeholder="__('Search by title, key or merchant name')" />

@if ($canBulk)
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3 p-2 border rounded" x-show="selected.length > 0" x-init="$watch('data', () => (selected = []))" x-cloak>
        <strong class="me-1" x-text="__(':count selected', { count: selected.length })"></strong>

        <select class="form-select form-select-sm w-auto" x-model="bulk.action" aria-label="{{ __('Action') }}">
            <option value="progress">{{ __('Change progress') }}</option>
            <option value="status">{{ __('Change status') }}</option>
            <option value="priority">{{ __('Change priority') }}</option>
            <option value="assignee">{{ __('Assign to') }}</option>
            <option value="archive">{{ __('Archive') }}</option>
        </select>

        <select class="form-select form-select-sm w-auto" x-model="bulk.progress" x-show="bulk.action === 'progress'" aria-label="{{ __('Progress') }}">
            @foreach (\App\Enums\IssueProgress::labels() as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>

        <select class="form-select form-select-sm w-auto" x-model="bulk.status" x-show="bulk.action === 'status'" aria-label="{{ __('Status') }}">
            @foreach (\App\Enums\IssueStatus::labels() as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>

        <select class="form-select form-select-sm w-auto" x-model="bulk.priority" x-show="bulk.action === 'priority'" aria-label="{{ __('Priority') }}">
            @foreach (\App\Enums\IssuePriority::labels() as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>

        <select class="form-select form-select-sm w-auto" x-model="bulk.assigned_to" x-show="bulk.action === 'assignee'" aria-label="{{ __('Assignee') }}">
            <option value="">{{ __('Unassigned') }}</option>
            @foreach ($assignees as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>

        <button type="button" class="btn btn-primary btn-sm" x-on:click="applyBulk()">{{ __('Apply') }}</button>
        <button type="button" class="btn btn-link btn-sm" x-on:click="selected = []">{{ __('Clear selection') }}</button>
    </div>
@endif

<div>{{ $issues->links() }}</div>

<x-listpage-table>
    <table class="table table-sm table-bordered table-hover">
        <thead>
            <tr>
                @if ($canBulk)
                    <th class="text-center" style="width: 1%">
                        <input type="checkbox" class="form-check-input" aria-label="{{ __('Select all on this page') }}" x-bind:checked="data.data.length > 0 && selected.length === data.data.length" x-on:change="selected = $event.target.checked ? data.data.map((issue) => issue.id) : []" />
                    </th>
                @endif

                <th style="width: 9%"><x-listpage-sorter column="id" :label="__('Key')" /></th>
                <th style="width: 25%"><x-listpage-sorter column="title" :label="__('Title')" /></th>
                <th class="text-center" style="width: 9%">{{ __('System') }}</th>
                <th class="text-center" style="width: 9%">{{ __('Type') }}</th>
                <th class="text-center" style="width: 9%"><x-listpage-sorter column="priority" :label="__('Priority')" /></th>
                <th class="text-center" style="width: 8%"><x-listpage-sorter column="status" :label="__('Status')" /></th>
                <th class="text-center" style="width: 11%"><x-listpage-sorter column="progress" :label="__('Progress')" /></th>
                <th style="width: 12%">{{ __('Assignee') }}</th>
                <th class="text-center" style="width: 10%"><x-listpage-sorter column="created_at" :label="__('Created At')" /></th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(issue, index) in data.data" x-bind:key="issue.id">
                <tr role="button" x-on:click="open(issue)">
                    @if ($canBulk)
                        <td class="text-center" x-on:click.stop>
                            <input type="checkbox" class="form-check-input" x-bind:value="issue.id" x-model.number="selected" x-bind:aria-label="__('Select :key', { key: issue.key })" />
                        </td>
                    @endif

                    <td class="text-nowrap" x-text="issue.key"></td>
                    <td class="text-break" x-text="issue.title"></td>
                    <td class="text-center" x-text="IssueSystem.labels[issue.system] ?? '-'"></td>
                    <td class="text-center" x-text="IssueType.labels[issue.type]"></td>
                    <td class="text-center">
                        <span class="badge" x-bind:class="'text-bg-' + IssuePriority.contexts[issue.priority]" x-text="IssuePriority.labels[issue.priority]"></span>
                    </td>
                    <td class="text-center">
                        <span class="badge" x-bind:class="'text-bg-' + IssueStatus.contexts[issue.status]" x-text="IssueStatus.labels[issue.status]"></span>
                    </td>
                    <td class="text-center">
                        <span class="badge" x-bind:class="'text-bg-' + IssueProgress.contexts[issue.progress]" x-text="IssueProgress.labels[issue.progress]"></span>
                    </td>
                    <td x-text="issue.assignee?.name ?? __('Unassigned')"></td>
                    <td class="text-center text-nowrap" x-html="Helper.formatDate(issue.created_at ?? '', 'Y-m-d H:i')"></td>
                </tr>
            </template>
            <tr x-show="data.data.length === 0" x-cloak>
                <td colspan="{{ $canBulk ? 10 : 9 }}" class="text-center text-muted">{{ __('No issues found.') }}</td>
            </tr>
        </tbody>
    </table>
</x-listpage-table>

<div>{{ $issues->links() }}</div>

@push('js')
    <script type="text/javascript">
        Object.assign(window.PageData, {
            savedFilters: @json(auth()->user()->savedFilters()->orderBy('name')->get(['id', 'name', 'params'])),
            savedFilterRequest: {!! \App\Http\Requests\SavedFilterRequest::extractJson(['method' => 'PATCH']) !!},
        });
    </script>
@endpush

@push('templates')
    <template id="saved_filter_modal">
        <x-label for="saved_filter_name" required>{{ __('Filter name') }}</x-label>
        <x-input type="text" name="name" id="saved_filter_name" x-model="data.name" :placeholder="__('e.g. My urgent OnPay bugs')" maxlength="100" />
        <x-error for="name" />
    </template>
@endpush
