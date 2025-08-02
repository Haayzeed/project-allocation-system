<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Config;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConfigController extends Controller
{
    public function index(): Response
    {
        $configs = Config::orderBy('key')->get();

        return Inertia::render('admin/configs/Index', [
            'configs' => $configs,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:configs',
            'value' => 'required|string',
            'description' => 'nullable|string|max:500',
            'type' => 'required|string|in:string,integer,boolean,decimal',
        ]);

        Config::create($validated);

        return redirect()->route('admin.configs.index')
            ->with('success', 'Configuration created successfully.');
    }

    public function update(Request $request, Config $config)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:configs,key,' . $config->id,
            'value' => 'required|string',
            'description' => 'nullable|string|max:500',
            'type' => 'required|string|in:string,integer,boolean,decimal',
        ]);

        $config->update($validated);

        return redirect()->route('admin.configs.index')
            ->with('success', 'Configuration updated successfully.');
    }

    public function destroy(Config $config)
    {
        $config->delete();

        return redirect()->route('admin.configs.index')
            ->with('success', 'Configuration deleted successfully.');
    }
}
