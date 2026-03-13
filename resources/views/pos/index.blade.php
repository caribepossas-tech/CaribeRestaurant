@extends('layouts.app')

@section('content')

@livewire('pos.pos')

<!-- Product Drawer -->
<x-right-drawer :title='__("modules.table.addTable")' id="drawer-add-table">
    @livewire('forms.addTable')
</x-right-drawer>

<button data-drawer-target="drawer-add-table" aria-controls="drawer-add-table" type="button" class="hidden" id="drawer-add-table-trigger"></button>

<script>
    function toggleAddTableDrawer(show = true) {
        const drawer = FlowbiteInstances.getInstance('drawer', 'drawer-add-table');
        if (drawer) {
            show ? drawer.show() : drawer.hide();
        }
    }

    document.addEventListener('livewire:navigated', () => {
        Livewire.on('hideAddTable', () => toggleAddTableDrawer(false));
        Livewire.on('tableAdded', () => toggleAddTableDrawer(false));
    });
</script>


    
@endsection