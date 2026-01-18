{{--
    Reusable pagination component with built-in safety guards
    
    Usage:
    @include('panels.partials.pagination', ['items' => $customers])
    
    This component handles:
    - Non-paginated collections (arrays/collections without pagination methods)
    - Empty datasets
    - Proper pagination display
--}}

@php
    // Safety check: ensure $items exists and is an object with hasPages method
    $canPaginate = isset($items) && is_object($items) && method_exists($items, 'hasPages');
@endphp

@if($canPaginate && $items->hasPages())
    <div class="mt-4">
        {{ $items->links() }}
    </div>
@endif
