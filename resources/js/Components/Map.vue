<script setup>
import {onMounted, ref, useTemplateRef, watch} from "vue";
import Map from 'ol/Map.js'
import {Tile as TileLayer} from 'ol/layer.js';
import OSM from "ol/source/OSM.js";
import View from 'ol/View.js';
import {Circle, Fill, Stroke, Style, Text} from "ol/style.js";
import Overlay from "ol/Overlay.js";
import TileWMS from 'ol/source/TileWMS.js';
import Feature from "ol/Feature";
import Point from 'ol/geom/Point.js';

const map = ref();
const mapRef = useTemplateRef('map');
const legendOpened = ref(false);
const popupRef = useTemplateRef('popup');
const popupContentRef = useTemplateRef('popupContent');
const activeTab = ref('legend');

const gotoFeature = (feature, cb) => {
    if (!map.value) return;

    map.value.getView().animate({
        center: feature.getGeometry().getCoordinates(),
        zoom: 15,
        duration: 500,
    }, cb);
};

const closePopup = () => {
    let overlay = map.value.getOverlayById('info');
    overlay.setPosition(undefined);
    popupContentRef.value.innerHTML = '';
};

const hasLegend = (layer) => {
    return layer.getSource() instanceof TileWMS
};

const legendUrl = (layer) => {
    if (hasLegend(layer)) {
        return layer
            .getSource()
            .getLegendUrl(map.value.getView().getResolution(), {
                LEGEND_OPTIONS: 'forceLabels:on'
            })
    }
}

onMounted(() => {
    let monumentsLayer = new TileLayer({
        source: new TileWMS({
            url: 'http://localhost:8080/geoserver/wms',
            params: {
                'LAYERS': 'gis:monuments',
                'TILED': true
            },
            serverType: 'geoserver',
        }),
        label: 'Monuments',
    });

    let boundariesLayer = new TileLayer({
        source: new TileWMS({
            url: 'http://localhost:8080/geoserver/wms',
            params: {
                'LAYERS': 'gis:world-administrative-boundaries',
                'TILED': true
            },
            serverType: 'geoserver',
        }),
        label: 'World Administrative Boundaries',
    });

    let riversLayer = new TileLayer({
        source: new TileWMS({
            url: 'http://localhost:8080/geoserver/wms',
            params: {'LAYERS': 'gis:world-rivers', 'TILED': true},
            serverType: 'geoserver',
        }),
        label: 'World Rivers',
    });

    map.value = new Map({
        target: mapRef.value,
        layers: [
            new TileLayer({
                source: new OSM(),
                label: 'OSM',
            }),
            boundariesLayer,
            riversLayer,
            monumentsLayer,
        ],
        view: new View({
            projection: "EPSG:4326",
            center: [-78.2161, -0.7022],
            zoom: 8,
        }),
        overlays: [
            new Overlay({
                id: 'info',
                element: popupRef.value,
                stopEvent: true,
            }),
        ],
    })

    map.value.on('singleclick', (e) => {
        if (e.dragging) return;

        let overlay = map.value.getOverlayById('info');
        overlay.setPosition(undefined);
        popupContentRef.value.innerHTML = '';

        const viewResolution = e.map.getView().getResolution()

        const url = monumentsLayer.getSource().getFeatureInfoUrl(
            e.coordinate,
            viewResolution,
            'EPSG:4326', {
                'INFO_FORMAT': 'application/json'
            })

        if (url) {
            fetch(url)
                .then((response) => response.json())
                .then((json) => {
                    if (json.features.length > 0) {
                        let jsonFeature = json.features[0]

                        let feature = new Feature({
                            geometry: new Point(jsonFeature.geometry.coordinates),
                            name: jsonFeature.properties.name,
                            image: jsonFeature.properties.image
                        })

                        let content = `<h4 class="text-gray-500 font-bold">${feature.get('name')}</h4>
                               <img src="${feature.get('image')}" alt="${feature.get('name')}" class="mt-2 w-full max-h-[200px] rounded-md shadow-md object-contain overflow-clip">`;
                        popupContentRef.value.innerHTML = content;
                        gotoFeature(feature, () => {
                            overlay.setPosition(
                                feature.getGeometry().getCoordinates()
                            );
                        });
                        return;
                    }
                })
        }
    })
});
</script>

<template>
    <div ref="map" class="relative h-[600px] overflow-clip rounded-md border border-slate-300 shadow-lg">
        <div class="absolute top-2 right-8 z-10 rounded-md bg-white bg-opacity-75">
            <div class="ol-unselectable ol-control">
                <button @click.prevent="legendOpened = ! legendOpened"
                        title="Open/Close details"
                        class="absolute inset-0 flex justify-center items-center">
                    <!-- Heroicon name: outline/globe -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 pl-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>
            </div>
        </div>
        <Transition name="legend">
            <div v-cloak v-if="legendOpened"
                 class="absolute right-0 top-16 left-2 bottom-2 z-10 max-w-sm rounded-md border border-slate-300 bg-white bg-opacity-50 shadow-sm">
                <div class="absolute inset-1 overflow-y-auto rounded-md bg-white bg-opacity-75 p-2 pt-1">
                    <div class="flex items-center justify-between pr-1">
                        <div class="flex justify-start space-x-4">
                            <h3 @click.prevent="activeTab = 'legend'" class="cursor-pointer text-slate-700"
                                v-bind:class="activeTab === 'legend' && 'font-bold'">Legend</h3>
                            <h3 @click.prevent="activeTab = 'monuments'" class="cursor-pointer text-slate-700"
                                v-bind:class="activeTab === 'monuments' && 'font-bold'">Monuments</h3>
                        </div>
                        <button @click.prevent="legendOpened = false"
                                class="mb-1 text-2xl font-black text-slate-400 transition hover:text-[#3369A1] focus:text-[#3369A1] focus:outline-none">&times;</button>
                    </div>
<!--                    TODO: Add transition animation css-->
                    <Transition name="tab">
                        <ul v-show="activeTab === 'legend'" class="mt-2 p-1 space-y-1 rounded-md border border-slate-300 bg-white">
                            <template v-if="map" v-for="(layer, index) in map.getAllLayers().reverse()" :key="index">
                                <li class="flex items-center p-0.5">
                                    <div class="px-2 py-1 w-full border border-gray-300 rounded-md">
                                        <div class="space-y-1">
                                            <label for="legend-range-{{ index }}" class="flex items-center">
                                                <span class="text-sm text-slate-600" v-text="layer.get('label')"></span>
                                            </label>
                                            <div v-show="hasLegend(layer)">
                                                <img v-bind:src="legendUrl(layer)" alt="Legend">
                                            </div>
                                        </div>
                                        <div class="mt-2 text-sm text-slate-600">
                                            <input class="w-full accent-[#3369A1]"
                                                   type="range"
                                                   min="0"
                                                   max="1"
                                                   step="0.01"
                                                   id="legend-range-{{ index }}"
                                                   :value="layer.getOpacity()"
                                                   @change="(e) => layer.setOpacity(Number(e.target.value))">
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </Transition>
                    <Transition name="tab">
                        <div v-show="activeTab === 'monuments'" class="mt-2 p-1 rounded-md border border-slate-300 bg-white">
                            Monuments
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
        <div v-cloak ref="popup" class="ol-popup ol-control transition">
            <div class="p-2 m-0.5 bg-white rounded-md">
                <div class="flex justify-between">
                    <h3 class="text-xs font-medium text-slate-400">Monument</h3>
                    <a href="#"
                       title="Close"
                       @click.prevent="closePopup"
                       class="-mt-1 font-black text-slate-400 transition hover:text-slate-600 focus:text-slate-600 focus:outline-none">&times;</a>
                </div>
                <div ref="popupContent" class="mt-2 overflow-y-auto min-h-[200px]"></div>
            </div>
        </div>
    </div>
</template>
