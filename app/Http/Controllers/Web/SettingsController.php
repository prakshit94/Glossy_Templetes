<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    public function index()
    {
        $this->authorize('settings.view');
        return view('settings.index');
    }

    public function update(Request $request)
    {
        $this->authorize('settings.edit');
        
        // Mock update logic for system settings
        return back()->with('success', 'System settings updated successfully.');
    }

    public function clearCache()
    {
        $this->authorize('settings.edit');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        
        return back()->with('success', 'System cache cleared.');
    }
}
