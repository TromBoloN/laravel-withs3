<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\FeedbackReceived;

class FeedbackController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'feedback' => 'required',
        ]);

        try {
            Mail::to(env('FEEDBACK_RECIPIENT_EMAIL'))->send(new FeedbackReceived($request->all()));
            return redirect()->route('employees.index')->with('success', 'Feedback sent successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to send feedback email: ' . $e->getMessage());
            return redirect()->route('employees.index')->with('error', 'Failed to send feedback. Please try again later.');
        }
    }
}
