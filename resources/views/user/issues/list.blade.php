<div class="row g-2 mb-3">
    <div class="col-md-3">
        <x-label for="status">{{ __('Status') }}</x-label>
        <x-select name="status" :options="['all' => __('All')] + \App\Enums\IssueStatus::labels()" x-model="params.status" x-on:change="load()" :selected="request()->query('status')" />
    </div>

    <div class="col-md-3">
        <x-label for="type">{{ __('Type') }}</x-label>
        <x-select name="type" :options="['all' => __('All')] + \App\Enums\IssueType::labels()" x-model="params.type" x-on:change="load()" :selected="request()->query('type')" />
    </div>

    <div class="col-md-3">
        <x-label for="priority">{{ __('Priority') }}</x-label>
        <x-select name="priority" :options="['all' => __('All')] + \App\Enums\IssuePriority::labels()" x-model="params.priority" x-on:change="load()" :selected="request()->query('priority')" />
    </div>

    <div class="col-md-3">
        <x-label for="assignee">{{ __('Assignee') }}</x-label>
        <x-select name="assignee" :options="['all' => __('All'), 'unassigned' => __('Unassigned')] + $assignees->all()" x-model="params.assignee" x-on:change="load()" :selected="request()->query('assignee')" />
    </div>
</div>

<div class="d-flex gap-2 mb-2" x-show="params.open == 1 || params.released_this_week == 1" @unless (request()->boolean('open') || request()->boolean('released_this_week')) x-cloak @endunless>
    <span class="badge text-bg-light border" x-show="params.open == 1">
        {{ __('Open issues only') }}
        <a href="#" class="ms-1 text-reset text-decoration-none" x-on:click.prevent="params.open = 0; load()" aria-label="{{ __('Remove') }}">&times;</a>
    </span>
    <span class="badge text-bg-light border" x-show="params.released_this_week == 1">
        {{ __('Released This Week') }}
        <a href="#" class="ms-1 text-reset text-decoration-none" x-on:click.prevent="params.released_this_week = 0; load()" aria-label="{{ __('Remove') }}">&times;</a>
    </span>
</div>

<x-listpage-searchbox class="mb-3" name="search_value" :placeholder="__('Search by title, key or merchant name')" />

<div>{{ $issues->links() }}</div>

<x-listpage-table>
    <table class="table table-sm table-bordered table-hover">
        <thead>
            <tr>
                <th style="width: 10%"><x-listpage-sorter column="id" :label="__('Key')" /></th>
                <th style="width: 35%"><x-listpage-sorter column="title" :label="__('Title')" /></th>
                <th class="text-center" style="width: 10%">{{ __('Type') }}</th>
                <th class="text-center" style="width: 10%"><x-listpage-sorter column="priority" :label="__('Priority')" /></th>
                <th class="text-center" style="width: 12%"><x-listpage-sorter column="status" :label="__('Status')" /></th>
                <th style="width: 13%">{{ __('Assignee') }}</th>
                <th class="text-center" style="width: 10%"><x-listpage-sorter column="created_at" :label="__('Created At')" /></th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(issue, index) in data.data" x-bind:key="issue.id">
                <tr role="button" x-on:click="open(issue)">
                    <td class="text-nowrap" x-text="issue.key"></td>
                    <td class="text-break" x-text="issue.title"></td>
                    <td class="text-center" x-text="IssueType.labels[issue.type]"></td>
                    <td class="text-center">
                        <span class="badge" x-bind:class="'text-bg-' + IssuePriority.contexts[issue.priority]" x-text="IssuePriority.labels[issue.priority]"></span>
                    </td>
                    <td class="text-center">
                        <span class="badge" x-bind:class="'text-bg-' + IssueStatus.contexts[issue.status]" x-text="IssueStatus.labels[issue.status]"></span>
                    </td>
                    <td x-text="issue.assignee?.name ?? __('Unassigned')"></td>
                    <td class="text-center text-nowrap" x-html="Helper.formatDate(issue.created_at ?? '', 'Y-m-d H:i')"></td>
                </tr>
            </template>
            <tr x-show="data.data.length === 0" x-cloak>
                <td colspan="7" class="text-center text-muted">{{ __('No issues found.') }}</td>
            </tr>
        </tbody>
    </table>
</x-listpage-table>

<div>{{ $issues->links() }}</div>
