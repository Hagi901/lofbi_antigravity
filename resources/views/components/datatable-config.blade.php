@props([
    'selector' => '.table-datatable',
    'order' => '[[0, "asc"]]',
    'columnDefs' => '[]',
    'pageLength' => 10,
])

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof $ !== 'undefined' && $.fn && $.fn.DataTable) {
            $('{{ $selector }}').DataTable({
                language: window.DataTablesIndonesian || {},
                order: {!! $order !!},
                columnDefs: {!! $columnDefs !!},
                pageLength: {{ $pageLength }},
                responsive: true
            });
        }
    });
</script>
