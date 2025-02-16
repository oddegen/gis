<script setup>
import {computed, onMounted, ref, useTemplateRef} from "vue";
import Map from 'ol/Map.js'
import TileLayer from "ol/layer/Tile.js";
import OSM from "ol/source/OSM.js";
import View from 'ol/View.js';
import VectorLayer from 'ol/layer/Vector.js';
import VectorSource from "ol/source/Vector.js";
import GeoJSON from "ol/format/GeoJSON.js";
import {Circle, Fill, Stroke, Style, Text} from "ol/style.js";

let map = ref();
const mapRef = useTemplateRef('map');
const legendOpened = ref(false);
let monumentsLayer = null;

const monumentsFeatures = computed(() => {
    if (!monumentsLayer) return [];

    return monumentsLayer.getSource().getFeatures();
})

const styleFunction = (feature, resolution) => {
    return new Style({
        image: new Circle({
            radius: 4,
            fill: new Fill({
                color: 'rgba(0, 255, 255, 1)',
            }),
            stroke: new Stroke({
                color: 'rgba(192, 192, 192, 1)',
                width: 2
            }),
        }),
        text: new Text({
            font: '12px sans-serif',
            textAlign: 'left',
            text: feature.get('name'),
            offsetY: -15,
            offsetX: 5,
            backgroundFill: new Fill({
                color: 'rgba(255, 255, 255, 0.5)',
            }),
            backgroundStroke: new Stroke({
                color: 'rgba(227, 227, 227, 1)',
            }),
            padding: [5, 2, 2, 5]
        })
    })
}

const gotoFeature = (feature) => {
    if (!map.value) return;

    map.value.getView().animate({
        center: feature.getGeometry().getCoordinates(),
        zoom: 10,
        duration: 2000,
    });
}

onMounted(() => {
    const paramsObj = {
        servive: 'WFS',
        version: '2.0.0',
        request: 'GetFeature',
        typeName: 'gis:monuments',
        outputFormat: 'application/json',
        crs: 'EPSG:4326',
        srsName: 'EPSG:4326',
    }

    const urlParams = new URLSearchParams(paramsObj)
    const monumentsUrl = 'http://localhost:8080/geoserver/wfs?' +  urlParams.toString()

    monumentsLayer = new VectorLayer({
        source: new VectorSource({
            format: new GeoJSON(),
            url: monumentsUrl,
        }),
        style: styleFunction,
        label: "Monuments",
    })

    map.value = new Map({
        target: mapRef.value,
        layers: [
            new TileLayer({
                source: new OSM(),
                label: 'OSM',
            }),
            monumentsLayer,
        ],
        view: new View({
            projection: "EPSG:4326",
            center: [0, 0],
            zoom: 2
        })
    })
})
</script>

<template>
    <div ref="map" class="relative h-[600px] overflow-clip rounded-md border border-slate-300 shadow-lg">
        <div class="absolute top-2 right-8 z-10 rounded-md bg-white bg-opacity-75">
            <div class="ol-unselectable ol-control">
                <button @click.prevent="legendOpened = ! legendOpened"
                        title="Open/Close legend"
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
                <div class="absolute inset-1 rounded-md bg-white bg-opacity-75 p-2">
                    <div class="flex items-start justify-between">
                        <h3 class="text-lg font-medium text-slate-700">Legend</h3>
                        <button @click.prevent="legendOpened = false"
                                class="text-2xl font-black text-slate-400 transition hover:text-[#3369A1] focus:text-[#3369A1] focus:outline-none">&times;</button>
                    </div>
                    <ul class="mt-2 space-y-1 rounded-md border border-slate-300 bg-white p-2">
                        <template v-if="map" v-for="(layer, index) in map.getAllLayers().reverse()" :key="index">
                            <li class="flex items-center px-2 py-1">
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" :checked="layer.getVisible()"
                                               @change="layer.setVisible(!layer.getVisible())"
                                               class="rounded border-slate-300 text-[#3369A1] shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-slate-600" v-text="layer.get('label')"></span>
                                    </label>
                                    <div v-if="layer.get('label') === 'Monuments' && layer.getVisible()">
                                        <div class="mt-2 ml-6 text-sm text-slate-600">
                                            <template v-for="(feature, index) in monumentsFeatures" :key="index">
                                                <a href="#" :title="'Go to ' +  feature.get('name')"
                                                   v-text="feature.get('name')" @click.prevent="gotoFeature(feature)"
                                                   class="block transition hover:text-slate-800 hover:underline focus:text-slate-800 focus:underline focus:outline-none">
                                                </a>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </Transition>
    </div>
</template>
