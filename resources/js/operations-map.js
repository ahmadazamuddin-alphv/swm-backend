(() => {
    const leaflet = () => window.L;

    const createPopup = (point) => {
        const content = document.createElement('div');
        content.className = 'swm-map-popup';

        const title = document.createElement('strong');
        title.textContent = point.title;
        content.append(title);

        if (point.description) {
            const description = document.createElement('span');
            description.textContent = point.description;
            content.append(description);
        }

        if (point.meta) {
            const meta = document.createElement('small');
            meta.textContent = point.meta;
            content.append(meta);
        }

        if (point.url && point.action) {
            const link = document.createElement('a');
            link.href = point.url;
            link.textContent = point.action;
            content.append(link);
        }

        return content;
    };

    const markerIcon = (point) => leaflet().divIcon({
        className: [
            'swm-leaflet-marker',
            `swm-leaflet-marker-${point.kind ?? 'report'}`,
            `swm-leaflet-marker-${point.tone ?? 'standard'}`,
            point.selected ? 'is-selected' : '',
        ].filter(Boolean).join(' '),
        html: point.label
            ? `<span data-label="${point.label}">${point.label}</span>`
            : '<span></span>',
        iconSize: [30, 30],
        iconAnchor: [15, 15],
        popupAnchor: [0, -13],
    });

    const callLivewire = (element, method, param) => {
        const root = element.closest('[wire\\:id]');
        if (!root || !window.Livewire) return;
        window.Livewire.find(root.getAttribute('wire:id'))?.call(method, param);
    };

    class OperationsMap extends HTMLElement {
        connectedCallback() {
            if (this.map || !leaflet()) {
                if (!leaflet() && this.querySelector('[data-map-status]')) {
                    this.querySelector('[data-map-status]').textContent = 'Map library is still loading…';
                    this.querySelector('[data-map-status]').hidden = false;
                    window.setTimeout(() => {
                        if (!this.map && leaflet()) this.connectedCallback();
                    }, 200);
                }
                return;
            }

            const dataNode = this.querySelector('script[type="application/json"]');
            const canvas = this.querySelector('[data-map-canvas]');
            const status = this.querySelector('[data-map-status]');
            if (!dataNode || !canvas) return;

            let data;
            try {
                data = JSON.parse(dataNode.textContent);
            } catch {
                if (status) status.textContent = 'The map data could not be loaded.';
                return;
            }

            const points = (data.points ?? []).filter((point) =>
                Number.isFinite(point.latitude) && Number.isFinite(point.longitude),
            );
            if (points.length === 0) {
                if (status) status.textContent = 'No valid coordinates are available.';
                return;
            }

            this.map = leaflet().map(canvas, {
                scrollWheelZoom: false,
                zoomControl: true,
                attributionControl: true,
            });

            let tileFailed = false;
            leaflet().tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>',
            })
                .on('tileerror', () => {
                    if (tileFailed || !status) return;
                    tileFailed = true;
                    status.textContent = 'Base-map tiles are unavailable. The plotted locations remain visible.';
                    status.hidden = false;
                })
                .addTo(this.map);

            const coordinates = [];
            const byId = new Map();
            points.forEach((point) => {
                const position = [point.latitude, point.longitude];
                coordinates.push(position);
                byId.set(point.id, position);
                const marker = leaflet().marker(position, { icon: markerIcon(point), title: point.title })
                    .addTo(this.map)
                    .bindPopup(createPopup(point));

                if (point.caseId != null) {
                    marker.on('click', () => callLivewire(this, 'selectCase', point.caseId));
                }
            });

            const lines = data.lines ?? [];
            const fallbackLayers = [];
            lines.forEach((line, index) => {
                const from = byId.get(line.from);
                const to = byId.get(line.to);
                if (!from || !to) return;

                fallbackLayers[index] = leaflet().polyline([from, to], {
                    color: line.tone === 'approach' ? '#606168' : '#a84848',
                    weight: line.tone === 'approach' ? 3 : 4,
                    opacity: data.routing ? 0.45 : 0.9,
                    dashArray: data.routing ? '6 8' : (line.tone === 'approach' ? '7 8' : null),
                }).addTo(this.map);
            });

            if (coordinates.length === 1) {
                this.map.setView(coordinates[0], 13);
            } else {
                this.map.fitBounds(coordinates, { padding: [30, 30], maxZoom: 14 });
            }

            const refreshSize = () => this.map?.invalidateSize();
            requestAnimationFrame(refreshSize);
            window.setTimeout(refreshSize, 100);
            window.setTimeout(refreshSize, 350);
            if (status && !tileFailed) status.hidden = true;

            if (data.routing?.endpoint && lines.length > 0) {
                this.loadRoadRoutes(data.routing.endpoint, lines, byId, fallbackLayers);
            }
        }

        async loadRoadRoutes(endpoint, lines, byId, fallbackLayers) {
            const routeStatus = this.querySelector('[data-map-route-status]');
            this.routeController = new AbortController();
            const timeout = window.setTimeout(() => this.routeController?.abort(), 10000);

            try {
                const results = await Promise.allSettled(lines.map(async (line, index) => {
                    const from = byId.get(line.from);
                    const to = byId.get(line.to);
                    if (!from || !to) throw new Error('Missing route waypoint');

                    const coordinates = `${from[1]},${from[0]};${to[1]},${to[0]}`;
                    const url = `${endpoint.replace(/\/$/, '')}/${coordinates}?overview=full&geometries=geojson&steps=false`;
                    const response = await fetch(url, {
                        headers: { Accept: 'application/json' },
                        signal: this.routeController.signal,
                    });
                    if (!response.ok) throw new Error(`Routing request failed with ${response.status}`);

                    const payload = await response.json();
                    const route = payload.code === 'Ok' ? payload.routes?.[0] : null;
                    if (!route?.geometry?.coordinates?.length) throw new Error('No road route returned');
                    if (!this.isConnected || !this.map) throw new Error('Map disconnected');

                    const routeCoordinates = route.geometry.coordinates.map(([longitude, latitude]) => [latitude, longitude]);
                    fallbackLayers[index]?.remove();
                    leaflet().polyline(routeCoordinates, {
                        color: line.tone === 'approach' ? '#52525b' : '#a84848',
                        weight: line.tone === 'approach' ? 4 : 5,
                        opacity: 0.95,
                        lineCap: 'round',
                        lineJoin: 'round',
                    }).addTo(this.map);

                    return { coordinates: routeCoordinates, distance: route.distance, duration: route.duration };
                }));

                const routes = results.filter((result) => result.status === 'fulfilled').map((result) => result.value);
                if (!this.isConnected || !this.map) return;
                if (routes.length === 0) {
                    if (routeStatus) routeStatus.textContent = 'Road routing unavailable · showing straight-line fallback';
                    return;
                }

                const distance = routes.reduce((total, route) => total + route.distance, 0) / 1000;
                const duration = Math.max(1, Math.round(routes.reduce((total, route) => total + route.duration, 0) / 60));
                const prefix = routes.length === lines.length ? 'Road route' : 'Partial road route';
                if (routeStatus) routeStatus.textContent = `${prefix} · ${distance.toFixed(1)} km · about ${duration} min`;

                const routedCoordinates = routes.flatMap((route) => route.coordinates);
                this.map.fitBounds(routedCoordinates, { padding: [30, 30], maxZoom: 14 });
            } catch (error) {
                if (error.name !== 'AbortError' && routeStatus) {
                    routeStatus.textContent = 'Road routing unavailable · showing straight-line fallback';
                }
            } finally {
                window.clearTimeout(timeout);
            }
        }

        disconnectedCallback() {
            this.routeController?.abort();
            this.map?.remove();
            this.map = null;
        }
    }

    if (!customElements.get('swm-operations-map')) {
        customElements.define('swm-operations-map', OperationsMap);
    }
})();
