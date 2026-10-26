@props([
    'viewUrl' => null,
    'editUrl' => null,
    'deleteAction' => null,
    'deleteConfirm' => 'Apakah Anda yakin ingin menghapus item ini? Tindakan ini tidak dapat dibatalkan.',
    'canEdit' => true,
    'canDelete' => false,
    'id' => null,
])

<div class="d-inline-flex flex-wrap align-items-center justify-content-center gap-1">
    {{ $slot }}

    @if($viewUrl)
        <a href="{{ $viewUrl }}" class="btn btn-sm btn-light btn-action-icon" title="Lihat Rincian" data-bs-toggle="tooltip">
            <i class="fa-regular fa-eye text-primary"></i>
        </a>
    @endif

    @if($canEdit && $editUrl)
        <a href="{{ $editUrl }}" class="btn btn-sm btn-outline-primary" title="Edit Data">
            <i class="fa-regular fa-pen-to-square"></i>
            <span>Edit</span>
        </a>
    @endif

    @if($canDelete && $deleteAction)
        <form action="{{ $deleteAction }}" method="POST" class="d-inline m-0 p-0" onsubmit="return confirm('{{ $deleteConfirm }}');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-light btn-action-icon text-danger" title="Hapus Data" data-bs-toggle="tooltip">
                <i class="fa-regular fa-trash-can"></i>
            </button>
        </form>
    @endif
</div>
