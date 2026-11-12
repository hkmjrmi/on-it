@extends('layouts.user')

@section('title', __('Create Issue'))

@section('content')
    <div class="onit-page-header">
        <div>
            <h1>@yield('title')</h1>
            <p>{{ __('Log a customer complaint, suggestion or bug') }}</p>
        </div>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-lg-8 col-xl-9" style="max-width: 820px">
            <div class="card">
                <div class="card-body">
                    <x-form x-data="Form()" is-post has-files :action="route('user.issues.store')" :bind-data="$issue">
                        <div class="mb-3">
                            <x-label for="title" required>{{ __('Title') }}</x-label>
                            <x-input type="text" name="title" placeholder="{{ __('Short summary of the problem') }}" autofill />
                            <x-error for="title" />
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <x-label for="system" required>{{ __('System') }}</x-label>
                                <x-select2 name="system" :options="\App\Enums\IssueSystem::labels()" :placeholder="__('Select system...')" autofill />
                                <x-error for="system" />
                            </div>

                            <div class="col-md-4">
                                <x-label for="type" required>{{ __('Type') }}</x-label>
                                <x-select2 name="type" :options="\App\Enums\IssueType::labels()" :placeholder="__('Select type...')" autofill />
                                <x-error for="type" />
                            </div>

                            <div class="col-md-4">
                                <x-label for="priority" required>{{ __('Priority') }}</x-label>
                                <x-select2 name="priority" :options="\App\Enums\IssuePriority::labels()" autofill />
                                <x-help>{{ __('Urgent = merchant cannot operate (e.g. cannot take payments)') }}</x-help>
                                <x-error for="priority" />
                            </div>
                        </div>

                        <div class="mb-3">
                            <x-label for="merchant_name">{{ __('Merchant / Customer') }}</x-label>
                            <x-input type="text" name="merchant_name" placeholder="{{ __('Enter merchant name...') }}" autofill />
                            <x-help>{{ __('Leave empty for internal issues') }}</x-help>
                            <x-error for="merchant_name" />
                        </div>

                        <div class="mb-3">
                            <x-label for="description" required>{{ __('Description') }}</x-label>
                            <x-textarea name="description" rows="6" placeholder="{{ __('What happened? What should have happened? Steps to reproduce if known.') }}" autosize autofill />
                            <x-error for="description" />
                        </div>

                        <div class="mb-4">
                            <x-label for="attachments">{{ __('Attachments') }}</x-label>
                            <div class="upload-box">
                                <span class="fas fa-cloud-arrow-up fa-lg mb-2 d-block" aria-hidden="true"></span>
                                <x-input type="file" name="attachments[]" id="attachments" accept="image/*,application/pdf" multiple />
                                <x-help>{{ __('Images or PDF, up to 5 files, 5 MB each.') }}</x-help>
                            </div>
                            <x-error for="attachments" />
                            <x-error for="attachments.*" />
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a class="btn btn-outline" href="{{ route('user.main') }}">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Create Issue') }}</button>
                        </div>
                    </x-form>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-xl-3">
            <aside class="card opie-tip">
                <div class="card-body">
                    <img src="{{ asset('images/opie/waving.webp') }}" alt="" />
                    <h4>{{ __("Opie's tips for a faster fix") }}</h4>
                    <ul>
                        <li>{{ __('Add a screenshot of the problem') }}</li>
                        <li>{{ __('Include the order or transaction number') }}</li>
                        <li>{{ __('Say what the merchant expected to happen') }}</li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
@endsection

@push('js')
    <script type="text/javascript">
        Alpine.data('Form', () =>
            window.AlpineComponents.Form({
                formRequest: {!! \App\Http\Requests\IssueRequest::extractJson() !!},
            })
        );
    </script>
@endpush
