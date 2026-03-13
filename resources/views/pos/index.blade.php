@extends('layouts.app')

@section('content')

@livewire('pos.pos')

<!-- Product Drawer -->
<x-right-drawer :title='__("modules.table.addTable")' id="drawer-add-table">
    @livewire('forms.addTable')
</x-right-drawer>

<button data-drawer-target="drawer-add-table" data-drawer-show="drawer-add-table" aria-controls="drawer-add-table" type="button" class="hidden" id="drawer-add-table-trigger"></button>

<script>
    document.addEventListener('livewire:navigated', () => {
        Livewire.on('hideAddTable', () => {
            const drawer = FlowbiteInstances.getInstance('drawer', 'drawer-add-table');
            if (drawer) {
                drawer.hide();
            }
        });

        Livewire.on('tableAdded', () => {
             const drawer = FlowbiteInstances.getInstance('drawer', 'drawer-add-table');
            if (drawer) {
                drawer.hide();
            }
        });
    });
</script>


    
@endsection