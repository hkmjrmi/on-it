@extends('layouts.user')

@section('title', __('Status Guide'))

@section('content')
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table mb-0">
                    <tbody>
                        @foreach (\App\Enums\IssueStatus::cases() as $status)
                            <tr>
                                <td class="text-nowrap" style="width: 180px">
                                    <span class="badge {{ $status->badgeClass() }}">{{ $status->label() }}</span>
                                </td>
                                <td>{{ $status->description() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
