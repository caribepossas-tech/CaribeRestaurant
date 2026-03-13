@extends('layouts.app')

@section('content')

@livewire('pos.pos')

<!-- Product Drawer -->
<x-right-drawer :title='__("modules.table.addTable")' id="drawer-add-table">
    @livewire('forms.addTable')
</x-right-drawer>

<button type="button" class="hidden" id="drawer-add-table-trigger"></button>

<script>
    function toggleAddTableDrawer(show = true) {
        const drawer = FlowbiteInstances.getInstance('drawer', 'drawer-add-table');
        if (drawer) {
            show ? drawer.show() : drawer.hide();
        }
    }

    document.addEventListener('livewire:navigated', () => {
        // Manually initialize the drawer to have full control and ensure it's hidden
        const $targetEl = document.getElementById('drawer-add-table');
        if ($targetEl && typeof Drawer !== 'undefined') {
            const options = {
                placement: 'right',
                backdrop: true,
                bodyScrolling: false,
                edge: false,
                edgeOffset: '',
                backdropClasses: 'bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-30',
            };
            const instance = new Drawer($targetEl, options);
            instance.hide(); // Force hide on init
        }

        Livewire.on('hideAddTable', () => toggleAddTableDrawer(false));
        Livewire.on('tableAdded', () => toggleAddTableDrawer(false));
    });
</script>


    
@endsection