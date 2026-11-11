<div class="onit-filters row g-2 mb-3">
    <div class="col-12 col-lg-4">
        <x-listpage-searchbox name="search_value" :placeholder="__('Search by title, key or merchant name')" />
    </div>

    <div class="col-6 col-md-4 col-lg">
        <x-select name="status" :aria-label="__('Status')" :options="['all' => __('All statuses')] + \App\Enums\IssueStatus::labels()" x-model="params.status" x-on:change="params.page = 1; load()" :selected="request()->query('status')" />
    </div>

    <div class="col-6 col-md-4 col-lg">
        <x-select name="system" :aria-label="__('System')" :options="['all' => __('All systems')] + \App\Enums\IssueSystem::labels()" x-model="params.system" x-on:change="params.page = 1; load()" :selected="request()->query('system')" />
    </div>

    <div class="col-6 col-md-4 col-lg">
        <x-select name="type" :aria-label="__('Type')" :options="['all' => __('All types')] + \App\Enums\IssueType::labels()" x-model="params.type" x-on:change="params.page = 1; load()" :selected="request()->query('type')" />
    </div>

    <div class="col-6 col-md-4 col-lg">
        <x-select name="priority" :aria-label="__('Priority')" :options="['all' => __('All priorities')] + \App\Enums\IssuePriority::labels()" x-model="params.priority" x-on:change="params.page = 1; load()" :selected="request()->query('priority')" />
    </div>

    <div class="col-6 col-md-4 col-lg">
        <x-select name="assignee" :aria-label="__('Assignee')" :options="['all' => __('All assignees'), 'unassigned' => __('Unassigned')] + $assignees->all()" x-model="params.assignee" x-on:change="params.page = 1; load()" :selected="request()->query('assignee')" />
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-3" x-show="params.open == 1 || params.released_this_week == 1" @unless (request()->boolean('open') || request()->boolean('released_this_week')) x-cloak @endunless>
    <span class="onit-filter-chip" x-show="params.open == 1">
        {{ __('Open issues only') }}
        <a href="#" x-on:click.prevent="params.open = 0; load()" aria-label="{{ __('Remove') }}">&times;</a>
    </span>
    <span class="onit-filter-chip" x-show="params.released_this_week == 1">
        {{ __('Released This Week') }}
        <a href="#" x-on:click.prevent="params.released_this_week = 0; load()" aria-label="{{ __('Remove') }}">&times;</a>
    </span>
</div>

<x-listpage-table>
    <table class="table table-hover align-middle onit-table">
        <thead>
            <tr>
                <th style="width: 9%"><x-listpage-sorter column="id" :label="__('Key')" /></th>
                <th style="width: 29%"><x-listpage-sorter column="title" :label="__('Title')" /></th>
                <th style="width: 9%">{{ __('System') }}</th>
                <th style="width: 9%">{{ __('Type') }}</th>
                <th style="width: 10%"><x-listpage-sorter column="priority" :label="__('Priority')" /></th>
                <th style="width: 12%"><x-listpage-sorter column="status" :label="__('Status')" /></th>
                <th style="width: 12%">{{ __('Assignee') }}</th>
                <th style="width: 10%"><x-listpage-sorter column="created_at" :label="__('Created')" /></th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(issue, index) in data.data" x-bind:key="issue.id">
                <tr role="button" x-on:click="open(issue)">
                    <td class="issue-key" x-text="issue.key"></td>
                    <td class="text-break"><a class="issue-title" x-bind:href="zroute('user.issues.show', issue.key)" x-on:click.stop x-text="issue.title"></a></td>
                    <td x-text="IssueSystem.labels[issue.system] ?? '-'"></td>
                    <td class="text-muted" x-text="IssueType.labels[issue.type]"></td>
                    <td>
                        <span class="badge" x-bind:class="'badge-priority-' + issue.priority" x-text="IssuePriority.labels[issue.priority]"></span>
                    </td>
                    <td>
                        <span class="badge text-nowrap" x-bind:class="'badge-status-' + issue.status" x-text="IssueStatus.labels[issue.status]"></span>
                    </td>
                    <td>
                        <span x-show="issue.assignee" x-text="issue.assignee?.name"></span>
                        <span class="text-muted fst-italic" x-show="!issue.assignee">{{ __('Unassigned') }}</span>
                    </td>
                    <td class="text-nowrap text-muted" x-html="Helper.formatDate(issue.created_at ?? '', 'j M Y')"></td>
                </tr>
            </template>
            <tr x-show="data.data.length === 0" x-cloak>
                <td colspan="8" class="text-center text-muted py-5">{{ __('No issues found.') }}</td>
            </tr>
        </tbody>
    </table>
</x-listpage-table>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 text-muted small">
    <span x-text="trans_choice(@js(__('app.issue_count')), data.meta.total, { count: data.meta.total.toLocaleString() })">{{ trans_choice(__('app.issue_count'), $issues->total(), ['count' => number_format($issues->total())]) }}</span>
    <div>{{ $issues->links() }}</div>
</div>
