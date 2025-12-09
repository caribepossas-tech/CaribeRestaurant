<div class="modal-body">
    <form id="verify-form" class="space-y-4">
        @csrf
        <p class="bg-gray-600 p-2 rounded text-white">Para el dominio:- {{ request()->getHost() }}</p>

        <div class="text-sm">Modulo: <b>{{ucwords($module)}}</b></div>

        <p>
            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">Codigo de Compra: 888541578547</span>
            <a href="https://kosari.net"
               class="font-medium text-sm hover:underline text-skin-base underline underline-offset-1" target="_blank">Visita kosari.net</a>
        </p>
        <div id="response-message"></div>

        <div class="w-full form-group">
            <x-label>Introduzca su código de compra</x-label>
            <x-input type="text" id="purchase_code" name="purchase_code" class="w-full mt-1 form-control"  placeholder="888541578547" />
        </div>
        <input type="hidden" id="module" name="module" value="{{ $module }}">

        <x-button id="save-module-verify" icon="check">Verificar</x-button>
    </form>
</div>


<script>
    $('#save-module-verify').click(function () {

        const url = "{{ route('superadmin.custom-modules.verify_purchase') }}";
        $.easyAjax({
            url: url,
            container: '#verify-form',
            type: "POST",
            messagePosition: 'inline',
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-module-verify",
            data: $('#verify-form').serialize(),
            success: function (response) {
                if (response.status === 'success') {
                    window.location.reload();
                }
            }
        })
    });

</script>
