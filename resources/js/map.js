import { Map, Marker, NavigationControl, Popup, setWorkerUrl } from 'maplibre-gl';
import maplibreWorkerUrl from 'maplibre-gl/dist/maplibre-gl-worker.mjs?worker&url';

setWorkerUrl(maplibreWorkerUrl);

export function mountInvestmentMap() {
    const node = document.getElementById('investment-map');

    if (!node || node.dataset.ready === '1') {
        return;
    }

    node.dataset.ready = '1';
    const status = document.getElementById('map-status');
    const map = new Map({
        container: node,
        style: 'https://tiles.openfreemap.org/styles/liberty',
        center: [-1.02, 7.95],
        zoom: 5.6,
        attributionControl: true,
    });

    map.addControl(new NavigationControl(), 'top-right');

    const setStatus = (text) => {
        if (status) {
            status.textContent = text;
        }
    };

    const loadViewport = () => {
        const bounds = map.getBounds();
        const params = new URLSearchParams(new FormData(document.getElementById('map-filters')));
        params.set('minLng', String(bounds.getWest()));
        params.set('minLat', String(bounds.getSouth()));
        params.set('maxLng', String(bounds.getEast()));
        params.set('maxLat', String(bounds.getNorth()));

        fetch(`${node.dataset.geojson}?${params.toString()}`, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('map');
                }

                return response.json();
            })
            .then((geojson) => {
                const source = map.getSource('opportunities');

                if (source) {
                    source.setData(geojson);
                }

                setStatus(`${geojson.features?.length || 0} opportunities in this view.`);
            })
            .catch(() => {
                setStatus('The map could not load opportunities for this view.');
            });
    };

    const start = () => {
        if (map.getSource('opportunities')) {
            return;
        }

        try {
        map.addSource('opportunities', {
            type: 'geojson',
            data: { type: 'FeatureCollection', features: [] },
            cluster: true,
            clusterRadius: 48,
        });
        map.addLayer({
            id: 'clusters',
            type: 'circle',
            source: 'opportunities',
            filter: ['has', 'point_count'],
            paint: {
                'circle-color': '#0f6e56',
                'circle-radius': 18,
            },
        });
        map.addLayer({
            id: 'cluster-count',
            type: 'symbol',
            source: 'opportunities',
            filter: ['has', 'point_count'],
            layout: {
                'text-field': ['get', 'point_count_abbreviated'],
                'text-size': 12,
            },
            paint: { 'text-color': '#ffffff' },
        });
        map.addLayer({
            id: 'points',
            type: 'circle',
            source: 'opportunities',
            filter: ['!', ['has', 'point_count']],
            paint: {
                'circle-color': '#d4a017',
                'circle-radius': 7,
                'circle-stroke-width': 2,
                'circle-stroke-color': '#0f172a',
            },
        });

        map.on('click', 'clusters', (event) => {
            const feature = event.features?.[0];
            const clusterId = feature?.properties?.cluster_id;
            const source = map.getSource('opportunities');

            if (!feature || clusterId === undefined || !source?.getClusterExpansionZoom) {
                return;
            }

            source.getClusterExpansionZoom(clusterId).then((zoom) => {
                map.easeTo({
                    center: feature.geometry.coordinates,
                    zoom,
                });
            });
        });

        map.on('click', 'points', (event) => {
            const feature = event.features?.[0];

            if (!feature) {
                return;
            }

            const properties = feature.properties || {};
            const popup = document.createElement('div');
            popup.className = 'opportunity-popup';
            const title = document.createElement('strong');
            title.textContent = properties.title || '';
            const place = document.createElement('p');
            place.textContent = [properties.district, properties.region].filter(Boolean).join(', ');
            const amount = document.createElement('p');
            amount.textContent = properties.amount || '';
            const link = document.createElement('a');
            link.href = properties.url || '#';
            link.textContent = 'View and inquire';
            popup.append(title, place, amount, link);
            new Popup({ className: 'opportunity-popup', closeButton: true }).setLngLat(event.lngLat).setDOMContent(popup).addTo(map);
        });

        loadViewport();
        } catch (error) {
            setStatus(error instanceof Error ? error.message : 'The map layer could not be added.');
        }
    };

    map.on('error', (event) => {
        const message = event?.error?.message;

        if (message) {
            setStatus(message);
        }
    });

    map.on('load', start);

    map.on('moveend', () => {
        if (map.getSource('opportunities')) {
            loadViewport();
        }
    });
    document.getElementById('map-apply')?.addEventListener('click', loadViewport);
    document.getElementById('map-locate')?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            setStatus('This browser cannot share a location.');

            return;
        }

        navigator.geolocation.getCurrentPosition((position) => {
            const lng = position.coords.longitude;
            const lat = position.coords.latitude;
            new Marker({ color: '#0369a1' }).setLngLat([lng, lat]).addTo(map);
            const params = new URLSearchParams(new FormData(document.getElementById('map-filters')));
            params.set('lng', String(lng));
            params.set('lat', String(lat));
            params.set('metres', '25000');
            fetch(`${node.dataset.nearby}?${params.toString()}`, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
                .then((response) => response.json())
                .then((geojson) => {
                    map.getSource('opportunities')?.setData(geojson);
                    map.flyTo({ center: [lng, lat], zoom: 9 });
                    setStatus(`${geojson.features?.length || 0} opportunities within 25 km. The radius is a technical limit until the business distance is confirmed.`);
                })
                .catch(() => setStatus('Nearby opportunities could not be loaded.'));
        }, () => setStatus('Location permission was not granted.'));
    });
}
