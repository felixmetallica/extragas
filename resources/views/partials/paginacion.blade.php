@if ($items->hasPages())
    <div class="tbl-footer">{{ $items->onEachSide(1)->links() }}</div>
@elseif ($items->total())
    <div class="tbl-footer"><small class="text-body-secondary">{{ $items->total() }} {{ $items->total() === 1 ? 'registro' : 'registros' }}</small></div>
@endif
