<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    <title>ToDo App</title>
</head>

<body>
    <div class="container mt-5">
        <h1>ToDo List</h1>
        <form id="addTaskForm" class="my-3" action="{{ route('tasks.store') }}" method="POST">
            @csrf
            <div class="input-group">
                <input type="text" name="title" id="taskInput" class="form-control" placeholder="Add new task" required>
                <button type="submit" class="btn btn-primary">Add Task</button>
            </div>
        </form>

        <form class="my-3" action="{{ route('tasks.index') }}" method="GET">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search tasks..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>

        <table class="table table-striped mt-3" id="tasksTable">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Task</th>
                    <th scope="col">Completed</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tasks as $task)
                    <tr data-id="{{ $task['id'] }}" class="task-row">
                        <th scope="row">{{ $task['id'] }}</th>
                        <td class="task-title">{{ $task['title'] }}</td>
                        <td>
                            @if ($task['completed'])
                                <span class="badge bg-success">Completed</span>
                            @else
                                <span class="badge bg-danger">Not Completed</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('tasks.toggle', $task['id']) }}" method="POST" style="display: inline;" class="toggle-form">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $task['completed'] ? 'btn-secondary' : 'btn-success' }}">
                                    {{ $task['completed'] ? 'Undo' : 'Complete' }}
                                </button>
                            </form>
                            <button type="button" class="btn btn-warning btn-sm edit-btn" data-id="{{ $task['id'] }}">Edit</button>
                            <form action="{{ route('tasks.destroy', $task['id']) }}" method="POST" style="display: inline;" class="delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const taskTable = document.getElementById('tasksTable');

            // Delete
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const row = form.closest('.task-row');
                    row.classList.add('fade-out');

                    row.addEventListener('animationend', function() {
                        form.submit();
                    });
                });
            });

            // Create
            const addTaskForm = document.getElementById('addTaskForm');
            addTaskForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const taskInput = document.getElementById('taskInput');
                if (taskInput.value.trim() !== '') {
                    const newRow = document.createElement('tr');
                    newRow.className = 'task-row fade-in';
                    newRow.innerHTML = `
                        <th scope="row">#</th>
                        <td>${taskInput.value}</td>
                        <td><span class="badge bg-danger">Not Completed</span></td>
                        <td>
                            <form style="display: inline;" class="toggle-form">
                                <button type="button" class="btn btn-sm btn-success">Complete</button>
                            </form>
                            <form style="display: inline;" class="delete-form">
                                <button type="button" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    `;
                    taskTable.querySelector('tbody').appendChild(newRow);
                    addTaskForm.submit();
                }
            });

            // Edit
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('.task-row');
                    const taskId = this.getAttribute('data-id');
                    const taskTitleCell = row.querySelector('.task-title');

                    const currentTitle = taskTitleCell.textContent.trim();
                    taskTitleCell.innerHTML = `
                        <div class="d-flex align-items-center">
                            <input type="text" value="${currentTitle}" class="form-control edit-input me-2">
                            <button class="btn btn-success btn-sm save-btn">Save</button>
                            <button class="btn btn-secondary btn-sm cancel-btn">Cancel</button>
                        </div>
                    `;

                    this.style.display = 'none';

                    const saveButton = taskTitleCell.querySelector('.save-btn');
                    const cancelButton = taskTitleCell.querySelector('.cancel-btn');
                    const inputField = taskTitleCell.querySelector('.edit-input');

                    // Update
                    saveButton.addEventListener('click', function() {
                        const updatedTitle = inputField.value.trim();
                        if (updatedTitle) {
                            fetch(`/tasks/${taskId}`, {
                                    method: 'PUT',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    },
                                    body: JSON.stringify({
                                        title: updatedTitle
                                    }),
                                })
                                .then(response => {
                                    if (response.ok) {
                                        return response.json();
                                    } else {
                                        throw new Error('Failed to update the task.');
                                    }
                                })
                                .then(data => {
                                    taskTitleCell.textContent = updatedTitle;
                                    button.style.display = 'inline-block';
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('Error updating task. Please try again.');
                                });
                        } else {
                            alert('Title cannot be empty.');
                        }
                    });

                    // Cancel
                    cancelButton.addEventListener('click', function() {
                        taskTitleCell.textContent = currentTitle;
                        button.style.display = 'inline-block';
                    });
                });
            });
        });
    </script>
</body>

</html>
