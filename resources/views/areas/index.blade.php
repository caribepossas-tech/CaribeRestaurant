@extends('layouts.app')

@section('content')

@livewire('area.areas')

<!-- Product Drawer -->
<x-right-drawer :title='__("modules.table.addArea")' id="drawer-add-area">
    @livewire('forms.addArea')
</x-right-drawer>

    
@endsection