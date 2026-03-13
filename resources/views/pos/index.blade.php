@extends('layouts.app')

@section('content')

@livewire('pos.pos')

<!-- Product Drawer -->
<x-right-drawer :title='__("modules.table.addTable")' id="drawer-add-table">
    @livewire('forms.addTable')
</x-right-drawer>

    
@endsection