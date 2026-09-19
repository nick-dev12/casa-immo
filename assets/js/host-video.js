'use strict';

(function (global) {
    const form = document.querySelector('.host-form-simple[data-host-managed="1"]');
    const slot = document.getElementById('hostVideoSlot');
    if (!form || !slot) {
        return;
    }

    const uploadUrl = form.dataset.videoUploadUrl || '';
    const csrfToken = form.querySelector('[name="_token"]')?.value || '';
    const note = document.getElementById('hostVideoNote');

    const messages = {
        uploading: note?.dataset.msgUploading || 'Envoi de la vidéo…',
        uploadError: note?.dataset.msgUploadError || 'Impossible d\'envoyer la vidéo.',
        removeError: note?.dataset.msgRemoveError || 'Impossible de supprimer la vidéo.',
        invalidType: note?.dataset.msgInvalidType || 'Format vidéo non pris en charge.',
        queued: note?.dataset.msgQueued || 'La vidéo sera envoyée lors de l\'enregistrement de l\'annonce.',
    };

    const allowedTypes = ['video/mp4', 'video/webm', 'video/quicktime'];
    let queuedFile = null;
    let isBusy = false;

    const setNote = (text, type) => {
        if (!note) {
            return;
        }
        note.textContent = text || note.dataset.defaultText || '';
        note.classList.toggle('is-error', type === 'error');
        note.classList.toggle('is-busy', type === 'busy');
    };

    if (note && !note.dataset.defaultText) {
        note.dataset.defaultText = note.textContent;
    }

    const resolveUploadUrl = (listingId) => {
        if (uploadUrl) {
            return uploadUrl;
        }
        if (!listingId) {
            return '';
        }
        const base = form.getAttribute('action') || '';
        const root = base.replace(/\/host\/properties(?:\/new|\/?)?$/, '');
        return root + '/host/properties/' + listingId + '/video';
    };

    const renderExisting = (video) => {
        slot.innerHTML = '';

        const card = document.createElement('article');
        card.className = 'host-video-card host-video-card-existing';
        card.id = 'hostVideoExisting';

        const preview = document.createElement('video');
        preview.className = 'host-video-preview';
        preview.controls = true;
        preview.playsInline = true;
        preview.preload = 'metadata';
        preview.src = video.url;

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'host-video-remove';
        removeBtn.id = 'hostVideoRemoveBtn';
        removeBtn.setAttribute('aria-label', 'Retirer la vidéo');
        removeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
        removeBtn.addEventListener('click', removeVideo);

        card.appendChild(preview);
        card.appendChild(removeBtn);
        slot.appendChild(card);
    };

    const renderAdd = () => {
        slot.innerHTML = '';

        const label = document.createElement('label');
        label.className = 'host-video-card host-video-add';
        label.id = 'hostVideoAdd';

        const input = document.createElement('input');
        input.type = 'file';
        input.id = 'hostVideoInput';
        input.className = 'host-video-file-input';
        input.accept = 'video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov';
        input.addEventListener('change', onFileSelected);

        const icon = document.createElement('i');
        icon.className = 'bi bi-camera-video';

        const title = document.createElement('span');
        title.textContent = 'Ajouter une vidéo';

        const hint = document.createElement('small');
        hint.textContent = 'MP4, WebM ou MOV';

        label.appendChild(input);
        label.appendChild(icon);
        label.appendChild(title);
        label.appendChild(hint);
        slot.appendChild(label);
    };

    const validateFile = (file) => {
        if (!file) {
            return false;
        }
        if (file.type && !allowedTypes.includes(file.type)) {
            setNote(messages.invalidType, 'error');
            return false;
        }
        return true;
    };

    const postVideo = async (targetUrl, body) => {
        const response = await fetch(targetUrl, {
            method: 'POST',
            body,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });

        let payload = {};
        try {
            payload = await response.json();
        } catch (err) {
            payload = {};
        }

        if (!response.ok || !payload.success) {
            throw new Error(payload.message || messages.uploadError);
        }

        return payload;
    };

    const uploadFile = async (file, targetUrl) => {
        const body = new FormData();
        body.append('_token', csrfToken);
        body.append('video', file, file.name);
        return postVideo(targetUrl, body);
    };

    const removeVideo = async () => {
        if (isBusy) {
            return;
        }

        if (!uploadUrl) {
            queuedFile = null;
            renderAdd();
            setNote('');
            return;
        }

        isBusy = true;
        setNote(messages.uploading, 'busy');

        try {
            const body = new FormData();
            body.append('_token', csrfToken);
            body.append('remove_video', '1');
            await postVideo(uploadUrl, body);
            queuedFile = null;
            renderAdd();
            setNote('');
        } catch (err) {
            setNote(err.message || messages.removeError, 'error');
        } finally {
            isBusy = false;
        }
    };

    const onFileSelected = async (event) => {
        const input = event.currentTarget;
        const file = input.files?.[0];
        input.value = '';

        if (!file || isBusy) {
            return;
        }

        if (!validateFile(file)) {
            return;
        }

        if (!uploadUrl) {
            queuedFile = file;
            slot.innerHTML = '';

            const card = document.createElement('article');
            card.className = 'host-video-card host-video-card-queued';

            const preview = document.createElement('video');
            preview.className = 'host-video-preview';
            preview.controls = true;
            preview.playsInline = true;
            preview.preload = 'metadata';
            preview.src = URL.createObjectURL(file);

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'host-video-remove';
            removeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
            removeBtn.addEventListener('click', () => {
                URL.revokeObjectURL(preview.src);
                queuedFile = null;
                renderAdd();
                setNote('');
            });

            card.appendChild(preview);
            card.appendChild(removeBtn);
            slot.appendChild(card);
            setNote(messages.queued, 'busy');
            return;
        }

        isBusy = true;
        setNote(messages.uploading, 'busy');

        try {
            const payload = await uploadFile(file, uploadUrl);
            if (payload.video) {
                renderExisting(payload.video);
                setNote('');
            } else {
                renderAdd();
                setNote('');
            }
        } catch (err) {
            setNote(err.message || messages.uploadError, 'error');
            renderAdd();
        } finally {
            isBusy = false;
        }
    };

    global.__hostUploadQueuedVideo = async (targetUrl) => {
        if (!queuedFile || !targetUrl) {
            return;
        }

        isBusy = true;
        setNote(messages.uploading, 'busy');

        try {
            const payload = await uploadFile(queuedFile, targetUrl);
            queuedFile = null;
            if (payload.video) {
                renderExisting(payload.video);
            }
            setNote('');
        } catch (err) {
            setNote(err.message || messages.uploadError, 'error');
            throw err;
        } finally {
            isBusy = false;
        }
    };

    const existingRemoveBtn = document.getElementById('hostVideoRemoveBtn');
    if (existingRemoveBtn) {
        existingRemoveBtn.addEventListener('click', removeVideo);
    }

    const existingInput = document.getElementById('hostVideoInput');
    if (existingInput) {
        existingInput.addEventListener('change', onFileSelected);
    }
})(window);
