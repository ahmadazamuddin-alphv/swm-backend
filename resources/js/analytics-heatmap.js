(() => {
    class AnalyticsHeatmap extends HTMLElement {
        connectedCallback() {
            if (this.ready) return;
            this.ready = true;
            const data = this.querySelector('script[type="application/json"]');
            const canvas = this.querySelector('[data-analytics-map-canvas]');
            if (!data || !canvas) return;
            try { this.points = JSON.parse(data.textContent); } catch { return; }
            this.start(canvas);
        }

        start(canvas) {
            if (!window.L) { window.setTimeout(() => this.start(canvas), 120); return; }
            const L = window.L;
            const map = this.map = L.map(canvas, { scrollWheelZoom: false, attributionControl: true });
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
            const locations = [];
            this.points.forEach((point) => {
                const risk = Number(point.risk || 0);
                const color = risk >= 80 ? '#d2222b' : risk >= 60 ? '#f08c00' : '#06b6d4';
                const latlng = [point.lat, point.lng];
                locations.push(latlng);
                L.circle(latlng, { radius: 310 + risk * 4, color, weight: 0, fillColor: color, fillOpacity: .09, interactive: false }).addTo(map);
                L.circle(latlng, { radius: 150 + risk * 2, color, weight: 0, fillColor: color, fillOpacity: .14, interactive: false }).addTo(map);
                L.circleMarker(latlng, { radius: 6, color: '#fff', weight: 2, fillColor: color, fillOpacity: 1 }).addTo(map)
                    .bindPopup(`<strong>${point.label}</strong><br>${point.area}<br><small>Risk ${risk}</small>`);
            });
            if (locations.length === 1) map.setView(locations[0], 13);
            else map.fitBounds(locations, { padding: [32, 32], maxZoom: 12 });
            requestAnimationFrame(() => map.invalidateSize());
        }

        disconnectedCallback() { this.map?.remove(); }
    }

    if (!customElements.get('swm-analytics-heatmap')) customElements.define('swm-analytics-heatmap', AnalyticsHeatmap);
})();
