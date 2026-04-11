@props(['title', 'count', 'icon'])

<div class="bg-white p-5 rounded-lg shadow-md border-l-4 border-{{ $attributes->get('color', 'blue') }}-500 transition duration-300 hover:shadow-lg">
    <div class="flex items-center">
        <div class="flex-shrink-0 mr-4 text-{{ $attributes->get('color', 'blue') }}-500">
            {!! $icon !!}
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">{{ $title }}</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $count }}</p>
        </div>
    </div>
</div>