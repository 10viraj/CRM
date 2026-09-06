<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function index()
    {
        $attachments = Attachment::with(['user', 'attachable'])->latest()->paginate(15);
        return view('attachments.index', compact('attachments'));
    }

    public function download(Attachment $attachment)
    {
        // In a real app, this would use Storage::download($attachment->file_path)
        // For our demo, we just return a back response with a toast message
        return back()->with('success', 'Download started for ' . $attachment->file_name);
    }
    
    public function destroy(Attachment $attachment)
    {
        // In a real app, delete from storage
        $attachment->delete();
        return back()->with('success', 'Attachment deleted successfully.');
    }
}
