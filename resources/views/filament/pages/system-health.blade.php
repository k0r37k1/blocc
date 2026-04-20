<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Last checked --}}
        @if ($lastRanAt)
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('Last checked') }}: {{ \Carbon\Carbon::parse($lastRanAt)->diffForHumans() }}
            </p>
        @else
            <x-filament::section>
                <div class="flex items-center gap-3 text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-information-circle class="w-5 h-5 shrink-0" />
                    <p class="text-sm">{{ __('No results yet. Click "Run checks now" to get started.') }}</p>
                </div>
            </x-filament::section>
        @endif

        {{-- Summary banner --}}
        @if ($checkResults->isNotEmpty())
            @php
                $failed  = $checkResults->filter(fn ($r) => in_array($r->status, ['failed', 'crashed']))->count();
                $warning = $checkResults->filter(fn ($r) => $r->status === 'warning')->count();
                $ok      = $checkResults->filter(fn ($r) => $r->status === 'ok')->count();
            @endphp

            <div @class([
                'rounded-xl border px-5 py-4 flex items-center gap-4',
                'bg-danger-50 border-danger-200 dark:bg-danger-950/30 dark:border-danger-800'      => $failed > 0,
                'bg-warning-50 border-warning-200 dark:bg-warning-950/30 dark:border-warning-800'  => $failed === 0 && $warning > 0,
                'bg-success-50 border-success-200 dark:bg-success-950/30 dark:border-success-800'  => $failed === 0 && $warning === 0,
            ])>
                @if ($failed > 0)
                    <x-heroicon-o-x-circle class="w-6 h-6 text-danger-500 shrink-0" />
                    <div>
                        <p class="font-semibold text-danger-700 dark:text-danger-400">{{ __(':count check(s) failed', ['count' => $failed]) }}</p>
                        @if ($warning > 0)
                            <p class="text-sm text-danger-600 dark:text-danger-500">{{ __(':count warning(s)', ['count' => $warning]) }}</p>
                        @endif
                    </div>
                @elseif ($warning > 0)
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-warning-500 shrink-0" />
                    <p class="font-semibold text-warning-700 dark:text-warning-400">{{ __(':count warning(s)', ['count' => $warning]) }}</p>
                @else
                    <x-heroicon-o-check-circle class="w-6 h-6 text-success-500 shrink-0" />
                    <p class="font-semibold text-success-700 dark:text-success-400">{{ __('All :count checks passed', ['count' => $ok]) }}</p>
                @endif
            </div>
        @endif

        {{-- Check results --}}
        @if ($checkResults->isNotEmpty())
            <x-filament::section>
                <x-slot name="heading">{{ __('Checks') }}</x-slot>

                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($checkResults as $result)
                        @php
                            $status    = $result->status;
                            $isFailed  = in_array($status, ['failed', 'crashed']);
                            $isWarning = $status === 'warning';
                            $isOk      = $status === 'ok';
                            $isSkipped = $status === 'skipped';
                        @endphp

                        <div class="flex items-start justify-between gap-4 py-3 first:pt-0 last:pb-0">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="mt-0.5 shrink-0">
                                    @if ($isFailed)
                                        <x-heroicon-o-x-circle class="w-5 h-5 text-danger-500" />
                                    @elseif ($isWarning)
                                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-warning-500" />
                                    @elseif ($isOk)
                                        <x-heroicon-o-check-circle class="w-5 h-5 text-success-500" />
                                    @else
                                        <x-heroicon-o-minus-circle class="w-5 h-5 text-gray-400" />
                                    @endif
                                </div>

                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $result->check_name }}</p>
                                    @if (filled($result->notification_message) && ! $isOk)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 break-words">{{ $result->notification_message }}</p>
                                    @elseif (filled($result->short_summary))
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $result->short_summary }}</p>
                                    @endif
                                </div>
                            </div>

                            <span @class([
                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset shrink-0',
                                'bg-danger-100 text-danger-700 ring-danger-200 dark:bg-danger-950/50 dark:text-danger-400 dark:ring-danger-800'      => $isFailed,
                                'bg-warning-100 text-warning-700 ring-warning-200 dark:bg-warning-950/50 dark:text-warning-400 dark:ring-warning-800' => $isWarning,
                                'bg-success-100 text-success-700 ring-success-200 dark:bg-success-950/50 dark:text-success-400 dark:ring-success-800' => $isOk,
                                'bg-gray-100 text-gray-600 ring-gray-200 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10'                        => $isSkipped,
                            ])>
                                {{ ucfirst($status) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        {{-- Environment info --}}
        <x-filament::section>
            <x-slot name="heading">{{ __('Environment') }}</x-slot>

            <dl class="grid grid-cols-1 gap-x-8 gap-y-0 sm:grid-cols-2">
                @foreach ([
                    __('PHP Version')    => PHP_VERSION,
                    __('Laravel')       => app()->version(),
                    __('Environment')   => app()->environment(),
                    __('Debug Mode')    => config('app.debug') ? __('On') : __('Off'),
                    __('Timezone')      => config('app.timezone'),
                    __('Cache Driver')  => config('cache.default'),
                    __('Queue Driver')  => config('queue.default'),
                    __('DB Connection') => config('database.default'),
                ] as $label => $value)
                    <div class="flex justify-between gap-2 text-sm border-b border-gray-100 dark:border-white/5 py-2.5">
                        <dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-white text-right">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-filament::section>

    </div>
</x-filament-panels::page>
