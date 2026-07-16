<x-layouts::app :title="__('Dashboard')">
    <div class="manage-page p-6">
        <h1 class="manage-title mb-1">
            Dashboard
        </h1>
        <p class="manage-subtitle">
            Welcome back, {{ auth()->user()->name }}.
        </p>
    </div>
</x-layouts::app>
