import path from 'node:path'

export const basePath = (append = '') => {
    return path.join(import.meta.dirname, '..', append);
}
