<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Repositorios
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <p class="text-right mb-4">
                <a href="{{ route('repositories.create') }}" class="bg-blue-500 text-black font-bold py-2 px-4 rounded-md text-xs">
                    Add Repository
                </a>
            </p>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">
                <table>
                    <thead>
                        <tr>
                            <th class="border px-4 py-2">ID</th>
                            <th class="border px-4 py-2">ENLACE</th>
                            <th class="border px-4 py-2">&nbsp;</th>
                            <th class="border px-4 py-2">&nbsp;</th>
                            <th class="border px-4 py-2">&nbsp;</th>
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
                                <td class="px-4 py-2">
                                    <a href="{{ route('repositories.edit', $oRepository) }}">Editar</a>
                                </td>
                                <td class="px-4 py-2">
                                    <form action="{{ route('repositories.destroy', $oRepository) }}" method="POST">
                                        @csrf
                                        @method('delete')
                                        <input type="submit" value="Eliminar" class="px-4 rounded-md bg-red-500 text-black">
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No tienes repositorios creados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
