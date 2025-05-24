import { defineConfig } from '@vite-pwa/assets-generator/config'

export default defineConfig({
    preset: {
        transparent: {
            sizes: [64, 192, 512]
        },
        maskable: {
            sizes: [512]
        },
        apple: {
            sizes: [180]
        }
    },
    images: ['public/favicon.svg']
})
