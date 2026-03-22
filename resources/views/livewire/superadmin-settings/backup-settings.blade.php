<div>
    <div class="mx-4 p-4 mb-4 bg-white border border-gray-200 rounded-lg shadow-sm 2xl:col-span-2 dark:border-gray-700 sm:p-6 dark:bg-gray-800">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold dark:text-white">@lang('modules.backup.title')</h3>

            <button wire:click="generateBackup" wire:loading.attr="disabled"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-skin-base border border-transparent rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-skin-base disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="generateBackup">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    @lang('modules.backup.generate')
                </span>
                <span wire:loading wire:target="generateBackup" class="inline-flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    @lang('modules.backup.generating')
                </span>
            </button>
        </div>

        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
            @lang('modules.backup.description')
        </p>

        @if(count($backups) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">@lang('modules.backup.fileName')</th>
                            <th scope="col" class="px-6 py-3">@lang('modules.backup.date')</th>
                            <th scope="col" class="px-6 py-3">@lang('modules.backup.size')</th>
                            <th scope="col" class="px-6 py-3 text-right">@lang('modules.backup.actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backups as $backup)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                        </svg>
                                        {{ $backup['name'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">{{ $backup['date'] }}</td>
                                <td class="px-6 py-4">{{ $backup['size'] }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('superadmin.backup.download', $backup['name']) }}"
                                       class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 dark:bg-blue-900 dark:text-blue-300 dark:hover:bg-blue-800 mr-2">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        @lang('app.download')
                                    </a>

                                    <button wire:click="deleteBackup('{{ $backup['name'] }}')"
                                        wire:confirm="@lang('modules.backup.confirmDelete')"
                                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 dark:bg-red-900 dark:text-red-300 dark:hover:bg-red-800">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        @lang('app.delete')
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-12 text-gray-400 dark:text-gray-500">
                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                <p class="text-sm">@lang('modules.backup.noBackups')</p>
            </div>
        @endif
    </div>

    <div class="mx-4 p-4 mb-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
        <h4 class="text-lg font-semibold dark:text-white mb-2">@lang('modules.backup.restoreTitle')</h4>

        <div class="p-4 mb-4 text-sm text-yellow-800 bg-yellow-50 border border-yellow-200 rounded-lg dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                @lang('modules.backup.restoreWarning')
            </div>
        </div>

        <div class="flex items-end gap-4">
            <div class="flex-1">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    @lang('modules.backup.selectFile')
                </label>
                <input type="file" wire:model="restoreFile" accept=".zip"
                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                    @if($isRestoring) disabled @endif>
                @error('restoreFile')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <button wire:click="restoreBackup"
                wire:confirm="@lang('modules.backup.confirmRestore')"
                wire:loading.attr="disabled"
                @if(!$restoreFile || $isRestoring) disabled @endif
                class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-yellow-600 border border-transparent rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="restoreBackup">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    @lang('modules.backup.restore')
                </span>
                <span wire:loading wire:target="restoreBackup" class="inline-flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    @lang('modules.backup.restoring')
                </span>
            </button>
        </div>

        <div wire:loading wire:target="restoreFile" class="mt-3">
            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                <svg class="animate-spin mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                @lang('modules.backup.uploading')
            </div>
        </div>
    </div>
</div>
