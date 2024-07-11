<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Aws\S3\S3Client;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::all();
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        Log::info('Store method called');

        try {
            $request->validate([
                'name' => 'required',
                'position' => 'required',
                'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $employee = new Employee;
            $employee->name = $request->name;
            $employee->position = $request->position;

            if ($request->hasFile('image')) {
                try {
                    Log::info('Attempting to upload file to S3');
                    $file = $request->file('image');
                    $path = $file->store('employee_images', 's3');

                    if ($path === false) {
                        throw new \Exception('S3 store method returned false');
                    }

                    $employee->image = $path;

                    // Generate and store S3 URL
                    $s3Config = Config::get('filesystems.disks.s3');
                    $s3Url = "https://{$s3Config['bucket']}.s3.{$s3Config['region']}.amazonaws.com/{$path}";
                    $employee->image_url = $s3Url;

                    Log::info('File uploaded successfully to S3', ['path' => $path, 'url' => $s3Url]);
                } catch (\Exception $e) {
                    Log::error('S3 upload failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return redirect()->route('employees.index')->with('error', 'Failed to upload image, but employee created.');
                }
            }

            try {
                $employee->save();
                Log::info('Employee saved successfully', $employee->toArray());
            } catch (\Exception $e) {
                Log::error('Failed to save employee to database', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->route('employees.index')->with('error', 'Failed to save employee to database.');
            }

            return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
        } catch (\Exception $e) {
            Log::error('Error in store method: ' . $e->getMessage());
            return redirect()->route('employees.index')->with('error', 'An error occurred while creating the employee.');
        }
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => 'required',
            'position' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $employee->name = $request->name;
        $employee->position = $request->position;

        if ($request->hasFile('image')) {
            try {
                // Delete old image from S3
                if ($employee->image) {
                    Storage::disk('s3')->delete($employee->image);
                }

                // Upload new image
                $path = $request->file('image')->store('employee_images', 's3');
                $employee->image = $path;

                // Generate and store new S3 URL
                $s3Config = Config::get('filesystems.disks.s3');
                $s3Url = "https://{$s3Config['bucket']}.s3.{$s3Config['region']}.amazonaws.com/{$path}";
                $employee->image_url = $s3Url;

                Log::info('File updated successfully in S3', ['path' => $path, 'url' => $s3Url]);
            } catch (\Exception $e) {
                Log::error('S3 update failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->back()->with('error', 'Failed to update image. Please try again.');
            }
        }

        $employee->save();

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->image) {
            try {
                Storage::disk('s3')->delete($employee->image);
                Log::info('S3 object deleted', ['path' => $employee->image]);
            } catch (\Exception $e) {
                Log::error('Failed to delete S3 object', ['error' => $e->getMessage()]);
            }
        }
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }
}
