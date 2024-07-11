<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
            Mail::to('your-email@example.com')->send(new FeedbackReceived($request->all()));
            return redirect()->route('employees.index')->with('success', 'Feedback sent successfully!');
        } catch (\Exception $e) {
            return redirect()->route('employees.index')->with('error', 'Failed to send feedback. Please try again later.');
        }
    }
}
