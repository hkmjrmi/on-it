<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\IssueAttachment;
use Illuminate\Support\Facades\Gate;

class IssueAttachmentController extends Controller
{
    /**
     * Stream the specified attachment.
     */
    public function __invoke(IssueAttachment $attachment)
    {
        abort_unless($attachment->issue, 404);

        Gate::authorize('view', $attachment->issue);

        return storage($attachment->disk)->response($attachment->path, $attachment->original_name, [
            'Content-Type' => $attachment->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
