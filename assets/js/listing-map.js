'use strict';

document.addEventListener('DOMContentLoaded', () => {
    if (typeof L === 'undefined') {
        return;
    }

    document.querySelectorAll('[data-listing-map]').forEach((element) => {
        const lat = Number.parseFloat(element.dataset.lat || '');
        const lng = Number.parseFloat(element.dataset.lng || '');
        const zoom = Number.parseInt(element.dataset.zoom || '17', 10);

        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            return;
        }

        const label = (element.dataset.label || '').trim();

        const map = L.map(element, {
            scrollWheelZoom: false,
            zoomControl: true,
            attributionControl: true,
        }).setView([lat, lng], Number.isFinite(zoom) ? zoom : 18);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a>',
            maxZoom: 19,
        }).addTo(map);

        const icon = L.divIcon({
            className: 'listing-map-marker-wrap',
            html: '<span class="listing-map-pin" aria-hidden="true"></span>',
            iconSize: [36, 44],
            iconAnchor: [18, 44],
            popupAnchor: [0, -40],
        });

        const marker = L.marker([lat, lng], { icon, alt: 'Position exacte du logement' }).addTo(map);
        if (label !== '') {
            marker.bindPopup(label).openPopup();
        }

        window.requestAnimationFrame(() => {
            map.invalidateSize();
        });
    });
});
