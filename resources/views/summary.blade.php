<div class="mx-2">
    @if(! $isSuccessful)
        @foreach($result['files'] as $file)
            <div class="mb-1 mt-2">
                <span class="px-1 bg-red text-white uppercase">File</span>
                <span class="ml-1">{{ ltrim(str_replace(base_path(), '', $file['name']), DIRECTORY_SEPARATOR) }}</span>
            </div>

            <div class="flex max-w-150">
                <span>Rules</span>
                <span class="flex-1 content-repeat-[.] text-gray ml-1"></span>
                <span class="ml-1">
                    @foreach($file['appliedFixers'] as $fixer)
                        <span class="text-red ml-1">{{ $fixer . ($loop->last ? '' : ', ') }}</span>
                    @endforeach
                </span>
            </div>

            <div class="flex space-x-1 mt-1">
                <span class="flex-1 content-repeat-[─] text-gray"></span>
            </div>
        @endforeach

        <div class="mb-2"></div>
    @endif

    <div class="flex space-x-1 mb-2">
        <span class="font-bold">
            Checked <span class="text-yellow">{{ count($result['files']) }}</span> files in <span class="text-yellow">{{ $result['time']['total'] ?? 0 }}</span> seconds.
            Using memory <span class="text-yellow">{{ $result['memory'] }}</span> MB.
        </span>
    </div>

    @if(! $isSuccessful)
        <div class="mb-1">
            <span class="px-1 bg-yellow text-white uppercase">Warn</span>
            <span class="ml-1">
                Run <span class="text-yellow">./vendor/bin/pint --dirty --test -v</span> to see coding standard detail issues.
            </span>
        </div>
        <div class="mb-1">
            <span class="px-1 bg-yellow text-white uppercase">Warn</span>
            <span class="ml-1">
                Run <span class="text-yellow">./vendor/bin/pint --dirty</span> to fix coding standard issues.
            </span>
        </div>
    @else
        <div class="mb-1">
            <span class="px-1 bg-green text-white uppercase">Success</span>
            <span class="ml-1">Your code is perfect, no syntax error found!</span>
        </div>
    @endif
</div>
