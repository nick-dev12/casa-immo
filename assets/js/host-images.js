'use strict';

(function () {
    const grid = document.getElementById('hostImagesGrid');
    const input = document.getElementById('hostImagesInput');
    const addCard = document.getElementById('hostImageAdd');
    const counter = document.getElementById('hostImagesCounter');
    const removeInputs = document.getElementById('hostRemoveImageInputs');
    const form = document.querySelector('.host-form-simple[data-host-managed="1"]');

    if (!grid || !input || !addCard || !counter || !removeInputs || !form) {
        return;
    }

    window.__hostImagesReady = true;

    const min = parseInt(counter.dataset.min || '4', 10);
    const max = parseInt(counter.dataset.max || '10', 10);
    const uploadUrl = form.dataset.imageUploadUrl || '';
    const uploadsBase = (form.dataset.uploadsBase || document.body.dataset.appUrl || '').replace(/\/$/, '');
    const csrfToken = form.querySelector('[name="_token"]')?.value || '';
    const msgCompressing = counter.dataset.msgCompressing || 'Optimisation…';
    const msgUploading = counter.dataset.msgUploading || 'Envoi…';
    const msgCompressError = counter.dataset.msgCompressError || 'Impossible de traiter une photo.';
    const msgUploadError = counter.dataset.msgUploadError || 'Erreur lors de l\'envoi d\'une photo.';
    const msgSubmitError = counter.dataset.msgSubmitError || 'Impossible d\'enregistrer l\'annonce.';
    const msgWaitPhotos = counter.dataset.msgWaitPhotos || 'Enregistrement des photos en cours…';

    /** @type {Map<string, { id: string, status: string, file?: File, task?: Promise<void>, previewUrl?: string, removed?: boolean }>} */
    const queue = new Map();
    /** @type {Set<Promise<void>>} */
    const backgroundTasks = new Set();
    let queueSeq = 0;
    let isSubmitting = false;
    let persistedImageCount = parseInt(counter.dataset.initialCount || '0', 10);

    const compressor = () => window.HostImageCompress;

    const countExistingInDom = () => grid.querySelectorAll('.host-image-card-existing:not([hidden])').length;

    const queuedCount = () => {
        let count = 0;
        queue.forEach((item) => {
            if (!item.removed && item.status !== 'saved') {
                count += 1;
            }
        });
        return count;
    };

    const readyFiles = () => {
        /** @type {File[]} */
        const files = [];
        queue.forEach((item) => {
            if (!item.removed && item.status === 'ready' && item.file) {
                files.push(item.file);
            }
        });
        return files;
    };

    const totalCount = () => {
        const domExisting = countExistingInDom();
        if (domExisting > persistedImageCount) {
            persistedImageCount = domExisting;
        }
        return persistedImageCount + queuedCount();
    };

    const remainingSlots = () => Math.max(0, max - totalCount());

    const isPublishSubmitter = (submitter) => submitter && (
        submitter.getAttribute('formaction')?.includes('/publish')
        || (submitter.name === 'intent' && submitter.value === 'publish')
    );

    const trackTask = (promise) => {
        backgroundTasks.add(promise);
        promise.finally(() => backgroundTasks.delete(promise));
        return promise;
    };

    const waitForBackground = () => Promise.all([...backgroundTasks]);

    const stripImageFields = (fd) => {
        while (fd.has('images[]')) {
            fd.delete('images[]');
        }
    };

    const buildFormData = (submitter) => {
        const reenable = [];
        form.querySelectorAll('[name]:disabled').forEach((field) => {
            reenable.push(field);
            field.disabled = false;
        });

        const fd = new FormData(form);
        stripImageFields(fd);

        reenable.forEach((field) => {
            field.disabled = true;
        });

        if (submitter?.name === 'intent') {
            fd.set('intent', submitter.value);
        }

        return fd;
    };

    const setPublishLoading = (active) => {
        const btn = document.getElementById('hostPublishBtn');
        if (!btn) {
            return;
        }
        btn.disabled = active;
        btn.textContent = active
            ? (btn.dataset.labelLoading || 'Publication en cours…')
            : (btn.dataset.labelDefault || 'Publier');
    };

    const updateCounter = () => {
        const total = totalCount();
        const busy = queuedCount() > 0;
        counter.textContent = total + ' / ' + max + ' photos';
        counter.classList.toggle('is-valid', total >= min && !busy);
        counter.classList.toggle('is-invalid', total > 0 && total < min);
        counter.classList.toggle('is-busy', busy);

        if (total >= max || isSubmitting) {
            addCard.classList.add('is-disabled');
        } else {
            addCard.classList.remove('is-disabled');
        }
    };

    const setCardStatus = (card, status, label) => {
        if (!card) {
            return;
        }
        card.dataset.queueStatus = status;
        let overlay = card.querySelector('.host-image-status');
        if (!overlay) {
            overlay = document.createElement('span');
            overlay.className = 'host-image-status';
            card.appendChild(overlay);
        }
        overlay.textContent = label || '';
        overlay.hidden = !label;
        card.classList.toggle('is-queue-error', status === 'error');
        card.classList.toggle('is-queue-busy', status === 'compressing' || status === 'uploading');
    };

    const createExistingCard = (image) => {
        const card = document.createElement('article');
        card.className = 'host-image-card host-image-card-existing';
        card.dataset.imageId = String(image.id);

        const img = document.createElement('img');
        img.alt = '';
        img.src = image.url || (uploadsBase + '/' + String(image.path || '').replace(/^\//, ''));

        if (image.is_primary) {
            const badge = document.createElement('span');
            badge.className = 'host-image-badge';
            badge.textContent = counter.dataset.labelPrimary || 'Principale';
            card.appendChild(badge);
        }

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'host-image-remove';
        btn.setAttribute('data-remove-existing', String(image.id));
        btn.setAttribute('aria-label', counter.dataset.labelRemove || 'Retirer');
        btn.innerHTML = '<i class="bi bi-x-lg"></i>';

        card.appendChild(img);
        card.appendChild(btn);
        return card;
    };

    const createQueueCard = (itemId, previewUrl) => {
        const card = document.createElement('article');
        card.className = 'host-image-card host-image-card-preview';
        card.dataset.queueId = itemId;

        const img = document.createElement('img');
        img.alt = '';
        img.src = previewUrl;

        const badge = document.createElement('span');
        badge.className = 'host-image-format-badge';
        badge.textContent = 'WebP';

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'host-image-remove';
        btn.setAttribute('aria-label', counter.dataset.labelRemove || 'Retirer');
        btn.innerHTML = '<i class="bi bi-x-lg"></i>';
        btn.addEventListener('click', () => removeQueueItem(itemId, card));

        card.appendChild(img);
        card.appendChild(badge);
        card.appendChild(btn);
        setCardStatus(card, 'compressing', msgCompressing);
        return card;
    };

    const removeQueueItem = (itemId, card) => {
        const item = queue.get(itemId);
        if (item) {
            item.removed = true;
        }
        if (item?.previewUrl) {
            URL.revokeObjectURL(item.previewUrl);
        }
        queue.delete(itemId);
        card.remove();
        updateCounter();
    };

    const promoteToExisting = (itemId, image, card) => {
        const item = queue.get(itemId);
        if (item?.previewUrl) {
            URL.revokeObjectURL(item.previewUrl);
        }
        const existing = createExistingCard(image);
        card.replaceWith(existing);
        queue.delete(itemId);
        if (typeof image.count === 'number') {
            persistedImageCount = image.count;
        } else {
            persistedImageCount += 1;
        }
        updateCounter();
    };

    const uploadFile = async (file, targetUrl) => {
        const body = new FormData();
        body.append('_token', csrfToken);
        body.append('images[]', file, file.name);

        const response = await fetch(targetUrl || uploadUrl, {
            method: 'POST',
            body,
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            credentials: 'same-origin',
        });

        let payload = {};
        try {
            payload = await response.json();
        } catch (err) {
            payload = {};
        }

        if (!response.ok || !payload.success) {
            throw new Error(payload.message || msgUploadError);
        }

        if (typeof payload.count === 'number') {
            persistedImageCount = payload.count;
        }

        return payload;
    };

    const processQueueItem = (itemId, file) => {
        const item = queue.get(itemId);
        if (!item || item.removed) {
            return Promise.resolve();
        }

        const card = grid.querySelector('[data-queue-id="' + itemId + '"]');
        const task = (async () => {
            const engine = compressor();
            if (!engine) {
                throw new Error(msgCompressError);
            }

            item.status = 'compressing';
            setCardStatus(card, 'compressing', msgCompressing);

            const webpFile = await engine.toWebp(file);
            if (item.removed) {
                return;
            }

            item.file = webpFile;
            if (card) {
                const img = card.querySelector('img');
                if (img) {
                    URL.revokeObjectURL(item.previewUrl || '');
                    item.previewUrl = URL.createObjectURL(webpFile);
                    img.src = item.previewUrl;
                }
            }

            if (uploadUrl) {
                item.status = 'uploading';
                setCardStatus(card, 'uploading', msgUploading);
                const payload = await uploadFile(webpFile, uploadUrl);
                if (item.removed) {
                    return;
                }
                if (payload.image && card) {
                    promoteToExisting(itemId, { ...payload.image, count: payload.count }, card);
                    return;
                }
                item.status = 'saved';
                persistedImageCount = payload.count ?? persistedImageCount;
                if (card) {
                    card.remove();
                }
                queue.delete(itemId);
            } else {
                item.status = 'ready';
                setCardStatus(card, 'ready', '');
            }
        })().catch((err) => {
            if (item.removed) {
                return;
            }
            item.status = 'error';
            setCardStatus(card, 'error', err.message || msgUploadError);
            throw err;
        }).finally(() => {
            updateCounter();
        });

        item.task = task;
        return trackTask(task);
    };

    const uploadReadyFiles = async (targetUrl) => {
        const endpoint = targetUrl || uploadUrl;
        if (!endpoint) {
            throw new Error(msgUploadError);
        }

        const files = readyFiles();
        for (const file of files) {
            await uploadFile(file, endpoint);
        }

        queue.forEach((item, itemId) => {
            if (item.status === 'ready') {
                const card = grid.querySelector('[data-queue-id="' + itemId + '"]');
                if (card) {
                    card.remove();
                }
                if (item.previewUrl) {
                    URL.revokeObjectURL(item.previewUrl);
                }
                queue.delete(itemId);
            }
        });

        updateCounter();
    };

    const submitForm = async (submitter) => {
        const fd = buildFormData(submitter);
        const action = submitter?.getAttribute('formaction') || form.getAttribute('action') || window.location.href;
        const isPublish = isPublishSubmitter(submitter);

        if (isPublish) {
            setPublishLoading(true);
        }

        try {
            const response = await fetch(action, {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            });

            let payload = {};
            try {
                payload = await response.json();
            } catch (err) {
                if (response.redirected) {
                    window.location.assign(response.url);
                    return;
                }
                throw new Error(msgSubmitError);
            }

            if (!response.ok || !payload.success) {
                throw new Error(payload.message || msgSubmitError);
            }

            if (payload.message && isPublish) {
                sessionStorage.setItem('hostFlashSuccess', payload.message);
            }

            const redirectUrl = payload.redirect
                || (isPublish ? form.dataset.publicListingUrl : '')
                || form.getAttribute('action')
                || window.location.href;

            window.location.assign(redirectUrl);
        } finally {
            if (isPublish) {
                setPublishLoading(false);
            }
        }
    };

    const extractListingIdFromUrl = (url) => {
        const match = String(url || '').match(/\/host\/(?:properties|lands)\/(\d+)/);
        return match ? parseInt(match[1], 10) : 0;
    };

    const resolveUploadUrl = (listingId, kind) => {
        if (uploadUrl) {
            return uploadUrl;
        }
        if (!listingId) {
            return '';
        }
        const base = form.getAttribute('action') || '';
        const root = base.replace(/\/host\/(?:properties|lands)(?:\/new|\/?)?$/, '');
        const segment = kind === 'terrain' ? 'lands' : 'properties';
        return root + '/host/' + segment + '/' + listingId + '/images';
    };

    const kindFromUrl = (url) => (String(url || '').includes('/host/lands/') ? 'terrain' : 'logement');

    const resolvePublishUrl = (listingId, kind) => {
        if (form.dataset.publishUrl) {
            return form.dataset.publishUrl;
        }
        if (!listingId) {
            return '';
        }
        const base = form.getAttribute('action') || '';
        const root = base.replace(/\/host\/(?:properties|lands)(?:\/new|\/?)?$/, '');
        const segment = kind === 'terrain' ? 'lands' : 'properties';
        return root + '/host/' + segment + '/' + listingId + '/publish';
    };

    const createListingThenUpload = async (submitter) => {
        const fd = buildFormData(submitter);
        const isPublish = isPublishSubmitter(submitter);
        if (isPublish) {
            fd.set('intent', 'save');
        }

        const response = await fetch(form.getAttribute('action') || '', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        });

        let payload = {};
        try {
            payload = await response.json();
        } catch (err) {
            payload = {};
        }

        if (!response.ok || payload.success === false) {
            throw new Error(payload.message || msgSubmitError);
        }

        const listingId = payload.listingId || extractListingIdFromUrl(payload.redirect || response.url);
        if (!listingId) {
            throw new Error(msgSubmitError);
        }

        const kind = kindFromUrl(payload.redirect || response.url);
        await uploadReadyFiles(resolveUploadUrl(listingId, kind));

        if (typeof globalThis.__hostUploadQueuedVideo === 'function' && kind !== 'terrain') {
            const videoUrl = resolveUploadUrl(listingId, kind).replace('/images', '/video');
            if (videoUrl.endsWith('/video')) {
                await globalThis.__hostUploadQueuedVideo(videoUrl);
            }
        }

        if (isPublish) {
            const publishAction = resolvePublishUrl(listingId, kind);
            await submitForm({ getAttribute: (name) => (name === 'formaction' ? publishAction : null) });
            return;
        }

        window.location.assign(payload.redirect || response.url);
    };

    const ensurePhotosReady = async () => {
        await waitForBackground();

        const errors = [...queue.values()].filter((item) => item.status === 'error' && !item.removed);
        if (errors.length > 0) {
            throw new Error(msgUploadError);
        }

        const pending = [...queue.values()].filter((item) => !item.removed && item.status !== 'saved' && item.status !== 'ready');
        if (pending.length > 0) {
            throw new Error(msgWaitPhotos);
        }
    };

    const handleSubmit = async (submitter) => {
        if (isSubmitting) {
            return;
        }

        const isPublish = isPublishSubmitter(submitter);
        if (isPublish) {
            try {
                await ensurePhotosReady();
            } catch (err) {
                alert(err.message || msgWaitPhotos);
                return;
            }
        }

        const total = totalCount();
        if (total > max) {
            alert('Maximum ' + max + ' photos autorisées.');
            return;
        }

        if (isPublish && total < min) {
            alert('Ajoutez au moins ' + min + ' photos avant de publier.');
            return;
        }

        isSubmitting = true;
        updateCounter();

        try {
            if (uploadUrl) {
                if (readyFiles().length > 0) {
                    await uploadReadyFiles();
                }
                await submitForm(submitter);
                return;
            }

            if (readyFiles().length > 0 || isPublish) {
                await createListingThenUpload(submitter);
                return;
            }

            await submitForm(submitter);
        } catch (err) {
            alert(err.message || msgSubmitError);
        } finally {
            isSubmitting = false;
            updateCounter();
        }
    };

    input.addEventListener('change', () => {
        const incoming = Array.from(input.files || []);
        input.value = '';

        if (incoming.length === 0 || isSubmitting) {
            return;
        }

        const slots = remainingSlots();
        const batch = incoming.slice(0, slots);
        if (batch.length === 0) {
            return;
        }

        batch.forEach((file) => {
            if (totalCount() >= max) {
                return;
            }

            const itemId = 'q' + String(++queueSeq);
            const previewUrl = URL.createObjectURL(file);
            queue.set(itemId, {
                id: itemId,
                status: 'queued',
                previewUrl,
            });

            const card = createQueueCard(itemId, previewUrl);
            grid.insertBefore(card, addCard);
            processQueueItem(itemId, file);
        });

        updateCounter();
    });

    grid.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-remove-existing]');
        if (!btn) {
            return;
        }

        const id = btn.getAttribute('data-remove-existing');
        if (!id) {
            return;
        }

        const card = btn.closest('.host-image-card-existing');
        if (card) {
            card.classList.add('is-removed');
            card.hidden = true;
        }

        persistedImageCount = Math.max(0, persistedImageCount - 1);

        if (!removeInputs.querySelector('[value="' + id + '"]')) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'remove_image_ids[]';
            hidden.value = id;
            removeInputs.appendChild(hidden);
        }

        updateCounter();
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        event.stopPropagation();
        handleSubmit(event.submitter);
    }, true);

    if (window.__hostFormPendingSubmit) {
        const pending = window.__hostFormPendingSubmit;
        window.__hostFormPendingSubmit = null;
        handleSubmit(pending.submitter);
    }

    updateCounter();
})();
