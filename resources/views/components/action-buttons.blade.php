@props([
    'viewUrl' => null,
    'editUrl' => null,
    'deleteAction' => null,
    'deleteConfirm' => 'Apakah Anda yakin ingin menghapus item ini? Tindakan ini tidak dapat dibatalkan.',
    'canEdit' => true,
    'canDelete' => false,
    'id' => null,
])

<div class="d-inline-flex align-items-center gap-1">
    {{ $slot }}

    @if($viewUrl)
        <a href="{{ $viewUrl }}" class="btn btn-sm btn-light btn-action-icon" title="Lihat Rincian" data-bs-toggle="tooltip">
            <i class="fa-regular fa-eye text-primary"></i>
        </a>
    @endif

    @if($canEdit && $editUrl)
        <a href="{{ $editUrl }}" class="btn btn-sm btn-light btn-action-icon" title="Edit Data" data-bs-toggle="tooltip">
            <i class="fa-regular fa-pen-to-square text-secondary"></i>
        </a>
    @endif

    @if($canDelete && $deleteAction)
        <form action="{{ $deleteAction }}" method="POST" class="d-inline" onsubmit="return confirm('{{ $deleteConfirm }}');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-light btn-action-icon text-danger" title="Hapus Data" data-bs-toggle="tooltip">
                <i class="fa-regular fa-trash-can"></i>
            </button>
        </form>
    @endif
</div>
