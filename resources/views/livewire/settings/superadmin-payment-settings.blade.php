<div>
    <div
        class="mx-4 p-4 mb-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
        <h3 class="mb-4 text-xl font-semibold dark:text-white">@lang('modules.settings.paymentgatewaySettings')</h3>
        <x-help-text class="mb-6">@lang('modules.settings.paymentHelpSuperadmin')</x-help-text>

        <div class="text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:text-gray-400 dark:border-gray-700">
            <ul class="flex flex-wrap -mb-px items-center">
                {{-- Razorpay Tab --}}
                <li class="me-2">
                    <span wire:click="activeSetting('razorpay')" @class(["inline-flex items-center gap-x-1 cursor-pointer select-none p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300", 'border-transparent' => ($activePaymentSetting != 'razorpay'), 'active border-skin-base dark:text-skin-base dark:border-skin-base text-skin-base' => ($activePaymentSetting == 'razorpay')])>
                        <svg class="h-4 w-4" width="24" height="24" viewBox="0 0 24 24"><defs><linearGradient id="a" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#0d3e8e"/><stop offset="100%" stop-color="#00c3f3"/></linearGradient></defs><path fill="url(#a)" d="m22.436 0-11.91 7.773-1.174 4.276 6.625-4.297L11.65 24h4.391z"/><path fill="#0D3E8E" d="M14.26 10.098 3.389 17.166 1.564 24h9.008z"/></svg>
                        @lang('modules.billing.razorpay')
                        <span @class(['flex w-3 h-3 me-3 rounded-full','bg-green-500' => $razorpayStatus, 'bg-red-500' => !$razorpayStatus  ])></span>
                    </span>
                </li>

                {{-- Stripe Tab --}}
                <li wire:click="activeSetting('stripe')" class="me-2">
                    <span @class(["inline-flex items-center gap-x-1 cursor-pointer select-none p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300", 'border-transparent' => ($activePaymentSetting != 'stripe'), 'active border-skin-base dark:text-skin-base dark:border-skin-base text-skin-base' => ($activePaymentSetting == 'stripe')])>
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="24" height="24" fill="#6772e5"><path d="M111.328 15.602c0-4.97-2.415-8.9-7.013-8.9s-7.423 3.924-7.423 8.863c0 5.85 3.32 8.8 8.036 8.8 2.318 0 4.06-.528 5.377-1.26V19.22a10.25 10.25 0 0 1-4.764 1.075c-1.9 0-3.556-.67-3.774-2.943h9.497a40 40 0 0 0 .063-1.748zm-9.606-1.835c0-2.186 1.35-3.1 2.56-3.1s2.454.906 2.454 3.1zM89.4 6.712a5.43 5.43 0 0 0-3.801 1.509l-.254-1.208h-4.27v22.64l4.85-1.032v-5.488a5.43 5.43 0 0 0 3.444 1.265c3.472 0 6.64-2.792 6.64-8.957.003-5.66-3.206-8.73-6.614-8.73zM88.23 20.1a2.9 2.9 0 0 1-2.288-.906l-.03-7.2a2.93 2.93 0 0 1 2.315-.96c1.775 0 2.998 2 2.998 4.528.003 2.593-1.198 4.546-2.995 4.546zM79.25.57l-4.87 1.035v3.95l4.87-1.032z" fill-rule="evenodd"/><path d="M74.38 7.035h4.87V24.04h-4.87z"/><path d="m69.164 8.47-.302-1.434h-4.196V24.04h4.848V12.5c1.147-1.5 3.082-1.208 3.698-1.017V7.038c-.646-.232-2.913-.658-4.048 1.43zm-9.73-5.646L54.698 3.83l-.02 15.562c0 2.87 2.158 4.993 5.038 4.993 1.585 0 2.756-.302 3.405-.643v-3.95c-.622.248-3.683 1.138-3.683-1.72v-6.9h3.683V7.035h-3.683zM46.3 11.97c0-.758.63-1.05 1.648-1.05a10.9 10.9 0 0 1 4.83 1.25V7.6a12.8 12.8 0 0 0-4.83-.888c-3.924 0-6.557 2.056-6.557 5.488 0 5.37 7.375 4.498 7.375 6.813 0 .906-.78 1.186-1.863 1.186-1.606 0-3.68-.664-5.307-1.55v4.63a13.5 13.5 0 0 0 5.307 1.117c4.033 0 6.813-1.992 6.813-5.485 0-5.796-7.417-4.76-7.417-6.943zM13.88 9.515c0-1.37 1.14-1.9 2.982-1.9A19.66 19.66 0 0 1 25.6 9.876v-8.27A23.2 23.2 0 0 0 16.862.001C9.762.001 5 3.72 5 9.93c0 9.716 13.342 8.138 13.342 12.326 0 1.638-1.4 2.146-3.37 2.146-2.905 0-6.657-1.202-9.6-2.802v8.378A24.4 24.4 0 0 0 14.973 32C22.27 32 27.3 28.395 27.3 22.077c0-10.486-13.42-8.613-13.42-12.56z" fill-rule="evenodd"/></svg>
                        @lang('modules.billing.stripe')
                        <span @class(['flex w-3 h-3 me-3 rounded-full','bg-green-500' => $stripeStatus, 'bg-red-500' => !$stripeStatus ])></span>
                    </span>
                </li>

                {{-- Wompi Tab --}}
                <li wire:click="activeSetting('wompi')" class="me-2">
                    <span @class(["inline-flex items-center gap-x-1 cursor-pointer select-none p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300", 'border-transparent' => ($activePaymentSetting != 'wompi'), 'active border-skin-base dark:text-skin-base dark:border-skin-base text-skin-base' => ($activePaymentSetting == 'wompi')])>
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93s3.06-7.44 7-7.93v15.86zm2-15.86c1.03.13 2 .45 2.87.93H13v-.93zM13 7h5.24c.25.31.48.65.68 1H13V7zm0 3h6.74c.08.33.15.66.19 1H13v-1zm0 9.93V19h2.87c-.87.48-1.84.8-2.87.93zM18.24 17H13v-1h5.92c-.2.35-.43.69-.68 1zm1.5-3H13v-1h6.93c-.04.34-.11.67-.19 1z" fill="#00C389"/>
                        </svg>
                        Wompi
                        <span @class(['flex w-3 h-3 me-3 rounded-full','bg-green-500' => $wompiStatus, 'bg-red-500' => !$wompiStatus ])></span>
                    </span>
                </li>

                {{-- Offline Payment Tab --}}
                <li wire:click="activeSetting('offline_payment_method')" class="me-2">
                        <span @class(["inline-flex items-center gap-x-2 cursor-pointer select-none p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300", 'border-transparent' => ($activePaymentSetting != 'offline_payment_method'), 'active border-skin-base dark:text-skin-base dark:border-skin-base text-skin-base' => ($activePaymentSetting == 'offline_payment_method')])>
                            <svg class="h-5 w-5 text-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g stroke-width="0"/><g stroke-linecap="round" stroke-linejoin="round"/><path d="M12 16h1c.667 0 2-.4 2-2s-1.333-2-2-2h-2c-.667 0-2-.4-2-2s1.333-2 2-2h1m0 8H9m3 0v2m3-10h-3m0 0V6m9 6a9 9 0 1 1-18 0 9 9 0 0 1 18 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        @lang('modules.billing.offlinePaymentMethod')
                        </span>
                </li>
            </ul>
        </div>

        <!-- Razorpay Form -->
        @if($activePaymentSetting == 'razorpay')
        <form wire:submit="submitFormRazorpay">
            <div class="grid gap-6">

                <div class="my-3">
                    <x-label for="razorpayStatus">
                        <div class="flex items-center cursor-pointer">
                            <x-checkbox name="razorpayStatus" id="razorpayStatus" wire:model.live='razorpayStatus'/>

                            <div class="ms-2">
                                @lang('modules.settings.enableRazorpay')
                            </div>
                        </div>
                    </x-label>
                </div>

                @if ($razorpayStatus)
                    <div>
                        <x-label for="selectRazorpayEnvironment" :value="__('modules.settings.selectEnvironment')"/>
                        <x-select id="selectRazorpayEnvironment" class="mt-1 block w-full" wire:model.live="selectRazorpayEnvironment">
                            <option value="test">@lang('app.test')</option>
                            <option value="live">@lang('app.live')</option>
                        </x-select>
                        <x-input-error for="selectRazorpayEnvironment" class="mt-2"/>
                    </div>

                    @if ($selectRazorpayEnvironment == 'live')
                        <div>
                            <x-label for="razorpayKey" value="Razorpay KEY"/>
                            <x-input id="razorpayKey" class="block mt-1 w-full" type="text" wire:model='razorpayKey'/>
                            <x-input-error for="razorpayKey" class="mt-2"/>
                        </div>

                        <div>
                            <x-label for="razorpaySecret" value="Razorpay SECRET"/>
                            <x-input-password id="razorpaySecret" class="block mt-1 w-full" type="text" wire:model='razorpaySecret'/>
                            <x-input-error for="razorpaySecret" class="mt-2"/>
                        </div>

                        <div>
                            <x-label for="razorpayWebhookKey" value="Razorpay Webhook Key"/>
                            <x-input-password id="razorpayWebhookKey" class="block mt-1 w-full" type="text" wire:model='razorpayWebhookKey'/>
                            <x-input-error for="razorpayWebhookKey" class="mt-2"/>
                        </div>
                    @else
                        <div>
                            <x-label for="testRazorpayKey" value="Test Razorpay KEY"/>
                            <x-input id="testRazorpayKey" class="block mt-1 w-full" type="text" wire:model='testRazorpayKey'/>
                            <x-input-error for="testRazorpayKey" class="mt-2"/>
                        </div>

                        <div>
                            <x-label for="testRazorpaySecret" value="Test Razorpay SECRET"/>
                            <x-input-password id="testRazorpaySecret" class="block mt-1 w-full" type="text" wire:model='testRazorpaySecret'/>
                            <x-input-error for="testRazorpaySecret" class="mt-2"/>
                        </div>

                        <div>
                            <x-label for="testRazorpayWebhookKey" value="Test Razorpay Webhook Key"/>
                            <x-input-password id="testRazorpayWebhookKey" class="block mt-1 w-full" type="text" wire:model='testRazorpayWebhookKey'/>
                            <x-input-error for="testRazorpayWebhookKey" class="mt-2"/>
                        </div>

                    @endif
                    <div class="mt-4">
                        <x-label value="Webhook URL" class="mb-1"/>
                        <div class="flex items-center">
                            <!-- Webhook URL Input -->
                            <x-input id="webhook-url" class="block w-full" type="text" value="{{ $webhookUrl }}" readonly/>
                            <button id="copy-button" type="button" onclick="copyWebhookUrl()" class="ml-2 px-3 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">
                                Copy
                            </button>
                        </div>
                    </div>

                @endif

                <div>
                    <x-button>@lang('app.save')</x-button>
                </div>
            </div>
        </form>
        @endif

        <!-- Stripe Form -->
        @if($activePaymentSetting == 'stripe')
        <form wire:submit="submitFormStripe">
            <div class="grid gap-6">
                <div class="my-3">
                    <x-label for="stripeStatus">
                        <div class="flex items-center cursor-pointer">
                            <x-checkbox name="stripeStatus" id="stripeStatus" wire:model.live='stripeStatus'/>

                            <div class="ms-2">
                                @lang('modules.settings.enableStripe')
                            </div>
                        </div>
                    </x-label>
                </div>

                @if ($stripeStatus)
                    <div>
                        <x-label for="selectStripeEnvironment" :value="__('modules.settings.selectEnvironment')"/>
                        <x-select id="selectStripeEnvironment" class="mt-1 block w-full" wire:model.live="selectStripeEnvironment">
                            <option value="test">@lang('app.test')</option>
                            <option value="live">@lang('app.live')</option>
                        </x-select>
                        <x-input-error for="selectStripeEnvironment" class="mt-2"/>
                    </div>

                    @if ($selectStripeEnvironment == 'live')
                        <div>
                            <x-label for="stripeKey" value="Stripe KEY"/>
                            <x-input id="stripeKey" class="block mt-1 w-full" type="text" wire:model='stripeKey'/>
                            <x-input-error for="stripeKey" class="mt-2"/>
                        </div>

                        <div>
                            <x-label for="stripeSecret" value="Stripe SECRET"/>
                            <x-input-password id="stripeSecret" class="block mt-1 w-full" type="text" wire:model='stripeSecret'/>
                            <x-input-error for="stripeSecret" class="mt-2"/>
                        </div>

                        <div>
                            <x-label for="stripeWebhookKey" value="Stripe Webhook Key"/>
                            <x-input-password id="stripeWebhookKey" class="block mt-1 w-full" type="text" wire:model='stripeWebhookKey'/>
                            <x-input-error for="stripeWebhookKey" class="mt-2"/>
                        </div>
                    @else
                        <div>
                            <x-label for="testStripeKey" value="Test Stripe KEY"/>
                            <x-input id="testStripeKey" class="block mt-1 w-full" type="text" wire:model='testStripeKey'/>
                            <x-input-error for="testStripeKey" class="mt-2"/>
                        </div>

                        <div>
                            <x-label for="testStripeSecret" value="Test Stripe SECRET"/>
                            <x-input-password id="testStripeSecret" class="block mt-1 w-full" type="text" wire:model='testStripeSecret'/>
                            <x-input-error for="testStripeSecret" class="mt-2"/>
                        </div>

                        <div>
                            <x-label for="testStripeWebhookKey" value="Test Stripe Webhook Key"/>
                            <x-input-password id="testStripeWebhookKey" class="block mt-1 w-full" type="text" wire:model='testStripeWebhookKey'/>
                            <x-input-error for="testStripeWebhookKey" class="mt-2"/>
                        </div>
                    @endif
                    <div class="mt-4">
                        <x-label value="Webhook URL" class="mb-1"/>
                        <div class="flex items-center">
                            <!-- Webhook URL Input -->
                            <x-input id="webhook-url" class="block w-full" type="text" value="{{ $webhookUrl }}" readonly/>
                            <button id="copy-button" type="button" onclick="copyWebhookUrl()" class="ml-2 px-3 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">
                                Copy
                            </button>
                        </div>
                    </div>
                @endif

                <div>
                    <x-button>@lang('app.save')</x-button>
                </div>
            </div>
        </form>
        @endif

        <!-- Wompi Form -->
        @if($activePaymentSetting == 'wompi')
        <form wire:submit="submitFormWompi">
            <div class="grid gap-6">
                <div class="my-3">
                    <x-label for="wompiStatus">
                        <div class="flex items-center cursor-pointer">
                            <x-checkbox name="wompiStatus" id="wompiStatus" wire:model.live='wompiStatus'/>

                            <div class="ms-2">
                                Habilitar Wompi (Colombia)
                            </div>
                        </div>
                    </x-label>
                </div>

                @if ($wompiStatus)
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center gap-2 text-blue-800 dark:text-blue-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium">Obtén tus credenciales en <a href="https://comercios.wompi.co" target="_blank" class="underline hover:no-underline">comercios.wompi.co</a></span>
                        </div>
                    </div>

                    <div>
                        <x-label for="selectWompiEnvironment" :value="__('modules.settings.selectEnvironment')"/>
                        <x-select id="selectWompiEnvironment" class="mt-1 block w-full" wire:model.live="selectWompiEnvironment">
                            <option value="test">Sandbox (Pruebas)</option>
                            <option value="live">Producción</option>
                        </x-select>
                        <x-input-error for="selectWompiEnvironment" class="mt-2"/>
                    </div>

                    @if ($selectWompiEnvironment == 'live')
                        <div>
                            <x-label for="wompiPublicKey" value="Llave Pública (Public Key)"/>
                            <x-input-password id="wompiPublicKey" class="block mt-1 w-full" wire:model='wompiPublicKey' placeholder="pub_prod_..."/>
                            <x-input-error for="wompiPublicKey" class="mt-2"/>
                        </div>

                        <div>
                            <x-label for="wompiPrivateKey" value="Llave Privada (Private Key)"/>
                            <x-input-password id="wompiPrivateKey" class="block mt-1 w-full" wire:model='wompiPrivateKey' placeholder="prv_prod_..."/>
                            <x-input-error for="wompiPrivateKey" class="mt-2"/>
                        </div>

                        <div>
                            <x-label for="wompiIntegritySecret" value="Secreto de Integridad (Integrity Secret)"/>
                            <x-input-password id="wompiIntegritySecret" class="block mt-1 w-full" wire:model='wompiIntegritySecret' placeholder="prod_integrity_..."/>
                            <x-input-error for="wompiIntegritySecret" class="mt-2"/>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Se usa para firmar las transacciones y verificar su integridad
                            </p>
                        </div>

                        <div>
                            <x-label for="wompiEventsSecret" value="Secreto de Eventos (Events Secret)"/>
                            <x-input-password id="wompiEventsSecret" class="block mt-1 w-full" wire:model='wompiEventsSecret' placeholder="prod_events_..."/>
                            <x-input-error for="wompiEventsSecret" class="mt-2"/>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Opcional: se usa para verificar webhooks de Wompi
                            </p>
                        </div>
                    @else
                        <div>
                            <x-label for="testWompiPublicKey" value="Test Llave Pública (Public Key)"/>
                            <x-input-password id="testWompiPublicKey" class="block mt-1 w-full" wire:model='testWompiPublicKey' placeholder="pub_test_..."/>
                            <x-input-error for="testWompiPublicKey" class="mt-2"/>
                        </div>

                        <div>
                            <x-label for="testWompiPrivateKey" value="Test Llave Privada (Private Key)"/>
                            <x-input-password id="testWompiPrivateKey" class="block mt-1 w-full" wire:model='testWompiPrivateKey' placeholder="prv_test_..."/>
                            <x-input-error for="testWompiPrivateKey" class="mt-2"/>
                        </div>

                        <div>
                            <x-label for="testWompiIntegritySecret" value="Test Secreto de Integridad (Integrity Secret)"/>
                            <x-input-password id="testWompiIntegritySecret" class="block mt-1 w-full" wire:model='testWompiIntegritySecret' placeholder="test_integrity_..."/>
                            <x-input-error for="testWompiIntegritySecret" class="mt-2"/>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Se usa para firmar las transacciones y verificar su integridad
                            </p>
                        </div>

                        <div>
                            <x-label for="testWompiEventsSecret" value="Test Secreto de Eventos (Events Secret)"/>
                            <x-input-password id="testWompiEventsSecret" class="block mt-1 w-full" wire:model='testWompiEventsSecret' placeholder="test_events_..."/>
                            <x-input-error for="testWompiEventsSecret" class="mt-2"/>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Opcional: se usa para verificar webhooks de Wompi
                            </p>
                        </div>
                    @endif

                    <div class="mt-4">
                        <x-label value="Webhook URL" class="mb-1"/>
                        <div class="flex items-center">
                            <x-input id="webhook-url-wompi" class="block w-full" type="text" value="{{ $webhookUrl }}" readonly/>
                            <button id="copy-button-wompi" type="button" onclick="copyWebhookUrl('webhook-url-wompi', 'copy-button-wompi')" class="ml-2 px-3 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">
                                Copy
                            </button>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Configura esta URL en tu panel de Wompi para recibir notificaciones de pago
                        </p>
                    </div>
                @endif

                <div>
                    <x-button>@lang('app.save')</x-button>
                </div>
            </div>
        </form>
        @endif

        @if($activePaymentSetting == 'offline_payment_method')
            @livewire('offline-payment.offline-payment-method-tab')
        @endif

    </div>

    <script>
        function copyWebhookUrl(inputId = 'webhook-url', buttonId = 'copy-button') {
            let webhookUrl = document.getElementById(inputId).value;
            let copyButton = document.getElementById(buttonId);

            // Create a temporary textarea element
            let tempTextArea = document.createElement("textarea");
            tempTextArea.value = webhookUrl;
            document.body.appendChild(tempTextArea);

            // Select and copy the text
            tempTextArea.select();
            tempTextArea.setSelectionRange(0, 99999); // For mobile devices
            document.execCommand("copy");

            // Remove the temporary textarea
            document.body.removeChild(tempTextArea);

            // Change button text to "Copied!"
            copyButton.innerText = "Copied!";

            // Revert text back to original after 2 seconds
            setTimeout(() => {
                copyButton.innerText = "Copy";
            }, 2000);
        }
    </script>
</div>
