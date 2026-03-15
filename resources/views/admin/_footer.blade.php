@php
    $gitHash = trim(exec('git -C ' . escapeshellarg(base_path()) . ' log -1 --format="%h"'));
    $gitDate = trim(exec('git -C ' . escapeshellarg(base_path()) . ' log -1 --format="%ci"'));
    $gitMessage = trim(exec('git -C ' . escapeshellarg(base_path()) . ' log -1 --format="%s"'));
@endphp
<footer class="text-center py-4 text-xs text-gray-400">
    <p>Dernier déploiement : <span class="font-mono">{{ $gitHash }}</span> — {{ $gitMessage }} — {{ $gitDate }}</p>
</footer>
