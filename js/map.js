function size(map) {
    let width = $("#mapDiv").width(),
        height = $("#mapDiv").parent().height() * 1.5;

    $('#mapDiv').width(width).height(height).css("max-height", "500px");
    map.invalidateSize();
}

function pad(str) {
    str = str.toString();
    return str.length < 3 ? pad("0" + str) : str;
}

function markersIcons() {
    let icons = ['tribunal', 'hangar', 'radar', 'warning', 'point'],
        markers = [];
    for (let i in icons) {
        markers[icons[i]] = L.icon({
            iconUrl: '/img/icons/' + icons[i] + '.png',
            iconSize: [20, 20]
        });
    }
    markers['city'] = L.icon({
        iconUrl: '/img/icons/city.png',
        iconSize: [10, 10]
    });
    markers['basic'] = L.divIcon({className: 'fas fa-map-marker map-icon', iconSize: [20, 20]});
    markers['maison'] = L.divIcon({className: 'fas fa-home map-icon map-icon', iconSize: [20, 20]});
    markers['maisonModerne'] = L.divIcon({className: 'fas fa-hotel map-icon', iconSize: [20, 20]});
    markers['garage'] = L.divIcon({className: 'fas fa-warehouse map-icon', iconSize: [20, 20]});
    markers['bungalow'] = L.divIcon({className: 'fas fa-igloo map-icon', iconSize: [20, 20]});
    markers['batimentIndustriel'] = L.divIcon({className: 'fas fa-industry map-icon', iconSize: [20, 20]});
    markers['stationService'] = L.divIcon({className: 'fas fa-gas-pump map-icon', iconSize: [20, 20]});
    markers['entreprise'] = L.divIcon({className: 'fas fa-building map-icon', iconSize: [20, 20]});
    markers['accident'] = L.divIcon({className: 'fas fa-car-crash map-icon', iconSize: [20, 20]});
    markers['drogue'] = L.divIcon({className: 'fas fa-cannabis map-icon', iconSize: [20, 20]});
    markers['vendeur'] = L.divIcon({className: 'fas fa-hand-holding-usd map-icon', iconSize: [20, 20]});
    markers['labo'] = L.divIcon({className: 'fas fa-flask map-icon', iconSize: [20, 20]});
    markers['change'] = L.divIcon({className: 'fas fa-exchange-alt map-icon', iconSize: [20, 20]});
    markers['mask'] = L.divIcon({className: 'fas fa-mask map-icon', iconSize: [20, 20]});
    markers['fire'] = L.divIcon({className: 'fas fa-fire map-icon', iconSize: [20, 20]});
    markers['tent'] = L.divIcon({className: 'fas fa-campground map-icon', iconSize: [20, 20]});
    markers['cross'] = L.divIcon({className: 'fas fa-star-of-life map-icon', iconSize: [20, 20]});

    return markers
}

L.Control.Custom = L.Control.extend({
    version: '1.0.1',
    options: {
        position: 'topright',
        id: '',
        title: '',
        classes: '',
        content: '',
        style: {},
        datas: {},
        events: {},
    },
    container: null,
    onAdd: function () {
        this.container = L.DomUtil.create('div');
        this.container.id = this.options.id;
        this.container.title = this.options.title;
        this.container.className = this.options.classes;
        this.container.innerHTML = this.options.content;

        for (let option in this.options.style)
            this.container.style[option] = this.options.style[option];
        for (let data in this.options.datas)
            this.container.dataset[data] = this.options.datas[data];
        L.DomEvent.disableClickPropagation(this.container);
        L.DomEvent.on(this.container, 'contextmenu', ev => L.DomEvent.stopPropagation(ev));
        L.DomEvent.disableScrollPropagation(this.container);

        for (let event in this.options.events)
            L.DomEvent.on(this.container, event, this.options.events[event], this.container);

        return this.container;
    },

    onRemove: function () {
        for (let event in this.options.events)
            L.DomEvent.off(this.container, event, this.options.events[event], this.container);
    },
});
L.control.custom = options => new L.Control.Custom(options);

let ratio = 16384 / 128;

let map, sat, search,
    capitals = L.layerGroup(),
    cities = L.layerGroup(),
    villages = L.layerGroup(),
    markers = markersIcons();

map = L.map('mapDiv', {
    bounds: [[1, 1], [127, 127]],
    maxBounds: [[1, 1], [127, 127]],
    minZoom: 3,
    maxZoom: 7,
    crs: L.CRS.Simple,
    zoomSnap: 0.5
}).setView([60, 85], 3);

//Data
sat = L.tileLayer('/img/map/{z}/{x}/{y}.png', {
    attribution: 'ALF - Belle-Île-en-Mer',
});
sat.getTileUrl = function (coords) {
    coords.y = -coords.y - 1;
    return L.TileLayer.prototype.getTileUrl.bind(sat)(coords);
};//Modification des coords
sat.addTo(map);

map.zoomToArmaCoords = str => {
    let reg = new RegExp('^[0-9]{3}(.| )[0-9]{3}$');
    if (reg.test(str)) {
        let coords = str.split(' ');
        if (coords.length === 1) coords = str.split('.');
        if (coords.length === 2) {
            let x = parseInt(coords[0]), y = parseInt(coords[1]);
            if (143 <= y <= 307 && 0 <= x <= 163)
                map.flyTo(new L.LatLng(((y - 144) * 100 + 64) / ratio, 100 * x / ratio), 7);
        }
    }
};

map.addMarker = str => {
    let d = str.split('[')[1].split(',');
    let x = d[0], y = d[1];
    if (143 <= y <= 307 && 0 <= x <= 163) {
        let marker = new L.marker([y / ratio, x / ratio]).addTo(map);
        map.fitBounds(L.latLngBounds([marker.getLatLng()]));
    }
};

//Recherche par coordonnées
L.control.custom({
    position: 'bottomleft',
    content:
        '<div class="input-group w-100">' +
        '<input id="searchCoords" class="form-control" type="search" placeholder="Coordonnées (ex: 012.345)"/>' +
        '<div class="input-group-append">' +
        '<div class="input-group-text" onclick="$(\'#searchCoords\').val(\'\');"><i class="fas fa-map-marked"></i></div>' +
        '</div></div>',
    classes: 'btn-group',
    style: {
        margin: '10px',
        padding: '0px 0 0 0',
        cursor: 'pointer'
    }
}).addTo(map);
$('#searchCoords').keyup(function () {
    map.zoomToArmaCoords($(this).val());
});

//Affichage coordonnées ARMA
L.control.coordinates({
    position: "bottomleft",
    labelFormatterLat: lat => pad(144 + Math.floor((lat * ratio - 64) / 100)),
    labelFormatterLng: lng => pad(Math.floor(lng * ratio / 100)),
    labelLng: true,
    enableUserInput: false
}).addTo(map);

L.control.coordinates({
    position: "bottomleft",
	decimals:2,
    labelFormatterLat: lat => pad(Math.floor(lat * ratio)),
    labelFormatterLng: lng => pad(Math.floor(lng * ratio)),
    labelLng: true,
    enableUserInput: false
}).addTo(map);

$('.marker').click(function () {
    map.flyTo(new L.LatLng($(this).parent().attr('data-y'), $(this).parent().attr('data-x')), 5);
});

size(map);
$(window).resize(function () {
    size(map);
});

window.map = map;
window.markers = markers;