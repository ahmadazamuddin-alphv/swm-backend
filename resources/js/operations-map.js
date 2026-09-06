(() => {
    const leaflet = () => window.L;
    const maplibre = () => window.maplibregl;
    const mapStyle = 'https://tiles.openfreemap.org/styles/bright';

    const toneColor = (tone) => ({
        high: '#D2222B', elevated: '#FDB915', standard: '#06B6D4',
        disposal: '#FDB915', gdp: '#D2222B', rakyat: '#FDB915', balanced: '#16A34A',
    }[tone] ?? '#06B6D4');

    const createPopup = (point) => {
        const content = document.createElement('div');
        content.className = 'swm-map-popup';
        const title = document.createElement('strong');
        title.textContent = point.title;
        content.append(title);
        [point.description, point.meta].filter(Boolean).forEach((value, index) => {
            const line = document.createElement(index === 0 ? 'span' : 'small');
            line.textContent = value;
            content.append(line);
        });
        if (point.url && point.action) {
            const link = document.createElement('a');
            link.href = point.url;
            link.textContent = point.action;
            content.append(link);
        }
        return content;
    };

    const markerIcon = (point) => leaflet().divIcon({
        className: ['swm-leaflet-marker', `swm-leaflet-marker-${point.kind ?? 'report'}`,
            `swm-leaflet-marker-${point.tone ?? 'standard'}`, point.selected ? 'is-selected' : ''].filter(Boolean).join(' '),
        html: point.label ? `<span data-label="${point.label}">${point.label}</span>` : '<span></span>',
        iconSize: [30, 30], iconAnchor: [15, 15], popupAnchor: [0, -13],
    });

    const callLivewire = (element, method, param) => {
        const root = element.closest('[wire\\:id]');
        if (root && window.Livewire) window.Livewire.find(root.getAttribute('wire:id'))?.call(method, param);
    };

    class OperationsMap extends HTMLElement {
        connectedCallback() {
            if (this.ready) return;
            this.ready = true;
            const node = this.querySelector('script[type="application/json"]');
            this.canvas = this.querySelector('[data-map-canvas]');
            this.status = this.querySelector('[data-map-status]');
            if (!node || !this.canvas) return;
            try { this.data = JSON.parse(node.textContent); } catch { this.showStatus('The map data could not be loaded.'); return; }
            this.points = (this.data.points ?? []).filter((point) => Number.isFinite(point.latitude) && Number.isFinite(point.longitude));
            if (!this.points.length) { this.showStatus('No valid coordinates are available.'); return; }
            if (maplibre()) this.init3d();
            else this.init2d();
        }

        init2d() {
            if (!leaflet()) { this.showStatus('Map is still loading…'); window.setTimeout(() => this.init2d(), 200); return; }
            this.map2d = leaflet().map(this.canvas, { scrollWheelZoom: false, zoomControl: true, attributionControl: true });
            leaflet().tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(this.map2d);
            const positions = [];
            const byId = new Map();
            this.points.forEach((point) => {
                const position = [point.latitude, point.longitude];
                positions.push(position); byId.set(point.id, position);
                const marker = leaflet().marker(position, { icon: markerIcon(point), title: point.title }).addTo(this.map2d).bindPopup(createPopup(point));
                if (point.caseId != null) marker.on('click', () => callLivewire(this, 'selectCase', point.caseId));
            });
            (this.data.lines ?? []).forEach((line) => {
                const from = byId.get(line.from); const to = byId.get(line.to);
                if (from && to) leaflet().polyline([from, to], { color: toneColor(line.tone), weight: 4, opacity: .8, dashArray: '6 8' }).addTo(this.map2d);
            });
            if (positions.length === 1) this.map2d.setView(positions[0], 13);
            else this.map2d.fitBounds(positions, { padding: [30, 30], maxZoom: 14 });
            requestAnimationFrame(() => this.map2d?.invalidateSize());
            this.hideStatus();
        }

        init3d() {
            const MapLibre = maplibre();
            if (!MapLibre) { this.init2d(); return; }
            this.map3d = new MapLibre.Map({
                container: this.canvas, style: mapStyle, center: [101.52, 3.07], zoom: 10.8,
                pitch: 56, bearing: -18, maxPitch: 75, attributionControl: true,
            });
            this.map3d.addControl(new MapLibre.NavigationControl({ visualizePitch: true }), 'top-right');
            this.map3d.on('load', () => {
                this.install3dLayers();
                this.fit3d();
                this.hideStatus();
            });
            this.map3d.on('error', () => this.showStatus('The map base is temporarily unavailable. Refresh to retry.'));
        }

        install3dLayers() {
            const map = this.map3d;
            map.addSource('swm-districts', { type: 'geojson', data: '/geo/selangor-districts.geojson' });
            map.addLayer({ id: 'swm-district-fill', type: 'fill', source: 'swm-districts', paint: {
                'fill-color': ['match', ['get', 'Daerah'], 'KLANG', '#f28482', 'PETALING', '#a9def9', 'GOMBAK', '#90dbf4', 'HULU LANGAT', '#f6bd60', 'KUALA LANGAT', '#cdb4db', 'HULU SELANGOR', '#b8e0d2', 'KUALA SELANGOR', '#f5cac3', 'SABAK BERNAM', '#84a59d', 'SEPANG', '#f7ede2', '#f7ede2'],
                'fill-opacity': .38,
            }});
            map.addLayer({ id: 'swm-district-line', type: 'line', source: 'swm-districts', paint: { 'line-color': '#7c2d12', 'line-width': 1.25, 'line-opacity': .7 } });
            const building = map.getStyle().layers.find((layer) => layer.type === 'fill' && layer['source-layer'] === 'building' && layer.source);
            if (building) map.addLayer({ id: 'swm-buildings-3d', type: 'fill-extrusion', source: building.source, 'source-layer': building['source-layer'], minzoom: 14, paint: { 'fill-extrusion-color': '#d6c6b1', 'fill-extrusion-height': ['coalesce', ['get', 'render_height'], 5], 'fill-extrusion-base': ['coalesce', ['get', 'render_min_height'], 0], 'fill-extrusion-opacity': .78 } });
            map.addSource('swm-cases', { type: 'geojson', data: { type: 'FeatureCollection', features: this.points.map((point) => ({ type: 'Feature', geometry: { type: 'Point', coordinates: [point.longitude, point.latitude] }, properties: { ...point } })) } });
            const colors = ['match', ['get', 'tone'], 'high', '#D2222B', 'elevated', '#FDB915', 'gdp', '#D2222B', 'rakyat', '#FDB915', 'balanced', '#16A34A', 'disposal', '#FDB915', '#06B6D4'];
            map.addLayer({ id: 'swm-case-halos', type: 'circle', source: 'swm-cases', paint: { 'circle-radius': 13, 'circle-color': colors, 'circle-opacity': .25, 'circle-pitch-scale': 'map' } });
            map.addLayer({ id: 'swm-case-points', type: 'circle', source: 'swm-cases', paint: { 'circle-radius': 7, 'circle-color': colors, 'circle-stroke-color': '#ffffff', 'circle-stroke-width': 2, 'circle-pitch-scale': 'map' } });
            map.addLayer({ id: 'swm-case-counts', type: 'symbol', source: 'swm-cases', filter: ['has', 'label'], layout: { 'text-field': ['get', 'label'], 'text-size': 11, 'text-allow-overlap': true, 'text-ignore-placement': true }, paint: { 'text-color': '#ffffff' } });
            (this.data.lines ?? []).forEach((line, index) => {
                const from = this.points.find((point) => point.id === line.from); const to = this.points.find((point) => point.id === line.to);
                if (!from || !to) return;
                const id = `swm-line-${index}`;
                map.addSource(id, { type: 'geojson', data: { type: 'Feature', geometry: { type: 'LineString', coordinates: [[from.longitude, from.latitude], [to.longitude, to.latitude]] }, properties: {} } });
                map.addLayer({ id, type: 'line', source: id, paint: { 'line-color': toneColor(line.tone), 'line-width': 3, 'line-opacity': .8, 'line-dasharray': [2, 2] } });
            });
            map.on('mouseenter', 'swm-case-points', () => { map.getCanvas().style.cursor = 'pointer'; });
            map.on('mouseleave', 'swm-case-points', () => { map.getCanvas().style.cursor = ''; });
            map.on('click', 'swm-case-points', (event) => {
                const id = event.features?.[0]?.properties?.id;
                const point = this.points.find((candidate) => candidate.id === id);
                if (!point) return;
                if (point.caseId != null) callLivewire(this, 'selectCase', point.caseId);
                new MapLibre.Popup({ offset: 12, closeButton: false }).setLngLat([point.longitude, point.latitude]).setDOMContent(createPopup(point)).addTo(map);
            });
        }

        fit3d() {
            const bounds = this.points.reduce((box, point) => box.extend([point.longitude, point.latitude]), new (maplibre().LngLatBounds)());
            if (this.points.length === 1) this.map3d.flyTo({ center: [this.points[0].longitude, this.points[0].latitude], zoom: 14.2, pitch: 58, bearing: -20, duration: 700 });
            else this.map3d.fitBounds(bounds, { padding: 48, maxZoom: 13.8, pitch: 54, bearing: -18, duration: 0 });
        }

        showStatus(message) { if (this.status) { this.status.textContent = message; this.status.hidden = false; } }
        hideStatus() { if (this.status) this.status.hidden = true; }
        destroyMap() { this.map2d?.remove(); this.map3d?.remove(); this.map2d = null; this.map3d = null; }
        disconnectedCallback() { this.destroyMap(); }
    }

    if (!customElements.get('swm-operations-map')) customElements.define('swm-operations-map', OperationsMap);
})();
