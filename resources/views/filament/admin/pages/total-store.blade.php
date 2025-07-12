<x-filament::widget>
    <x-filament::card>
        <h2 class="text-lg font-bold">Total Store</h2>
        <p class="text-2xl text-primary">
            {{ $this->getStats() }}
        </p>
    </x-filament::card>
</x-filament::widget>
