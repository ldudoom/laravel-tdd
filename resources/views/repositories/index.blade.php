<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Repositorios
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">
                <table>
                    <thead>
                        <tr>
                            <th class="border px-4 py-2">ID</th>
                            <th class="border px-4 py-2">ENLACE</th>
                            <th class="border px-4 py-2">nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($aRepositories as $oRepository)
                            <tr>
                                <td class="border px-4 py-2">{{ $oRepository->id }}</td>
                                <td class="border px-4 py-2">{{ $oRepository->url }}</td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('repositories.show', $oRepository) }}">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">No tienes repositorios creados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
