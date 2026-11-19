@extends('layouts.user')

@section('title', __('Status Guide'))

@section('content')
    <div class="col-md-12">
        <p class="text-muted">{{ __('Every issue has a status and a progress. The status says whether the issue is still being handled. The progress says where the work is.') }}</p>

        @foreach ([__('Status') => \App\Enums\IssueStatus::cases(), __('Progress') => \App\Enums\IssueProgress::cases()] as $heading => $cases)
            <div class="card shadow-sm mb-4">
                <div class="card-header">{{ $heading }}</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <tbody>
                            @foreach ($cases as $case)
                                <tr>
                                    <td class="text-nowrap" style="width: 180px">
                                        <span class="badge {{ $case->badgeClass() }}">{{ $case->label() }}</span>
                                    </td>
                                    <td>{{ $case->description() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@endsection
