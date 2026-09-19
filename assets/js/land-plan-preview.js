'use strict';

(function (global) {
    const MAX_W = 280;
    const MAX_H = 160;
    const LABEL_SPACE = 28;
    const FILL = '#ecfdf5';
    const STROKE = '#1b4d24';
    const SVG_NS = 'http://www.w3.org/2000/svg';

    const unitLabels = {
        m2: 'm²',
        ha: 'ha',
        are: 'are',
    };

    const formatNumber = (value) => {
        if (!Number.isFinite(value)) {
            return '';
        }
        return String(Math.round(value * 100) / 100).replace('.', ',');
    };

    const formatAreaDisplay = (area, unit) => {
        const label = unitLabels[unit] || unit;
        return `${formatNumber(area)} ${label}`;
    };

    const createSvgText = (x, y, text, className, attrs = {}) => {
        const node = document.createElementNS(SVG_NS, 'text');
        node.setAttribute('x', String(x));
        node.setAttribute('y', String(y));
        node.setAttribute('class', className);
        Object.entries(attrs).forEach(([key, value]) => {
            node.setAttribute(key, value);
        });
        node.textContent = text;
        return node;
    };

    const renderLandPlan = (container, opts) => {
        if (!container) {
            return;
        }

        const length = parseFloat(opts.length || '0');
        const width = parseFloat(opts.width || '0');
        const unit = opts.unit || 'm2';
        const squareMeters = Number.isFinite(length) && Number.isFinite(width) && length > 0 && width > 0
            ? length * width
            : 0;

        let area = parseFloat(opts.area);
        if (!Number.isFinite(area) || area <= 0) {
            area = squareMeters;
        }

        if (unit === 'ha' && squareMeters > 0) {
            area = squareMeters / 10000;
        } else if (unit === 'are' && squareMeters > 0) {
            area = squareMeters / 100;
        } else if (squareMeters > 0) {
            area = squareMeters;
        }

        const emptyEl = container.querySelector('.land-plan-empty');
        const svgEl = container.querySelector('.land-plan-svg');

        if (!Number.isFinite(length) || !Number.isFinite(width) || length <= 0 || width <= 0) {
            if (emptyEl) {
                emptyEl.hidden = false;
                emptyEl.textContent = container.dataset.emptyText || opts.emptyText || '';
            }
            if (svgEl) {
                svgEl.hidden = true;
                while (svgEl.firstChild) {
                    svgEl.removeChild(svgEl.firstChild);
                }
            }
            return;
        }

        if (emptyEl) {
            emptyEl.hidden = true;
        }
        if (!svgEl) {
            return;
        }

        svgEl.hidden = false;

        const innerW = MAX_W - LABEL_SPACE;
        const innerH = MAX_H - LABEL_SPACE;
        const scale = Math.min(innerW / length, innerH / width);
        const rectW = length * scale;
        const rectH = width * scale;
        const svgW = rectW + LABEL_SPACE;
        const svgH = rectH + LABEL_SPACE;
        const offsetX = LABEL_SPACE / 2;
        const offsetY = 6;

        const areaText = formatAreaDisplay(area, unit);

        svgEl.setAttribute('viewBox', `0 0 ${svgW} ${svgH}`);
        svgEl.setAttribute('width', String(Math.round(svgW)));
        svgEl.setAttribute('height', String(Math.round(svgH)));

        while (svgEl.firstChild) {
            svgEl.removeChild(svgEl.firstChild);
        }

        const rect = document.createElementNS(SVG_NS, 'rect');
        rect.setAttribute('x', String(offsetX));
        rect.setAttribute('y', String(offsetY));
        rect.setAttribute('width', String(rectW));
        rect.setAttribute('height', String(rectH));
        rect.setAttribute('fill', FILL);
        rect.setAttribute('stroke', STROKE);
        rect.setAttribute('stroke-width', '2');
        rect.setAttribute('rx', '3');
        svgEl.appendChild(rect);

        svgEl.appendChild(createSvgText(
            offsetX + rectW / 2,
            offsetY + rectH / 2,
            areaText,
            'land-plan-area-text',
            { 'text-anchor': 'middle', 'dominant-baseline': 'middle' },
        ));

        svgEl.appendChild(createSvgText(
            offsetX + rectW / 2,
            offsetY + rectH + 18,
            `${formatNumber(length)} m`,
            'land-plan-dim-text',
            { 'text-anchor': 'middle' },
        ));

        svgEl.appendChild(createSvgText(
            offsetX - 6,
            offsetY + rectH / 2,
            `${formatNumber(width)} m`,
            'land-plan-dim-text',
            {
                'text-anchor': 'end',
                'dominant-baseline': 'middle',
                transform: `rotate(-90, ${offsetX - 6}, ${offsetY + rectH / 2})`,
            },
        ));
    };

    const syncLivePreview = (container) => {
        const lengthInput = document.getElementById('hostLandLength');
        const widthInput = document.getElementById('hostLandWidth');
        const unitSelect = document.getElementById('hostLandAreaUnit');
        const areaInput = document.getElementById('hostLandArea');

        const length = parseFloat(lengthInput?.value || '0');
        const width = parseFloat(widthInput?.value || '0');
        const unit = unitSelect?.value || 'm2';
        const area = parseFloat(areaInput?.value || '0');

        renderLandPlan(container, {
            length,
            width,
            area: Number.isFinite(area) && area > 0 ? area : undefined,
            unit,
        });
    };

    const initStaticPreviews = () => {
        document.querySelectorAll('.land-plan-preview[data-land-plan="static"]').forEach((container) => {
            renderLandPlan(container, {
                length: container.dataset.length,
                width: container.dataset.width,
                area: container.dataset.area,
                unit: container.dataset.unit || 'm2',
            });
        });
    };

    const initLivePreviews = () => {
        document.querySelectorAll('.land-plan-preview[data-land-plan="live"]').forEach((container) => {
            const lengthInput = document.getElementById('hostLandLength');
            const widthInput = document.getElementById('hostLandWidth');
            const unitSelect = document.getElementById('hostLandAreaUnit');

            syncLivePreview(container);

            [lengthInput, widthInput, unitSelect].forEach((field) => {
                if (!field) {
                    return;
                }
                field.addEventListener('input', () => syncLivePreview(container));
                field.addEventListener('change', () => syncLivePreview(container));
            });
        });
    };

    const initAll = () => {
        initStaticPreviews();
        initLivePreviews();
    };

    global.renderLandPlan = renderLandPlan;
    global.syncLiveLandPlan = syncLivePreview;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})(window);
