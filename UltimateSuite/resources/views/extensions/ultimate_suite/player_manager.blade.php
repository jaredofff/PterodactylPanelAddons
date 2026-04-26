@extends('layouts.admin')

@section('title')
    Ultimate Suite - Player Manager
@endsection

@section('content-header')
    <h1>Player Manager<small>{{ $player_name }}</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.extensions.ultimate_suite.index') }}">Ultimate Suite</a></li>
        <li class="active">Player Manager</li>
    </ol>
@endsection

@section('content')
<div class="row" x-data="playerManager()">
    <div class="col-xs-12">
        <div class="nav-tabs-custom nav-tabs-cyan">
            <ul class="nav nav-tabs">
                <li :class="{ 'active': tab === 'inventory' }"><a href="javascript:;" @click="tab = 'inventory'">Inventario</a></li>
                <li :class="{ 'active': tab === 'stats' }"><a href="javascript:;" @click="tab = 'stats'">Estadísticas</a></li>
                <li :class="{ 'active': tab === 'location' }"><a href="javascript:;" @click="tab = 'location'">Ubicación</a></li>
            </ul>
            <div class="tab-content bg-neutral-900 text-white">
                <!-- Inventario -->
                <div class="tab-pane" :class="{ 'active': tab === 'inventory' }">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="inventory-grid grid grid-cols-9 gap-2 p-4 bg-neutral-800 rounded-lg">
                                <template x-for="i in 36" :key="i">
                                    <div class="inventory-slot w-16 h-16 bg-neutral-900 border-2 border-neutral-700 rounded-md hover:border-cyan-500 flex items-center justify-center relative">
                                        <template x-if="inventory[i+8]">
                                            <div class="group">
                                                <img :src="'https://minetar.com/items/' + inventory[i+8].id" class="w-10 h-10">
                                                <span class="absolute bottom-1 right-1 text-xs font-bold" x-text="inventory[i+8].count"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="box box-solid bg-neutral-800 border-neutral-700">
                                <div class="box-header with-border">
                                    <h3 class="box-title text-white">Equipamiento</h3>
                                </div>
                                <div class="box-body">
                                    <div class="flex flex-col space-y-2">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-neutral-900 border border-neutral-700 rounded flex items-center justify-center">🛡️</div>
                                            <span>Armadura</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="tab-pane" :class="{ 'active': tab === 'stats' }">
                    <div class="row p-4">
                        <div class="col-md-4">
                            <label class="block text-neutral-400 text-xs uppercase font-bold mb-2">Vida (Health)</label>
                            <input type="number" x-model="stats.health" class="form-control bg-neutral-800 border-neutral-700 text-white">
                        </div>
                        <div class="col-md-4">
                            <label class="block text-neutral-400 text-xs uppercase font-bold mb-2">Comida (Food)</label>
                            <input type="number" x-model="stats.food" class="form-control bg-neutral-800 border-neutral-700 text-white">
                        </div>
                        <div class="col-md-4">
                            <label class="block text-neutral-400 text-xs uppercase font-bold mb-2">Nivel XP</label>
                            <input type="number" x-model="stats.xp" class="form-control bg-neutral-800 border-neutral-700 text-white">
                        </div>
                    </div>
                </div>

                <!-- Ubicación -->
                <div class="tab-pane" :class="{ 'active': tab === 'location' }">
                    <div class="row p-4">
                        <div class="col-md-4">
                            <label>X</label>
                            <input type="number" x-model="location.pos[0]" class="form-control bg-neutral-800 border-neutral-700 text-white">
                        </div>
                        <div class="col-md-4">
                            <label>Y</label>
                            <input type="number" x-model="location.pos[1]" class="form-control bg-neutral-800 border-neutral-700 text-white">
                        </div>
                        <div class="col-md-4">
                            <label>Z</label>
                            <input type="number" x-model="location.pos[2]" class="form-control bg-neutral-800 border-neutral-700 text-white">
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer bg-neutral-800 border-neutral-700">
                <button class="btn btn-primary bg-cyan-600 border-none pull-right" @click="save()">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-neutral-900 { background-color: #171717; }
    .bg-neutral-800 { background-color: #262626; }
    .border-neutral-700 { border-color: #404040; }
    .text-white { color: #ffffff; }
    .nav-tabs-cyan .nav-tabs > li.active > a { border-top-color: #06b6d4 !important; background-color: #262626 !important; color: #fff !important; }
    .grid { display: grid; }
    .grid-cols-9 { grid-template-columns: repeat(9, minmax(0, 1fr)); }
    .gap-2 { gap: 0.5rem; }
</style>

<script>
    function playerManager() {
        return {
            tab: 'inventory',
            inventory: @json($inventory),
            stats: @json($stats),
            location: @json($location),
            save() {
                // Lógica de guardado vía AJAX
                console.log('Saving...', this.stats, this.location);
            }
        }
    }
</script>
@endsection
