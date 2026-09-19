'use strict';

/**
 * Compresse et convertit systématiquement en WebP (même grandes images).
 */
window.HostImageCompress = {
    maxDimension: 1600,
    quality: 0.72,

    /**
     * @param {File} file
     * @returns {Promise<File>}
     */
    toWebp(file) {
        if (!file || !String(file.type || '').startsWith('image/')) {
            return Promise.reject(new Error('Format non supporté'));
        }

        return new Promise((resolve, reject) => {
            const url = URL.createObjectURL(file);
            const img = new Image();

            img.onload = () => {
                URL.revokeObjectURL(url);

                let width = img.naturalWidth || img.width;
                let height = img.naturalHeight || img.height;

                if (!width || !height) {
                    reject(new Error('Dimensions invalides'));
                    return;
                }

                const max = this.maxDimension;
                const scale = Math.min(1, max / width, max / height);
                width = Math.max(1, Math.round(width * scale));
                height = Math.max(1, Math.round(height * scale));

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                if (!ctx) {
                    reject(new Error('Canvas indisponible'));
                    return;
                }

                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(
                    (blob) => {
                        if (!blob) {
                            reject(new Error('Conversion WebP impossible'));
                            return;
                        }

                        resolve(new File(
                            [blob],
                            this.webpName(file.name),
                            { type: 'image/webp', lastModified: Date.now() }
                        ));
                    },
                    'image/webp',
                    this.quality
                );
            };

            img.onerror = () => {
                URL.revokeObjectURL(url);
                reject(new Error('Impossible de lire l\'image'));
            };

            img.src = url;
        });
    },

    /**
     * @param {string} name
     * @returns {string}
     */
    webpName(name) {
        const base = String(name || 'photo').replace(/\.[^.]+$/, '');

        return (base || 'photo') + '.webp';
    },
};
