<x-filament-panels::page>
    @php
        $failed  = $checkResults->filter(fn ($r) => in_array($r->status, ['failed', 'crashed']))->count();
        $warning = $checkResults->filter(fn ($r) => $r->status === 'warning')->count();
        $ok      = $checkResults->filter(fn ($r) => $r->status === 'ok')->count();

        $statusColor = fn (string $status): string => match (true) {
            in_array($status, ['failed', 'crashed']) => 'danger',
            $status === 'warning'                    => 'warning',
            $status === 'ok'                         => 'success',
            default                                  => 'gray',
        };

        $statusIcon = fn (string $status): string => match (true) {
            in_array($status, ['failed', 'crashed']) => 'heroicon-o-x-circle',
            $status === 'warning'                    => 'heroicon-o-exclamation-triangle',
            $status === 'ok'                         => 'heroicon-o-check-circle',
            default                                  => 'heroicon-o-minus-circle',
        };
    @endphp

    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

        {{-- No results yet --}}
        @if ($checkResults->isEmpty())
            <x-filament::callout
                color="gray"
                icon="heroicon-o-information-circle"
                :heading="__('No results yet')"
                :description="__('Click «Run checks now» to run all health checks.')"
            />
        @else

            {{-- Summary banner --}}
            @if ($failed > 0)
                <x-filament::callout
                    color="danger"
                    icon="heroicon-o-x-circle"
                    :heading="__(':count check(s) failed', ['count' => $failed])"
                    :description="$warning > 0 ? __(':count warning(s)', ['count' => $warning]) : null"
                />
            @elseif ($warning > 0)
                <x-filament::callout
                    color="warning"
                    icon="heroicon-o-exclamation-triangle"
                    :heading="__(':count warning(s)', ['count' => $warning])"
                />
            @else
                <x-filament::callout
                    color="success"
                    icon="heroicon-o-check-circle"
                    :heading="__('All :count checks passed', ['count' => $ok])"
                />
            @endif

            {{-- Last checked --}}
            @if ($lastRanAt)
                <p style="font-size: 0.875rem; color: var(--gray-500);">
                    {{ __('Last checked') }}: {{ \Carbon\Carbon::parse($lastRanAt)->diffForHumans() }}
                </p>
            @endif

            {{-- Check results --}}
            <x-filament::section :heading="__('Checks')">
                <div style="display: flex; flex-direction: column;">
                    @foreach ($checkResults as $loop_result)
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 0.75rem 0; border-top: 1px solid rgb(var(--primary-200));" class="dark:[border-top-color:rgb(var(--primary-800))]">
                            <div style="display: flex; align-items: center; gap: 0.75rem; min-width: 0;">
                                <x-filament::icon
                                    :icon="$statusIcon($loop_result->status)"
                                    style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;"
                                />
                                <div>
                                    <p style="font-size: 0.875rem; font-weight: 500;">
                                        {{ $loop_result->label ?: $loop_result->name }}
                                    </p>
                                    @if (filled($loop_result->notificationMessage) && $loop_result->status !== 'ok')
                                        <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.125rem;">
                                            {{ $loop_result->notificationMessage }}
                                        </p>
                                    @elseif (filled($loop_result->shortSummary))
                                        <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.125rem;">
                                            {{ $loop_result->shortSummary }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <x-filament::badge :color="$statusColor($loop_result->status)">
                                {{ ucfirst($loop_result->status) }}
                            </x-filament::badge>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>

        @endif

        {{-- Environment info --}}
        <x-filament::section :heading="__('Environment')">
            <dl style="display: flex; flex-direction: column;">
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
                    <div style="display: flex; justify-content: space-between; gap: 0.5rem; font-size: 0.875rem; padding: 0.5rem 0; border-bottom: 1px solid rgb(var(--primary-200));" class="dark:[border-bottom-color:rgb(var(--primary-800))]">
                        <dt style="color: var(--gray-500);">{{ $label }}</dt>
                        <dd style="font-weight: 500;">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-filament::section>

    </div>
</x-filament-panels::page>
