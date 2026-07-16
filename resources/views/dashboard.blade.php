<x-layouts::app :title="__('Dashboard')">
    <div style="font-family: var(--font-body); color: var(--c-fg);" class="p-6">
        <h1 style="font-size: 2rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--c-fg);">
            Dashboard
        </h1>
        <p style="color: var(--c-muted); font-size: 0.875rem;">
            Welcome back, {{ auth()->user()->name }}.
        </p>
    </div>
</x-layouts::app>
