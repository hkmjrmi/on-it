@extends('layouts.user')

@section('title', __('New Issue'))

@section('content')
    <div class="col-md-12">
        <x-form x-data="Form()" has-files :action="route('user.issues.store')" :bind-data="$issue">
            <div class="row mb-3">
                <x-label for="title" class="col-md-2" required>{{ __('Title') }}</x-label>

                <div class="col-md-6">
                    <x-input type="text" name="title" placeholder="{{ __('Summarise the issue in one line...') }}" autofill />

                    <x-error for="title" />
                </div>
            </div>

            <div class="row mb-3">
                <x-label for="type" class="col-md-2" required>{{ __('Type') }}</x-label>

                <div class="col-md-6">
                    <x-select2 name="type" :options="\App\Enums\IssueType::labels()" :placeholder="__('Select type...')" autofill />

                    <x-error for="type" />
                </div>
            </div>

            <div class="row mb-3">
                <x-label for="priority" class="col-md-2" required>{{ __('Priority') }}</x-label>

                <div class="col-md-6">
                    <x-select2 name="priority" :options="\App\Enums\IssuePriority::labels()" autofill />

                    <x-help>{{ __('Urgent = merchant cannot operate') }}</x-help>

                    <x-error for="priority" />
                </div>
            </div>

            <div class="row mb-3">
                <x-label for="merchant_name" class="col-md-2">{{ __('Merchant Name') }}</x-label>

                <div class="col-md-6">
                    <x-input type="text" name="merchant_name" placeholder="{{ __('Enter merchant name...') }}" autofill />

                    <x-help>{{ __('Leave blank if the issue is not related to a merchant.') }}</x-help>

                    <x-error for="merchant_name" />
                </div>
            </div>

            <div class="row mb-3">
                <x-label for="description" class="col-md-2" required>{{ __('Description') }}</x-label>

                <div class="col-md-6">
                    <x-textarea name="description" rows="6" placeholder="{{ __('Describe what happened, steps to reproduce and what is expected...') }}" autosize autofill />

                    <x-error for="description" />
                </div>
            </div>

            <div class="row mb-3">
                <x-label for="attachments" class="col-md-2">{{ __('Attachments') }}</x-label>

                <div class="col-md-6">
                    <x-input type="file" name="attachments[]" id="attachments" accept="image/*,application/pdf" multiple />

                    <x-help>{{ __('Images or PDF, up to 5 files, 5 MB each.') }}</x-help>

                    <x-error for="attachments" />
                    <x-error for="attachments.*" />
                </div>
            </div>

            <hr />

            <div class="row mb-3">
                <div class="col-md-6 offset-md-2">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Submit') }}
                    </button>
                </div>
            </div>
        </x-form>
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
