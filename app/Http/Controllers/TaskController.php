<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TaskController extends Controller
{
    private $tasksFile;

    public function __construct()
    {
        $this->tasksFile = storage_path('app/tasks.json'); // Using local storage
    }

    public function index(Request $request)
    {
        $tasks = json_decode(File::get($this->tasksFile), true);

        // Apply search filter
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $tasks = array_filter($tasks, function ($task) use ($searchTerm) {
                return strpos(strtolower($task['title']), strtolower($searchTerm)) !== false;
            });
        }

        return view('tasks.index', compact('tasks'));
    }

    public function store(Request $request)
    {
        $request->validate(['title' => 'required']);

        $tasks = json_decode(File::get($this->tasksFile), true);
        $tasks[] = ['id' => count($tasks) + 1, 'title' => $request->title, 'completed' => false];

        File::put($this->tasksFile, json_encode($tasks));

        return redirect()->route('tasks.index');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['title' => 'required']);
        $tasks = json_decode(File::get($this->tasksFile), true);

        foreach ($tasks as &$task) {
            if ($task['id'] == $id) {
                $task['title'] = $request->title;
            }
        }

        File::put($this->tasksFile, json_encode($tasks));

        return response()->json(['message' => 'Task updated successfully']);
    }

    public function destroy($id)
    {
        $tasks = json_decode(File::get($this->tasksFile), true);

        $tasks = array_filter($tasks, fn($task) => $task['id'] != $id);
        $tasks = array_values($tasks);

        foreach ($tasks as $index => &$task) {
            $task['id'] = $index + 1;
        }

        File::put($this->tasksFile, json_encode($tasks));

        return redirect()->route('tasks.index');
    }

    public function toggle($id)
    {
        $tasks = json_decode(File::get($this->tasksFile), true);

        foreach ($tasks as &$task) {
            if ($task['id'] == $id) {
                $task['completed'] = !$task['completed'];
            }
        }

        File::put($this->tasksFile, json_encode($tasks));

        return redirect()->route('tasks.index');
    }
}
